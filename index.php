<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Submission</title>
</head>
<body>
    <h1>Submit a Photo</h1>
    <form id="photoForm" enctype="multipart/form-data" action="upload.php" method="POST">
        <input type="file" accept="image/*" capture="environment" id="photoInput" name="photo" required>
        <button type="submit" id="submitButton">Submit</button>
    </form>

    <script>
        document.getElementById('photoForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true; // Disable the submit button

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

                alert('Score: ' + parsedContent.score + '\nExplanation: ' + parsedContent.explanation);

                // Display user_image
                const userImage = document.createElement('img');
                userImage.src = result.images.user_image;
                document.body.appendChild(userImage);

                // Display reference_images
                result.images.reference_images.forEach(imageData => {
                    const referenceImage = document.createElement('img');
                    referenceImage.src = imageData;
                    document.body.appendChild(referenceImage);
                });
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            } finally {
                submitButton.disabled = false; // Re-enable the submit button
            }
        });
    </script>
</body>
</html>