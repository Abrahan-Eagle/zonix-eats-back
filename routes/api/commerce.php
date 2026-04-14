<?php

use App\Http\Controllers\Commerce\DashboardController;
use App\Http\Controllers\Commerce\OrderController as CommerceOrderController;
use App\Http\Controllers\Commerce\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:commerce'])->prefix('commerce')->group(function () {
    Route::get('/commerces', [\App\Http\Controllers\Commerce\CommerceListController::class, 'index']);
    Route::post('/commerces', [\App\Http\Controllers\Commerce\CommerceListController::class, 'store']);
    Route::get('/posts', [\App\Http\Controllers\Commerce\CommercePostController::class, 'index']);
    Route::put('/commerces/{commerce}/set-primary', [\App\Http\Controllers\Commerce\CommerceListController::class, 'setPrimary']);
    Route::get('/', [\App\Http\Controllers\Commerce\CommerceDataController::class, 'show']);
    Route::put('/', [\App\Http\Controllers\Commerce\CommerceDataController::class, 'update']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/logo', [\App\Http\Controllers\Commerce\CommerceDataController::class, 'uploadLogo']);
    Route::put('/products/{id}/toggle-disponible', [ProductController::class, 'toggleDisponible']);
    Route::resource('/products', ProductController::class);
    Route::get('/orders', [CommerceOrderController::class, 'index']);
    Route::get('/orders/{order}', [CommerceOrderController::class, 'show']);
    Route::put('/orders/{order}/status', [CommerceOrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/validate-payment', [CommerceOrderController::class, 'validatePayment']);
    Route::post('/orders/{id}/approve-for-payment', [CommerceOrderController::class, 'approveForPayment']);
    Route::post('/orders/{id}/reject', [CommerceOrderController::class, 'rejectOrder']);
    Route::get('/orders/{id}/pickup-qr', [CommerceOrderController::class, 'pickupQr']);
    Route::post('orders/{id}/validar-comprobante', [\App\Http\Controllers\Commerce\OrderController::class, 'validarComprobante']);
    Route::put('promotions/{id}/toggle', [\App\Http\Controllers\Commerce\CommercePromotionController::class, 'toggle']);
    Route::apiResource('promotions', \App\Http\Controllers\Commerce\CommercePromotionController::class);

    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Commerce\AnalyticsController::class, 'getOverview']);
        Route::get('/revenue', [\App\Http\Controllers\Commerce\AnalyticsController::class, 'getRevenue']);
        Route::get('/orders', [\App\Http\Controllers\Commerce\AnalyticsController::class, 'getOrders']);
        Route::get('/products', [\App\Http\Controllers\Commerce\AnalyticsController::class, 'getProducts']);
        Route::get('/customers', [\App\Http\Controllers\Commerce\AnalyticsController::class, 'getCustomers']);
        Route::get('/performance', [\App\Http\Controllers\Commerce\AnalyticsController::class, 'getPerformance']);
    });
});
