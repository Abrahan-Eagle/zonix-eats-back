<?php

use App\Http\Controllers\Authenticator\AuthController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Profiles\AddressController;
use App\Http\Controllers\Profiles\DocumentController;
use App\Http\Controllers\Profiles\PhoneController;
use App\Http\Controllers\Profiles\ProfileController;
use App\Http\Controllers\UserAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('onboarding')->group(function () {
        Route::put('/{id}', [AuthController::class, 'update']);
    });

    Route::get('/profile', [ProfileController::class, 'showCurrent']);
    Route::put('/profile', [ProfileController::class, 'updateCurrent']);
    Route::get('/profile/export', [UserAccountController::class, 'export']);

    Route::prefix('user')->group(function () {
        Route::get('/privacy-settings', [UserAccountController::class, 'getPrivacySettings']);
        Route::put('/privacy-settings', [UserAccountController::class, 'updatePrivacySettings']);
        Route::delete('/account', [UserAccountController::class, 'deleteAccount']);
    });

    Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/', [ProfileController::class, 'store']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::post('/{id}', [ProfileController::class, 'update']);
        Route::delete('/{id}', [ProfileController::class, 'destroy']);
    });

    Route::prefix('phones')->group(function () {
        Route::get('/', [PhoneController::class, 'index']);
        Route::post('/', [PhoneController::class, 'store']);
        Route::get('/{id}', [PhoneController::class, 'show']);
        Route::put('/{id}', [PhoneController::class, 'update']);
        Route::delete('/{id}', [PhoneController::class, 'destroy']);
    });

    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/{id}', [DocumentController::class, 'show']);
        Route::put('/{id}', [DocumentController::class, 'update']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });

    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::post('/getCountries', [AddressController::class, 'getCountries']);
        Route::post('/get-states-by-country', [AddressController::class, 'getState']);
        Route::post('/get-cities-by-state', [AddressController::class, 'getCity']);
    });

    Route::get('/cities/{id}', [AddressController::class, 'getCityById']);

    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [\App\Http\Controllers\PaymentMethodController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\PaymentMethodController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\PaymentMethodController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\PaymentMethodController::class, 'destroy']);
        Route::patch('/{id}/default', [\App\Http\Controllers\PaymentMethodController::class, 'setDefault']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getNotifications']);
        Route::get('/stats', [NotificationController::class, 'getStats']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/', [NotificationController::class, 'store']);
        Route::delete('/{notificationId}', [NotificationController::class, 'delete']);
        Route::post('/push', [NotificationController::class, 'sendPushNotification']);
        Route::get('/settings', [NotificationController::class, 'getNotificationSettings']);
        Route::put('/settings', [NotificationController::class, 'updateNotificationSettings']);
        Route::post('/fcm/register', [NotificationController::class, 'registerFcmToken']);
        Route::post('/fcm/unregister', [NotificationController::class, 'unregisterFcmToken']);
    });
});

if (app()->environment(['local', 'testing'])) {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/test/auth', function () {
            $user = \Illuminate\Support\Facades\Auth::user();

            return response()->json([
                'authenticated' => true,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_email' => $user->email,
                'token_valid' => true,
            ]);
        });
    });
}
