<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\DeliveryAgent;
use App\Models\Review;
use Illuminate\Support\Facades\Log;

class OrderTrackingController extends Controller
{
    /**
     * Obtener estado actual del pedido
     */
    public function getOrderStatus($orderId): JsonResponse
    {
        try {
            $order = Order::with(['commerce', 'orderDelivery.agent.profile', 'items'])
                ->findOrFail($orderId);

            $statusInfo = $this->getStatusInfo($order->status);
            
            $trackingData = [
                'order_id' => $order->id,
                'status' => $order->status,
                'status_info' => $statusInfo,
                'estimated_delivery_time' => $this->calculateEstimatedDeliveryTime($order),
                'current_step' => $this->getCurrentStep($order->status),
                'total_steps' => 5,
                'restaurant' => [
                    'name' => $order->commerce->name ?? 'Restaurante',
                    'address' => $order->commerce->address ?? 'N/A',
                    'phone' => $order->commerce->phone ?? 'N/A'
                ],
                'delivery_agent' => $order->orderDelivery?->agent ? [
                    'name' => $order->orderDelivery->agent->profile->firstName ?? 'Repartidor',
                    'phone' => $order->orderDelivery->agent->phone ?? 'N/A',
                    'vehicle' => $order->orderDelivery->agent->vehicle_type ?? 'Moto',
                    'current_location' => [
                        'lat' => $order->orderDelivery->agent->current_latitude ?? 0,
                        'lng' => $order->orderDelivery->agent->current_longitude ?? 0
                    ]
                ] : null,
                'order_details' => [
                    'total_items' => $order->items->count(),
                    'total_amount' => (float) $order->total,
                    'delivery_address' => $order->delivery_address,
                    'special_instructions' => $order->notes ?? ''
                ],
                'timeline' => $this->generateTimeline($order)
            ];

            return response()->json([
                'success' => true,
                'data' => $trackingData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado del pedido'
            ], 500);
        }
    }

    /**
     * Datos completos del repartidor para la vista "Detalle del Repartidor".
     * Incluye: foto, nombre, rating real, verified, entregas, años, puntualidad, vehículo, reseñas.
     */
    public function getDeliveryAgentLocation($orderId): JsonResponse
    {
        try {
            $order = Order::with('orderDelivery.agent.profile')
                ->findOrFail($orderId);

            $agent = $order->orderDelivery?->agent;
            if (!$agent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay repartidor asignado aún'
                ], 404);
            }

            $profile = $agent->profile;
            $name = trim(($profile->firstName ?? '') . ' ' . ($profile->lastName ?? '')) ?: 'Repartidor';

