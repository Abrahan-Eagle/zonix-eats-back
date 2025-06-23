<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Commerce;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::all());
    }

    public function updateStatus($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $order->estado = $request->input('estado');
        $order->save();
        return response()->json(['message' => 'Estado actualizado', 'order' => $order]);
    }

    public function commerces()
    {
        return response()->json(Commerce::all());
    }
}
