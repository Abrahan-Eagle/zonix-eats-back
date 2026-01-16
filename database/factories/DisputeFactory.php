<?php

namespace Database\Factories;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Commerce;
use App\Models\DeliveryAgent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dispute>
 */
class DisputeFactory extends Factory
{
    protected $model = Dispute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Para disputes, generalmente se reporta desde un Profile
        // pero puede ser contra Commerce o DeliveryAgent (a través de sus profile_id)
        $reportedByProfile = Profile::factory();
        $reportedAgainstType = $this->faker->randomElement([Profile::class, Commerce::class, DeliveryAgent::class]);
        
        // Si es contra Commerce o DeliveryAgent, necesitamos obtener su profile_id
        $reportedAgainstId = null;
        if ($reportedAgainstType === Profile::class) {
            $reportedAgainstId = Profile::factory();
        } elseif ($reportedAgainstType === Commerce::class) {
            $commerce = Commerce::factory()->create();
            $reportedAgainstId = $commerce->profile_id;
        } else { // DeliveryAgent
            $agent = DeliveryAgent::factory()->create();
            $reportedAgainstId = $agent->profile_id;
        }
        
        return [
            'order_id' => Order::factory(),
            'reported_by_type' => Profile::class,
            'reported_by_id' => $reportedByProfile,
            'reported_against_type' => $reportedAgainstType,
            'reported_against_id' => $reportedAgainstId,
            'type' => $this->faker->randomElement(['quality_issue', 'delivery_problem', 'payment_issue', 'other']),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'in_review', 'resolved', 'closed']),
            'admin_notes' => $this->faker->optional(0.4)->paragraph(),
            'resolved_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the dispute is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the dispute is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'resolved_at' => null,
        ]);
    }
}
