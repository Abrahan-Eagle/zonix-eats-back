<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $deliveryAgentId;
    public $companyId;
    public $latitude;
    public $longitude;
    public $estimatedArrival;

    public function __construct($orderId, $deliveryAgentId, $latitude, $longitude, $estimatedArrival = null, $companyId = null)
    {
        $this->orderId = $orderId;
        $this->deliveryAgentId = $deliveryAgentId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->estimatedArrival = $estimatedArrival;
        $this->companyId = $companyId;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('delivery.' . $this->deliveryAgentId),
        ];

        if ($this->orderId) {
            $channels[] = new PrivateChannel('orders.' . $this->orderId);
        }

        if ($this->companyId) {
            $channels[] = new PrivateChannel('company.' . $this->companyId);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'delivery_agent_id' => $this->deliveryAgentId,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'estimated_arrival' => $this->estimatedArrival,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'DeliveryLocationUpdated';
    }
} 