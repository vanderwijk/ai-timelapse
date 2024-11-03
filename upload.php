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

// Function to resize image
function resizeImage($file, $max_width, $max_height) {
    list($orig_width, $orig_height, $image_type) = getimagesize($file);
    $width = $orig_width;
    $height = $orig_height;

    // Calculate new dimensions
    if ($width > $max_width || $height > $max_height) {
        $ratio = $width / $height;
        if ($max_width / $max_height > $ratio) {
            $max_width = $max_height * $ratio;
        } else {
            $max_height = $max_width / $ratio;
        }
        $width = $max_width;
        $height = $max_height;
    }

    // Resample the image
    $image_p = imagecreatetruecolor($width, $height);
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file);
            break;
        default:
            throw new Exception('Unsupported image type');
    }
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

    // Save the resized image to a temporary file
    $temp_file = tempnam(sys_get_temp_dir(), 'resized_');
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image_p, $temp_file, 75); // Adjust quality as needed
            break;
        case IMAGETYPE_PNG:
            imagepng($image_p, $temp_file);
            break;
    }

    return $temp_file;
}

// Resize user image
$resizedUserImage = resizeImage($file['tmp_name'], 400, 400); // Adjust max width and height as needed
$userImageBase64 = base64_encode(file_get_contents($resizedUserImage));

// Paths to the reference images
$referenceImagePaths = [
    'images/6714fd433c8e5.png',
    'images/60ba23cb24a44.png'
];

// Convert reference images to base64
$referenceImagesBase64 = [];
foreach ($referenceImagePaths as $path) {
    $resizedReferenceImage = resizeImage($path, 800, 800); // Adjust max width and height as needed
    $referenceImagesBase64[] = base64_encode(file_get_contents($resizedReferenceImage));
}

$responses = [];
foreach ($referenceImagesBase64 as $referenceImageBase64) {
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
            ['role' => 'user', 'content' => $referenceImageBase64]
        ],
        'max_tokens' => 100 // Adjust this value as needed to limit the output length
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
        http_response_code(500);
        exit;
    }

    $responses[] = $response;
    curl_close($ch);
}

echo json_encode($responses);
?>