<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            box-sizing: border-box;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        input[type="file"] {
            margin-bottom: 10px;
        }
        h2, p {
            color: #333;
        }
        .images {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 10px;
            width: 100%;
        }
        #userImage {
            grid-column: span 2;
        }
        img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submit a Photo</h1>
        <form id="photoForm" enctype="multipart/form-data">
            <input type="file" accept="image/*" capture="environment" id="photoInput" name="photo" required>
        </form>

        <h2 id="score"></h2>

        <p id="explanation"></p>

        <div class="images">
            <div id="userImage"></div>
            <div id="referenceImage1"></div>
            <div id="referenceImage2"></div>
        </div>
    </div>

    <script>
        document.getElementById('photoInput').addEventListener('change', function() {
            document.getElementById('photoForm').dispatchEvent(new Event('submit'));
        });

        document.getElementById('photoForm').addEventListener('submit', async function(event) {
            event.preventDefault();

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
            }
        });
    </script>
</body>
</html>