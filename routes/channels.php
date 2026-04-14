<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Canal para usuario específico
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal para órdenes específicas
Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (! $order) {
        return false;
    }

    // Usuario puede escuchar si es el comprador (profile_id), comercio o repartidor
    return $order->profile_id === $user->profile?->id ||
           $order->commerce_id === $user->profile?->commerce?->id ||
           $order->orderDelivery?->agent_id === $user->profile?->deliveryAgent?->id;
});

// Canal para comercio específico
Broadcast::channel('commerce.{commerceId}', function ($user, $commerceId) {
    return $user->role === 'commerce' && $user->profile?->commerce?->id === (int) $commerceId;
});

// Canal para empresa de delivery (notificaciones de órdenes pendientes de asignación)
Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    if ($user->role !== 'delivery_company') {
        return false;
    }
    $company = \App\Models\DeliveryCompany::where('profile_id', $user->profile?->id)->first();

    return $company && (int) $company->id === (int) $companyId;
});

// Canal para repartidor específico (motorizado, o empresa viendo a sus agentes)
Broadcast::channel('delivery.{deliveryAgentId}', function ($user, $deliveryAgentId) {
    if (in_array($user->role, ['delivery', 'delivery_agent'], true)) {
        return $user->profile?->deliveryAgent?->id === (int) $deliveryAgentId;
    }
    if ($user->role === 'delivery_company') {
        $company = \App\Models\DeliveryCompany::where('profile_id', $user->profile?->id)->first();

        return $company && \App\Models\DeliveryAgent::where('company_id', $company->id)->where('id', (int) $deliveryAgentId)->exists();
    }

    return false;
});

// Canal para usuario específico (alias)
Broadcast::channel('user.{userId}', function ($user, $userId) {
    if (config('app.debug')) {
        Log::debug('Broadcasting: Authorizing user channel', ['auth_user_id' => $user->id, 'requested_user_id' => $userId]);
    }

    return (int) $user->id === (int) $userId;
});

// Canal público para órdenes (solo lectura)
Broadcast::channel('orders', function ($user) {
    return $user->role === 'commerce' || in_array($user->role, ['delivery', 'delivery_agent'], true) || $user->role === 'users';
});

// Canal de presencia para chat de órdenes
Broadcast::channel('presence-chat.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (! $order) {
        return false;
    }

    // Verificar que el usuario tiene acceso a esta orden
    $hasAccess = $order->profile_id === $user->profile?->id ||
                 $order->commerce_id === $user->profile?->commerce?->id ||
                 $order->orderDelivery?->agent_id === $user->profile?->deliveryAgent?->id;

    if ($hasAccess) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
        ];
    }

    return false;
});
