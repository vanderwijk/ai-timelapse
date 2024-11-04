<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Photo Submission</title>
	<link rel="stylesheet" href="style.css">
	<script src="script.js" defer></script>
</head>
<body>
	<div class="container">
		<h1>Submit a Photo</h1>

		<form id="photoForm" enctype="multipart/form-data">
			<input type="file" accept="image/*" capture="environment" id="photoInput" name="photo" required>
			<button type="button" id="takePhotoButton">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera">
					<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
					<circle cx="12" cy="13" r="4"></circle>
				</svg>
				Take Photo
			</button>
			<div class="spinner" id="loadingSpinner"></div>
		</form>

		<h2 id="score"></h2>

		<p id="explanation"></p>

		<div class="images">
			<div id="userImage"></div>
			<div id="referenceImage1"></div>
			<div id="referenceImage2"></div>
		</div>
	</div>

</body>
</html>