            // Rating y reseñas desde tabla reviews (reviewable_type = DeliveryAgent)
            $reviewsQuery = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $agent->id);
            $reviewsCount = $reviewsQuery->count();
            $avgRating = $reviewsQuery->avg('rating');
            $rating = $reviewsCount > 0 && $avgRating !== null
                ? round((float) $avgRating, 1)
                : ($agent->rating !== null ? (float) $agent->rating : null);

            // Entregas completadas (orden entregada)
            $deliveriesCount = OrderDelivery::where('agent_id', $agent->id)
                ->whereHas('order', fn ($q) => $q->where('status', 'delivered'))
                ->count();

            // Años activo desde creación del agente
            $yearsActive = $agent->created_at ? (int) $agent->created_at->diffInYears(now()) : 0;

            // Puntualidad: % de entregas sin retraso (por ahora null si no se trackea)
            $punctualityPercent = null;

            // Verificado: tiene al menos una entrega o tiene rating
            $verified = $deliveriesCount > 0 || ($rating !== null && $reviewsCount > 0);

            // Últimas reseñas con comentario para "Sobre el repartidor"
            $reviews = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $agent->id)
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get(['rating', 'comment', 'created_at'])
                ->map(fn ($r) => [
                    'rating' => (int) $r->rating,
                    'comment' => $r->comment,
                    'created_at' => $r->created_at?->toIso8601String(),
                ])
                ->values()
                ->all();

            $location = [
                'agent_id' => $agent->id,
                'name' => $name,
                'phone' => $agent->phone ?? null,
                'photo_url' => $profile->photo_users ?? null,
                'vehicle' => $agent->vehicle_type ?? 'Moto',
                'license_plate' => $agent->license_number ?? null,
                'rating' => $rating,
                'reviews_count' => $reviewsCount,
                'verified' => $verified,
                'deliveries_count' => $deliveriesCount,
                'years_active' => $yearsActive,
                'punctuality_percent' => $punctualityPercent,
                'reviews' => $reviews,
                'current_location' => [
                    'lat' => $agent->current_latitude !== null ? (float) $agent->current_latitude : null,
                    'lng' => $agent->current_longitude !== null ? (float) $agent->current_longitude : null,
                    'updated_at' => $agent->last_location_update ?? now()
                ],
                'estimated_arrival' => $this->calculateEstimatedArrival($order),
                'customer_location' => null,
            ];

            if ($order->delivery_latitude !== null && $order->delivery_longitude !== null) {
                $location['customer_location'] = [
                    'lat' => (float) $order->delivery_latitude,
                    'lng' => (float) $order->delivery_longitude,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $location
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting delivery agent location: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la ubicación del repartidor'
            ], 500);
        }
    }

    /**
     * Actualizar estado del pedido (para testing)
     */
    public function updateOrderStatus(Request $request, $orderId): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,preparing,ready,on_way,delivered,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Estado inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::findOrFail($orderId);
            $order->update([
                'status' => $request->status,
                'status_updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado del pedido actualizado',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'status_info' => $this->getStatusInfo($order->status)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado del pedido'
            ], 500);
        }
    }

    /**
     * Obtener información del estado
     */
    private function getStatusInfo(string $status): array
    {
        $statusMap = [
            'pending' => [
                'title' => 'Pedido Pendiente',
                'description' => 'Tu pedido está siendo revisado por el restaurante',
                'icon' => 'hourglass_empty',
                'color' => '#FFA726'
            ],
            'confirmed' => [
                'title' => 'Pedido Confirmado',
                'description' => 'El restaurante ha confirmado tu pedido',
                'icon' => 'check_circle',
                'color' => '#4CAF50'
            ],
            'preparing' => [
                'title' => 'Preparando tu Pedido',
                'description' => 'El restaurante está preparando tu comida',
                'icon' => 'restaurant',
                'color' => '#2196F3'
            ],
            'ready' => [
                'title' => 'Listo para Enviar',
                'description' => 'Tu pedido está listo y esperando al repartidor',
                'icon' => 'local_shipping',
                'color' => '#9C27B0'
            ],
            'on_way' => [
                'title' => 'En Camino',
                'description' => 'El repartidor está llevando tu pedido',
                'icon' => 'directions_car',
                'color' => '#FF9800'
            ],
            'delivered' => [
                'title' => 'Entregado',
                'description' => 'Tu pedido ha sido entregado exitosamente',
                'icon' => 'done_all',
                'color' => '#4CAF50'
            ],
            'cancelled' => [
                'title' => 'Cancelado',
                'description' => 'Tu pedido ha sido cancelado',
                'icon' => 'cancel',
                'color' => '#F44336'
            ]
        ];

        return $statusMap[$status] ?? $statusMap['pending'];
    }

    /**
     * Obtener paso actual del proceso
     */
    private function getCurrentStep(string $status): int
    {
        $stepMap = [
            'pending' => 1,
            'confirmed' => 2,
            'preparing' => 3,
            'ready' => 4,
            'on_way' => 5,
            'delivered' => 5,
            'cancelled' => 0
        ];

        return $stepMap[$status] ?? 1;
    }

    /**
     * Calcular tiempo estimado de entrega
     */
    private function calculateEstimatedDeliveryTime(Order $order): string
    {
        $baseTime = 30; // 30 minutos base
        
        // Ajustar según el estado
        switch ($order->status) {
            case 'pending':
            case 'confirmed':
                return now()->addMinutes($baseTime)->format('H:i');
            case 'preparing':
                return now()->addMinutes($baseTime - 10)->format('H:i');
            case 'ready':
                return now()->addMinutes($baseTime - 20)->format('H:i');
            case 'on_way':
                return now()->addMinutes(15)->format('H:i');
            default:
                return now()->addMinutes($baseTime)->format('H:i');
        }
    }

    /**
     * Calcular tiempo estimado de llegada del repartidor
     */
    private function calculateEstimatedArrival(Order $order): string
    {
        // Actualizado: usar 'shipped' en lugar de 'on_way'
        if ($order->status !== 'shipped') {
            return 'N/A';
        }

        return now()->addMinutes(15)->format('H:i');
    }

    /**
     * Generar timeline del pedido
     */
    private function generateTimeline(Order $order): array
    {
        $timeline = [];

        // Estado actual
        $timeline[] = [
            'status' => $order->status,
            'title' => $this->getStatusInfo($order->status)['title'],
            'description' => $this->getStatusInfo($order->status)['description'],
            'timestamp' => $order->status_updated_at ?? $order->created_at,
            'completed' => true,
            'icon' => $this->getStatusInfo($order->status)['icon']
        ];

        // Agregar estados futuros
        $futureStates = ['confirmed', 'preparing', 'ready', 'on_way', 'delivered'];
        $currentIndex = array_search($order->status, $futureStates);
        
        if ($currentIndex !== false) {
            for ($i = $currentIndex + 1; $i < count($futureStates); $i++) {
                $futureStatus = $futureStates[$i];
                $timeline[] = [
                    'status' => $futureStatus,
                    'title' => $this->getStatusInfo($futureStatus)['title'],
                    'description' => $this->getStatusInfo($futureStatus)['description'],
                    'timestamp' => null,
                    'completed' => false,
                    'icon' => $this->getStatusInfo($futureStatus)['icon']
                ];
            }
        }

        return $timeline;
    }
} 