<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commerce;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $commerces = Commerce::all();
        foreach ($commerces as $commerce) {
            // Asignar 2-3 métodos de pago variados a cada comercio
            PaymentMethod::factory()->count(3)->create([
                'commerce_id' => $commerce->id,
            ]);
        }
    }
} 