<?php

use App\Http\Controllers\Admin\CommerceController;
use App\Http\Controllers\Admin\DeliveryCompanyController;
use App\Http\Controllers\Admin\DeliverySettingsController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin', \App\Http\Middleware\AdminAuditMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}/role', [AdminUserController::class, 'updateRole']);
    Route::put('/users/{id}/status', [AdminUserController::class, 'updateStatus']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    Route::get('/users/{id}/activity', [AdminUserController::class, 'getUserActivity']);

    Route::get('/statistics', [AdminReportController::class, 'getStatistics']);
    Route::get('/system-health', [AdminReportController::class, 'getSystemHealth']);
    Route::get('/realtime-metrics', [AdminReportController::class, 'getRealtimeMetricsSnapshot']);
    Route::get('/analytics', [AdminReportController::class, 'getAnalytics']);
    Route::get('/delivery/observability/summary', [AdminReportController::class, 'getDeliveryObservabilitySummary']);
    Route::get('/delivery/observability/incidents', [AdminReportController::class, 'getDeliveryObservabilityIncidents']);
    Route::get('/delivery/observability/incident-orders', [AdminReportController::class, 'getDeliveryObservabilityIncidentOrders']);
    Route::get('/delivery/observability/history', [AdminReportController::class, 'getDeliveryObservabilityHistory']);
    Route::get('/delivery/observability/runbooks', [AdminReportController::class, 'getDeliveryObservabilityRunbooks']);

    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getOverview']);
        Route::get('/revenue', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getRevenue']);
        Route::get('/orders', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getOrders']);
        Route::get('/customers', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getCustomers']);
        Route::get('/restaurants', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getRestaurants']);
        Route::get('/delivery', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getDelivery']);
        Route::get('/marketing', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getMarketing']);
        Route::post('/custom-report', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getCustomReport']);
        Route::post('/export', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'exportData']);
        Route::get('/export/download/{filename}', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'downloadExport']);
        Route::get('/realtime', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getRealTime']);
        Route::get('/predictive', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getPredictive']);
        Route::get('/comparative', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getComparative']);
        Route::get('/kpi-dashboard', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'getKPIDashboard']);
    });

    Route::get('/security-logs', [AdminReportController::class, 'getSecurityLogs']);

    Route::get('/settings', [AdminReportController::class, 'getSystemSettings']);
    Route::put('/settings', [AdminReportController::class, 'updateSystemSettings']);

    Route::post('/notifications', [AdminReportController::class, 'sendSystemNotification']);

    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::get('/reviews/reported', [AdminReportController::class, 'getReportedReviews']);
    Route::post('/reviews/{reviewId}/moderate', [AdminReportController::class, 'moderateReview']);

    Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index']);
    Route::get('/disputes/stats', [\App\Http\Controllers\Admin\DisputeController::class, 'stats']);
    Route::get('/disputes/{id}', [\App\Http\Controllers\Admin\DisputeController::class, 'show']);
    Route::post('/disputes/{id}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'resolve']);

    // Delivery settings (singleton config)
    Route::get('/delivery-settings', [DeliverySettingsController::class, 'index']);
    Route::put('/delivery-settings', [DeliverySettingsController::class, 'update']);

    // Delivery zones CRUD
    Route::apiResource('delivery-zones', DeliveryZoneController::class);

    // Commerces
    Route::get('/commerces', [CommerceController::class, 'index']);
    Route::get('/commerces/{id}', [CommerceController::class, 'show']);
    Route::put('/commerces/{id}/status', [CommerceController::class, 'updateStatus']);
    Route::put('/commerces/{id}/toggle-open', [CommerceController::class, 'toggleOpen']);

    // Delivery companies
    Route::get('/delivery-companies', [DeliveryCompanyController::class, 'index']);
    Route::get('/delivery-companies/{id}', [DeliveryCompanyController::class, 'show']);
    Route::get('/delivery-companies/{id}/agents', [DeliveryCompanyController::class, 'agents']);

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index']);
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus']);
});
