<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Commerce;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carts = Cart::all();
        
        if ($carts->isEmpty()) {
            $this->command->warn('No hay carritos. Ejecuta CartSeeder primero.');
            return;
        }
        
        foreach ($carts as $cart) {
            // Seleccionar un comercio aleatorio
            $commerce = Commerce::inRandomOrder()->first();
            
            if (!$commerce) {
                continue;
            }
            
            // Agregar productos del mismo comercio (uni-commerce)
            $products = Product::where('commerce_id', $commerce->id)
                ->where('available', true)
                ->inRandomOrder()
                ->take(rand(1, 3))
                ->get();
            
            foreach ($products as $product) {
                CartItem::factory()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        }
        
        $this->command->info('CartItemSeeder ejecutado exitosamente.');
    }
}
