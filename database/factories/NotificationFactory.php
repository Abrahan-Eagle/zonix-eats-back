<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $types = ['system', 'account', 'security'];
        $type = $this->faker->randomElement($types);

        $titles = [
            'system' => ['Actualización disponible', 'Mantenimiento programado', 'Nueva funcionalidad'],
            'account' => ['Perfil actualizado', 'Bienvenido', 'Verifica tu cuenta'],
            'security' => ['Nuevo inicio de sesión', 'Contraseña cambiada', 'Actividad detectada'],
        ];

        return [
            'profile_id' => Profile::factory(),
            'title' => $this->faker->randomElement($titles[$type]),
            'body' => $this->faker->sentence(12),
            'type' => $type,
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
            'data' => [
                'entity_id' => $this->faker->optional()->uuid(),
                'entity_type' => $this->faker->optional()->randomElement(['profile', 'user', 'settings']),
            ],
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }
}
