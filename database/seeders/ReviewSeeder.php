<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Profile;
use App\Models\Order;
use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\OrderDelivery;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Crear reviews para comercios
        $orders = Order::where('status', 'delivered')->get();
        
        if ($orders->isEmpty()) {
            $this->command->warn('No hay órdenes entregadas para crear reviews.');
            return;
        }
        
        foreach ($orders->take(10) as $order) {
            Review::factory()->forCommerce()->create([
                'profile_id' => $order->profile_id,
                'order_id' => $order->id,
                'reviewable_type' => Commerce::class,
                'reviewable_id' => $order->commerce_id,
            ]);
        }
        
        // Crear reviews para delivery agents
        $deliveryOrders = Order::where('delivery_type', 'delivery')
            ->where('status', 'delivered')
            ->whereHas('orderDelivery')
            ->get();
            
        if ($deliveryOrders->isEmpty()) {
            $this->command->warn('No hay órdenes con delivery entregadas para crear reviews.');
            return;
        }
            
        foreach ($deliveryOrders->take(10) as $order) {
            $orderDelivery = $order->orderDelivery;
            if ($orderDelivery && $orderDelivery->agent) {
                Review::factory()->forDeliveryAgent()->create([
                    'profile_id' => $order->profile_id,
                    'order_id' => $order->id,
                    'reviewable_type' => DeliveryAgent::class,
                    'reviewable_id' => $orderDelivery->agent->id,
                ]);
            }
        }
        
        $this->command->info('ReviewSeeder ejecutado exitosamente.');
    }
}
