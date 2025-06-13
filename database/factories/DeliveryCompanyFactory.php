<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryCompany>
 */
class DeliveryCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => \App\Models\Profile::factory()->delivery(),
            'nombre' => $this->faker->company,
            'ruc' => $this->faker->numerify('J-########-#'),
            'telefono' => $this->faker->phoneNumber,
            'direccion' => $this->faker->address,
            'activo' => $this->faker->boolean(90),
        ];
    }
}
