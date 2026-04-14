<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrderPendingAssignment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $eventId;

    private string $occurredAt;

    public function __construct(
        public Order $order
    ) {
        $this->eventId = (string) Str::uuid();
        $this->occurredAt = now()->toISOString();
    }

    public function broadcastOn(): array
    {
        if (! $this->order->delivery_company_id) {
            return [];
        }

        return [
            new PrivateChannel('company.'.$this->order->delivery_company_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderPendingAssignment';
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing(['commerce']);

        return [
            'event_id' => $this->eventId,
            'schema_version' => 'v1',
            'occurred_at' => $this->occurredAt,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'commerce_name' => $this->order->commerce?->business_name ?? $this->order->commerce?->name,
            'delivery_address' => $this->order->delivery_address,
            'delivery_fee' => $this->order->delivery_fee,
            'message' => 'Orden pendiente de asignación de repartidor',
        ];
    }
}
