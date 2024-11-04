<?php

$apiKey = $_ENV['OPENAI_API_KEY'];

if (!isset($_FILES['photo'])) {
	echo json_encode(['error' => 'No file part']);
	http_response_code(400);
	exit;
}

// Check if the file was uploaded without errors
if ($_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = $_FILES['photo']['name'];
    $fileSize = $_FILES['photo']['size'];
    $fileType = $_FILES['photo']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Sanitize file name
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    // Check if the file type is allowed
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Directory in which the uploaded file will be moved
        $uploadFileDir = sys_get_temp_dir() . '/';
        
        // Ensure the directory exists
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            echo 'File is successfully uploaded.';
        } else {
            echo 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
        }
    } else {
        echo 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
    }
} else {
    echo 'There was some error uploading the file. Error code: ' . $_FILES['photo']['error'];
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
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($file);
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
		case IMAGETYPE_GIF:
			imagegif($image_p, $temp_file);
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
	$resizedReferenceImage = resizeImage($path, 400, 400); // Adjust max width and height as needed
	$referenceImagesBase64[] = base64_encode(file_get_contents($resizedReferenceImage));
}

// Combine reference images into one string
$combinedReferenceImagesBase64 = implode(',', $referenceImagesBase64);

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
		[
			'role' => 'system',
			'content' => 'You are a helpful assistant, and you only reply with JSON.'
		],
		[
			'role' => 'user',
			'content' => [
				[
					'type' => 'text',
					'text' => 'These three images are taken by people at a specific location using their mobile phone. The first two images are reference images. Your task is to screen the third image to make sure that it does not have any people in the foreground (so no selfies) and that the composition of the third image is the same as the reference images. Please answer with a score of likelihood from 0 to 100 and provide an explanation for your score. Return the response in JSON format with \'score\' and \'explanation\' as keys.'
				],
				[
					'type' => 'image_url',
					'image_url' => [
						'url' => "data:image/png;base64,{$referenceImagesBase64[0]}"
					]
				],
				[
						'type' => 'image_url',
						'image_url' => [
							'url' => "data:image/png;base64,{$referenceImagesBase64[1]}"
						]
					],
				[
					'type' => 'image_url',
					'image_url' => [
						'url' => "data:image/png;base64,{$userImageBase64}"
					]
				]
			]
		]
	],
	'response_format' => [
		"type" => 'json_object',
	],
	'max_tokens' => 1000
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if (curl_errno($ch)) {
	echo json_encode(['error' => curl_error($ch)]);
	http_response_code(500);
	exit;
}

// Decode the response to add images
$responseData = json_decode($response, true);
$responseData['images'] = [
	'user_image' => "data:image/png;base64,{$userImageBase64}",
	'reference_images' => [
		"data:image/png;base64,{$referenceImagesBase64[0]}",
		"data:image/png;base64,{$referenceImagesBase64[1]}"
	]
];

// Encode the response back to JSON and echo it
echo json_encode($responseData);
?>