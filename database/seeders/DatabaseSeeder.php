<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Datos de referencia primero; luego ZonixDemoSeeder (5 users, 10 comercios, 1 delivery company,
     * 3 delivery, 1 admin; direcciones GPS Carabobo/Valencia). Flujo demo anterior en _archive/.
     */
    public function run(): void
    {
        $this->call([
            BanksSeeder::class,
            OperatorCodeSeeder::class,
            CountriesSeeder::class,
            StatesSeeder::class,
            CitiesSeeder::class,
            CategorySeeder::class,
            BusinessTypeSeeder::class,

            ZonixDemoSeeder::class,
        ]);
    }
}
