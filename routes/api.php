<?php

use App\Models\PushSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post("push-subscribe", function(Request $request) {
    $subscriptionData = $request->input('subscription');
    $data = new PushSubscription;
    $data->endpoint =  $subscriptionData['endpoint']; 
    $data->subscription =  $request->getContent();
    $data->useragent = $request->header('User-Agent');
    $data->lastupdated = Carbon::now();
    $data->save();

    return response()->json(['status' => 'Subscription stored']);
});
