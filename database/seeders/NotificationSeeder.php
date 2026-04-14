<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 30 notifications de prueba
        Notification::factory(30)->create();

        $this->command->info('NotificationSeeder ejecutado exitosamente.');
    }
}
