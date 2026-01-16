<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        
        if ($orders->isEmpty()) {
            $this->command->warn('No hay órdenes. Ejecuta OrderSeeder primero.');
            return;
        }
        
        foreach ($orders as $order) {
            $commerceProducts = Product::where('commerce_id', $order->commerce_id)
                ->where('available', true)
                ->inRandomOrder()
                ->take(rand(1, 4))
                ->get();
            
            foreach ($commerceProducts as $product) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                    'unit_price' => $product->price,
                ]);
            }
            
            // Recalcular total de la orden
            $total = $order->orderItems()->sum(DB::raw('quantity * unit_price'));
            $order->update(['total' => $total]);
        }
        
        $this->command->info('OrderItemSeeder ejecutado exitosamente.');
    }
}
