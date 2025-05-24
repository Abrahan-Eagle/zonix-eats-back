<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Product;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        // User::factory(10)->create();
        // Commerce::factory(5)->create();
        // Product::factory(20)->create();
        // Post::factory(10)->create();
        // PostLike::factory(30)->create();
        // Order::factory(15)->create();
        // OrderItem::factory(30)->create();
        // DeliveryCompany::factory(2)->create();
        // DeliveryAgent::factory(5)->create();
        // OrderDelivery::factory(5)->create();

          // Crear usuarios base
        $users = User::factory()->count(10)->create();

        // Crear comercios para algunos usuarios
        $users->take(3)->each(function ($user) {
            $commerce = Commerce::factory()->create(['user_id' => $user->id]);

            // Crear productos y publicaciones para cada comercio
            Product::factory()->count(5)->create(['commerce_id' => $commerce->id]);
            Post::factory()->count(3)->create(['commerce_id' => $commerce->id]);
        });

        // Crear publicaciones para likear
        $posts = Post::all();
        $users->each(function ($user) use ($posts) {
            $liked = $posts->random(2);
            foreach ($liked as $post) {
                PostLike::factory()->create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
            }
        });

        // Crear órdenes para usuarios compradores
        $commerces = Commerce::all();
        $users->each(function ($user) use ($commerces) {
            if ($user->tipo === 'comprador') {
                $commerce = $commerces->random();
                $order = Order::factory()->create([
                    'user_id' => $user->id,
                    'commerce_id' => $commerce->id,
                ]);

                $products = $commerce->products()->inRandomOrder()->take(2)->get();
                foreach ($products as $product) {
                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                    ]);
                }
            }
        });

        // Crear empresas y agentes de delivery
        $deliveryCompanies = DeliveryCompany::factory()->count(2)->create();
        $deliveryCompanies->each(function ($company) use ($users) {
            $agents = DeliveryAgent::factory()->count(2)->create([
                'company_id' => $company->id,
                'user_id' => $users->random()->id,
            ]);

            // Asignar agentes a pedidos existentes
            Order::inRandomOrder()->take($agents->count())->get()->each(function ($order, $index) use ($agents) {
                OrderDelivery::factory()->create([
                    'order_id' => $order->id,
                    'agent_id' => $agents[$index]->id,
                ]);
            });
        });


        $this->call([
            UserSeeder::class,
            CommerceSeeder::class,
            ProductSeeder::class,
            PostSeeder::class,
            PostLikeSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            DeliveryCompanySeeder::class,
            DeliveryAgentSeeder::class,
            OrderDeliverySeeder::class,
        ]);


    }
}
