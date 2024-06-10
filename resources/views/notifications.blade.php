<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swift SSE Notifications</title>
    <link crossorigin="use-credentials" rel="manifest" href="/manifest.json" />
</head>

<body>
    <h1>Notifications</h1>
    <ul id="notifications"></ul>
    <button id="subscribe">Subscribe</button>
    <h1>Device Details</h1>
    <ul id="device-details"></ul>

    <script src="{{ secure_asset('/sw.js') }}"></script>
    <script>
        async function requestPermission() {
            if ('Notification' in window) {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    console.log('Notification permission granted oke');
                    // Subscribe to push notifications
                    subscribeToPushNotifications();
                    setTimeout(startEventSource, 5000);

                } else {
                    console.log('Notification permission denied.');
                    // close EventSource if the notification denied
                    stopEventSource();
                }
            } else {
                alert('Your browser does not support notifications.');
            }
        }

        function startEventSource() {
            let lastNotification = null;
            let lastTime = null;

            const eventSource = new EventSource('/notifications');
            eventSource.onmessage = function(event) {
                const notification = JSON.parse(event.data);
                console.log(notification);
                //check if the message is the same as the last message
                if (lastNotification && notification.message === lastNotification) {
                    if (lastTime && notification.time != lastTime) {
                        // Update the last message
                        lastNotification = notification.message;

                    } else {
                        console.log('Same notification received. Stopping EventSource.');
                        notification.message = '';
                        return;
                    }
                }

                // update the last message
                lastNotification = notification.message;
                lastTime = notification.time;

                // add notification to list
                const li = document.createElement('li');
                li.textContent = `${notification.message} at ${notification.time}`;
                document.getElementById('notifications').appendChild(li);

                // display a notification in the browser if permission is granted
                if (Notification.permission === 'granted') {
                    new Notification('New Notification', {
                        body: notification.message,
                    });
                }
            };
        }


        function stopEventSource() {
            const eventSource = new EventSource('/notifications');
            if (eventSource) {
                eventSource.close();
            }
        }

        // handle button subscribe
        document.getElementById('subscribe').addEventListener('click', function() {
            // request notification permission when the Subscribe button is clicked
            requestPermission();
        });

        //get detail device
        function getDeviceDetails() {
            const details = [{
                name: 'User Agent',
                value: navigator.userAgent
            }, ];

            const ul = document.getElementById('device-details');
            ul.innerHTML = '';
            details.forEach(detail => {
                const li = document.createElement('li');
                li.textContent = `${detail.name}: ${detail.value}`;
                ul.appendChild(li);
            });
        }

        // load device details when the page loads
        document.addEventListener('DOMContentLoaded', getDeviceDetails);

        // function to subscribe to push notification
        function subscribeToPushNotifications() {
            if ('serviceWorker' in navigator) {
                // const userAgent = navigator.userAgent;
                $dd = navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                })
                navigator.serviceWorker.ready.then(async (swRegistration) => {
                    // registering service workers
                    const applicationServerKey = urlB64ToUint8Array(
                        'BEDiM-FMR3437Pq1dG-IO8cvG0OaIp9ijJN38KOsZ58LJtByiOXiE-jzZ_YN6wF6jMeC_Ny6aucsdqt1HhDLSiU'
                    ); //change with your public key VAPID
                    const subscription = await swRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: applicationServerKey
                    });
                    // send subscription data to the server
                    await fetch('/api/push-subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            subscription
                        })
                    });
                    alert("Push notification subscribed successfully!");
                    console.log('success');
                }).catch((err) => {
                    console.error('Error during service worker registration:', err);
                });
            } else {
                alert('Service workers are not supported in this browser.');
            }
        }
        // Function to convert VAPID public key from Base64 URL Safe to Uint8Array
        function urlB64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>


</body>

</html>
