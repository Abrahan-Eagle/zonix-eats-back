<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PaymentValidated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public $isValidated;

    public $validatedBy;

    private string $eventId;

    private string $occurredAt;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, bool $isValidated, $validatedBy = null)
    {
        $this->order = $order;
        $this->isValidated = $isValidated;
        $this->validatedBy = $validatedBy;
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
        $this->order->loadMissing('profile');
        $userId = $this->order->profile?->user_id;

        $channels = [
            new PrivateChannel('orders.'.$this->order->id),
            new PrivateChannel('commerce.'.$this->order->commerce_id),
        ];

        if ($userId) {
            $channels[] = new PrivateChannel('user.'.$userId);
        }

        return [
            ...$channels,
        ];
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
            'order_number' => $this->order->orderNumber ?? 'ORD-'.$this->order->id,
            'is_validated' => $this->isValidated,
            'validated_by' => $this->validatedBy,
            'status' => $this->order->status,
            'message' => $this->isValidated ? 'Pago validado correctamente' : 'Pago rechazado',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PaymentValidated';
    }
}
