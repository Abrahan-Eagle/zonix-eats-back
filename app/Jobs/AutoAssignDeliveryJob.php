<?php

namespace App\Jobs;

use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Services\DeliveryFeeService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cuando el comercio marca la orden como "processing", intenta auto-asignar al agente más cercano.
 * Notifica al agente por FCM; programa timeout 60s para notificar a la empresa si nadie acepta.
 */
class AutoAssignDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $order = Order::with(['commerce.addresses', 'deliveryCompany'])->find($this->orderId);
        if (! $order || ! in_array($order->status, ['processing', 'shipped'])) {
            return;
        }
        if ($order->orderDelivery) {
            return; // Ya asignada
        }
        $company = $order->deliveryCompany;
        if (! $company) {
            return;
        }

        $commerce = $order->commerce;
        $commerceAddress = $commerce->addresses()->whereNotNull('latitude')->whereNotNull('longitude')->first();
        $commerceLat = (float) ($commerceAddress?->latitude ?? config('zonix.default_commerce_lat', 10.1620));
        $commerceLng = (float) ($commerceAddress?->longitude ?? config('zonix.default_commerce_lng', -68.0074));

        $agentIds = DeliveryAgent::where('company_id', $company->id)->pluck('id')->toArray();
        if (empty($agentIds)) {
            $this->notifyCompanyPending($order, $notificationService);

            return;
        }

        $candidates = DeliveryAgent::whereIn('id', $agentIds)
            ->where('working', true)
            ->with('profile.user')
            ->get()
            ->filter(function ($agent) {
                $hasActive = OrderDelivery::where('agent_id', $agent->id)
                    ->whereHas('order', fn ($q) => $q->whereIn('status', ['processing', 'shipped']))
                    ->exists();

                return ! $hasActive;
            })
            ->map(function ($agent) use ($commerceLat, $commerceLng) {
                $lat = $agent->current_latitude ?? $commerceLat;
                $lng = $agent->current_longitude ?? $commerceLng;
                $distanceKm = DeliveryFeeService::distanceKm($commerceLat, $commerceLng, (float) $lat, (float) $lng);

                return ['agent' => $agent, 'distance_km' => $distanceKm];
            })
            ->sortBy('distance_km')
            ->values();

        $nearest = $candidates->first();
        if (! $nearest) {
            $this->notifyCompanyPending($order, $notificationService);

            return;
        }

        $agent = $nearest['agent'];
        $profile = $agent->profile;
        if (! $profile) {
            AutoAssignTimeoutJob::dispatch($this->orderId)->delay(now()->addSeconds(60));

            return;
        }

        // Notificar al agente (Pusher ya se dispara con OrderStatusChanged; la orden sigue "shipped" sin asignar hasta que acepte o la company asigne)
        // Enviar notificación push para que el agente vea "Nueva orden para aceptar"
        $notificationService->notify(
            $profile->id,
            'Nueva orden para entregar',
            'Orden #'.($order->order_number ?? $order->id).' lista para recoger. Tienes 60 segundos para aceptar.',
            'order',
            ['order_id' => $order->id, 'action' => 'accept_order']
        );

        // Programar timeout: si en 60s no hay orderDelivery, notificar a la empresa
        AutoAssignTimeoutJob::dispatch($this->orderId)->delay(now()->addSeconds(60));
    }

    private function notifyCompanyPending(Order $order, NotificationService $notificationService): void
    {
        $company = $order->deliveryCompany;
        if (! $company || ! $company->profile_id) {
            return;
        }
        $notificationService->notify(
            $company->profile_id,
            'Orden pendiente de asignación',
            'Orden #'.($order->order_number ?? $order->id).' no tiene repartidores disponibles. Asígnala manualmente.',
            'order',
            ['order_id' => $order->id, 'action' => 'assign_order']
        );
        event(new \App\Events\OrderPendingAssignment($order));
    }
}
