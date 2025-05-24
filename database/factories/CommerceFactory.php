<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Commerce>
 */
class CommerceFactory extends Factory
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
            'nombre_local' => $this->faker->company,
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'pago_movil_banco' => $this->faker->word,
            'pago_movil_cedula' => $this->faker->numerify('########'),
            'pago_movil_telefono' => $this->faker->phoneNumber,
        ];
    }
}
