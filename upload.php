<?php

$apiKey = $_ENV['OPENAI_API_KEY'];

if (!isset($_FILES['photo'])) {
	echo json_encode(['error' => 'No file part']);
	http_response_code(400);
	exit;
}

$file = $_FILES['photo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
	// return full error message
	$uploadErrors = [
		UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
		UPLOAD_ERR_NO_FILE => 'No file was uploaded',
		UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
		UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
	];
	$error = $uploadErrors[$file['error']] ?? 'Unknown error';
	echo json_encode(['error' => $error]);
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
		$width = intval($max_width);
		$height = intval($max_height);
	}

	// Resample the image
	// todo: make black and white to reduce size
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
// todo: make this dynamic based on the location
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

// todo: save the uploaded file if the score is above a certain threshold, use timestamp as filename use DigitalOcean Spaces

// todo: add the image to the database along with the location, score, explanation and timestamp

// todo: add a button to view all submissions

// todo: add approved images to video using ffmpeg

// todo: make a management tool to set the reference images