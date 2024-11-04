<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Timelapse</title>
	<link rel="stylesheet" href="style.css">
</head>

<?php // get the location of the video file based on the id in the url parameter
if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$video = 'timelapse' . $id . '.mp4';
} else {
	$video = 'timelapse.mp4';
} ?>
<body>
	<h1>Timelapse</h1>
	<video controls>
		<source src="<?php echo $video; ?>" type="video/mp4">
		Your browser does not support the video tag.
	</video>
</body>
</html>