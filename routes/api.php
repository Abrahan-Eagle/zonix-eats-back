<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authenticator\AuthController;
use App\Http\Controllers\GasTicket\Admin\DataVerificationController;
use App\Http\Controllers\GasTicket\AdminController;
use App\Http\Controllers\GasTicket\GasCylinderController;
use App\Http\Controllers\GasTicket\GasTicketController;
use App\Http\Controllers\Profiles\PhoneController;
use App\Http\Controllers\Profiles\EmailController;
use App\Http\Controllers\Profiles\AddressController;
use App\Http\Controllers\Profiles\DocumentController;
use App\Http\Controllers\Profiles\NeighborhoodAssociationController;
use App\Http\Controllers\Profiles\ProfileController;
use App\Http\Controllers\GasTicket\Admin\SalesAdminController;
use Illuminate\Support\Facades\Artisan;

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

    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/{id}', [DocumentController::class, 'show']);
        Route::put('/{id}', [DocumentController::class, 'update']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });

    Route::prefix('sales-admin')->group(function () {
        Route::post('/tickets/{id}/verify', [SalesAdminController::class, 'verifyTicket']);
        Route::post('/tickets/{id}/waiting', [SalesAdminController::class, 'markAsWaiting']);
        Route::post('/tickets/{id}/cancel', [SalesAdminController::class, 'cancelTicket']);
    });

    Route::prefix('dispatch')->group(function () {
        Route::post('/tickets/{qrCodeId}/qr-code', [SalesAdminController::class, 'qrCode']);
        Route::post('/tickets/{qrCodeId}/qr-code-gas-cylinder-admin-sale', [SalesAdminController::class, 'qrCodeGasCylinderAdminSale']);
        Route::post('/tickets/{id}/dispatch', [SalesAdminController::class, 'dispatchTicket']);
    });

    Route::prefix('tickets')->group(function () {
        Route::get('/', [GasTicketController::class, 'index']);
        Route::post('/', [GasTicketController::class, 'store']);
        Route::get('/{id}', [GasTicketController::class, 'show']);
        Route::get('/getGasCylinders/{id}', [GasTicketController::class, 'getGasCylinders']);
        Route::get('/stations/getGasStations', [GasTicketController::class, 'getGasStations']);
        Route::put('/{id}', [GasTicketController::class, 'update']);
        Route::delete('/{id}', [GasTicketController::class, 'destroy']);
    });

    Route::post('/admin/close-cycle', [AdminController::class, 'closeCycle']);

    Route::prefix('cylinders')->group(function () {
        Route::get('/', [GasCylinderController::class, 'index']);
        Route::post('/', [GasCylinderController::class, 'store']);
        Route::get('/getGasSuppliers', [GasCylinderController::class, 'getGasSuppliers']);
        Route::get('/{id}', [GasCylinderController::class, 'show']);
        Route::put('/{id}', [GasCylinderController::class, 'update']);
        Route::delete('/{id}', [GasCylinderController::class, 'destroy']);
    });

    Route::prefix('phones')->group(function () {
        Route::get('/', [PhoneController::class, 'index']);
        Route::post('/', [PhoneController::class, 'store']);
        Route::get('/{id}', [PhoneController::class, 'show']);
        Route::put('/{id}', [PhoneController::class, 'update']);
        Route::delete('/{id}', [PhoneController::class, 'destroy']);
    });

    Route::prefix('emails')->group(function () {
        Route::get('/', [EmailController::class, 'index']);
        Route::post('/', [EmailController::class, 'store']);
        Route::get('/{id}', [EmailController::class, 'show']);
        Route::put('/{id}', [EmailController::class, 'update']);
        Route::delete('/{id}', [EmailController::class, 'destroy']);
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


    Route::get('/qr-profile/{id}', [ProfileController::class, 'getProfileId']);

    Route::prefix('data-verification/{profile_id}')->group(function () {
        // Ruta para obtener las verificaciones de datos
        Route::get('/', [DataVerificationController::class, 'getdataVerifications']);
        // Rutas para actualizar el estado de las direcciones, cilindros de gas, teléfonos, documentos y correos electrónicos
        Route::post('/update-status-check-scanner/profiles', [DataVerificationController::class, 'updateVerificationsProfiles']);
        Route::post('/update-status-check-scanner/addresses', [DataVerificationController::class, 'updateVerificationsAddresses']);
        Route::post('/update-status-check-scanner/gas-cylinders', [DataVerificationController::class, 'updateVerificationsGasCylinders']);
        Route::post('/update-status-check-scanner/phones', [DataVerificationController::class, 'updateVerificationsPhones']);
        Route::post('/update-status-check-scanner/documents', [DataVerificationController::class, 'updateVerificationsDocuments']);
        Route::post('/update-status-check-scanner/emails', [DataVerificationController::class, 'updateVerificationsEmails']);
    });





});
