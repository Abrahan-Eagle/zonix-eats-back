<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed core data: reference data + admin user.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OpticalPartnerSeeder::class,
            BanksSeeder::class,
            OperatorCodeSeeder::class,
            CountriesSeeder::class,
            StatesSeeder::class,
            CitiesSeeder::class,
            UserSeeder::class,
            OpticalPartnerUserSeeder::class,
        ]);
    }
}
