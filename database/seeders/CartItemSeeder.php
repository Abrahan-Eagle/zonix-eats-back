<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Commerce;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

            if (! $commerce) {
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
                    'line_id' => (string) Str::uuid(),
                    'quantity' => rand(1, 3),
                ]);
            }
        }

        $this->command->info('CartItemSeeder ejecutado exitosamente.');
    }
}
