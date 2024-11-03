<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'];

if (!isset($_FILES['photo'])) {
    echo json_encode(['error' => 'No file part']);
    http_response_code(400);
    exit;
}

$file = $_FILES['photo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No selected file']);
    http_response_code(400);
    exit;
}

// Convert user image to base64
$userImageBase64 = base64_encode(file_get_contents($file['tmp_name']));

// Paths to the reference images
$referenceImagePaths = [
    'images/6714fd433c8e5.png',
    'images/60ba23cb24a44.png'
];

// Convert reference images to base64
$referenceImagesBase64 = [];
foreach ($referenceImagePaths as $path) {
    $referenceImagesBase64[] = base64_encode(file_get_contents($path));
}

// Create the request to OpenAI API
$client = new Client();
$response = $client->post('https://api.openai.com/v1/chat/completions', [
    'headers' => [
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ],
    'json' => [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'These images are taken by people at a specific location using their mobile phone. The first two images are reference images. Your task is to screen the third image to make sure that it does not have any people in the foreground (so no selfies) and that the composition of the third image is the same as the reference images. Please answer with a score of likelihood from 0 to 100 and provide an explanation for your score. Return the response in JSON format with \'score\' and \'explanation\' as keys.'],
            ['role' => 'user', 'content' => 'data:image/png;base64,' . $referenceImagesBase64[0]],
            ['role' => 'user', 'content' => 'data:image/png;base64,' . $referenceImagesBase64[1]],
            ['role' => 'user', 'content' => 'data:image/png;base64,' . $userImageBase64],
        ],
        'max_tokens' => 300,
    ],
]);

if ($response->getStatusCode() === 200) {
    $result = json_decode($response->getBody(), true);
    $choice = $result['choices'][0]['message']['content'] ?? null;

    if ($choice) {
        $content = trim($choice);
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}') + 1;
        $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart);

        $result = json_decode($jsonContent, true);
        $score = $result['score'] ?? null;
        $explanation = $result['explanation'] ?? null;

        echo json_encode(['score' => $score, 'text' => $explanation]);
    } else {
        error_log('Unexpected response structure: message or content not found');
        echo json_encode(['score' => null, 'text' => 'Error: Unexpected response structure']);
    }
} else {
    error_log('Error calling OpenAI API: ' . $response->getBody());
    echo json_encode(['score' => null, 'text' => 'Error: Failed to call OpenAI API']);
}