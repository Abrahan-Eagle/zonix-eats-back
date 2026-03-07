<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commerce;
use App\Models\PaymentMethod;
use App\Models\DeliveryAgent;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        // Métodos de pago para comercios
        $commerces = Commerce::all();
        foreach ($commerces as $commerce) {
            PaymentMethod::factory()->count(2)->create([
                'payable_type' => 'App\\Models\\Commerce',
                'payable_id' => $commerce->id,
            ]);
        }
        
        // Métodos de pago para usuarios
        $users = \App\Models\User::where('role', 'users')->take(10)->get();
        foreach ($users as $user) {
            PaymentMethod::factory()->count(1)->create([
                'payable_type' => 'App\\Models\\User',
                'payable_id' => $user->id,
            ]);
        }
        
        // Métodos de pago para delivery agents
        $deliveryAgents = DeliveryAgent::all();
        foreach ($deliveryAgents->take(5) as $agent) {
            PaymentMethod::factory()->count(1)->create([
                'payable_type' => 'App\\Models\\DeliveryAgent',
                'payable_id' => $agent->id,
            ]);
        }
    }
} 