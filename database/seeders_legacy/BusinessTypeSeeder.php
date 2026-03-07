<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessType;

class BusinessTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Restaurant', 'icon' => 'restaurant', 'description' => 'Restaurantes de comida completa, almuerzos y cenas.'],
            ['name' => 'Comida Rápida', 'icon' => 'fastfood', 'description' => 'Hamburguesas, perros calientes, pizzas y comida rápida.'],
            ['name' => 'Heladería', 'icon' => 'icecream', 'description' => 'Helados artesanales, barquillas y postres fríos.'],
            ['name' => 'Panadería', 'icon' => 'bakery_dining', 'description' => 'Pan, dulces, pastelería y productos de horno.'],
            ['name' => 'Cafetería', 'icon' => 'coffee', 'description' => 'Café, té, infusiones y meriendas.'],
            ['name' => 'Pizzería', 'icon' => 'local_pizza', 'description' => 'Pizzas artesanales y comerciales.'],
            ['name' => 'Arepera', 'icon' => 'restaurant', 'description' => 'Arepas rellenas, empanadas y comida criolla rápida.'],
            ['name' => 'Charcutería', 'icon' => 'storefront', 'description' => 'Quesos, jamones, embutidos y productos selectos.'],
            ['name' => 'Licorería', 'icon' => 'liquor', 'description' => 'Bebidas alcohólicas, licores, cervezas y vinos.'],
            ['name' => 'Bodegón', 'icon' => 'store', 'description' => 'Productos importados, snacks, golosinas y bebidas.'],
            ['name' => 'Sushi Bar', 'icon' => 'set_meal', 'description' => 'Sushi, comida japonesa y fusión asiática.'],
            ['name' => 'Marisquería', 'icon' => 'set_meal', 'description' => 'Pescados, mariscos, ceviches y cocteles marinos.'],
            ['name' => 'Parrilla', 'icon' => 'outdoor_grill', 'description' => 'Carnes a la parrilla, chorizos y guarniciones.'],
            ['name' => 'Pastelería', 'icon' => 'cake', 'description' => 'Tortas, pasteles, cupcakes y postres decorados.'],
            ['name' => 'Comida China', 'icon' => 'ramen_dining', 'description' => 'Arroz frito, lumpias y platos chinos.'],
            ['name' => 'Comida Árabe', 'icon' => 'kebab_dining', 'description' => 'Shawarma, falafel, kibbe y platos árabes.'],
            ['name' => 'Comida Mexicana', 'icon' => 'restaurant', 'description' => 'Tacos, burritos, nachos y platos mexicanos.'],
            ['name' => 'Comida Italiana', 'icon' => 'dinner_dining', 'description' => 'Pastas, lasañas y gastronomía italiana.'],
            ['name' => 'Comida Vegetariana', 'icon' => 'eco', 'description' => 'Opciones vegetarianas y saludables.'],
            ['name' => 'Comida Vegana', 'icon' => 'spa', 'description' => 'Platos 100% vegetales.'],
            ['name' => 'Jugos y Batidos', 'icon' => 'local_bar', 'description' => 'Jugos naturales, batidos y smoothies.'],
            ['name' => 'Chocolatería', 'icon' => 'cookie', 'description' => 'Bombones, tabletas y postres de chocolate.'],
            ['name' => 'Food Truck', 'icon' => 'local_shipping', 'description' => 'Comida callejera desde camiones de comida.'],
            ['name' => 'Dulcería', 'icon' => 'cake', 'description' => 'Dulces criollos, golosinas y confitería.'],
            ['name' => 'Minimarket', 'icon' => 'shopping_cart', 'description' => 'Productos de consumo diario, snacks y bebidas.'],
        ];

        foreach ($types as $type) {
            BusinessType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
