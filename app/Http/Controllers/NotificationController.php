<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    
            while (true) {
                if (Cache::has('notification')) {
                    $notification = Cache::get('notification');
                    Log::info('Notification found:', $notification);
                    // dd($notification);
                    // Periksa apakah pesan null atau kosong
                    if (!empty($notification['message'])) {
                        // Periksa apakah pesan sama dengan pesan terakhir
                        if ($lastNotification && $notification['message'] === $lastNotification) {
                            Log::info('Same notification found. Stopping StreamedResponse.');
                            Cache::forget('notification');
                            break;
                        }
                        $lastNotification = $notification['message'];
                        echo "data: " . json_encode($notification) . "\n\n";
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
    
    
    
    // public function subscribe(Request $request)
    // {
    //     $notification = [
    //         'message' => 'You have a new notification!',
    //         'time' => now()->toDateTimeString()
    //     ];
    //     Cache::put('notification', $notification, now()->addMinutes(5));
    //     // dd($notification);
    //     Log::info('Notification stored:', $notification); // Pastikan notifikasi disimpan dengan benar
    //     return response()->json(['status' => 'Notification sent']);
    // }
    public function showNotificationForm()
    {
        return view('send_notification');
    }

    public function sendNotification(Request $request)
    {
        $message = $request->input('message');

        // Simpan notifikasi ke dalam cache atau database
        Cache::put('notification', [
            'message' => $message,
            'time' => now()->toDateTimeString()
        ], now()->addMinutes(5));

        return response()->json(['message' => 'Notification sent successfully']);
    }
}

