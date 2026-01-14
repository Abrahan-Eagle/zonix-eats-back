<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Commerce;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Listado de reportes disponibles',
            'available_reports' => [
                'statistics' => '/api/admin/statistics',
                'analytics' => '/api/admin/analytics',
                'security-logs' => '/api/admin/security-logs',
            ]
        ]);
    }

    public function getStatistics()
    {
        $totalUsers = User::count();
        $activeUsers = User::whereHas('profile', function($q) {
            $q->where('status', 'active');
        })->count();
        $suspendedUsers = User::whereHas('profile', function($q) {
            $q->where('status', 'suspended');
        })->count();

        $userDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');

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
        return response()->json([
            'server_status' => 'healthy',
            'database_status' => DB::connection()->getPdo() ? 'healthy' : 'unhealthy',
            'api_status' => 'healthy',
            'uptime' => '99.9%',
            'response_time' => '120ms',
            'active_connections' => User::whereNotNull('remember_token')->count(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
            'last_backup' => now()->subDay()->toIso8601String(),
            'security_alerts' => 0,
            'performance_score' => 95,
        ]);
    }

    public function getAnalytics(Request $request)
    {
        $period = $request->input('period', 'month');
        $metric = $request->input('metric');

        $data = [];

        if (!$metric || $metric === 'user_growth') {
            $data['user_growth'] = $this->getUserGrowthData($period);
        }

        if (!$metric || $metric === 'revenue_growth') {
            $data['revenue_growth'] = $this->getRevenueGrowthData($period);
        }

        if (!$metric || $metric === 'order_volume') {
            $data['order_volume'] = $this->getOrderVolumeData($period);
        }

        $data['top_performing_roles'] = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->orderByDesc('count')
            ->get()
            ->map(function($item) use ($totalUsers) {
                return [
                    'role' => $item->role,
                    'count' => $item->count,
                    'percentage' => round(($item->count / User::count()) * 100, 1)
                ];
            });

        return response()->json($data);
    }

    public function getSecurityLogs(Request $request)
    {
        // Por ahora retornar estructura básica
        // TODO: Implementar tabla de security_logs si es necesario
        return response()->json([
            'message' => 'Security logs endpoint',
            'logs' => []
        ]);
    }

    public function getSystemSettings()
    {
        return response()->json([
            'app_name' => config('app.name', 'ZONIX EATS'),
            'app_version' => '1.0.0',
            'maintenance_mode' => config('app.maintenance_mode', false),
            'registration_enabled' => env('REGISTRATION_ENABLED', true),
            'email_verification_required' => env('EMAIL_VERIFICATION_REQUIRED', false),
            'phone_verification_required' => env('PHONE_VERIFICATION_REQUIRED', false),
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
                'settings' => $request->all()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'System settings updated successfully',
                'settings' => $updatedSettings,
                'updated_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating system settings: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error actualizando configuración del sistema'
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
                'target_role' => 'nullable|string|in:users,commerce,delivery,admin',
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

            $sentCount = 0;
            foreach ($targetUsers as $userId) {
                $user = User::with('profile')->find($userId);
                if ($user && $user->profile) {
                    Notification::create([
                        'profile_id' => $user->profile->id,
                        'title' => $request->title,
                        'body' => $request->message,
                        'type' => $request->type,
                        'read_at' => null,
                    ]);
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
            Log::error('Error sending system notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error enviando notificación: ' . $e->getMessage()
            ], 500);
        }
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
                'value' => $count
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
                'value' => round($revenue, 2)
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
                'value' => $count
            ];
        }
        
        return $data;
    }
}
