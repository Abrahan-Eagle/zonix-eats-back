<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Obtener las órdenes del comprador autenticado.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserOrders()
    {
        return Order::where('user_id', Auth::id())->latest()->get();
    }

    /**
     * Crear una nueva orden con productos y datos validados.
     *
     * @param array $validated
     * @param int $userId
     * @return \App\Models\Order
     */
    public function createOrder(array $validated, $userId)
    {
        // Obtener el perfil del comprador
        $user = \App\Models\User::find($userId);
        $profile = $user ? $user->profile : null;
        if (!$profile) {
            throw new \Exception('El usuario no tiene perfil asociado.');
        }
        $order = \App\Models\Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $validated['commerce_id'],
            'user_id' => $userId,
            'tipo_entrega' => $validated['tipo_entrega'] ?? $validated['delivery_type'] ?? 'pickup',
            'estado' => 'pendiente_pago',
            'total' => $validated['total'] ?? 0,
            'notas' => $validated['notas'] ?? null,
        ]);
        foreach ($validated['products'] as $product) {
            $productModel = \App\Models\Product::find($product['id']);
            $order->products()->attach($product['id'], [
                'cantidad' => $product['quantity'],
                'precio_unitario' => $productModel ? $productModel->precio : 0
            ]);
        }
        return $order->load('products');
    }

    /**
     * Obtener detalles de una orden específica del comprador.
     *
     * @param int $orderId
     * @param int $userId
     * @return \App\Models\Order|null
     */
    public function getOrderDetails($orderId, $userId)
    {
        return Order::where('user_id', $userId)->with('products')->find($orderId);
    }

    /**
     * Cancelar una orden pendiente del comprador.
     *
     * @param int $orderId
     * @param int $userId
     * @return true|string  True si se cancela, mensaje de error si no.
     */
    public function cancelOrder($orderId, $userId)
    {
        $order = Order::where('user_id', $userId)->find($orderId);
        if (!$order) {
            return 'Orden no encontrada';
        }
        if ($order->status === 'pending') {
            $order->update(['status' => 'cancelled']);
            return true;
        }
        return 'No se puede cancelar esta orden';
    }

    // Aquí puedes agregar más métodos relacionados a la lógica de órdenes
}
