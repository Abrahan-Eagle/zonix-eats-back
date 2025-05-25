<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $order = Order::create([
            'user_id' => Auth::id(),
            'commerce_id' => $request->commerce_id,
            'tipo_entrega' => $request->tipo_entrega,
            'estado' => 'pendiente_pago',
            'total' => $request->total,
            'comprobante_url' => $request->comprobante_url ?? null,
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
            ]);
        }

        return response()->json(['message' => 'Orden creada exitosamente', 'order' => $order], 201);
    }

    public function myOrders()
    {
        return Order::with('orderItems')->where('user_id', Auth::id())->get();
    }


}
