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


Route::post('/whatsapp/webhook', [WhatsAppController::class, 'handleWebhook']);
