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
        <button type="submit">Submit</button>
    </form>

    <script>
        document.getElementById('photoForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const photoInput = document.getElementById('photoInput');
            const formData = new FormData();
            formData.append('photo', photoInput.files[0]);

            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            const messageContent = result.choices[0].message.content;
            const jsonString = messageContent.match(/```json\n([\s\S]*?)\n```/)[1];
            const parsedContent = JSON.parse(jsonString);

            alert('Score: ' + parsedContent.score + '\nExplanation: ' + parsedContent.explanation);
        });
    </script>
</body>
</html>