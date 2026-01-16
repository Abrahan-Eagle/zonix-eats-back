<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Profile;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'city_id' => $this->faker->optional(0.8)->passthrough(City::factory()),
            'street' => $this->faker->streetAddress(),
            'house_number' => $this->faker->optional(0.7)->buildingNumber(),
            'postal_code' => $this->faker->optional(0.5)->postcode(),
            'latitude' => $this->faker->latitude(10.0, 10.5), // Coordenadas de Venezuela (Caracas)
            'longitude' => $this->faker->longitude(-67.0, -66.5),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'is_default' => $this->faker->boolean(20), // 20% son direcciones por defecto
        ];
    }

    /**
     * Indicate that the address is default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
