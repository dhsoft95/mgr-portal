<?php

use App\Http\Controllers\InstagramAuthController;
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
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');


Route::get('posts/create', CreatePost::class);


Route::post('/whatsapp/webhook', [WhatsAppController::class, 'handleWebhook']);



// Welcome route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard route (you may want to keep this protected by auth middleware)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Instagram auth routes
Route::get('/instagram/auth', [InstagramAuthController::class, 'redirectToInstagram'])->name('instagram.auth');
Route::get('/instagram/callback', [InstagramAuthController::class, 'handleCallback'])->name('instagram.callback');
