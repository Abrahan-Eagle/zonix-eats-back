<?php

namespace Database\Seeders;

use App\Models\Commerce;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyers = Profile::whereHas('user', function ($query) {
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

            $deliveryCompanyId = null;
            if ($deliveryType === 'delivery' && rand(0, 1) === 1) {
                $deliveryCompanyId = DeliveryCompany::query()->inRandomOrder()->value('id');
            }

            Order::factory()->create([
                'profile_id' => $buyer->id,
                'commerce_id' => $commerce->id,
                'status' => $status,
                'delivery_type' => $deliveryType,
                'delivery_company_id' => $deliveryCompanyId,
            ]);
        }

        $this->command->info('OrderSeeder ejecutado exitosamente.');
    }
}
