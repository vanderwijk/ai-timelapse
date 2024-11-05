document.getElementById('takePhotoButton').addEventListener('click', function() {
	document.getElementById('photoInput').click();
});

document.getElementById('photoInput').addEventListener('change', function() {
	if (this.files && this.files.length > 0) {
		document.getElementById('photoForm').dispatchEvent(new Event('submit'));
	}
});

document.getElementById('photoForm').addEventListener('submit', async function(event) {
	event.preventDefault();

	// Show the loading spinner
	document.getElementById('loadingSpinner').style.display = 'inline-block';

	// Clear previous content
	document.getElementById('score').innerText = '';
	document.getElementById('explanation').innerText = '';
	document.getElementById('userImage').innerHTML = '';
	document.getElementById('referenceImage1').innerHTML = '';
	document.getElementById('referenceImage2').innerHTML = '';

	const photoInput = document.getElementById('photoInput');
	const formData = new FormData();
	formData.append('photo', photoInput.files[0]);

	try {
		const response = await fetch('upload.php', {
			method: 'POST',
			body: formData
		});

		const result = await response.json();
		const messageContent = result.choices[0].message.content;
		const parsedContent = JSON.parse(messageContent);

		// Display score and explanation
		document.getElementById('score').innerText = 'Score: ' + parsedContent.score;
		document.getElementById('explanation').innerText = 'Explanation: ' + parsedContent.explanation;

		// Display user_image
		const userImage = document.createElement('img');
		userImage.src = result.images.user_image;
		document.getElementById('userImage').appendChild(userImage);

		// Display reference_images
		result.images.reference_images.forEach((imageData, index) => {
			const referenceImage = document.createElement('img');
			referenceImage.src = imageData;
			document.getElementById(`referenceImage${index + 1}`).appendChild(referenceImage);
		});
	} catch (error) {
		console.error('Error:', error);
		alert('An error occurred while processing your request.');
	} finally {
		// Hide the loading spinner
		document.getElementById('loadingSpinner').style.display = 'none';
	}
});