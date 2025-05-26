<?php

use App\Http\Controllers\Admin\CommerceController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authenticator\AuthController;
use App\Http\Controllers\Commerce\OrderController as CommerceOrderController;
use App\Http\Controllers\Commerce\ProductController;
use App\Http\Controllers\Delivery\OrderController;
use App\Http\Controllers\Profiles\ProfileController;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;



// Ruta pública para pruebas
Route::get('/ping', fn() => response()->json(['message' => 'API funcionando']));



Route::get('/env-test', function () {
    dd(env('APP_NAME'), env('DB_DATABASE'), env('APP_DEBUG'));
});


Route::get('/migrate-refresh', function () {
    Artisan::call('migrate:refresh', ['--seed' => true]);
    return 'Database migration refreshed and seeded successfully!';
});


Route::prefix('auth')->group(function () {
    Route::post('/google', [AuthController::class, 'googleUser']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUser']);
});

Route::post('/orders', [OrderController::class, 'store'])->middleware(['auth:sanctum', 'role:comprador', 'commerce.open']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('onboarding')->group(function () {
        Route::put('/{id}', [AuthController::class, 'update']);
    });

    Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/', [ProfileController::class, 'store']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::post('/{id}', [ProfileController::class, 'update']);
        Route::delete('/{id}', [ProfileController::class, 'destroy']);
    });



     /**
     * Buyer
     */
    Route::prefix('buyer')->group(function () {
        Route::get('/orders', [OrderController::class, 'orders']);
        Route::post('/orders', [OrderController::class, 'placeOrder']);
    });

    /**
     * Commerce (Dueño del restaurante)
     */
    Route::prefix('commerce')->group(function () {
        Route::get('/products', [ProductController::class, 'products']);
        Route::post('/products', [ProductController::class, 'storeProduct']);
        Route::get('/orders', [CommerceOrderController::class, 'orders']);
        Route::post('/orders/{id}/status', [CommerceOrderController::class, 'updateOrderStatus']);
    });

    /**
     * Delivery
     */
    Route::prefix('delivery')->group(function () {
        Route::get('/available-orders', [OrderController::class, 'availableOrders']);
        Route::post('/orders/{id}/accept', [OrderController::class, 'acceptOrder']);
        Route::post('/orders/{id}/deliver', [OrderController::class, 'deliverOrder']);
    });

    /**
     * Admin
     */
    Route::prefix('admin')->group(function () {
        // Usuarios
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Comercios
        Route::get('/commerces', [CommerceController::class, 'index']);
        Route::put('/commerces/{id}/status', [CommerceController::class, 'updateStatus']);
    });


});
