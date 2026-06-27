<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'google_id' => null,
            'given_name' => $this->faker->firstName(),
            'family_name' => $this->faker->lastName(),
            'profile_pic' => $this->faker->imageUrl(),
            'role' => 'user',
            'completed_onboarding' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): Factory
    {
        return $this->state(['role' => 'admin']);
    }

    public function opticalPartner(): Factory
    {
        return $this->state(['role' => 'optical_partner']);
    }

    public function user(): Factory
    {
        return $this->state(['role' => 'user']);
    }
}
