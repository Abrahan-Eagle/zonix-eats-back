<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Commerce;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commerces = Commerce::all();
        
        if ($commerces->isEmpty()) {
            $this->command->warn('No hay comercios para crear posts.');
            return;
        }
        
        foreach ($commerces as $commerce) {
            // Crear 2-4 posts por comercio
            Post::factory()->count(rand(2, 4))->create([
                'commerce_id' => $commerce->id,
            ]);
        }
        
        $this->command->info('PostSeeder ejecutado exitosamente.');
    }
}
