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
        return Order::where('delivery_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->with('items', 'commerce')
            ->get();
    }

    public function accept($id)
    {
        $order = Order::where('estado', 'pendiente')
            ->whereNull('delivery_id')
            ->findOrFail($id);

        $order->delivery_id = Auth::id();
        $order->estado = 'aceptado';
        $order->save();

        return response()->json(['message' => 'Pedido aceptado']);
    }

    public function markAsDelivered($id)
    {
        $order = Order::where('delivery_id', Auth::id())
            ->findOrFail($id);

        $order->estado = 'entregado';
        $order->save();

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
