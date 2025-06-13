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



Route::prefix('auth')->group(function () {
    Route::post('/google', [AuthController::class, 'googleUser']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUser']);
});

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('onboarding')->group(function () {
        Route::put('/{id}', [AuthController::class, 'update']);
    });

    // Perfil
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Buyer
    Route::middleware('role:buyer')->prefix('buyer')->group(function () {
        Route::get('/restaurants', [Buyer\RestaurantController::class, 'index']);
        Route::get('/restaurants/{id}', [Buyer\RestaurantController::class, 'show']);
        Route::post('/cart/add', [Buyer\CartController::class, 'add']);
        Route::get('/cart', [Buyer\CartController::class, 'show']);
        Route::post('/orders', [Buyer\OrderController::class, 'store']);
        Route::get('/orders', [Buyer\OrderController::class, 'index']);
    });

    // Commerce
    Route::middleware('role:commerce')->prefix('commerce')->group(function () {
        Route::get('/dashboard', [Commerce\DashboardController::class, 'index']);
        Route::resource('/products', Commerce\ProductController::class);
        Route::get('/orders', [Commerce\OrderController::class, 'index']);
        Route::put('/orders/{id}/status', [Commerce\OrderController::class, 'updateStatus']);
        Route::post('/delivery/request', [Commerce\DeliveryRequestController::class, 'store']);
    });

    // Delivery
    Route::middleware('role:delivery')->prefix('delivery')->group(function () {
        Route::get('/orders', [Delivery\OrderController::class, 'index']);
        Route::put('/orders/{id}/accept', [Delivery\OrderController::class, 'accept']);
        Route::put('/orders/{id}/deliver', [Delivery\OrderController::class, 'deliver']);
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [Admin\UserController::class, 'index']);
        Route::put('/users/{id}/role', [Admin\UserController::class, 'updateRole']);
        Route::get('/reports', [Admin\ReportController::class, 'index']);
    });
});
















// // Ruta pública para pruebas
// Route::get('/ping', fn() => response()->json(['message' => 'API funcionando']));



// Route::get('/env-test', function () {
//     dd(env('APP_NAME'), env('DB_DATABASE'), env('APP_DEBUG'));
// });


// Route::get('/migrate-refresh', function () {
//     Artisan::call('migrate:refresh', ['--seed' => true]);
//     return 'Database migration refreshed and seeded successfully!';
// });



// Route::prefix('auth')->group(function () {
//     Route::post('/google', [AuthController::class, 'googleUser']);
//     Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
//     Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUser']);
// });

// Route::post('/orders', [OrderController::class, 'store'])->middleware(['auth:sanctum', 'role:comprador', 'commerce.open']);

// Route::middleware('auth:sanctum')->group(function () {

//     Route::prefix('onboarding')->group(function () {
//         Route::put('/{id}', [AuthController::class, 'update']);
//     });

//     Route::prefix('profiles')->group(function () {
//         Route::get('/', [ProfileController::class, 'index']);
//         Route::post('/', [ProfileController::class, 'store']);
//         Route::get('/{id}', [ProfileController::class, 'show']);
//         Route::post('/{id}', [ProfileController::class, 'update']);
//         Route::delete('/{id}', [ProfileController::class, 'destroy']);
//     });


//     // En routes/api.php
// Route::prefix('commerce')->group(function () {
    
//     // Productos del comercio
//     Route::get('/products', [ProductController::class, 'index']);
//     Route::post('/products', [ProductController::class, 'store']);
//     Route::get('/products/{id}', [ProductController::class, 'show']);
//     Route::put('/products/{id}', [ProductController::class, 'update']);
//     Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
//     // Nuevas funcionalidades
//     Route::put('/products/{id}/toggle-disponible', [ProductController::class, 'toggleDisponible']);
//     Route::get('/products-stats', [ProductController::class, 'estadisticas']);
    
// });



//      /**
//      * Buyer
//      */
//     Route::prefix('buyer')->group(function () {
//         Route::get('/orders', [OrderController::class, 'orders']);
//         Route::post('/orders', [OrderController::class, 'placeOrder']);
//     });

//     /**
//      * Commerce (Dueño del restaurante)
//      */
//     Route::prefix('commerce')->group(function () {
//         Route::get('/products', [ProductController::class, 'products']);
//         Route::post('/products', [ProductController::class, 'storeProduct']);
//         Route::get('/orders', [CommerceOrderController::class, 'orders']);
//         Route::post('/orders/{id}/status', [CommerceOrderController::class, 'updateOrderStatus']);
//     });

//     /**
//      * Delivery
//      */
//     Route::prefix('delivery')->group(function () {
//         Route::get('/available-orders', [OrderController::class, 'availableOrders']);
//         Route::post('/orders/{id}/accept', [OrderController::class, 'acceptOrder']);
//         Route::post('/orders/{id}/deliver', [OrderController::class, 'deliverOrder']);
//     });

//     /**
//      * Admin
//      */
//     Route::prefix('admin')->group(function () {
//         // Usuarios
//         Route::get('/users', [UserController::class, 'index']);
//         Route::get('/users/{id}', [UserController::class, 'show']);
//         Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
//         Route::delete('/users/{id}', [UserController::class, 'destroy']);

//         // Comercios
//         Route::get('/commerces', [CommerceController::class, 'index']);
//         Route::put('/commerces/{id}/status', [CommerceController::class, 'updateStatus']);
//     });


// });
