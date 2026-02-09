<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Obtener las órdenes del comprador autenticado.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserOrders($perPage = 15)
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        if (!$profile) {
            return collect();
        }
        
        return Order::where('profile_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->with('commerce', 'products')
            ->paginate($perPage);
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
        $user = \App\Models\User::with('profile')->find($userId);
        $profile = $user ? $user->profile : null;
        
        if (!$profile) {
            return null;
        }
        
        return Order::where('profile_id', $profile->id)
            ->where('id', $orderId)
            ->with('products')
            ->first();
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
        $user = \App\Models\User::find($userId);
        $profile = $user ? $user->profile : null;
        
        if (!$profile) {
            return 'Usuario sin perfil';
        }
        
        $order = Order::where('profile_id', $profile->id)->find($orderId);
        if (!$order) {
            return 'Orden no encontrada';
        }
        if ($order->status === 'pending_payment') {
            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => 'Customer requested cancellation',
            ]);
            return true;
        }
        return 'No se puede cancelar esta orden';
    }

    // Aquí puedes agregar más métodos relacionados a la lógica de órdenes
}
