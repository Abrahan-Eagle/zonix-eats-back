<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Commerce;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Services\DeliveryObservabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function getDeliveryObservabilitySummary(DeliveryObservabilityService $observabilityService)
    {
        try {
            $data = $observabilityService->getSummary(null, [
                'window_hours' => request()->query('window_hours'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Resumen de observabilidad de delivery obtenido correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en observabilidad delivery summary: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error obteniendo resumen de observabilidad',
            ], 500);
        }
    }

    public function getDeliveryObservabilityIncidents(DeliveryObservabilityService $observabilityService)
    {
        try {
            $incidents = $observabilityService->getIncidents(null, [
                'type' => request()->query('type'),
                'priority' => request()->query('priority'),
                'window_hours' => request()->query('window_hours'),
                'page' => request()->query('page', 1),
                'per_page' => request()->query('per_page', 20),
            ]);

            return response()->json([
                'success' => true,
                'data' => $incidents,
                'message' => 'Incidentes de observabilidad obtenidos correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en observabilidad delivery incidents: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error obteniendo incidentes de observabilidad',
            ], 500);
        }
    }

    public function getDeliveryObservabilityIncidentOrders(DeliveryObservabilityService $observabilityService)
    {
        try {
            $orders = $observabilityService->getIncidentOrders(null, [
                'type' => request()->query('type'),
                'window_hours' => request()->query('window_hours'),
                'page' => request()->query('page', 1),
                'per_page' => request()->query('per_page', 20),
            ]);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'Ordenes de incidentes obtenidas correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en observabilidad delivery incident orders: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error obteniendo ordenes de incidentes',
            ], 500);
        }
    }

    public function getDeliveryObservabilityRunbooks(DeliveryObservabilityService $observabilityService)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $observabilityService->getRunbooks(),
            ],
            'message' => 'Runbooks de observabilidad obtenidos correctamente',
        ]);
    }

    public function getDeliveryObservabilityHistory(DeliveryObservabilityService $observabilityService)
    {
        try {
            $history = $observabilityService->getHistory(
                null,
                (int) request()->query('page', 1),
                (int) request()->query('per_page', 24),
                request()->query('window_hours') !== null ? (int) request()->query('window_hours') : null
            );

            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => 'Historico de observabilidad obtenido correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en observabilidad delivery history: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error obteniendo historico de observabilidad',
            ], 500);
        }
    }

    public function index()
    {
        return response()->json([
            'message' => 'Listado de reportes disponibles',
            'available_reports' => [
                'statistics' => '/api/admin/statistics',
                'analytics' => '/api/admin/analytics',
                'security-logs' => '/api/admin/security-logs',
            ],
        ]);
    }

    public function getStatistics()
    {
        $totalUsers = User::count();
        $activeUsers = User::whereHas('profile', function ($q) {
            $q->where('status', 'active');
        })->count();
        $suspendedUsers = User::whereHas('profile', function ($q) {
            $q->where('status', 'suspended');
        })->count();

        $roleCounts = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');

        // Compatibilidad: buyers = users; delivery = suma de delivery_company + delivery_agent + delivery
        $userDistribution = $roleCounts->toArray();
        $userDistribution['buyers'] = $roleCounts->get('users', 0);
        $userDistribution['delivery'] = ($roleCounts->get('delivery_company', 0) + $roleCounts->get('delivery_agent', 0) + $roleCounts->get('delivery', 0));

        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'delivered')->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return response()->json([
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'suspended_users' => $suspendedUsers,
            'user_distribution' => $userDistribution,
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'average_order_value' => round($averageOrderValue, 2),
            'total_commerces' => Commerce::count(),
        ]);
    }

    public function getSystemHealth()
    {
        $pdoOk = false;
        $dbPingMs = null;
        try {
            DB::connection()->getPdo();
            $pdoOk = true;
            $t0 = microtime(true);
            DB::select('select 1');
            $dbPingMs = round((microtime(true) - $t0) * 1000, 2);
        } catch (\Throwable $e) {
            $pdoOk = false;
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'server_status' => 'healthy',
                'database_status' => $pdoOk ? 'healthy' : 'unhealthy',
                'api_status' => 'healthy',
                'database_ping_ms' => $dbPingMs,
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'active_sessions_approx' => User::whereNotNull('remember_token')->count(),
                'last_backup_at' => env('LAST_BACKUP_AT'),
                'uptime_note' => 'El uptime del servidor no se mide en este endpoint; usar monitorización externa.',
            ],
        ]);
    }

    /**
     * Exporta contadores de métricas en cache (Pusher auth, FCM, emisión de notificaciones).
     */
    public function getRealtimeMetricsSnapshot()
    {
        $keys = [
            'metrics:realtime:notification_broadcast_emitted_total',
            'metrics:realtime:fcm_sent_total',
            'metrics:realtime:notification_emit_failed_total',
            'metrics:realtime:fcm_skipped_no_token_total',
            'metrics:realtime:fcm_skipped_preferences_total',
            'metrics:realtime:fcm_failed_total',
            'metrics:realtime:broadcast_auth_success_total',
            'metrics:realtime:broadcast_auth_denied_total',
            'metrics:realtime:broadcast_auth_error_total',
        ];
        $metrics = [];
        foreach ($keys as $k) {
            $metrics[$k] = (int) Cache::get($k, 0);
        }

        return response()->json([
            'success' => true,
            'message' => 'Métricas en cache (contadores; se reinician con flush de cache / deploy).',
            'data' => $metrics,
        ]);
    }

    public function getAnalytics(Request $request)
    {
        $period = $request->input('period', 'month');
        $metric = $request->input('metric');

        $data = [];

        if (! $metric || $metric === 'user_growth') {
            $data['user_growth'] = $this->getUserGrowthData($period);
        }

        if (! $metric || $metric === 'revenue_growth') {
            $data['revenue_growth'] = $this->getRevenueGrowthData($period);
        }

        if (! $metric || $metric === 'order_volume') {
            $data['order_volume'] = $this->getOrderVolumeData($period);
        }

        $totalUsers = User::count() ?: 1;
        $data['top_performing_roles'] = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($totalUsers) {
                return [
                    'role' => $item->role,
                    'count' => $item->count,
                    'percentage' => round(($item->count / $totalUsers) * 100, 1),
                ];
            });

        return response()->json($data);
    }

    public function getSecurityLogs(Request $request)
    {
        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        $query = AdminAuditLog::query()->orderByDesc('id');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action').'%');
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if (in_array($status, ['success', 'ok', '2xx'], true)) {
                $query->where('success', true);
            } elseif (in_array($status, ['error', 'failed', '4xx', '5xx'], true)) {
                $query->where('success', false);
            }
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            // Compatibilidad con clientes que esperan logs en raíz.
            'logs' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'message' => 'Security logs obtenidos correctamente',
        ]);
    }

    public function getSystemSettings()
    {
        $settingsPath = storage_path('app/system_settings.json');
        $persistedSettings = file_exists($settingsPath)
            ? (json_decode((string) file_get_contents($settingsPath), true) ?? [])
            : [];

        return response()->json([
            'app_name' => $persistedSettings['app_name'] ?? config('app.name', 'ZONIX EATS'),
            'app_version' => '1.0.0',
            'maintenance_mode' => $persistedSettings['maintenance_mode'] ?? config('app.maintenance_mode', false),
            'registration_enabled' => $persistedSettings['registration_enabled'] ?? env('REGISTRATION_ENABLED', true),
            'email_verification_required' => $persistedSettings['email_verification_required'] ?? env('EMAIL_VERIFICATION_REQUIRED', false),
            'phone_verification_required' => $persistedSettings['phone_verification_required'] ?? env('PHONE_VERIFICATION_REQUIRED', false),
            'max_file_size' => env('MAX_FILE_SIZE', '10MB'),
            'allowed_file_types' => ['jpg', 'png', 'pdf', 'jpeg'],
            'session_timeout' => config('sanctum.expiration', 60),
            'password_policy' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_special_chars' => false,
            ],
            'notification_settings' => [
                'email_notifications' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
                'push_notifications' => env('PUSH_NOTIFICATIONS_ENABLED', true),
                'sms_notifications' => env('SMS_NOTIFICATIONS_ENABLED', false),
            ],
        ]);
    }

    public function updateSystemSettings(Request $request)
    {
        try {
            $request->validate([
                'app_name' => 'sometimes|string|max:255',
                'maintenance_mode' => 'sometimes|boolean',
                'registration_enabled' => 'sometimes|boolean',
                'email_verification_required' => 'sometimes|boolean',
                'phone_verification_required' => 'sometimes|boolean',
            ]);

            // Guardar en archivo de configuración o base de datos
            // Por ahora, guardamos en un archivo JSON en storage
            $settingsPath = storage_path('app/system_settings.json');
            $currentSettings = file_exists($settingsPath)
                ? json_decode(file_get_contents($settingsPath), true)
                : [];

            $updatedSettings = array_merge($currentSettings, $request->only([
                'app_name',
                'maintenance_mode',
                'registration_enabled',
                'email_verification_required',
                'phone_verification_required',
            ]));

            $updatedSettings['updated_at'] = now()->toIso8601String();
            $updatedSettings['updated_by'] = auth()->id();

            file_put_contents($settingsPath, json_encode($updatedSettings, JSON_PRETTY_PRINT));

            Log::info('System settings updated', [
                'updated_by' => auth()->id(),
                'settings' => $request->all(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'System settings updated successfully',
                'settings' => $updatedSettings,
                'updated_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating system settings: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error actualizando configuración del sistema',
            ], 500);
        }
    }

    public function sendSystemNotification(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|string|in:info,warning,error,success',
                'target_users' => 'nullable|array',
                'target_role' => 'nullable|string|in:users,commerce,delivery_company,delivery_agent,delivery,admin',
            ]);

            $targetUsers = $request->target_users ?? [];
            $targetRole = $request->target_role;

            // Si se especifica un rol, obtener todos los usuarios de ese rol
            if ($targetRole && empty($targetUsers)) {
                $users = User::where('role', $targetRole)->with('profile')->get();
                $targetUsers = $users->pluck('id')->toArray();
            }

            // Si no hay usuarios objetivo, enviar a todos
            if (empty($targetUsers)) {
                $users = User::with('profile')->get();
                $targetUsers = $users->pluck('id')->toArray();
            }

            $notificationService = app(\App\Services\NotificationService::class);
            $sentCount = 0;
            foreach ($targetUsers as $userId) {
                $user = User::with('profile')->find($userId);
                if ($user && $user->profile) {
                    $notificationService->notify(
                        $user->profile->id,
                        $request->title,
                        $request->message,
                        $request->type,
                    );
                    $sentCount++;
                }
            }

            Log::info('System notification sent', [
                'title' => $request->title,
                'type' => $request->type,
                'recipients_count' => $sentCount,
                'sent_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'id' => now()->timestamp,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'target_users' => $targetUsers,
                'status' => 'sent',
                'sent_at' => now()->toIso8601String(),
                'recipients_count' => $sentCount,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error sending system notification: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error enviando notificación: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar reseñas reportadas para moderación operativa.
     */
    public function getReportedReviews(Request $request)
    {
        if (! Schema::hasColumn('reviews', 'moderation_status')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'reviews' => [],
                    'pagination' => null,
                ],
                'message' => 'Moderación no disponible en el esquema actual',
            ]);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        $query = Review::query()
            ->with(['profile', 'order'])
            ->where('moderation_status', 'reported')
            ->orderByDesc('reported_at')
            ->orderByDesc('updated_at');

        $reviews = $query->paginate($perPage);

        $items = $reviews->getCollection()->map(function (Review $review) {
            $profile = $review->profile;
            $authorName = $profile
                ? trim(($profile->firstName ?? '').' '.($profile->lastName ?? ''))
                : 'Usuario';

            return [
                'id' => $review->id,
                'order_id' => $review->order_id,
                'reviewable_type' => $review->reviewable_type,
                'reviewable_id' => $review->reviewable_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'moderation_status' => $review->moderation_status,
                'reported_reason' => Schema::hasColumn('reviews', 'reported_reason') ? $review->reported_reason : null,
                'reported_at' => Schema::hasColumn('reviews', 'reported_at') ? optional($review->reported_at)->toISOString() : null,
                'author_name' => $authorName !== '' ? $authorName : 'Usuario',
                'created_at' => optional($review->created_at)->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $items,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ],
            'message' => 'Reseñas reportadas obtenidas exitosamente',
        ]);
    }

    /**
     * Moderar reseña reportada: approved o rejected.
     */
    public function moderateReview(Request $request, int $reviewId)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:approved,rejected',
            'reason' => 'nullable|string|max:500',
        ]);

        $review = Review::find($reviewId);
        if (! $review) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Reseña no encontrada',
                'error_code' => 'REVIEWS_NOT_FOUND',
            ], 404);
        }

        if (! Schema::hasColumn('reviews', 'moderation_status')) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Moderación no disponible en el esquema actual',
                'error_code' => 'REVIEWS_MODERATION_SCHEMA_MISSING',
            ], 400);
        }

        $updatePayload = [
            'moderation_status' => $validated['action'],
        ];
        if (Schema::hasColumn('reviews', 'reported_reason') && ! empty($validated['reason'])) {
            $updatePayload['reported_reason'] = $validated['reason'];
        }
        $review->update($updatePayload);

        return response()->json([
            'success' => true,
            'data' => [
                'review_id' => $review->id,
                'moderation_status' => $review->moderation_status,
            ],
            'message' => 'Reseña moderada exitosamente',
        ]);
    }

    private function getUserGrowthData($period)
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 90);
        $data = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', '<=', $date)->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'value' => $count,
            ];
        }

        return $data;
    }

    private function getRevenueGrowthData($period)
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 90);
        $data = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->where('status', 'delivered')
                ->sum('total');
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'value' => round($revenue, 2),
            ];
        }

        return $data;
    }

    private function getOrderVolumeData($period)
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 90);
        $data = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Order::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'value' => $count,
            ];
        }

        return $data;
    }
}
