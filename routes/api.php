<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/messages', [MessageController::class, 'index']);
Route::post('/messages', [MessageController::class, 'store']);
Route::post('/messages/ai-reply', [MessageController::class, 'aiReply']);

Route::get('/chats', [ChatController::class, 'index']);
Route::middleware('auth.token')->group(function () {
    Route::post('/chats', [ChatController::class, 'store']);
    Route::post('/chats/join', [ChatController::class, 'join']);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth.token')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
