<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification</title>
</head>
<body>
    <h1>Send Notification</h1>
    <form id="notificationForm">
        @csrf <!-- Menambahkan CSRF token -->
        <label for="message">Message:</label><br>
        <input type="text" id="message" name="message"><br>
        <button type="submit">Send Notification</button>
    </form>

    <script>
        document.getElementById('notificationForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Hindari perilaku default pengiriman formulir

            const formData = new FormData(this);

            fetch('/send-notification', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message); // Tampilkan pesan respons
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
