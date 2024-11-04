<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Submission</title>
    <style>
        img {
            width: 100%;
            height: auto;
        }
        .images {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 10px;
        }
        #userImage {
            grid-column: span 2;
        }
    </style>
</head>
<body>
    <h1>Submit a Photo</h1>
    <form id="photoForm" enctype="multipart/form-data" action="upload.php" method="POST">
        <input type="file" accept="image/*" capture="environment" id="photoInput" name="photo" required>
        <button type="submit" id="submitButton">Submit</button>
    </form>

    <h2 id="score"></h2>

    <p id="explanation"></p>

    <div class="images">
        <div id="userImage"></div>
        <div id="referenceImages"></div>
    </div>

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

                // Display score and explanation
                document.getElementById('score').innerText = 'Score: ' + parsedContent.score;
                document.getElementById('explanation').innerText = 'Explanation: ' + parsedContent.explanation;

                // Display user_image
                const userImage = document.createElement('img');
                userImage.src = result.images.user_image;
                document.getElementById('userImage').appendChild(userImage);

                // Display reference_images
                result.images.reference_images.forEach(imageData => {
                    const referenceImage = document.createElement('img');
                    referenceImage.src = imageData;
                    document.getElementById('referenceImages').appendChild(referenceImage);
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