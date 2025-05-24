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


        User::factory(10)->create();
        Commerce::factory(5)->create();
        Product::factory(20)->create();
        Post::factory(10)->create();
        PostLike::factory(30)->create();
        Order::factory(15)->create();
        OrderItem::factory(30)->create();
        DeliveryCompany::factory(2)->create();
        DeliveryAgent::factory(5)->create();
        OrderDelivery::factory(5)->create();



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
