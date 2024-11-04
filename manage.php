<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Manage Timelapse</title>
	<link rel="stylesheet" href="style.css">
</head>

<?php // get the location of the video file based on the id in the url parameter
if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$video = 'timelapse' . $id . '.mp4';
} else {
	$video = 'timelapse.mp4';
} 

// todo: add code to upload a reference image
// todo: save the reference image to the database along with the location

?>

<body>
	<h1>Set Timelapse reference image</h1>
	<form action="upload.php" method="post" enctype="multipart/form-data">
		<select name="location" id="location">
			<option value="1">Location 1</option>
			<option value="2">Location 2</option>
			<option value="3">Location 3</option>
		</select>
		<input type="file" name="file" id="file">
		<input type="submit" value="Upload Image" name="submit">
	</form>
</body>
</html>