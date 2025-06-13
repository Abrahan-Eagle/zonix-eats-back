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
            'profile_id' => \App\Models\Profile::factory()->commerce(),
            'nombre_local' => $this->faker->company,
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'pago_movil_banco' => $this->faker->randomElement(['Banco de Venezuela', 'Banesco', 'Mercantil', 'Provincial']),
            'pago_movil_cedula' => $this->faker->numerify('V########'),
            'pago_movil_telefono' => $this->faker->phoneNumber,
            'abierto' => $this->faker->boolean(70),
            'horario' => json_encode([
                'lunes' => '9:00 AM - 5:00 PM',
                'martes' => '9:00 AM - 5:00 PM',
                'miercoles' => '9:00 AM - 5:00 PM',
                'jueves' => '9:00 AM - 5:00 PM',
                'viernes' => '9:00 AM - 5:00 PM',
                'sabado' => '9:00 AM - 2:00 PM',
                'domingo' => 'Cerrado'
            ]),
        ];

    }
}
