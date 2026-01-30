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
        $restaurantImages = [
            'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=500&h=300&fit=crop',
            'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=500&h=300&fit=crop',
        ];

        $membershipType = $this->faker->randomElement(['basic', 'premium', 'enterprise']);
        $membershipFees = ['basic' => 50, 'premium' => 100, 'enterprise' => 200];
        
        return [
            'profile_id' => Profile::factory(),
            'business_name' => $this->faker->company,
            'business_type' => $this->faker->randomElement(['restaurant', 'cafe', 'bakery', 'fast_food', 'pizzeria', 'bar', 'food_truck']),
            'tax_id' => $this->faker->numerify('J-########-#'),
            'image' => $this->faker->randomElement($restaurantImages),
            'address' => $this->faker->address,
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
            'membership_type' => $membershipType,
            'membership_monthly_fee' => $membershipFees[$membershipType],
            'membership_expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'commission_percentage' => $this->faker->randomFloat(2, 5, 15), // 5% a 15%
            'cancellation_count' => $this->faker->numberBetween(0, 5),
            'last_cancellation_date' => $this->faker->optional(0.3)->dateTimeBetween('-6 months', 'now'),
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
