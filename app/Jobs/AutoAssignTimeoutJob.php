<?php

namespace App\Jobs;

use App\Models\DeliveryAssignmentTimeout;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Ejecutado 60 segundos después de intentar auto-asignar. Si la orden sigue sin repartidor, notifica a la empresa.
 */
class AutoAssignTimeoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $order = Order::with('deliveryCompany')->find($this->orderId);
        if (! $order || ! in_array($order->status, ['processing', 'shipped'])) {
            return;
        }
        if ($order->orderDelivery) {
            return; // Ya aceptó un agente o la company asignó
        }

        $company = $order->deliveryCompany;
        if (! $company || ! $company->profile_id) {
            return;
        }

        $notificationService->notify(
            $company->profile_id,
            'Orden pendiente de asignación',
            'Nadie aceptó la orden #'.($order->order_number ?? $order->id).'. Asígnala manualmente.',
            'order',
            ['order_id' => $order->id, 'action' => 'assign_order']
        );

        Log::warning('Delivery assignment timeout', [
            'event_code' => 'DELIVERY_ORDER_ASSIGNMENT_TIMEOUT',
            'order_id' => $order->id,
            'company_id' => $company->id,
            'agent_id' => null,
            'occurred_at' => now()->toISOString(),
        ]);

        DeliveryAssignmentTimeout::create([
            'order_id' => $order->id,
            'company_id' => $company->id,
            'occurred_at' => now(),
            'source' => 'auto_assign_timeout_job',
        ]);

        event(new \App\Events\OrderPendingAssignment($order));
    }
}
