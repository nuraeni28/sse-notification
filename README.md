## Running Ngrok
Ngrok is used to access websites using https.<br/>
Run ngrok according to the OS you have : https://ngrok.com/docs/getting-started/ 

## Configured The VAPID (Voluntary Application Server Identification for Web Push)

1. Open the `NotificationController.php` file.

2. Replace the VAPID configuration with the new one. You can generate a new VAPID key using [VAPID Generator](https://tools.reactpwa.com/vapid).

3. Enter the new VAPID configuration as in the example below:

   ```php
   $webPush = new WebPush([
       'VAPID' => [
           'subject' => 'mailto:your-email@example.com',
           'publicKey' => 'YOUR_NEW_PUBLIC_KEY',
           'privateKey' => 'YOUR_NEW_PRIVATE_KEY',
       ],
   ]);




