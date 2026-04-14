<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    private string $eventId;

    private string $occurredAt;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->eventId = (string) Str::uuid();
        $this->occurredAt = now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('orders.'.$this->order->id),
        ];
        if ($this->order->commerce_id) {
            $channels[] = new PrivateChannel('commerce.'.$this->order->commerce_id);
        }
        $this->order->loadMissing('orderDelivery');
        if ($this->order->orderDelivery && $this->order->orderDelivery->agent_id) {
            $channels[] = new PrivateChannel('delivery.'.$this->order->orderDelivery->agent_id);
            $agent = $this->order->orderDelivery->agent;
            if ($agent && $agent->company_id) {
                $channels[] = new PrivateChannel('company.'.$agent->company_id);
            }
        }
        if ($this->order->delivery_company_id) {
            $channels[] = new PrivateChannel('company.'.$this->order->delivery_company_id);
        }

        return array_values(array_unique($channels, SORT_REGULAR));
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusChanged';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->eventId,
            'schema_version' => 'v1',
            'occurred_at' => $this->occurredAt,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'message' => "Order {$this->order->id} status changed to {$this->order->status}",
        ];
    }
}
