<?php

use App\Http\Controllers\Delivery\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:delivery_agent,delivery'])->prefix('delivery')->group(function () {
    Route::get('/orders', [DeliveryController::class, 'index']);
    Route::patch('/orders/{id}/status', [DeliveryController::class, 'updateStatus']);
    Route::get('/orders/{id}', [DeliveryController::class, 'show']);

    Route::get('/me', [DeliveryController::class, 'me']);
    Route::get('/status', [DeliveryController::class, 'getStatus']);
    Route::patch('/working', [DeliveryController::class, 'updateWorking']);
    Route::get('/available-orders', [DeliveryController::class, 'getAvailableOrders']);
    Route::get('/assigned-orders/{deliveryAgentId}', [DeliveryController::class, 'getAssignedOrders']);
    Route::post('/orders/{orderId}/accept', [DeliveryController::class, 'acceptOrder']);
    Route::post('/orders/{orderId}/reject', [DeliveryController::class, 'rejectOrder']);
    Route::post('/orders/{orderId}/scan-pickup', [DeliveryController::class, 'scanPickup']);
    Route::post('/orders/{orderId}/arrived', [DeliveryController::class, 'arrived']);
    Route::post('/orders/{orderId}/scan-delivery', [DeliveryController::class, 'scanDelivery']);
    Route::post('/location/update', [DeliveryController::class, 'updateLocation']);
    Route::get('/statistics/{deliveryAgentId}', [DeliveryController::class, 'getStatistics']);
    Route::post('/orders/{orderId}/report-issue', [DeliveryController::class, 'reportIssue']);
    Route::get('/history/{deliveryAgentId}', [DeliveryController::class, 'getHistory']);
    Route::get('/earnings/{deliveryAgentId}', [DeliveryController::class, 'getEarnings']);
    Route::get('/routes/{deliveryAgentId}', [DeliveryController::class, 'getRoutes']);
});
