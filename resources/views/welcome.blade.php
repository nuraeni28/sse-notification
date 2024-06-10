<!DOCTYPE html>
<html>

<head>
    <title>Swift SSE Notifications</title>
</head>

<body>
    <h1>Swift SSE Notifications</h1>
    <script>
        if (window.EventSource) {
            const source = new EventSource('/notifications');

            source.onmessage = function(event) {
                const data = JSON.parse(event.data);
                const message = data.message;

                if (Notification.permission === "granted") {
                    new Notification(message);
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            new Notification(message);
                        }
                    });
                }
            };
        } else {
            console.log("Your browser does not support Server-Sent Events.");
        }
    </script>
</body>

</html>
