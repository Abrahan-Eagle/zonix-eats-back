<?php

use App\Http\Controllers\Partner\PatientController as PartnerPatientController;
use App\Http\Controllers\Partner\PrescriptionController as PartnerPrescriptionController;
use App\Http\Controllers\Patient\PrescriptionController as PatientPrescriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:optical_partner', \App\Http\Middleware\EnsureOpticalPartner::class])
    ->prefix('partner')
    ->group(function () {
        Route::get('/patients', [PartnerPatientController::class, 'index']);
        Route::post('/patients', [PartnerPatientController::class, 'store']);
        Route::get('/patients/{id}', [PartnerPatientController::class, 'show']);

        Route::get('/prescriptions', [PartnerPrescriptionController::class, 'index']);
        Route::post('/prescriptions', [PartnerPrescriptionController::class, 'store']);
        Route::post('/prescriptions/ocr', [PartnerPrescriptionController::class, 'ocr'])
            ->middleware('throttle:10,1');
        Route::get('/prescriptions/{id}', [PartnerPrescriptionController::class, 'show']);
        Route::post('/prescriptions/{id}/approve', [PartnerPrescriptionController::class, 'approve']);
        Route::post('/prescriptions/{id}/reject', [PartnerPrescriptionController::class, 'reject']);
    });

Route::middleware(['auth:sanctum', 'role:user'])->prefix('patient')->group(function () {
    Route::get('/prescriptions', [PatientPrescriptionController::class, 'index']);
    Route::post('/prescriptions/{id}/confirm', [PatientPrescriptionController::class, 'confirm']);
});
