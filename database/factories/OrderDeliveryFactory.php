<?php

namespace Database\Factories;

use App\Models\DeliveryAgent;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDelivery>
 */
class OrderDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'agent_id' => DeliveryAgent::factory(),
            'estado_envio' => 'asignado',
        ];
    }
}
