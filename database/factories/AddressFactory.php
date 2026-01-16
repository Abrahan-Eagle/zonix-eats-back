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
        // Obtener una ciudad aleatoria (siempre debe haber ciudades por el CitiesSeeder)
        $city = City::inRandomOrder()->first();
        
        return [
            'profile_id' => Profile::factory(),
            'city_id' => $city ? $city->id : 1, // Fallback a ID 1 si no hay ciudades (no debería pasar)
            'street' => $this->faker->streetAddress(),
            'house_number' => $this->faker->optional(0.7)->buildingNumber(),
            'postal_code' => $this->faker->optional(0.5)->postcode(),
            'latitude' => $this->faker->latitude(10.0, 10.5), // Coordenadas de Venezuela (Caracas)
            'longitude' => $this->faker->longitude(-67.0, -66.5),
            'status' => $this->faker->randomElement(['completeData', 'incompleteData', 'notverified']),
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
