<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Commerce;

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
            'profile_id' => Profile::factory(),
            'business_name' => $this->faker->company,
            'image' => $this->faker->imageUrl(),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'mobile_payment_bank' => $this->faker->randomElement(['Banesco', 'Mercantil', 'Bancaribe', 'Provincial']),
            'mobile_payment_id' => $this->faker->numerify('##########'),
            'mobile_payment_phone' => $this->faker->numerify('##########'),
            'open' => $this->faker->boolean(70),
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '18:00'],
                'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                'thursday' => ['open' => '08:00', 'close' => '18:00'],
                'friday' => ['open' => '08:00', 'close' => '18:00'],
                'saturday' => ['open' => '09:00', 'close' => '16:00'],
                'sunday' => ['open' => '10:00', 'close' => '15:00'],
            ],
        ];
    }

    /**
     * Indicate that the commerce should be created with a profile.
     */
    public function withProfile()
    {
        return $this->afterCreating(function (Commerce $commerce) {
            $profile = Profile::factory()->create();
            $commerce->update(['profile_id' => $profile->id]);
        });
    }
}
