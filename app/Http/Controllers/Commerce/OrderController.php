<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
  public function index()
    {
        return Order::with('orderItems')
            ->where('commerce_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus($id, $status)
    {
        $order = Order::where('commerce_id', Auth::id())->findOrFail($id);
        $order->estado = $status;
        $order->save();

        return response()->json(['message' => 'Estado de la orden actualizado']);
    }
}
