<?php

namespace Database\Seeders;

use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Database\Seeder;

class OrderDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryOrders = Order::where('delivery_type', 'delivery')
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->whereDoesntHave('orderDelivery')
            ->get();

        $agents = DeliveryAgent::where('working', true)->get();

        if ($deliveryOrders->isEmpty() || $agents->isEmpty()) {
            $this->command->warn('No hay órdenes con delivery o agentes disponibles.');

            return;
        }

        foreach ($deliveryOrders->take(15) as $order) {
            $eligibleAgents = $agents->filter(function (DeliveryAgent $agent) use ($order) {
                if ($order->delivery_company_id) {
                    return (int) $agent->company_id === (int) $order->delivery_company_id;
                }

                return $agent->company_id === null;
            });

            if ($eligibleAgents->isEmpty()) {
                continue;
            }

            $agent = $eligibleAgents->random();

            OrderDelivery::factory()->create([
                'order_id' => $order->id,
                'agent_id' => $agent->id,
                'delivery_fee' => $order->delivery_fee ?? config('zonix.seeder.default_delivery_fee', 5.00),
                'status' => collect(['assigned', 'in_transit', 'delivered'])->random(),
            ]);
        }

        $this->command->info('OrderDeliverySeeder ejecutado exitosamente.');
    }
}
