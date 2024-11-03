<?php

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
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$data = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => 'Analyze these images.'],
        ['role' => 'user', 'content' => $userImageBase64],
        ['role' => 'user', 'content' => implode(',', $referenceImagesBase64)]
    ]
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
    http_response_code(500);
    exit;
}

curl_close($ch);

echo $response;
?>