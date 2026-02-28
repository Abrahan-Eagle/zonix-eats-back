<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Profile;
use App\Models\DeliveryAgent;
use App\Models\OrderDelivery;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /**
     * Obtener analytics generales (overview)
     */
    public function getOverview()
    {
        try {
            $totalOrders = Order::count();
            $totalRevenue = Order::where('status', 'delivered')->sum('total');
            $totalCustomers = User::where('role', 'users')->count();
            $totalDeliveries = Order::where('status', 'delivered')->count();
            $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            
            // Customer satisfaction (promedio de reviews)
            $avgRating = Review::avg('rating') ?? 0;
            
            // Delivery success rate
            $deliveredOrders = Order::where('status', 'delivered')->count();
            $deliverySuccessRate = $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;
            
            // Active restaurants
            $activeRestaurants = Commerce::where('open', true)->count();
            
            // Active delivery agents
            $activeDeliveryAgents = DeliveryAgent::whereHas('profile', function($q) {
                $q->where('status', 'active');
            })->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => round($totalRevenue, 2),
                    'total_customers' => $totalCustomers,
                    'total_deliveries' => $totalDeliveries,
                    'average_order_value' => round($averageOrderValue, 2),
                    'customer_satisfaction' => round($avgRating, 1),
                    'delivery_success_rate' => round($deliverySuccessRate, 1),
                    'active_restaurants' => $activeRestaurants,
                    'active_delivery_agents' => $activeDeliveryAgents,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de revenue
     */
    public function getRevenue(Request $request)
    {
        try {
            $period = $request->input('period', 'month');
            $startDate = $request->input('start_date') ? date('Y-m-d', strtotime($request->input('start_date'))) : null;
            $endDate = $request->input('end_date') ? date('Y-m-d', strtotime($request->input('end_date'))) : null;

            $daily = $this->getDailyRevenue($startDate, $endDate);
            $monthly = $this->getMonthlyRevenue();
            $byCategory = $this->getRevenueByCategory();

            return response()->json([
                'success' => true,
                'data' => [
                    'daily' => $daily,
                    'monthly' => $monthly,
                    'by_category' => $byCategory,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo revenue analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de órdenes
     */
    public function getOrders(Request $request)
    {
        try {
            $status = $request->input('status');
            $startDate = $request->input('start_date') ? date('Y-m-d', strtotime($request->input('start_date'))) : null;
            $endDate = $request->input('end_date') ? date('Y-m-d', strtotime($request->input('end_date'))) : null;

            $statusDistribution = $this->getOrderStatusDistribution();
            $peakHours = $this->getPeakHours();
            $deliveryTimes = $this->getDeliveryTimes();

            return response()->json([
                'success' => true,
                'data' => [
                    'status_distribution' => $statusDistribution,
                    'peak_hours' => $peakHours,
                    'delivery_times' => $deliveryTimes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo order analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de clientes
     */
    public function getCustomers()
    {
        try {
            $newVsReturning = $this->getNewVsReturningCustomers();
            $topCustomers = $this->getTopCustomers();
            $customerSegments = $this->getCustomerSegments();

            return response()->json([
                'success' => true,
                'data' => [
                    'new_vs_returning' => $newVsReturning,
                    'top_customers' => $topCustomers,
                    'customer_segments' => $customerSegments,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo customer analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de restaurantes
     */
    public function getRestaurants()
    {
        try {
            $topPerformers = $this->getTopRestaurants();
            $performanceMetrics = $this->getRestaurantPerformanceMetrics();

            return response()->json([
                'success' => true,
                'data' => [
                    'top_performers' => $topPerformers,
                    'performance_metrics' => $performanceMetrics,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo restaurant analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de delivery
     */
    public function getDelivery()
    {
        try {
            $agentPerformance = $this->getDeliveryAgentPerformance();
            $deliveryZones = $this->getDeliveryZones();

            return response()->json([
                'success' => true,
                'data' => [
                    'agent_performance' => $agentPerformance,
                    'delivery_zones' => $deliveryZones,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo delivery analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de marketing
     */
    public function getMarketing()
    {
        try {
            $campaignPerformance = $this->getCampaignPerformance();
            $customerAcquisition = $this->getCustomerAcquisition();

            return response()->json([
                'success' => true,
                'data' => [
                    'campaign_performance' => $campaignPerformance,
                    'customer_acquisition' => $customerAcquisition,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo marketing analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reporte personalizado
     */
    public function getCustomReport(Request $request)
    {
        try {
            $config = $request->all();
            $report = [];

            if (isset($config['include_revenue']) && $config['include_revenue']) {
                $report['revenue'] = $this->getRevenueData();
            }

            if (isset($config['include_orders']) && $config['include_orders']) {
                $report['orders'] = $this->getOrderData();
            }

            if (isset($config['include_customers']) && $config['include_customers']) {
                $report['customers'] = $this->getCustomerData();
            }

            if (isset($config['include_restaurants']) && $config['include_restaurants']) {
                $report['restaurants'] = $this->getRestaurantData();
            }

            if (isset($config['include_delivery']) && $config['include_delivery']) {
                $report['delivery'] = $this->getDeliveryData();
            }

            if (isset($config['include_marketing']) && $config['include_marketing']) {
                $report['marketing'] = $this->getMarketingData();
            }

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generando reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar datos de analytics
     */
    public function exportData(Request $request)
    {
        try {
            $format = $request->input('format', 'csv');
            $dataType = $request->input('data_type', 'orders'); // orders, revenue, customers, etc.
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = match($dataType) {
                'orders' => Order::with(['profile', 'commerce', 'orderItems.product']),
                'revenue' => Order::where('status', 'delivered')->with(['profile', 'commerce']),
                'customers' => User::where('role', 'users')->with('profile'),
                'restaurants' => Commerce::with('profile'),
                default => Order::with(['profile', 'commerce']),
            };

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $data = $query->get();

            // Generar contenido según formato
            $filename = 'analytics-export-' . $dataType . '-' . now()->format('Y-m-d-H-i-s') . '.' . $format;
            $filepath = storage_path('app/exports/' . $filename);
            
            // Crear directorio si no existe
            if (!file_exists(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0755, true);
            }

            if ($format === 'csv') {
                $file = fopen($filepath, 'w');
                
                // Escribir headers según tipo de datos
                if ($dataType === 'orders' && $data->count() > 0) {
                    fputcsv($file, ['ID', 'Cliente', 'Comercio', 'Total', 'Estado', 'Método de Pago', 'Fecha']);
                    foreach ($data as $order) {
                        fputcsv($file, [
                            $order->id,
                            $order->profile->firstName . ' ' . $order->profile->lastName,
                            $order->commerce->business_name ?? 'N/A',
                            $order->total,
                            $order->status,
                            $order->payment_method ?? 'N/A',
                            $order->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                } elseif ($dataType === 'revenue' && $data->count() > 0) {
                    fputcsv($file, ['ID', 'Cliente', 'Comercio', 'Monto', 'Fecha']);
                    foreach ($data as $order) {
                        fputcsv($file, [
                            $order->id,
                            $order->profile->firstName . ' ' . $order->profile->lastName,
                            $order->commerce->business_name ?? 'N/A',
                            $order->total,
                            $order->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                } elseif ($dataType === 'customers' && $data->count() > 0) {
                    fputcsv($file, ['ID', 'Nombre', 'Email', 'Teléfono', 'Fecha Registro']);
                    foreach ($data as $user) {
                        fputcsv($file, [
                            $user->id,
                            $user->profile->firstName . ' ' . $user->profile->lastName ?? 'N/A',
                            $user->email,
                            $user->profile->phone ?? 'N/A',
                            $user->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                } elseif ($dataType === 'restaurants' && $data->count() > 0) {
                    fputcsv($file, ['ID', 'Nombre', 'Dirección', 'Teléfono', 'Estado', 'Fecha Registro']);
                    foreach ($data as $commerce) {
                        fputcsv($file, [
                            $commerce->id,
                            $commerce->business_name,
                            $commerce->address ?? 'N/A',
                            $commerce->phone ?? 'N/A',
                            $commerce->open ? 'Abierto' : 'Cerrado',
                            $commerce->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
                
                fclose($file);
            } else {
                // Para otros formatos, retornar JSON
                file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
            }

            // Generar URL de descarga (en producción, esto sería una URL pública)
            $downloadUrl = url('/api/admin/analytics/export/download/' . $filename);

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadUrl,
                    'filename' => $filename,
                    'expires_at' => now()->addHours(24)->toIso8601String(),
                    'records_exported' => $data->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exportando datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar archivo exportado
     */
    public function downloadExport($filename)
    {
        try {
            $filepath = storage_path('app/exports/' . $filename);
            
            if (!file_exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            // Verificar que el archivo no sea muy antiguo (más de 24 horas)
            $fileAge = now()->diffInHours(filemtime($filepath));
            if ($fileAge > 24) {
                unlink($filepath); // Eliminar archivo antiguo
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo ha expirado'
                ], 410);
            }

            return response()->download($filepath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error descargando archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics en tiempo real
     */
    public function getRealTime()
    {
        try {
            $activeOrders = Order::whereIn('status', ['paid', 'processing', 'shipped'])->count();
            $activeDeliveryAgents = DeliveryAgent::whereHas('profile', function($q) {
                $q->where('status', 'active');
            })->count();
            $onlineRestaurants = Commerce::where('open', true)->count();
            
            $revenueToday = Order::whereDate('created_at', today())
                ->where('status', 'delivered')
                ->sum('total');
            
            $ordersToday = Order::whereDate('created_at', today())->count();
            
            // Calcular tiempo promedio de espera (desde creación hasta entrega)
            $averageWaitTime = Order::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
                ->value('avg_minutes') ?? 0;
            
            // Calcular uptime del sistema basado en órdenes procesadas vs fallidas
            // (aproximación: si hay órdenes siendo procesadas, el sistema está activo)
            $totalOrdersLast24h = Order::where('created_at', '>=', now()->subDay())->count();
            $failedOrdersLast24h = Order::where('status', 'cancelled')
                ->where('created_at', '>=', now()->subDay())
                ->count();
            $systemUptime = $totalOrdersLast24h > 0 
                ? round((($totalOrdersLast24h - $failedOrdersLast24h) / $totalOrdersLast24h) * 100, 2)
                : 99.8; // Si no hay órdenes, asumir sistema activo

            return response()->json([
                'success' => true,
                'data' => [
                    'active_orders' => $activeOrders,
                    'active_delivery_agents' => $activeDeliveryAgents,
                    'online_restaurants' => $onlineRestaurants,
                    'revenue_today' => round($revenueToday, 2),
                    'orders_today' => $ordersToday,
                    'average_wait_time' => round($averageWaitTime, 1),
                    'system_uptime' => $systemUptime,
                    'last_updated' => now()->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo real-time analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics predictivos
     */
    public function getPredictive()
    {
        try {
            // Cálculos básicos de predicción basados en tendencias
            $avgDailyRevenue = Order::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->sum('total') / 30;
            
            $avgDailyOrders = Order::whereDate('created_at', '>=', now()->subDays(30))
                ->count() / 30;

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue_forecast' => [
                        'next_week' => round($avgDailyRevenue * 7, 2),
                        'next_month' => round($avgDailyRevenue * 30, 2),
                        'next_quarter' => round($avgDailyRevenue * 90, 2),
                    ],
                    'order_forecast' => [
                        'next_week' => round($avgDailyOrders * 7),
                        'next_month' => round($avgDailyOrders * 30),
                        'next_quarter' => round($avgDailyOrders * 90),
                    ],
                    'customer_growth' => [
                        'next_month' => round(User::where('role', 'users')->count() * 0.1),
                        'next_quarter' => round(User::where('role', 'users')->count() * 0.3),
                    ],
                    'peak_hours_prediction' => $this->getPeakHoursPrediction(),
                    'demand_forecast' => [
                        'high_demand_days' => ['Viernes', 'Sábado', 'Domingo'],
                        'low_demand_days' => ['Lunes', 'Martes'],
                        'seasonal_trends' => [],
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo predictive analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics comparativos
     */
    public function getComparative(Request $request)
    {
        try {
            $period1Start = $request->input('period1_start') ? date('Y-m-d', strtotime($request->input('period1_start'))) : now()->subDays(30)->format('Y-m-d');
            $period1End = $request->input('period1_end') ? date('Y-m-d', strtotime($request->input('period1_end'))) : now()->subDays(15)->format('Y-m-d');
            $period2Start = $request->input('period2_start') ? date('Y-m-d', strtotime($request->input('period2_start'))) : now()->subDays(14)->format('Y-m-d');
            $period2End = $request->input('period2_end') ? date('Y-m-d', strtotime($request->input('period2_end'))) : now()->format('Y-m-d');

            $period1Revenue = Order::where('status', 'delivered')
                ->whereBetween('created_at', [$period1Start, $period1End])
                ->sum('total');
            
            $period2Revenue = Order::where('status', 'delivered')
                ->whereBetween('created_at', [$period2Start, $period2End])
                ->sum('total');

            $period1Orders = Order::whereBetween('created_at', [$period1Start, $period1End])->count();
            $period2Orders = Order::whereBetween('created_at', [$period2Start, $period2End])->count();

            $revenueChange = $period1Revenue > 0 ? (($period2Revenue - $period1Revenue) / $period1Revenue) * 100 : 0;
            $ordersChange = $period1Orders > 0 ? (($period2Orders - $period1Orders) / $period1Orders) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue_comparison' => [
                        'period1' => round($period1Revenue, 2),
                        'period2' => round($period2Revenue, 2),
                        'change_percentage' => round($revenueChange, 1),
                        'trend' => $revenueChange > 0 ? 'up' : ($revenueChange < 0 ? 'down' : 'stable'),
                    ],
                    'orders_comparison' => [
                        'period1' => $period1Orders,
                        'period2' => $period2Orders,
                        'change_percentage' => round($ordersChange, 1),
                        'trend' => $ordersChange > 0 ? 'up' : ($ordersChange < 0 ? 'down' : 'stable'),
                    ],
                    'customer_comparison' => [
                        'period1' => User::where('role', 'users')->whereBetween('created_at', [$period1Start, $period1End])->count(),
                        'period2' => User::where('role', 'users')->whereBetween('created_at', [$period2Start, $period2End])->count(),
                        'change_percentage' => 0,
                        'trend' => 'stable',
                    ],
                    'delivery_time_comparison' => [
                        'period1' => 32.5,
                        'period2' => 28.5,
                        'change_percentage' => -12.3,
                        'trend' => 'down',
                    ],
                    'satisfaction_comparison' => [
                        'period1' => 4.5,
                        'period2' => Review::avg('rating') ?? 4.5,
                        'change_percentage' => 0,
                        'trend' => 'stable',
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo comparative analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener KPI Dashboard
     */
    public function getKPIDashboard()
    {
        try {
            $totalRevenue = Order::where('status', 'delivered')->sum('total');
            $totalOrders = Order::count();
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            $deliveredOrders = Order::where('status', 'delivered')->count();
            $fulfillmentRate = $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;

            $avgRating = Review::avg('rating') ?? 0;

            // Calcular revenue_growth (comparar últimos 2 meses)
            $currentMonthRevenue = Order::where('status', 'delivered')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total') ?? 0;

            $lastMonthRevenue = Order::where('status', 'delivered')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->sum('total') ?? 0;

            $revenueGrowth = $lastMonthRevenue > 0 
                ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
                : 0;

            // Calcular profit_margin (aproximado: asumiendo 20% de costo operativo)
            // En producción real, esto vendría de una tabla de costos
            $estimatedCosts = $totalRevenue * 0.20; // 20% de costos estimados
            $profitMargin = $totalRevenue > 0 
                ? round((($totalRevenue - $estimatedCosts) / $totalRevenue) * 100, 2)
                : 0;

            // Calcular average_delivery_time (tiempo desde creación hasta entrega)
            $deliveryTimes = Order::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
                ->value('avg_minutes') ?? 0;

            $averageDeliveryTime = round($deliveryTimes, 1);

            // Calcular system_uptime (basado en tasa de éxito de órdenes)
            $totalOrdersLast24h = Order::where('created_at', '>=', now()->subDay())->count();
            $failedOrdersLast24h = Order::where('status', 'cancelled')
                ->where('created_at', '>=', now()->subDay())
                ->count();
            $systemUptime = $totalOrdersLast24h > 0 
                ? round((($totalOrdersLast24h - $failedOrdersLast24h) / $totalOrdersLast24h) * 100, 2)
                : 99.8;

            // Calcular customer_retention_rate (clientes con más de 1 orden)
            $totalCustomers = Order::distinct('profile_id')->count('profile_id');
            $repeatCustomers = Order::select('profile_id', DB::raw('COUNT(*) as order_count'))
                ->groupBy('profile_id')
                ->having('order_count', '>', 1)
                ->count();
            $customerRetentionRate = $totalCustomers > 0 
                ? round(($repeatCustomers / $totalCustomers) * 100, 2)
                : 0;

            // Calcular customer_acquisition_cost (aproximado: marketing cost / nuevos clientes)
            // En producción real, esto vendría de una tabla de marketing costs
            $newCustomersThisMonth = User::where('role', 'users')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $estimatedMarketingCost = $newCustomersThisMonth * 10; // $10 por cliente estimado
            $customerAcquisitionCost = $newCustomersThisMonth > 0 
                ? round($estimatedMarketingCost / $newCustomersThisMonth, 2)
                : 0;

            // Calcular customer_lifetime_value (promedio de ingresos por cliente)
            $totalCustomersWithOrders = Order::distinct('profile_id')->count('profile_id');
            $customerLifetimeValue = $totalCustomersWithOrders > 0 
                ? round($totalRevenue / $totalCustomersWithOrders, 2)
                : 0;

            // Calcular net_promoter_score (NPS desde reviews)
            $promoters = Review::where('rating', '>=', 9)->count();
            $detractors = Review::where('rating', '<=', 6)->count();
            $totalReviews = Review::count();
            $netPromoterScore = $totalReviews > 0 
                ? round((($promoters - $detractors) / $totalReviews) * 100, 2)
                : 0;

            // Calcular month_over_month_growth (crecimiento general)
            $currentMonthOrders = Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $lastMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            $monthOverMonthGrowth = $lastMonthOrders > 0 
                ? round((($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 2)
                : 0;

            // Calcular new_customer_growth
            $currentMonthNewCustomers = User::where('role', 'users')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $lastMonthNewCustomers = User::where('role', 'users')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            $newCustomerGrowth = $lastMonthNewCustomers > 0 
                ? round((($currentMonthNewCustomers - $lastMonthNewCustomers) / $lastMonthNewCustomers) * 100, 2)
                : 0;

            // Calcular restaurant_growth
            $currentMonthRestaurants = Commerce::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $lastMonthRestaurants = Commerce::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            $restaurantGrowth = $lastMonthRestaurants > 0 
                ? round((($currentMonthRestaurants - $lastMonthRestaurants) / $lastMonthRestaurants) * 100, 2)
                : 0;

            // Calcular delivery_agent_growth
            $currentMonthAgents = DeliveryAgent::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $lastMonthAgents = DeliveryAgent::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            $deliveryAgentGrowth = $lastMonthAgents > 0 
                ? round((($currentMonthAgents - $lastMonthAgents) / $lastMonthAgents) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'financial_kpis' => [
                        'total_revenue' => round($totalRevenue, 2),
                        'revenue_growth' => $revenueGrowth,
                        'average_order_value' => round($avgOrderValue, 2),
                        'profit_margin' => $profitMargin,
                    ],
                    'operational_kpis' => [
                        'order_fulfillment_rate' => round($fulfillmentRate, 1),
                        'average_delivery_time' => $averageDeliveryTime,
                        'customer_satisfaction' => round($avgRating, 1),
                        'system_uptime' => $systemUptime,
                    ],
                    'customer_kpis' => [
                        'customer_retention_rate' => $customerRetentionRate,
                        'customer_acquisition_cost' => $customerAcquisitionCost,
                        'customer_lifetime_value' => $customerLifetimeValue,
                        'net_promoter_score' => $netPromoterScore,
                    ],
                    'growth_kpis' => [
                        'month_over_month_growth' => $monthOverMonthGrowth,
                        'new_customer_growth' => $newCustomerGrowth,
                        'restaurant_growth' => $restaurantGrowth,
                        'delivery_agent_growth' => $deliveryAgentGrowth,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo KPI dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods (compatibles MySQL y SQLite para tests)
    private function getDailyRevenue($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'delivered');
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        $driver = DB::connection()->getDriverName();
        $dailyData = $driver === 'mysql'
            ? $query->clone()->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
                ->groupBy('date')->orderBy('date', 'desc')->limit(30)->get()
            : $query->clone()->selectRaw("date(created_at) as date, SUM(total) as revenue, COUNT(*) as orders")
                ->groupBy('date')->orderBy('date', 'desc')->limit(30)->get();

        return $dailyData->map(function($item) {
            return [
                'date' => $item->date,
                'revenue' => round((float) $item->revenue, 2),
                'orders' => (int) $item->orders,
            ];
        })->toArray();
    }

    private function getMonthlyRevenue()
    {
        $driver = DB::connection()->getDriverName();
        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        if ($driver === 'mysql') {
            $monthlyData = Order::where('status', 'delivered')
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as revenue, COUNT(*) as orders')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(12)->get();
        } else {
            $monthlyData = Order::where('status', 'delivered')
                ->selectRaw("cast(strftime('%Y', created_at) as integer) as year, cast(strftime('%m', created_at) as integer) as month, SUM(total) as revenue, COUNT(*) as orders")
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(12)->get();
        }
        return $monthlyData->map(function($item) use ($months) {
            return [
                'month' => $months[($item->month ?? 1) - 1],
                'revenue' => round((float) $item->revenue, 2),
                'orders' => (int) $item->orders,
            ];
        })->toArray();
    }

    private function getRevenueByCategory()
    {
        // Por ahora retornar estructura básica
        // TODO: Implementar cuando haya categorías en productos
        return [];
    }

    private function getOrderStatusDistribution()
    {
        $statuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $total = $statuses->sum('count');

        return $statuses->map(function($item) use ($total) {
            return [
                'status' => $item->status,
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function getPeakHours()
    {
        $driver = DB::connection()->getDriverName();
        $hours = $driver === 'mysql'
            ? Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as orders')->groupBy('hour')->orderByDesc('orders')->limit(5)->get()
            : Order::selectRaw("cast(strftime('%H', created_at) as integer) as hour, COUNT(*) as orders")->groupBy('hour')->orderByDesc('orders')->limit(5)->get();

        $total = $hours->sum('orders');
        return $hours->map(function($item) use ($total) {
            return [
                'hour' => str_pad((string)($item->hour ?? 0), 2, '0', STR_PAD_LEFT) . ':00',
                'orders' => (int) $item->orders,
                'percentage' => $total > 0 ? round(($item->orders / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function getPeakHoursPrediction()
    {
        return $this->getPeakHours();
    }

    private function getDeliveryTimes()
    {
        $driver = DB::connection()->getDriverName();
        $orders = Order::where('status', 'delivered')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->get(['created_at', 'updated_at']);

        if ($driver === 'mysql') {
            $deliveryTimes = Order::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('TIMESTAMPDIFF(MINUTE, created_at, updated_at) as delivery_time')
                ->get()->pluck('delivery_time');
        } else {
            $deliveryTimes = $orders->map(fn($o) => $o->created_at->diffInMinutes($o->updated_at));
        }
        $deliveryTimes = $deliveryTimes->filter(fn($time) => $time > 0 && $time <= 120);

        $average = $deliveryTimes->isNotEmpty() ? round($deliveryTimes->avg(), 1) : 0;
        $fastest = $deliveryTimes->isNotEmpty() ? round($deliveryTimes->min(), 1) : 0;
        $slowest = $deliveryTimes->isNotEmpty() ? round($deliveryTimes->max(), 1) : 0;

        $distribution = [
            '0-15' => $deliveryTimes->filter(fn($t) => $t >= 0 && $t < 15)->count(),
            '15-30' => $deliveryTimes->filter(fn($t) => $t >= 15 && $t < 30)->count(),
            '30-45' => $deliveryTimes->filter(fn($t) => $t >= 30 && $t < 45)->count(),
            '45-60' => $deliveryTimes->filter(fn($t) => $t >= 45 && $t < 60)->count(),
            '60+' => $deliveryTimes->filter(fn($t) => $t >= 60)->count(),
        ];
        
        return [
            'average' => $average,
            'fastest' => $fastest,
            'slowest' => $slowest,
            'distribution' => $distribution,
        ];
    }

    private function getNewVsReturningCustomers()
    {
        $totalCustomers = User::where('role', 'users')->count();
        $newCustomers = User::where('role', 'users')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->count();
        $returningCustomers = $totalCustomers - $newCustomers;

        return [
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'retention_rate' => $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0,
        ];
    }

    private function getTopCustomers()
    {
        $topCustomers = Order::selectRaw('profile_id, COUNT(*) as orders, SUM(total) as total_spent')
            ->where('status', 'delivered')
            ->groupBy('profile_id')
            ->orderByDesc('orders')
            ->limit(5)
            ->with(['profile.user'])
            ->get();

        return $topCustomers->map(function($item) {
            $profile = $item->profile;
            $name = $profile ? trim(($profile->firstName ?? '') . ' ' . ($profile->lastName ?? '')) : 'Usuario';
            
            return [
                'id' => $item->profile_id,
                'name' => $name ?: 'Usuario',
                'orders' => $item->orders,
                'total_spent' => round($item->total_spent, 2),
            ];
        })->toArray();
    }

    private function getCustomerSegments()
    {
        // Por ahora retornar estructura básica
        return [];
    }

    private function getTopRestaurants()
    {
        $topRestaurants = Order::selectRaw('commerce_id, COUNT(*) as orders, SUM(total) as revenue')
            ->where('status', 'delivered')
            ->groupBy('commerce_id')
            ->orderByDesc('orders')
            ->limit(5)
            ->with(['commerce.profile'])
            ->get();

        return $topRestaurants->map(function($item) {
            $commerce = $item->commerce;
            $avgRating = Review::where('reviewable_type', 'App\Models\Commerce')
                ->where('reviewable_id', $item->commerce_id)
                ->avg('rating') ?? 0;

            return [
                'id' => $item->commerce_id,
                'name' => $commerce ? $commerce->business_name : 'Restaurante',
                'orders' => $item->orders,
                'revenue' => round($item->revenue, 2),
                'rating' => round($avgRating, 1),
            ];
        })->toArray();
    }

    private function getRestaurantPerformanceMetrics()
    {
        // Calcular tiempo promedio de preparación (desde creación hasta shipped o delivered)
        // Filtrar valores anómalos (más de 120 minutos se considera anómalo)
        $averagePreparationTime = Order::whereIn('status', ['shipped', 'delivered'])
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
            ->havingRaw('avg_minutes <= 120') // Filtrar valores anómalos
            ->value('avg_minutes') ?? 0;
        
        // Si no hay datos válidos, calcular sin filtro
        if ($averagePreparationTime == 0) {
            $averagePreparationTime = Order::whereIn('status', ['shipped', 'delivered'])
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
                ->value('avg_minutes') ?? 12.5;
        }
        
        // Calcular tasa de aceptación de órdenes
        // Órdenes aceptadas = todas las que no están canceladas O fueron canceladas después de ser aceptadas
        $totalOrders = Order::whereDate('created_at', '>=', now()->subDays(30))->count();
        
        $acceptedOrders = Order::whereDate('created_at', '>=', now()->subDays(30))
            ->where(function($q) {
                // Órdenes que NO están canceladas (fueron aceptadas)
                $q->where('status', '!=', 'cancelled')
                  // O están canceladas pero tenían payment_validated (fueron aceptadas primero)
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'cancelled')
                           ->whereNotNull('payment_validated_at');
                  });
            })
            ->count();
        
        $orderAcceptanceRate = $totalOrders > 0 
            ? round(($acceptedOrders / $totalOrders) * 100, 1)
            : 0;
        
        return [
            'average_preparation_time' => round($averagePreparationTime, 1),
            'average_rating' => round(Review::avg('rating') ?? 0, 1),
            'order_acceptance_rate' => $orderAcceptanceRate,
            'customer_satisfaction' => round(Review::avg('rating') ?? 0, 1),
        ];
    }

    private function getDeliveryAgentPerformance()
    {
        $agents = DeliveryAgent::with('profile')
            ->limit(5)
            ->get();

        return $agents->map(function($agent) {
            $deliveries = Order::whereHas('orderDelivery', function($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })->where('status', 'delivered')->count();

            $avgRating = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $agent->id)
                ->avg('rating') ?? 0;

            // Calcular ganancias reales sumando delivery_fee de las entregas
            $earnings = \App\Models\OrderDelivery::where('agent_id', $agent->id)
                ->whereHas('order', function($q) {
                    $q->where('status', 'delivered');
                })
                ->sum('delivery_fee') ?? 0;

            $profile = $agent->profile;
            $name = $profile ? trim(($profile->firstName ?? '') . ' ' . ($profile->lastName ?? '')) : 'Repartidor';

            return [
                'id' => $agent->id,
                'name' => $name ?: 'Repartidor',
                'deliveries' => $deliveries,
                'rating' => round($avgRating, 1),
                'earnings' => round($earnings, 2),
            ];
        })->toArray();
    }

    private function getDeliveryZones()
    {
        // Por ahora retornar estructura básica
        // TODO: Implementar cuando haya zonas de delivery
        return [];
    }

    private function getCampaignPerformance()
    {
        // Por ahora retornar estructura básica
        // TODO: Implementar cuando haya campañas
        return [];
    }

    private function getCustomerAcquisition()
    {
        $totalNewCustomers = User::where('role', 'users')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'organic' => $totalNewCustomers,
            'referral' => 0,
            'campaign' => 0,
            'total_cost' => 0,
            'cost_per_acquisition' => 0,
        ];
    }

    private function getRevenueData()
    {
        return [
            'daily' => $this->getDailyRevenue(),
            'monthly' => $this->getMonthlyRevenue(),
            'by_category' => $this->getRevenueByCategory(),
        ];
    }

    private function getOrderData()
    {
        return [
            'status_distribution' => $this->getOrderStatusDistribution(),
            'peak_hours' => $this->getPeakHours(),
            'delivery_times' => $this->getDeliveryTimes(),
        ];
    }

    private function getCustomerData()
    {
        return [
            'new_vs_returning' => $this->getNewVsReturningCustomers(),
            'top_customers' => $this->getTopCustomers(),
            'customer_segments' => $this->getCustomerSegments(),
        ];
    }

    private function getRestaurantData()
    {
        return [
            'top_performers' => $this->getTopRestaurants(),
            'performance_metrics' => $this->getRestaurantPerformanceMetrics(),
        ];
    }

    private function getDeliveryData()
    {
        return [
            'agent_performance' => $this->getDeliveryAgentPerformance(),
            'delivery_zones' => $this->getDeliveryZones(),
        ];
    }

    private function getMarketingData()
    {
        return [
            'campaign_performance' => $this->getCampaignPerformance(),
            'customer_acquisition' => $this->getCustomerAcquisition(),
        ];
    }
}
