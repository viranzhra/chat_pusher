<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController; 

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/chat/user/{user}', [App\Http\Controllers\ChatController::class, 'chat'])->name('chat');
Route::get('/chat/room/{room}', [App\Http\Controllers\ChatController::class, 'room'])->name('chat.room');
Route::get('/chat/get/{room}', [App\Http\Controllers\ChatController::class, 'getChat'])->name('chat.get');
Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendChat'])->name('chat.send');
Route::delete('/chat/delete/{chatId}', [ChatController::class, 'deleteChat'])->name('chat.delete');
Route::delete('/chat/delete-all/{id}', [ChatController::class, 'deleteAll']);
