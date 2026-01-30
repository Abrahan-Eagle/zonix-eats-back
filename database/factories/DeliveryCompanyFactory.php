<?php

namespace Database\Factories;

use App\Models\Profile;
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
            'profile_id' => Profile::factory(),
            'name' => $this->faker->company,
            'tax_id' => $this->faker->unique()->numerify('J-########-#'),
            'address' => $this->faker->address,
            'image' => $this->faker->optional(0.7)->imageUrl(),
            'open' => $this->faker->boolean(80),
            'schedule' => [
                'monday' => ['open' => '00:00', 'close' => '23:59'],
                'tuesday' => ['open' => '00:00', 'close' => '23:59'],
                'wednesday' => ['open' => '00:00', 'close' => '23:59'],
                'thursday' => ['open' => '00:00', 'close' => '23:59'],
                'friday' => ['open' => '00:00', 'close' => '23:59'],
                'saturday' => ['open' => '00:00', 'close' => '23:59'],
                'sunday' => ['open' => '00:00', 'close' => '23:59'],
            ],
            'active' => $this->faker->boolean(80), // 80% probability of being active
        ];
    }
}
