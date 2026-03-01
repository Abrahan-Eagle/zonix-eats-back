<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
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
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderTracking($orderId)
    {
        $user = Auth::user();
        if (!$user?->profile) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        $order = Order::where('profile_id', $user->profile->id)
            ->where('id', $orderId)
            ->with(['orderDelivery.agent', 'commerce', 'profile.addresses'])
            ->first();

        if (!$order) {
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
            $base = rtrim(config('zonix.osrm_base_url', 'http://router.project-osrm.org'), '/');
            // OSRM: lng,lat;lng,lat;lng,lat (origen → waypoint → destino)
            $coords = "{$dlng},{$dlat};{$waypointLng},{$waypointLat};{$clng},{$clat}";
            $osrmUrl = "{$base}/route/v1/driving/{$coords}";
            try {
                $response = Http::timeout(8)->get($osrmUrl, ['overview' => 'full', 'geometries' => 'geojson']);
                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['routes'][0]['geometry']['coordinates'])) {
                        $polyline = [];
                        foreach ($data['routes'][0]['geometry']['coordinates'] as $c) {
                            $polyline[] = ['lat' => (float) $c[1], 'lng' => (float) $c[0]];
                        }
                        $tracking['routes']['to_customer'] = $polyline;
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('OSRM route with waypoint: ' . $e->getMessage());
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
            ],
            'tracking' => $tracking,
        ]);
    }

    /**
     * Actualizar ubicación del delivery (llamado por el repartidor).
     * Acepta lat/lng o latitude/longitude.
     *
     * @param Request $request
     * @param int $orderId
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