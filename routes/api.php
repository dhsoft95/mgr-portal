<?php

use App\Http\Controllers\FacebookMessageController;
use App\Http\Controllers\InstagramController;
use App\Http\Controllers\InstagramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/send-message', [FacebookMessageController::class, 'sendMessage']);

Route::post('/instagram/publish', [InstagramController::class, 'publishPost']);
Route::get('/instagram/messages', [InstagramController::class, 'readMessages']);
Route::post('/instagram/send-message', [InstagramController::class, 'sendMessage']);

// Instagram Webhook route
Route::match(['get', 'post'], '/instagram/webhook', [InstagramWebhookController::class, 'handleWebhook']);
