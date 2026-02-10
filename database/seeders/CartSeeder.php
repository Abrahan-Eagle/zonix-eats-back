<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\User;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'users')->with('profile')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios compradores para crear carritos.');
            return;
        }
        
        $created = 0;
        foreach ($users->take(10) as $user) {
            $profile = $user->profile;
            if ($profile === null) {
                continue;
            }
            Cart::factory()->create([
                'profile_id' => $profile->id,
            ]);
            $created++;
        }
        
        if ($created === 0) {
            $this->command->warn('Ningún usuario comprador tiene perfil; no se crearon carritos.');
        }
        
        $this->command->info('CartSeeder ejecutado exitosamente.');
    }
}
