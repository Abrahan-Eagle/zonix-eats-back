<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\PaymentValidated;
use App\Services\NotificationService;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

class OrderNotificationSubscriber
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle order created events.
     */
    public function onOrderCreated(OrderCreated $event)
    {
        Log::debug('OrderNotificationSubscriber: Handling OrderCreated event', ['order_id' => $event->order->id]);
        $order = $event->order;

        $commerce = $order->commerce;

        if ($commerce && $commerce->profile_id) {
            $orderNumber = $order->order_number ?? $order->id;
            $this->notificationService->notify(
                $commerce->profile_id,
                'Nuevo Pedido Recibido',
                "Has recibido un nuevo pedido #{$orderNumber}.",
                'commerce_order',
                ['order_id' => (string)$order->id, 'order_number' => $order->order_number ?? (string)$order->id]
            );
        }
    }

    /**
     * Handle order status changed events.
     */
    public function onOrderStatusChanged(OrderStatusChanged $event)
    {
        $order = $event->order;
        $status = $order->status;
        $profileId = $order->profile_id;

        $messages = [
            'preparing' => 'Tu pedido está siendo preparado.',
            'ready' => 'Tu pedido está listo para ser retirado/enviado.',
            'shipped' => 'Tu pedido ya salió del comercio.',
            'out_for_delivery' => 'Tu pedido está en camino a tu dirección.',
            'delivered' => '¡Pedido entregado! Esperamos que lo disfrutes.',
            'cancelled' => 'Tu pedido ha sido cancelado.',
            'pending_payment' => 'Tu pedido ha sido aprobado. Por favor procede a realizar el pago.',
        ];

        Log::debug('OrderNotificationSubscriber: Handling OrderStatusChanged event', [
            'order_id' => $order->id,
            'status' => $status,
            'has_message' => isset($messages[$status]),
        ]);


        if (isset($messages[$status])) {
            $this->notificationService->notify(
                $profileId,
                'Actualización de Pedido',
                $messages[$status],
                'order',
                ['order_id' => (string)$order->id, 'status' => $status]
            );
        }

        // Si se asignó un repartidor, notificar al repartidor
        if ($status === 'searching_delivery' && $order->delivery_agent_id) {
            // Esto suele ocurrir en otros eventos, pero por si acaso
        }
    }

    /**
     * Handle payment validated events.
     */
    public function onPaymentValidated(PaymentValidated $event)
    {
        Log::debug('OrderNotificationSubscriber: Handling PaymentValidated event', [
            'order_id' => $event->order->id,
            'is_validated' => $event->isValidated,
        ]);
        $order = $event->order;

        $isValidated = $event->isValidated;
        $orderNumber = $order->order_number ?? $order->id;

        if ($isValidated) {
            $this->notificationService->notify(
                $order->profile_id,
                'Pago Validado',
                "El pago de tu pedido #{$orderNumber} ha sido validado correctamente.",
                'order',
                ['order_id' => (string)$order->id]
            );
        } else {
            // Si el pago es rechazado, el controlador suele pasar una razón opcional o podemos sacarla del pedido si se guardó
            $this->notificationService->notify(
                $order->profile_id,
                'Pago Rechazado',
                "El comprobante de pago de tu pedido #{$orderNumber} ha sido rechazado.",
                'order',
                ['order_id' => (string)$order->id]
            );
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            OrderCreated::class => 'onOrderCreated',
            OrderStatusChanged::class => 'onOrderStatusChanged',
            PaymentValidated::class => 'onPaymentValidated',
        ];
    }
}
