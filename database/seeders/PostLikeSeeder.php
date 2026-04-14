<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class PostLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $profiles = Profile::all();

        if ($posts->isEmpty() || $profiles->isEmpty()) {
            $this->command->warn('No hay posts o perfiles para crear likes.');

            return;
        }

        // Crear likes aleatorios
        foreach ($posts as $post) {
            $likers = $profiles->random(rand(1, 5));

            foreach ($likers as $profile) {
                PostLike::factory()->create([
                    'post_id' => $post->id,
                    'profile_id' => $profile->id,
                ]);
            }
        }

        $this->command->info('PostLikeSeeder ejecutado exitosamente.');
    }
}
