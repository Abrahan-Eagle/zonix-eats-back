<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Chat en tiempo real: mismo canal privado que OrderStatusChanged (orders.{id} → private-orders.{id} en Pusher).
 */
class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $orderId,
        public array $messagePayload,
        public int $senderProfileId,
        public string $senderName,
        public string $senderRole,
    ) {
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.'.$this->orderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewMessage';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'message' => $this->messagePayload,
            'sender_id' => $this->senderProfileId,
            'sender_name' => $this->senderName,
            'sender_role' => $this->senderRole,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
