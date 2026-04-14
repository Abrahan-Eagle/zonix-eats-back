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
                ['order_id' => (string) $order->id, 'order_number' => $order->order_number ?? (string) $order->id]
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
            'pending_payment' => 'Tu pedido fue creado. Por favor sube el comprobante de pago.',
            'paid' => 'Tu pago ha sido confirmado. El comercio procesará tu pedido.',
            'processing' => 'Tu pedido está siendo preparado.',
            'shipped' => 'Tu pedido va en camino.',
            'delivered' => '¡Pedido entregado! Esperamos que lo disfrutes.',
            'cancelled' => 'Tu pedido ha sido cancelado.',
        ];

        Log::debug('OrderNotificationSubscriber: Handling OrderStatusChanged event', [
            'order_id' => $order->id,
            'status' => $status,
            'has_message' => isset($messages[$status]),
        ]);

        if (isset($messages[$status])) {
            $body = $messages[$status];
            if ($status === 'shipped' && ($order->delivery_type ?? '') === 'pickup') {
                $body = 'Tu pedido está listo para recoger en el comercio.';
            }

            $this->notificationService->notify(
                $profileId,
                'Actualización de Pedido',
                $body,
                'order',
                ['order_id' => (string) $order->id, 'status' => $status]
            );
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
                ['order_id' => (string) $order->id]
            );
        } else {
            // Si el pago es rechazado, el controlador suele pasar una razón opcional o podemos sacarla del pedido si se guardó
            $this->notificationService->notify(
                $order->profile_id,
                'Pago Rechazado',
                "El comprobante de pago de tu pedido #{$orderNumber} ha sido rechazado.",
                'order',
                ['order_id' => (string) $order->id]
            );
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
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
