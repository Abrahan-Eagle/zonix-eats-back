<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:delivery_company'])->prefix('delivery-company')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'dashboard']);
    Route::get('/agents', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'agents']);
    Route::post('/agents', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'storeAgent']);
    Route::get('/agents/{id}', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'agentDetail']);
    Route::patch('/agents/{id}', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'updateAgentStatus']);
    Route::patch('/agents/{id}/payout', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'updateAgentPayout']);
    Route::patch('/settings', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'updateSettings']);
    Route::get('/orders', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'orders']);
    Route::get('/orders/pending', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'pendingOrders']);
    Route::get('/orders/pending-payment', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'pendingPaymentOrders']);
    Route::post('/orders/{id}/validate-delivery-payment', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'validateDeliveryPayment']);
    Route::get('/orders/{id}/available-agents', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'availableAgentsForOrder']);
    Route::post('/orders/{id}/assign', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'assignOrder']);
    Route::get('/earnings', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'earnings']);
    Route::get('/observability/summary', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'observabilitySummary']);
    Route::get('/observability/incidents', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'observabilityIncidents']);
    Route::get('/observability/incident-orders', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'observabilityIncidentOrders']);
    Route::get('/observability/history', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'observabilityHistory']);
    Route::get('/observability/runbooks', [\App\Http\Controllers\DeliveryCompany\CompanyController::class, 'observabilityRunbooks']);
});
