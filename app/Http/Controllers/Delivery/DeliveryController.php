<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DeliveryAgent;
use App\Models\OrderDelivery;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DeliveryController extends Controller
{
    /**
     * Get available orders for delivery
     */
    public function getAvailableOrders()
    {
        try {
            $availableOrders = Order::with(['commerce', 'profile', 'items'])
                ->whereIn('status', ['paid', 'processing'])
                ->whereDoesntHave('orderDelivery')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $availableOrders
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching available orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available orders'
            ], 500);
        }
    }

    /**
     * Get orders assigned to delivery agent
     */
    public function getAssignedOrders($deliveryAgentId)
    {
        try {
            $assignedOrders = Order::with(['commerce', 'profile', 'items', 'delivery'])
                ->whereHas('delivery', function ($query) use ($deliveryAgentId) {
                    $query->where('agent_id', $deliveryAgentId);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $assignedOrders
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching assigned orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching assigned orders'
            ], 500);
        }
    }

    /**
     * Accept order for delivery
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Validar que la orden está en estado 'shipped' (antes era 'paid' o 'preparing')
            if ($order->status !== 'shipped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not available for delivery'
                ], 400);
            }

            $deliveryAgent = DeliveryAgent::where('profile_id', Auth::user()->profile->id)->first();
            
            if (!$deliveryAgent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found'
                ], 404);
            }

            // Verificar que la orden no esté ya asignada
            if ($order->orderDelivery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order already assigned'
                ], 400);
            }

            // Create delivery assignment
            OrderDelivery::create([
                'order_id' => $orderId,
                'agent_id' => $deliveryAgent->id,
                'status' => 'assigned',
                'costo_envio' => $order->delivery_fee ?? 0,
                'notas' => $request->input('notes', '')
            ]);

            // Update order status
            // El estado ya debe estar en 'shipped' cuando el comercio marca como enviado
            // No cambiar estado aquí, solo crear OrderDelivery

            return response()->json([
                'message' => 'Orden aceptada',
                'success' => true,
                'data' => $order->load(['commerce', 'profile', 'items', 'delivery'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error accepting order'
            ], 500);
        }
    }

    /**
     * Update delivery location
     */
    public function updateLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            $deliveryAgent = DeliveryAgent::where('profile_id', Auth::user()->profile->id)->first();
            
            if (!$deliveryAgent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found'
                ], 404);
            }

            $deliveryAgent->update([
                'current_latitude' => $request->latitude,
                'current_longitude' => $request->longitude,
                'last_location_update' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating location: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating location'
            ], 500);
        }
    }

    /**
     * Get delivery statistics
     */
    public function getStatistics($deliveryAgentId)
    {
        try {
            $deliveryAgent = DeliveryAgent::findOrFail($deliveryAgentId);
            
            $totalDeliveries = OrderDelivery::where('agent_id', $deliveryAgentId)->count();
            $completedDeliveries = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')->count();
            $cancelledDeliveries = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'cancelled')->count();

            $totalEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->sum('costo_envio') ?? 0;

            // Calcular average_rating desde reviews
            $averageRating = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $deliveryAgentId)
                ->avg('rating') ?? 0;

            // Contar total_reviews
            $totalReviews = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $deliveryAgentId)
                ->count();

            // Calcular average_delivery_time (tiempo promedio desde asignación hasta entrega)
            $deliveryTimes = Order::whereHas('orderDelivery', function($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId);
                })
                ->where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
                ->value('avg_minutes') ?? 0;

            $averageDeliveryTime = round($deliveryTimes, 1);

            // Calcular on_time_deliveries y late_deliveries
            // Asumiendo que una entrega es "a tiempo" si se completa en menos de 45 minutos
            $onTimeDeliveries = Order::whereHas('orderDelivery', function($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId);
                })
                ->where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('TIMESTAMPDIFF(MINUTE, created_at, updated_at) as delivery_minutes')
                ->get()
                ->filter(function($order) {
                    return $order->delivery_minutes <= 45;
                })
                ->count();

            $lateDeliveries = $completedDeliveries - $onTimeDeliveries;

            // Calcular customer_satisfaction desde ratings
            $customerSatisfaction = $totalReviews > 0 
                ? round(($averageRating / 5) * 100, 1)
                : 0;

            $statistics = [
                'total_deliveries' => $totalDeliveries,
                'completed_deliveries' => $completedDeliveries,
                'cancelled_deliveries' => $cancelledDeliveries,
                'total_earnings' => round($totalEarnings, 2),
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
                'on_time_deliveries' => $onTimeDeliveries,
                'late_deliveries' => max(0, $lateDeliveries),
                'average_delivery_time' => $averageDeliveryTime,
                'total_distance' => 0, // Requiere tracking GPS real
                'fuel_efficiency' => 0, // Requiere datos de vehículo
                'customer_satisfaction' => $customerSatisfaction,
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery statistics'
            ], 500);
        }
    }

    /**
     * Report delivery issue
     */
    public function reportIssue(Request $request, $orderId)
    {
        try {
            $request->validate([
                'issue' => 'required|string|max:255',
                'description' => 'required|string|max:1000'
            ]);

            $order = Order::findOrFail($orderId);
            
            // TODO: Create support ticket or issue record
            Log::info('Delivery issue reported', [
                'order_id' => $orderId,
                'issue' => $request->issue,
                'description' => $request->description,
                'delivery_agent_id' => Auth::user()->profile->deliveryAgent->id ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Issue reported successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error reporting delivery issue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reporting issue'
            ], 500);
        }
    }

    /**
     * Update order status for delivery
     */
    public function updateOrderStatus($orderId, Request $request)
    {
        try {
            $order = Order::whereHas('delivery', function($query) {
                $query->where('agent_id', Auth::user()->profile->deliveryAgent->id);
            })->findOrFail($orderId);

            $request->validate([
                'status' => 'required|in:delivered'
            ]);

            $order->update(['status' => $request->status]);

            // Update delivery status as well
            if ($order->delivery) {
                $order->orderDelivery->update(['status' => $request->status === 'delivered' ? 'delivered' : 'shipped']);
                
                // Si se marca como entregado, actualizar estado de orden
                if ($request->status === 'delivered') {
                    $order->update(['status' => 'delivered']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado de la orden actualizado',
                'data' => $order->load(['commerce', 'profile', 'items', 'delivery'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de orden de delivery: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar estado de orden'
            ], 500);
        }
    }

    /**
     * Get delivery history for a delivery agent
     */
    public function getHistory($deliveryAgentId, Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = Order::with(['commerce', 'profile', 'items', 'delivery'])
                ->whereHas('delivery', function($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId);
                })
                ->whereIn('status', ['delivered', 'cancelled']);

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery history'
            ], 500);
        }
    }

    /**
     * Get delivery earnings for a delivery agent
     */
    public function getEarnings($deliveryAgentId, Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered');

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $deliveries = $query->with('order')->get();

            $totalEarnings = $deliveries->sum('costo_envio');
            $totalDeliveries = $deliveries->count();
            
            $deliveryTimes = [];
            foreach ($deliveries as $delivery) {
                if ($delivery->order && $delivery->order->created_at && $delivery->updated_at) {
                    $deliveryTimes[] = $delivery->updated_at->diffInMinutes($delivery->order->created_at);
                }
            }
            $averageDeliveryTime = count($deliveryTimes) > 0 
                ? array_sum($deliveryTimes) / count($deliveryTimes) 
                : 0;

            // Calculate today's earnings
            $todayEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->whereDate('updated_at', today())
                ->sum('costo_envio');

            // Calculate weekly earnings
            $weeklyEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('costo_envio');

            // Calculate monthly earnings
            $monthlyEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('costo_envio');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_earnings' => $totalEarnings,
                    'total_deliveries' => $totalDeliveries,
                    'average_delivery_time' => round($averageDeliveryTime, 2),
                    'today_earnings' => $todayEarnings,
                    'weekly_earnings' => $weeklyEarnings,
                    'monthly_earnings' => $monthlyEarnings,
                    'delivery_fees' => $deliveries->pluck('costo_envio')->toArray(),
                    'delivery_dates' => $deliveries->pluck('updated_at')->map(function($date) {
                        return $date->toIso8601String();
                    })->toArray(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery earnings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery earnings'
            ], 500);
        }
    }

    /**
     * Get delivery routes for a delivery agent
     */
    public function getRoutes($deliveryAgentId)
    {
        try {
            $assignedOrders = Order::with(['commerce', 'profile', 'delivery'])
                ->whereHas('delivery', function($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId)
                      ->whereIn('status', ['assigned', 'shipped']);
                })
                ->get();

            // Group orders by route (simplified - in production would use routing algorithm)
            $routes = [];
            foreach ($assignedOrders as $index => $order) {
                $startLat = $order->commerce->latitude ?? -12.0464;
                $startLng = $order->commerce->longitude ?? -77.0428;
                
                $deliveryAddress = $order->delivery_address ? json_decode($order->delivery_address, true) : null;
                $endLat = $deliveryAddress['lat'] ?? $startLat;
                $endLng = $deliveryAddress['lng'] ?? $startLng;
                
                // Calcular distancia y tiempo real usando OSRM
                $distance = 5.0; // Default
                $estimatedTime = 30; // Default (minutos)
                
                try {
                    $osrmUrl = "http://router.project-osrm.org/route/v1/driving/$startLng,$startLat;$endLng,$endLat";
                    $response = Http::timeout(5)->get($osrmUrl, [
                        'overview' => 'false',
                    ]);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        if (!empty($data['routes']) && !empty($data['routes'][0])) {
                            $routeData = $data['routes'][0];
                            $distance = round($routeData['distance'] / 1000, 2); // Convertir metros a km
                            $estimatedTime = round($routeData['duration'] / 60); // Convertir segundos a minutos
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error calculando ruta OSRM: ' . $e->getMessage());
                    // Usar valores por defecto si falla
                }
                
                $routes[] = [
                    'id' => $index + 1,
                    'name' => 'Ruta ' . ($index + 1),
                    'orders' => [$order->id],
                    'estimated_time' => $estimatedTime,
                    'total_distance' => $distance,
                    'status' => $order->delivery->status ?? 'assigned',
                    'start_location' => [
                        'lat' => $startLat,
                        'lng' => $startLng
                    ],
                    'end_location' => [
                        'lat' => $endLat,
                        'lng' => $endLng
                    ],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $routes
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery routes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery routes'
            ], 500);
        }
    }
} 