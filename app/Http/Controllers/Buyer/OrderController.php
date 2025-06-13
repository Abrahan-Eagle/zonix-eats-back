<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

public function index()
    {
        $orders = Order::where('buyer_id', Auth::id())->latest()->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'commerce_id' => 'required|exists:users,id',
            'delivery_type' => 'required|in:pickup,delivery',
            'address' => 'nullable|string'
        ]);

        $order = Order::create([
            'buyer_id' => Auth::id(),
            'commerce_id' => $validated['commerce_id'],
            'delivery_type' => $validated['delivery_type'],
            'address' => $validated['address'],
            'status' => 'pending',
        ]);

        // Save products to pivot table (order_product)
        foreach ($validated['products'] as $product) {
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);
        }

        return response()->json(['message' => 'Orden creada con éxito', 'order' => $order], 201);
    }

    public function show($id)
    {
        $order = Order::where('buyer_id', Auth::id())->with('products')->findOrFail($id);
        return response()->json($order);
    }

    public function cancel($id)
    {
        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);
        if ($order->status === 'pending') {
            $order->update(['status' => 'cancelled']);
            return response()->json(['message' => 'Orden cancelada']);
        }

        return response()->json(['error' => 'No se puede cancelar esta orden'], 400);
    }

}
