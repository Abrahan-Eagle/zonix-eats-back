<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Routing\RouteCalculationService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    protected $trackingService;

    protected RouteCalculationService $routeCalculation;

    public function __construct(TrackingService $trackingService, RouteCalculationService $routeCalculation)
    {
        $this->trackingService = $trackingService;
        $this->routeCalculation = $routeCalculation;
    }

    /**
     * Obtener información de tracking para una orden.
     *
     * En producción los datos vienen siempre de la base de datos / GPS:
     * - Repartidor: orderDelivery.agent.current_latitude/longitude (actualizados por la app
     *   del repartidor cuando tiene el GPS encendido y envía ubicación).
     * - Cliente (destino): order.profile.addresses (lat/long guardados cuando el usuario
     *   guarda su dirección desde el celular o el mapa).
     * - Comercio: commerce.addresses (lat/long del establecimiento).
     * No se usan coordenadas fijas para usuarios reales; los fallbacks solo aplican cuando
     * no hay datos en BD (ej. comercio sin dirección guardada).
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderTracking($orderId)
    {
        $user = Auth::user();
        if (! $user?->profile) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        $order = Order::where('profile_id', $user->profile->id)
            ->where('id', $orderId)
            ->with(['orderDelivery.agent', 'commerce', 'profile.addresses'])
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        }

        $deliveryLat = null;
        $deliveryLng = null;
        $orderDelivery = $order->orderDelivery;
        if ($orderDelivery?->agent) {
            $agent = $orderDelivery->agent;
            if ($agent->current_latitude !== null && $agent->current_longitude !== null) {
                $deliveryLat = (float) $agent->current_latitude;
                $deliveryLng = (float) $agent->current_longitude;
            }
        }

        $commerce = $order->commerce;
        // Fallback solo si el comercio no tiene dirección con coords en BD (en producción deberían tenerla)
        $commerceLat = config('zonix.default_commerce_lat');
        $commerceLon = config('zonix.default_commerce_lng');
        if ($commerce && $commerce->addresses()->exists()) {
            $addr = $commerce->addresses()->first();
            if ($addr && $addr->latitude !== null && $addr->longitude !== null) {
                $commerceLat = (float) $addr->latitude;
                $commerceLon = (float) $addr->longitude;
            }
        }

        // Destino de entrega: 1) coords guardadas en la orden (GPS/casa/otra ubicación elegida), 2) sino dirección del perfil.
        $customerLat = null;
        $customerLon = null;
        if ($order->delivery_latitude !== null && $order->delivery_longitude !== null) {
            $customerLat = (float) $order->delivery_latitude;
            $customerLon = (float) $order->delivery_longitude;
        } elseif ($order->profile && $order->profile->addresses()->exists()) {
            $customerAddr = $order->profile->addresses()->where('is_default', true)->first()
                ?? $order->profile->addresses()->first();
            if ($customerAddr && $customerAddr->latitude !== null && $customerAddr->longitude !== null) {
                $customerLat = (float) $customerAddr->latitude;
                $customerLon = (float) $customerAddr->longitude;
            }
        }

        // Solo datos reales: repartidor desde GPS (BD), cliente desde dirección guardada. No inventar coords.
        $orderData = [
            'id' => $orderId,
            'status' => $order->status,
            'commerce_lat' => $commerceLat,
            'commerce_lon' => $commerceLon,
            'delivery_lat' => $deliveryLat,
            'delivery_lon' => $deliveryLng,
            'customer_lat' => $customerLat,
            'customer_lon' => $customerLon,
        ];

        $tracking = $this->trackingService->getOrderTracking($orderData);

        // Ruta repartidor → cliente: waypoint opcional (ej. para demo o un punto fijo). En producción suele ir vacío: ruta directa con coords reales de BD/GPS.
        $waypointLat = config('zonix.tracking_waypoint_lat');
        $waypointLng = config('zonix.tracking_waypoint_lng');
        $dlat = $orderData['delivery_lat'];
        $dlng = $orderData['delivery_lon'];
        $clat = $orderData['customer_lat'];
        $clng = $orderData['customer_lon'];
        if ($waypointLat !== null && $waypointLng !== null && $dlat !== null && $dlng !== null && $clat !== null && $clng !== null) {
            try {
                $routeResult = $this->routeCalculation->calculateViaPoints([
                    ['lat' => (float) $dlat, 'lng' => (float) $dlng],
                    ['lat' => (float) $waypointLat, 'lng' => (float) $waypointLng],
                    ['lat' => (float) $clat, 'lng' => (float) $clng],
                ], 'driving');
                if (! empty($routeResult['polyline'])) {
                    $tracking['routes']['to_customer'] = $routeResult['polyline'];
                }
            } catch (\Throwable $e) {
                Log::debug('Route with waypoint failed: '.$e->getMessage());
            }
        }

        // ETA en minutos solo si hay dato real (orden o cálculo con coords reales). Sin inventar.
        $etaMinutes = $order->estimated_delivery_time !== null
            ? (int) $order->estimated_delivery_time
            : ($tracking['estimated_times']['to_customer'] ?? null);

        $routes = $tracking['routes'] ?? [];
        $deliveryLoc = $tracking['delivery_location'] ?? null;
        $customerLoc = $tracking['customer_location'] ?? null;
        // Ruta solo si hay coords reales de repartidor y cliente (no dibujar con datos inventados)
        $routeToCustomer = ($deliveryLat !== null && $customerLat !== null) ? ($routes['to_customer'] ?? []) : [];

        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $deliveryLoc['lat'] ?? null,
                'longitude' => $deliveryLoc['lng'] ?? null,
                'delivery_location' => $deliveryLoc,
                'commerce_location' => $tracking['commerce_location'] ?? null,
                'customer_latitude' => $customerLat,
                'customer_longitude' => $customerLon,
                'estimated_times' => $tracking['estimated_times'] ?? null,
                'estimated_delivery_time_minutes' => $etaMinutes,
                'route_to_customer' => $routeToCustomer,
                // Contrato unificado: tracking incluye timeline de estados para evitar doble consulta.
                'timeline' => $this->generateTimeline($order),
            ],
            'tracking' => $tracking,
        ]);
    }

    private function getStatusInfo(string $status): array
    {
        $statusMap = [
            'pending_payment' => ['title' => 'Pendiente de Pago', 'description' => 'Tu pedido fue creado. Sube el comprobante de pago.', 'icon' => 'hourglass_empty'],
            'paid' => ['title' => 'Pago Confirmado', 'description' => 'El comercio validó tu pago y procesará el pedido.', 'icon' => 'check_circle'],
            'processing' => ['title' => 'Preparando tu Pedido', 'description' => 'El restaurante está preparando tu comida.', 'icon' => 'restaurant'],
            'shipped' => ['title' => 'En Camino', 'description' => 'El repartidor está llevando tu pedido.', 'icon' => 'directions_car'],
            'delivered' => ['title' => 'Entregado', 'description' => 'Tu pedido ha sido entregado exitosamente.', 'icon' => 'done_all'],
            'cancelled' => ['title' => 'Cancelado', 'description' => 'Tu pedido ha sido cancelado.', 'icon' => 'cancel'],
        ];

        return $statusMap[$status] ?? $statusMap['pending_payment'];
    }

    private function generateTimeline(Order $order): array
    {
        $history = DB::table('order_status_history')
            ->where('order_id', $order->id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get(['to_status', 'occurred_at']);

        $timeline = [];
        $seen = [];

        foreach ($history as $entry) {
            $status = (string) $entry->to_status;
            if (isset($seen[$status])) {
                continue;
            }
            $info = $this->getStatusInfo($status);
            $timeline[] = [
                'status' => $status,
                'title' => $info['title'],
                'description' => $info['description'],
                'timestamp' => $entry->occurred_at,
                'completed' => true,
                'icon' => $info['icon'],
            ];
            $seen[$status] = true;
        }

        $currentStatus = (string) $order->status;
        if (! isset($seen[$currentStatus])) {
            $info = $this->getStatusInfo($currentStatus);
            $timeline[] = [
                'status' => $currentStatus,
                'title' => $info['title'],
                'description' => $info['description'],
                'timestamp' => $order->status_updated_at ?? $order->updated_at,
                'completed' => true,
                'icon' => $info['icon'],
            ];
            $seen[$currentStatus] = true;
        }

        $futureStates = ['pending_payment', 'paid', 'processing', 'shipped', 'delivered'];
        $currentIndex = array_search($currentStatus, $futureStates, true);
        if ($currentIndex !== false && $currentStatus !== 'cancelled') {
            for ($i = $currentIndex + 1; $i < count($futureStates); $i++) {
                $futureStatus = $futureStates[$i];
                if (isset($seen[$futureStatus])) {
                    continue;
                }
                $futureInfo = $this->getStatusInfo($futureStatus);
                $timeline[] = [
                    'status' => $futureStatus,
                    'title' => $futureInfo['title'],
                    'description' => $futureInfo['description'],
                    'timestamp' => null,
                    'completed' => false,
                    'icon' => $futureInfo['icon'],
                ];
            }
        }

        return $timeline;
    }

    /**
     * Actualizar ubicación del delivery (llamado por el repartidor).
     * Acepta lat/lng o latitude/longitude.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDeliveryLocation(Request $request, $orderId)
    {
        $lat = $request->input('lat') ?? $request->input('latitude');
        $lng = $request->input('lng') ?? $request->input('longitude');

        $request->merge([
            'lat' => $lat,
            'lng' => $lng,
        ]);

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $order = Order::where('id', $orderId)->with('orderDelivery.agent')->first();
        if ($order?->orderDelivery?->agent) {
            $order->orderDelivery->agent->update([
                'current_latitude' => $validated['lat'],
                'current_longitude' => $validated['lng'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ubicación actualizada',
            'data' => ['latitude' => (float) $validated['lat'], 'longitude' => (float) $validated['lng']],
        ]);
    }
}
