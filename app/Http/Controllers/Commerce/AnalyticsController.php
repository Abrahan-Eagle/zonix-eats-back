<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Obtener analytics generales del comercio
     */
    public function getOverview()
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;

            // Total de órdenes del comercio
            $totalOrders = Order::where('commerce_id', $commerceId)->count();
            
            // Total de ingresos (solo órdenes entregadas)
            $totalRevenue = Order::where('commerce_id', $commerceId)
                ->where('status', 'delivered')
                ->sum('total');
            
            // Valor promedio de orden
            $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            
            // Total de clientes únicos
            $totalCustomers = Order::where('commerce_id', $commerceId)
                ->distinct('profile_id')
                ->count('profile_id');
            
            // Clientes recurrentes (más de 1 orden)
            $repeatCustomers = Order::where('commerce_id', $commerceId)
                ->select('profile_id', DB::raw('COUNT(*) as order_count'))
                ->groupBy('profile_id')
                ->having('order_count', '>', 1)
                ->count();
            
            // Tasa de crecimiento (comparar último mes con anterior)
            $currentMonthOrders = Order::where('commerce_id', $commerceId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            $lastMonthOrders = Order::where('commerce_id', $commerceId)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            
            $growthRate = $lastMonthOrders > 0 
                ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 
                : 0;

            // Promedio de calificación
            $avgRating = Review::where('reviewable_type', 'App\Models\Commerce')
                ->where('reviewable_id', $commerceId)
                ->avg('rating') ?? 0;

            // Órdenes por estado
            $ordersByStatus = Order::where('commerce_id', $commerceId)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_sales' => round($totalRevenue, 2),
                    'total_orders' => $totalOrders,
                    'average_order_value' => round($averageOrderValue, 2),
                    'growth_rate' => round($growthRate, 1),
                    'customer_count' => $totalCustomers,
                    'repeat_customers' => $repeatCustomers,
                    'average_rating' => round($avgRating, 1),
                    'orders_by_status' => $ordersByStatus,
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
     * Obtener analytics de revenue por período
     */
    public function getRevenue(Request $request)
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;
            
            $period = $request->input('period', 'month');
            $startDate = $request->input('start_date') ? date('Y-m-d', strtotime($request->input('start_date'))) : null;
            $endDate = $request->input('end_date') ? date('Y-m-d', strtotime($request->input('end_date'))) : null;

            $daily = $this->getDailyRevenue($commerceId, $startDate, $endDate);
            $monthly = $this->getMonthlyRevenue($commerceId);
            $byProduct = $this->getRevenueByProduct($commerceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'daily' => $daily,
                    'monthly' => $monthly,
                    'by_product' => $byProduct,
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
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;

            $statusDistribution = $this->getOrderStatusDistribution($commerceId);
            $ordersByDay = $this->getOrdersByDay($commerceId);
            $peakHours = $this->getPeakHours($commerceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'status_distribution' => $statusDistribution,
                    'orders_by_day' => $ordersByDay,
                    'peak_hours' => $peakHours,
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
     * Obtener productos más vendidos
     */
    public function getProducts()
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;

            $topProducts = $this->getTopProducts($commerceId);
            $productsByCategory = $this->getProductsByCategory($commerceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'top_products' => $topProducts,
                    'by_category' => $productsByCategory,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo product analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener analytics de clientes
     */
    public function getCustomers()
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;

            $newVsReturning = $this->getNewVsReturningCustomers($commerceId);
            $topCustomers = $this->getTopCustomers($commerceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'new_vs_returning' => $newVsReturning,
                    'top_customers' => $topCustomers,
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
     * Obtener métricas de rendimiento
     */
    public function getPerformance()
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;

            // Tiempo promedio de preparación (estimado basado en estados)
            $avgPreparationTime = $this->getAveragePreparationTime($commerceId);
            
            // Tasa de aceptación de órdenes (todas las órdenes son aceptadas por defecto)
            $totalOrders = Order::where('commerce_id', $commerceId)->count();
            $acceptedOrders = Order::where('commerce_id', $commerceId)
                ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->count();
            
            $acceptanceRate = $totalOrders > 0 ? ($acceptedOrders / $totalOrders) * 100 : 0;

            // Satisfacción del cliente (promedio de reviews)
            $avgRating = Review::where('reviewable_type', 'App\Models\Commerce')
                ->where('reviewable_id', $commerceId)
                ->avg('rating') ?? 0;

            // Tasa de cancelación
            $cancelledOrders = Order::where('commerce_id', $commerceId)
                ->where('status', 'cancelled')
                ->count();
            
            $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'average_preparation_time' => round($avgPreparationTime, 1),
                    'order_acceptance_rate' => round($acceptanceRate, 1),
                    'customer_satisfaction' => round($avgRating, 1),
                    'cancellation_rate' => round($cancellationRate, 1),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo performance analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods
    private function getDailyRevenue($commerceId, $startDate = null, $endDate = null)
    {
        $query = Order::where('commerce_id', $commerceId)
            ->where('status', 'delivered');
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $dailyData = $query->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return $dailyData->map(function($item) {
            return [
                'date' => $item->date,
                'revenue' => round($item->revenue, 2),
                'orders' => $item->orders,
            ];
        })->toArray();
    }

    private function getMonthlyRevenue($commerceId)
    {
        $monthlyData = Order::where('commerce_id', $commerceId)
            ->where('status', 'delivered')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        return $monthlyData->map(function($item) use ($months) {
            return [
                'month' => $months[$item->month - 1],
                'revenue' => round($item->revenue, 2),
                'orders' => $item->orders,
            ];
        })->toArray();
    }

    private function getRevenueByProduct($commerceId)
    {
        $productRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.commerce_id', $commerceId)
            ->where('orders.status', 'delivered')
            ->select('products.id', 'products.name', 
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'),
                DB::raw('SUM(order_items.quantity) as quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return $productRevenue->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'revenue' => round($item->revenue, 2),
                'quantity' => $item->quantity,
            ];
        })->toArray();
    }

    private function getOrderStatusDistribution($commerceId)
    {
        $statuses = Order::where('commerce_id', $commerceId)
            ->selectRaw('status, COUNT(*) as count')
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

    private function getOrdersByDay($commerceId)
    {
        $ordersByDay = Order::where('commerce_id', $commerceId)
            ->selectRaw('DAYNAME(created_at) as day, COUNT(*) as orders')
            ->groupBy('day')
            ->orderByRaw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        $dayNames = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo',
        ];

        return $ordersByDay->map(function($item) use ($dayNames) {
            return [
                'day' => $dayNames[$item->day] ?? $item->day,
                'orders' => $item->orders,
            ];
        })->toArray();
    }

    private function getPeakHours($commerceId)
    {
        $hours = Order::where('commerce_id', $commerceId)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders')
            ->groupBy('hour')
            ->orderByDesc('orders')
            ->limit(5)
            ->get();

        $total = $hours->sum('orders');

        return $hours->map(function($item) use ($total) {
            return [
                'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00',
                'orders' => $item->orders,
                'percentage' => $total > 0 ? round(($item->orders / $total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function getTopProducts($commerceId)
    {
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.commerce_id', $commerceId)
            ->where('orders.status', 'delivered')
            ->select('products.id', 'products.name', 
                DB::raw('SUM(order_items.quantity) as sales'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sales')
            ->limit(10)
            ->get();

        return $topProducts->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'sales' => $item->sales,
                'revenue' => round($item->revenue, 2),
            ];
        })->toArray();
    }

    private function getProductsByCategory($commerceId)
    {
        // Por ahora retornar estructura básica
        // TODO: Implementar cuando haya categorías en productos
        return [];
    }

    private function getNewVsReturningCustomers($commerceId)
    {
        $totalCustomers = Order::where('commerce_id', $commerceId)
            ->distinct('profile_id')
            ->count('profile_id');
        
        $newCustomers = Order::where('commerce_id', $commerceId)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->distinct('profile_id')
            ->count('profile_id');
        
        $returningCustomers = $totalCustomers - $newCustomers;

        return [
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'retention_rate' => $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0,
        ];
    }

    private function getTopCustomers($commerceId)
    {
        $topCustomers = Order::where('commerce_id', $commerceId)
            ->where('status', 'delivered')
            ->selectRaw('profile_id, COUNT(*) as orders, SUM(total) as total_spent')
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

    private function getAveragePreparationTime($commerceId)
    {
        // Calcular tiempo promedio entre 'paid' y 'shipped'
        // Por ahora retornar estimado basado en timestamps
        $orders = Order::where('commerce_id', $commerceId)
            ->whereIn('status', ['shipped', 'delivered'])
            ->whereNotNull('updated_at')
            ->get();

        if ($orders->isEmpty()) {
            return 15.0; // Tiempo por defecto
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($orders as $order) {
            $preparationTime = $order->created_at->diffInMinutes($order->updated_at);
            if ($preparationTime > 0 && $preparationTime < 120) { // Filtrar valores anómalos
                $totalMinutes += $preparationTime;
                $count++;
            }
        }

        return $count > 0 ? $totalMinutes / $count : 15.0;
    }
}
