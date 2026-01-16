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
            'order_id' => \App\Models\Order::factory(),
            'agent_id' => \App\Models\DeliveryAgent::factory(),
            'status' => $this->faker->randomElement(['assigned', 'in_transit', 'delivered', 'failed']),
            'delivery_fee' => $this->faker->randomFloat(2, 5, 50),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
