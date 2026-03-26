<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profiles\PhoneController;
use App\Http\Controllers\WebSocket\WebSocketController;

Route::get('/operator-codes', [PhoneController::class, 'getOperatorCodes']);

Route::prefix('websocket')->group(function () {
    Route::post('/connect', [WebSocketController::class, 'connect']);
    Route::post('/disconnect', [WebSocketController::class, 'disconnect']);
    Route::post('/subscribe', [WebSocketController::class, 'subscribe']);
    Route::post('/unsubscribe', [WebSocketController::class, 'unsubscribe']);
    Route::post('/auth', [WebSocketController::class, 'authenticate']);
});

Route::get('/available-payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'getAvailableMethods']);

Route::get('/banks', [\App\Http\Controllers\BankController::class, 'index']);

Route::get('/ping', fn() => response()->json(['message' => 'API funcionando']));
