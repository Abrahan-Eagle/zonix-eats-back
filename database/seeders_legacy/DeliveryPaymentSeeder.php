<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryPayment;
use App\Models\Order;
use App\Models\OrderDelivery;

class DeliveryPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderDeliveries = OrderDelivery::whereHas('order', function($query) {
            $query->whereIn('status', ['shipped', 'delivered']);
        })->get();
        
        if ($orderDeliveries->isEmpty()) {
            $this->command->warn('No hay entregas completadas para crear pagos.');
            return;
        }
        
        foreach ($orderDeliveries as $orderDelivery) {
            // El delivery recibe 100% del delivery_fee
            DeliveryPayment::factory()->create([
                'order_id' => $orderDelivery->order_id,
                'delivery_agent_id' => $orderDelivery->agent_id,
                'amount' => $orderDelivery->delivery_fee,
                'status' => collect(['pending_payment_to_delivery', 'paid_to_delivery'])->random(),
            ]);
        }
        
        $this->command->info('DeliveryPaymentSeeder ejecutado exitosamente.');
    }
}
