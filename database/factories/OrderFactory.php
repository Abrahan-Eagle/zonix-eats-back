<?php

namespace Database\Factories;

use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'commerce_id' => Commerce::factory(),
            'tipo_entrega' => $this->faker->randomElement(['pickup', 'delivery']),
            'estado' => 'pendiente_pago',
            'total' => $this->faker->randomFloat(2, 10, 300),
            'comprobante_url' => $this->faker->imageUrl(),
        ];
    }
}
