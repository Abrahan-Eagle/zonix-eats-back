<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Obtener información de tracking para una orden.
     * Incluye ubicación actual del repartidor si existe.
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
            ->with(['orderDelivery.agent', 'commerce'])
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
        $commerceLat = 19.4326;
        $commerceLon = -99.1332;
        if ($commerce && $commerce->addresses()->exists()) {
            $addr = $commerce->addresses()->first();
            if ($addr && $addr->latitude !== null && $addr->longitude !== null) {
                $commerceLat = (float) $addr->latitude;
                $commerceLon = (float) $addr->longitude;
            }
        }

        $orderData = [
            'id' => $orderId,
            'status' => $order->status,
            'commerce_lat' => $commerceLat,
            'commerce_lon' => $commerceLon,
            'delivery_lat' => $deliveryLat ?? $commerceLat,
            'delivery_lon' => $deliveryLng ?? $commerceLon,
            'customer_lat' => $commerceLat,
            'customer_lon' => $commerceLon,
        ];

        $tracking = $this->trackingService->getOrderTracking($orderData);

        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $tracking['delivery_location']['lat'],
                'longitude' => $tracking['delivery_location']['lng'],
                'delivery_location' => $tracking['delivery_location'],
                'commerce_location' => $tracking['commerce_location'],
                'estimated_times' => $tracking['estimated_times'] ?? null,
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