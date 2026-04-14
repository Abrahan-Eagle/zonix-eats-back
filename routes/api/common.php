<?php

use App\Http\Controllers\Authenticator\AuthController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Location\LocationController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Profiles\AddressController;
use App\Http\Controllers\Profiles\DocumentController;
use App\Http\Controllers\Profiles\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Legacy compat: /api/orders (sin prefijo buyer) — usada por tests y versiones anteriores
    Route::get('/orders', [\App\Http\Controllers\Buyer\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\Buyer\OrderController::class, 'store'])->middleware('throttle:create');

    Route::prefix('onboarding')->group(function () {
        Route::put('/{id}', [AuthController::class, 'update']);
    });

    Route::get('/profile', [ProfileController::class, 'showCurrent']);
    Route::put('/profile', [ProfileController::class, 'updateCurrent']);
    Route::get('/profile/export', [\App\Http\Controllers\Buyer\ExportController::class, 'export']);

    Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/', [ProfileController::class, 'store']);
        Route::post('/delivery-agent', [ProfileController::class, 'createDeliveryAgent']);
        Route::post('/commerce', [ProfileController::class, 'createCommerce']);
        Route::post('/add-commerce', [ProfileController::class, 'addCommerceToProfile']);
        Route::post('/delivery-company', [ProfileController::class, 'createDeliveryCompany']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::post('/{id}', [ProfileController::class, 'update']);
        Route::delete('/{id}', [ProfileController::class, 'destroy']);
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

    Route::prefix('payments')->group(function () {
        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        Route::post('/methods', [PaymentController::class, 'addPaymentMethod']);
        Route::post('/process', [PaymentController::class, 'processPayment']);
        Route::get('/history', [PaymentController::class, 'getTransactionHistory']);
        Route::post('/{transactionId}/refund', [PaymentController::class, 'refundPayment']);
        Route::get('/statistics', [PaymentController::class, 'getPaymentStatistics']);
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
    });

    Route::prefix('location')->group(function () {
        Route::post('/update', [LocationController::class, 'updateLocation']);
        Route::get('/nearby-places', [LocationController::class, 'getNearbyPlaces']);
        Route::get('/delivery-routes', [LocationController::class, 'getDeliveryRoutes']);
        Route::post('/calculate-route', [LocationController::class, 'calculateRoute']);
        Route::post('/geocode', [LocationController::class, 'getCoordinatesFromAddress']);
        Route::get('/delivery-zones', [LocationController::class, 'getDeliveryZones']);
    });

    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'getConversations']);
        Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
        Route::post('/conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
        Route::post('/conversations/{conversationId}/read', [ChatController::class, 'markMessagesAsRead']);
        Route::post('/conversations', [ChatController::class, 'createConversation']);
        Route::delete('/conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
        Route::get('/search', [ChatController::class, 'searchMessages']);
        Route::post('/block', [ChatController::class, 'blockUser']);
        Route::delete('/block/{userId}', [ChatController::class, 'unblockUser']);
        Route::get('/blocked-users', [ChatController::class, 'getBlockedUsers']);
        Route::post('/fcm/register', [ChatController::class, 'registerFcmToken']);
        Route::post('/fcm/unregister', [ChatController::class, 'unregisterFcmToken']);
    });
});

if (app()->environment(['local', 'testing'])) {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/test/products', function () {
            $products = \App\Models\Product::where('available', true)->get();

            return response()->json($products);
        });
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
