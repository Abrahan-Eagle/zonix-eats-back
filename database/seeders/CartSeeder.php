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
        $users = User::where('role', 'users')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios compradores para crear carritos.');
            return;
        }
        
        // Crear carrito para algunos usuarios
        foreach ($users->take(10) as $user) {
            Cart::factory()->create([
                'user_id' => $user->id,
            ]);
        }
        
        $this->command->info('CartSeeder ejecutado exitosamente.');
    }
}
