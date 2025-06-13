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
            'estado_envio' => $this->faker->randomElement(['asignado', 'en_camino', 'entregado', 'fallido']),
            'costo_envio' => $this->faker->randomFloat(2, 5, 50),
            'notas' => $this->faker->boolean(30) ? $this->faker->sentence : null,
        ];
    }
}
