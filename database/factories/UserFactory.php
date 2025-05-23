<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Profile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Cambia si lo necesitas
            'google_id' => null, // Si no estás probando Google ID, déjalo en null
            'given_name' => $this->faker->firstName(),
            'family_name' => $this->faker->lastName(),
            'profile_pic' => null, // Puedes cambiarlo si tienes imágenes
            'AccessToken' => null,
            'role' => 'users', // Cambia esto a 'admin' o 'users'
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $user->profile()->save(Profile::factory()->create());
        });
    }
}

