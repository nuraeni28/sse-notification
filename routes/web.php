<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/notifications', [NotificationController::class, 'notifications']);
// Route::post('/subscribe', [NotificationController::class, 'subscribe']);
Route::get('/send-notification', [NotificationController::class, 'showNotificationForm']);
Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
Route::get('/notifications-view', function () {
      Cache::forget('notification');
    return view('notifications');
});
