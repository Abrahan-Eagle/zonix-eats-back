<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Profile;
use App\Models\Order;
use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        $reviewableType = $this->faker->randomElement([Commerce::class, \App\Models\DeliveryAgent::class]);
        
        return [
            'profile_id' => Profile::factory(),
            'order_id' => Order::factory(),
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableType === Commerce::class 
                ? Commerce::factory() 
                : \App\Models\DeliveryAgent::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.8)->sentence(),
        ];
    }

    public function forCommerce()
    {
        return $this->state(function (array $attributes) {
            return [
                'reviewable_type' => Commerce::class,
                'reviewable_id' => Commerce::factory(),
            ];
        });
    }

    public function forDeliveryAgent()
    {
        return $this->state(function (array $attributes) {
            return [
                'reviewable_type' => \App\Models\DeliveryAgent::class,
                'reviewable_id' => \App\Models\DeliveryAgent::factory(),
            ];
        });
    }
} 