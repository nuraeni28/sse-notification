## Running Ngrok
Ngrok is used to access websites using https.<br/>
Run ngrok according to the OS you have : https://ngrok.com/docs/getting-started/ 

## Configured The VAPID (Voluntary Application Server Identification for Web Push)
VAPID is used to identify the server sending the push notification and ensure that the sender is a legitimate sender. Change VAPID configuration in NotificationController.php
```javascript
'VAPID' => [
    'subject' => 'mailto:nuraeniexecutive18@gmail.com',
    'publicKey' => 'BEDiM-FMR3437Pq1dG-IO8cvG0OaIp9ijJN38KOsZ58LJtByiOXiE-jzZ_YN6wF6jMeC_Ny6aucsdqt1HhDLSiU',
    'privateKey' => 'OAW8vIPz7055qyIGQpyIeAnG1phHaD7iSG7R0g0Cpdk',
],
```
## .env 
TELEGRAM_WEBHOOK_URL= 'WEBHOOK_URL/api/swiftbot/webhook'

## Step Running The Telegram Blasting System

The Telegram Blasting System using Laravel and Library (irazasyed/telegram-bot-sd)

- Run the command on the terminal
```bash
$ php artisan migrate
```
```bash
$ php artisan serve
```

- Run the command on the other terminal
```bash
$ php artisan queue:work
```

## Connect To Telegram Bot
- Make sure the telegram user has a username (ex : @nen_28)
- Add bot (@swift28_bot) and click start to start blasting message. Like the picture below
![Screenshot_2](https://github.com/nuraeni28/telegram-blasting/assets/68740508/1e84aa84-fd3d-4f15-8084-622f6dbd3b88)


## Send Blasting With API 

```http
POST /api/blast-message
```
- Body
Example :
```javascript
[
    {
        "message": "example message with low priority",
        "usernames": ["nen_28"],
        "priority": "low"
    },
    {
        "message": "example message with low priority",
        "usernames": ["taufik27"],
        "priority": "high"
    }
]
```

- Responses
```javascript
{
  "message" : string,
  "success" : bool,
  "data"    : array
}
```


