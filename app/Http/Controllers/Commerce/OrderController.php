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

    /**
     * Validar o rechazar comprobante de pago de una orden.
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validarComprobante(Request $request, $id)
    {
        $request->validate([
            'accion' => 'required|in:validar,rechazar',
        ]);
        $order = \App\Models\Order::where('commerce_id', \Auth::id())->findOrFail($id);
        if (!$order->comprobante_url) {
            return response()->json(['error' => 'No hay comprobante para validar'], 400);
        }
        if ($request->accion === 'validar') {
            $order->estado = 'comprobante_validado';
        } else {
            $order->estado = 'comprobante_rechazado';
        }
        $order->save();
        return response()->json(['message' => 'Comprobante ' . $request->accion, 'estado' => $order->estado]);
    }
}
