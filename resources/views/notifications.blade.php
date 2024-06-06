<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel SSE Notifications</title>
</head>

<body>
    <h1>Notifications</h1>
    <ul id="notifications"></ul>
    <button id="subscribe">Subscribe</button>
    <h1>Device Details</h1>
    <ul id="device-details"></ul>

    <script>
         document.addEventListener('DOMContentLoaded', async () => {
                if ('Notification' in window) {
                    // Periksa status izin notifikasi
                    const currentPermission = Notification.permission;
                    if (currentPermission === 'granted') {
                        console.log('Notification permission already granted.');
                        
                        startEventSource();
                    }  else {
                        console.log('Notification permission denied.');
                        // Menutup koneksi EventSource jika izin ditolak
                        stopEventSource();
                    }
                } else {
                    alert('Your browser does not support notifications.');
                }
            });



        async function requestPermission() {
            if ('Notification' in window) {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                    // Melakukan langganan push notification
                    subscribeToPushNotifications();
                    setTimeout(startEventSource, 5000); 
                    
                } else {
                    console.log('Notification permission denied.');
                    // Menutup koneksi EventSource jika izin ditolak
                    stopEventSource();
                }
            } else {
                alert('Your browser does not support notifications.');
            }
        }

        function startEventSource() {
            let lastNotification = null; // Variabel untuk menyimpan pesan terakhir

            const eventSource = new EventSource('/notifications');
            eventSource.onmessage = function(event) {
                const notification = JSON.parse(event.data);
                
                // Periksa apakah pesan sama dengan pesan terakhir
                // if (lastNotification && notification.message === lastNotification) {
                //     console.log('Same notification received. Stopping EventSource.');
                //     eventSource.close(); // Menutup koneksi EventSource
                //     return;
                // }

                // Update pesan terakhir
                lastNotification = notification.message;

                // Menambahkan notifikasi ke daftar
                const li = document.createElement('li');
                li.textContent = `${notification.message} at ${notification.time}`;
                document.getElementById('notifications').appendChild(li);

                // Menampilkan notifikasi di browser jika izin diberikan
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

        // Menangani klik tombol Subscribe
        document.getElementById('subscribe').addEventListener('click', function() {
            // Meminta izin notifikasi saat tombol Subscribe diklik
            requestPermission();
        });

        function getDeviceDetails() {
            const details = [
                { name: 'User Agent', value: navigator.userAgent },
            ];

            const ul = document.getElementById('device-details');
            ul.innerHTML = '';
            details.forEach(detail => {
                const li = document.createElement('li');
                li.textContent = `${detail.name}: ${detail.value}`;
                ul.appendChild(li);
            });
        }

        // Load device details when the page loads
        document.addEventListener('DOMContentLoaded', getDeviceDetails);

        // Fungsi untuk melakukan langganan push notification
        function subscribeToPushNotifications() {
            if ('serviceWorker' in navigator) {
                // const userAgent = navigator.userAgent;
                navigator.serviceWorker.ready.then(async (swRegistration) => {
                    // Mendaftarkan service worker
                    const subscription = await swRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: "BLMnf15iZSxbmXB1AGOzighScnRn37-34767SAJ5_OcmiPr681Z4Y_3llV5i3Hfg9tMrXyhqY36ngdQ5aNQWmew"
                    });
                    // Mengirim data langganan ke server
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
                }).catch((err) => {
                    console.error('Error during service worker registration:', err);
                });
            } else {
                alert('Service workers are not supported in this browser.');
            }
        }
    </script>


</body>

</html>
