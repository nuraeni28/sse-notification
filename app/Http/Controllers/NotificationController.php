<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription as WebPushSubscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Minishlink\WebPush\WebPush;

class NotificationController extends Controller
{
    public function __construct()
    {
        set_time_limit(0); // No time limit
    }

    public function notifications()
    {
        $response = new StreamedResponse(function () {
            Log::info('Starting StreamedResponse loop.');

            $lastNotification = null;
            $time = null;

            while (true) {
                if (Cache::has('notification')) {
                    $notification = Cache::get('notification');
                    Log::info('Notification found:', $notification);

                    // check message null or not
                    if (!empty($notification['message'])) {
                        // check if the message is the same as the last message
                        if (($lastNotification && $notification['message'] === $lastNotification) || ($time && $notification['time'] === $time)) {
                            Log::info('Same notification found. Stopping StreamedResponse.');
                            // Cache::forget('notification');
                            break;
                        }

                        $time = $notification['time'];
                        $lastNotification = $notification['message'];
                        echo 'data: ' . json_encode($notification) . "\n\n";
                        ob_flush();
                        flush();
                    } else {
                        Log::info('Empty notification message. Skipping.');
                        break;
                    }
                } else {
                    Log::info('No notification found.');
                    break;
                }
                sleep(5);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    public function sendNotification(Request $request)
    {
        $message = $request->input('message');
        $currentTime = now()->toDateTimeString(); //get time now

        Cache::forget('notification'); // remove old notification from cache

        // save notification to cache
        Cache::put(
            'notification',
            [
                'message' => $message,
                'time' => $currentTime,
            ],
            now()->addMinutes(5), // expired cache
        );
        $this->sendWebPushNotification($message);

        return response()->json(['message' => 'Notification sent successfully']);
    }

    public function showNotificationForm()
    {
        return view('send_notification');
    }
    private function sendWebPushNotification($message)
    {
        // web push initialization with VAPID keys
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'mailto:nuraeniexecutive18@gmail.com',
                'publicKey' => 'BEDiM-FMR3437Pq1dG-IO8cvG0OaIp9ijJN38KOsZ58LJtByiOXiE-jzZ_YN6wF6jMeC_Ny6aucsdqt1HhDLSiU',
                'privateKey' => 'OAW8vIPz7055qyIGQpyIeAnG1phHaD7iSG7R0g0Cpdk',
            ],
        ]);

        // get all data subscriptions from database
        $subscriptions = PushSubscription::all();
        foreach ($subscriptions as $sub) {
            // take the JSON subscription data and decode it into an array
            $subscriptionData = json_decode($sub->subscription, true);
            log::info($subscriptionData['subscription']['endpoint']);
            if ($subscriptionData) {
                // Make sure the data subscription has a 'subscription' key that contains 'endpoint' and 'keys'
                if (isset($subscriptionData['subscription']['endpoint']) && isset($subscriptionData['subscription']['keys'])) {
                    // creat object WebPushSubscription
                    $subscription = WebPushSubscription::create([
                        'endpoint' => $subscriptionData['subscription']['endpoint'],
                        'keys' => [
                            'p256dh' => $subscriptionData['subscription']['keys']['p256dh'],
                            'auth' => $subscriptionData['subscription']['keys']['auth'],
                        ],
                    ]);

                    // Siapkan payload notifikasi
                    $payload = json_encode([
                        'title' => 'New Notification',
                        'body' => $message,
                        'icon' => 'logo.png', // change with your icon path
                        'data' => [
                            'url' => 'https://www.google.com/', // change with your destination URL
                        ],
                    ]);

                    // add notifications to the queue
                    $webPush->queueNotification($subscription, $payload);
                } else {
                    Log::error('Subscription data missing endpoint or keys: ' . json_encode($subscriptionData));
                }
            } else {
                Log::error('Failed to decode subscription data for: ' . $sub->id);
            }
        }

        // execute all push requests and log the results
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                Log::info("Notification sent successfully to {$endpoint}.");
            } else {
                Log::error("Notification failed to {$endpoint}: {$report->getReason()}");
            }
        }
    }
}
