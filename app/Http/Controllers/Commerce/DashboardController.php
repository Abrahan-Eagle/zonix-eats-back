<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            $commerceId = $commerce->id;

            // Órdenes pendientes
            $pendingOrders = Order::where('commerce_id', $commerceId)
                ->whereIn('status', ['paid', 'preparing'])
                ->count();

            // Órdenes de hoy
            $todayOrders = Order::where('commerce_id', $commerceId)
                ->whereDate('created_at', today())
                ->count();

            // Ingresos de hoy
            $todayRevenue = Order::where('commerce_id', $commerceId)
                ->where('status', 'delivered')
                ->whereDate('created_at', today())
                ->sum('total');

            // Total de productos
            $totalProducts = Product::where('commerce_id', $commerceId)->count();

            // Productos activos (contar todos si no hay columna status)
            $activeProducts = $totalProducts;

            // Últimas órdenes
            try {
                $recentOrders = Order::where('commerce_id', $commerceId)
                    ->with(['profile', 'items'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($order) {
                        try {
                            $customerName = 'Cliente';
                            if ($order->profile) {
                                $firstName = $order->profile->firstName ?? '';
                                $lastName = $order->profile->lastName ?? '';
                                $customerName = trim($firstName . ' ' . $lastName) ?: 'Cliente';
                            }
                            
                            $itemsCount = 0;
                            try {
                                $itemsCount = $order->items ? $order->items->count() : 0;
                            } catch (\Exception $e) {
                                $itemsCount = 0;
                            }
                            
                            return [
                                'id' => $order->id,
                                'status' => $order->status,
                                'total' => $order->total,
                                'customer_name' => $customerName,
                                'created_at' => $order->created_at->toIso8601String(),
                                'items_count' => $itemsCount,
                            ];
                        } catch (\Exception $e) {
                            return [
                                'id' => $order->id,
                                'status' => $order->status ?? 'unknown',
                                'total' => $order->total ?? 0,
                                'customer_name' => 'Cliente',
                                'created_at' => $order->created_at ? $order->created_at->toIso8601String() : now()->toIso8601String(),
                                'items_count' => 0,
                            ];
                        }
                    });
            } catch (\Exception $e) {
                $recentOrders = [];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_orders' => $pendingOrders,
                    'today_orders' => $todayOrders,
                    'today_revenue' => round($todayRevenue, 2),
                    'total_products' => $totalProducts,
                    'active_products' => $activeProducts,
                    'recent_orders' => $recentOrders,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}
