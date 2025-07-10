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
            'profile_id' => \App\Models\Profile::factory(),
            'commerce_id' => \App\Models\Commerce::factory(),
            'user_id' => function (array $attributes) {
                return $attributes['profile_id'] ? \App\Models\Profile::find($attributes['profile_id'])->user_id : \App\Models\User::factory();
            },
            'tipo_entrega' => $this->faker->randomElement(['pickup', 'delivery']),
            'estado' => $this->faker->randomElement(['pendiente_pago', 'pagado', 'preparando', 'en_camino', 'entregado', 'cancelado']),
            'total' => $this->faker->randomFloat(2, 10, 500),
            'comprobante_url' => $this->faker->imageUrl(),
            'notas' => $this->faker->boolean(30) ? $this->faker->sentence : null,
        ];
    }
}
