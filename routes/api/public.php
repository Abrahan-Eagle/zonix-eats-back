<?php

use App\Http\Controllers\Profiles\PhoneController;
use Illuminate\Support\Facades\Route;

Route::get('/operator-codes', [PhoneController::class, 'getOperatorCodes']);

Route::get('/available-payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'getAvailableMethods']);

Route::get('/banks', [\App\Http\Controllers\BankController::class, 'index']);

Route::get('/ping', fn () => response()->json(['message' => 'API funcionando']));
