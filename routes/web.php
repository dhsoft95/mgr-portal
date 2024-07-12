<?php

use App\Http\Controllers\WhatsAppController;
use App\Livewire\CreatePost;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return redirect('/admin');
});
Route::get('/test-chart', function () {
    return view('test');
});

Route::get('posts/create', CreatePost::class);

// Define routes for WhatsApp webhook and message sending
//Route::get('/webhook', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook.get');
//Route::post('/webhook', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook.post');
//Route::post('/send-message', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send.message');
//Route::get('/send-test-message', [WhatsAppController::class, 'testSendMessage']);
//
//Route::get('/test-webhook', [WhatsAppController::class, 'testWebhook']);
//Route::post('/whatsapp/webhook', [WhatsAppController::class, 'handleWebhook']);
Route::post('/whatsapp/webhook', [WhatsAppController::class, 'handleWebhook']);
