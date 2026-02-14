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
        $city = City::inRandomOrder()->first();
        // Carabobo, Valencia Venezuela: lat 10.0-10.3, lng -68.2 a -67.5
        $lat = $this->faker->latitude(10.08, 10.28);
        $lng = $this->faker->longitude(-68.12, -67.55);
        return [
            'profile_id' => Profile::factory(),
            'city_id' => $city ? $city->id : 1,
            'street' => $this->faker->streetAddress(),
            'house_number' => $this->faker->optional(0.7)->buildingNumber(),
            'postal_code' => $this->faker->optional(0.5)->postcode(),
            'latitude' => $lat,
            'longitude' => $lng,
            'status' => $this->faker->randomElement(['completeData', 'incompleteData', 'notverified']),
            'is_default' => $this->faker->boolean(20),
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
