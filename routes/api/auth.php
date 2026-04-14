<?php

use App\Http\Controllers\Authenticator\AuthController;
use App\Http\Controllers\BroadcastingController;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', [BroadcastingController::class, 'authenticate'])->middleware('auth:sanctum');

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/google', [AuthController::class, 'googleUser']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'getUser']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
    });
});
