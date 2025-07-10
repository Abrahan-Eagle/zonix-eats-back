<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        return [
            'profile_id' => Profile::factory(),
            'reviewable_id' => 1,
            'reviewable_type' => 'App\\Models\\Commerce',
            'rating' => $this->faker->numberBetween(1, 5),
            'comentario' => $this->faker->sentence,
        ];
    }
} 