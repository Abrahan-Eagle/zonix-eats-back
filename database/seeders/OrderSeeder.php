<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Commerce;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyers = Profile::whereHas('user', function($query) {
            $query->where('role', 'users');
        })->get();
        
        $commerces = Commerce::all();
        
        if ($buyers->isEmpty() || $commerces->isEmpty()) {
            $this->command->warn('No hay compradores o comercios. Ejecuta UserSeeder y CommerceSeeder primero.');
            return;
        }
        
        // Crear órdenes con diferentes estados
        $statuses = ['pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        foreach ($buyers->take(20) as $buyer) {
            $commerce = $commerces->random();
            $status = collect($statuses)->random();
            $deliveryType = collect(['pickup', 'delivery'])->random();
            
            Order::factory()->create([
                'profile_id' => $buyer->id,
                'commerce_id' => $commerce->id,
                'status' => $status,
                'delivery_type' => $deliveryType,
            ]);
        }
        
        $this->command->info('OrderSeeder ejecutado exitosamente.');
    }
}
