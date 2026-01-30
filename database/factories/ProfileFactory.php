<?php

namespace Database\Factories;

use App\Models\OperatorCode;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = \App\Models\Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'firstName' => $this->faker->firstName,
            'middleName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'secondLastName' => $this->faker->lastName,
            'photo_users' => $this->faker->imageUrl(),
            'date_of_birth' => $this->faker->date(),
            'maritalStatus' => $this->faker->randomElement(['married', 'divorced', 'single', 'widowed']),
            'sex' => $this->faker->randomElement(['F', 'M', 'O']),
            'status' => $this->faker->randomElement(['completeData', 'incompleteData', 'notverified']),
            'address' => $this->faker->address,
        ];
    }

    /**
     * Crear un teléfono principal en tabla phones tras crear el perfil.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Profile $profile) {
            $operatorCode = OperatorCode::first()
                ?? OperatorCode::create(['name' => 'Movilnet', 'code' => '0412']);
            Phone::create([
                'profile_id' => $profile->id,
                'operator_code_id' => $operatorCode->id,
                'number' => $this->faker->numerify('#######'),
                'is_primary' => true,
                'status' => true,
            ]);
        });
    }

    // Estados adicionales para diferentes roles
    public function commerce(): Factory
    {
        return $this->state([
        ]);
    }

    public function delivery(): Factory
    {
        return $this->state([
        ]);
    }
}
