<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        // Verificar que el usuario tenga un deliveryAgent
        if (!$profile || !$profile->deliveryAgent) {
            return response()->json(['error' => 'User is not a delivery agent'], 403);
        }
        
        // Obtener órdenes asignadas al delivery agent actual
        $orders = Order::whereHas('orderDelivery', function($query) use ($profile) {
            $query->where('agent_id', $profile->deliveryAgent->id);
        })->orderBy('created_at', 'desc')->get();

        return response()->json($orders);
    }

    public function availableOrders()
    {
        // Obtener órdenes disponibles para asignar
        $orders = Order::whereDoesntHave('orderDelivery')
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function acceptOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Verificar que la orden no esté ya asignada
        if ($order->orderDelivery) {
            return response()->json(['error' => 'Orden ya asignada'], 400);
        }

        // Asignar la orden al delivery agent
        $order->orderDelivery()->create([
            'agent_id' => Auth::user()->profile->deliveryAgent->id,
            'status' => 'assigned'
        ]);

        return response()->json(['message' => 'Orden aceptada']);
    }

    public function updateOrderStatus($orderId, Request $request)
    {
        $order = Order::whereHas('orderDelivery', function($query) {
            $query->where('agent_id', Auth::user()->profile->deliveryAgent->id);
        })->findOrFail($orderId);

        $order->update($request->all());

        return response()->json(['message' => 'Estado de la orden actualizado']);
    }

    public function updateStatus($id, Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        if (!$profile || !$profile->deliveryAgent) {
            return response()->json(['error' => 'User is not a delivery agent'], 403);
        }

        $order = Order::whereHas('orderDelivery', function($query) use ($profile) {
            $query->where('agent_id', $profile->deliveryAgent->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:assigned,in_transit,delivered'
        ]);

        $order->update([
            'status' => $validated['status']
        ]);

        return response()->json(['message' => 'Estado de la orden actualizado']);
    }

    public function markAsDelivered($id)
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        if (!$profile || !$profile->deliveryAgent) {
            return response()->json(['error' => 'User is not a delivery agent'], 403);
        }

        $order = Order::whereHas('orderDelivery', function($query) use ($profile) {
            $query->where('agent_id', $profile->deliveryAgent->id);
        })->findOrFail($id);

        $order->update([
            'status' => 'delivered'
        ]);

        return response()->json(['message' => 'Pedido entregado con éxito']);
    }

    public function toggleWorkingStatus(Request $request)
        {
            $agent = DeliveryAgent::where('user_id', Auth::id())->firstOrFail();

            $agent->update([
                'trabajando' => !$agent->trabajando
            ]);

            return response()->json([
                'message' => 'Estado actualizado',
                'trabajando' => $agent->trabajando
            ]);
        }
}
