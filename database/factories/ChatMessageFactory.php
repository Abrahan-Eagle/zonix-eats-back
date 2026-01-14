<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'sender_id' => Profile::factory(),
            'sender_type' => $this->faker->randomElement(['customer', 'restaurant', 'delivery_agent']),
            'recipient_type' => $this->faker->randomElement(['restaurant', 'delivery_agent', 'all']),
            'content' => $this->faker->sentence(),
            'type' => 'text',
            'read_at' => null,
        ];
    }
}
