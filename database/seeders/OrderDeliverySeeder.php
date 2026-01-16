<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\DeliveryAgent;

class OrderDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryOrders = Order::where('delivery_type', 'delivery')
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->get();
        
        $agents = DeliveryAgent::where('working', true)->get();
        
        if ($deliveryOrders->isEmpty() || $agents->isEmpty()) {
            $this->command->warn('No hay órdenes con delivery o agentes disponibles.');
            return;
        }
        
        foreach ($deliveryOrders->take(15) as $order) {
            $agent = $agents->random();
            
            OrderDelivery::factory()->create([
                'order_id' => $order->id,
                'agent_id' => $agent->id,
                'delivery_fee' => $order->delivery_fee ?? 5.00,
                'status' => collect(['assigned', 'in_transit', 'delivered'])->random(),
            ]);
        }
        
        $this->command->info('OrderDeliverySeeder ejecutado exitosamente.');
    }
}
