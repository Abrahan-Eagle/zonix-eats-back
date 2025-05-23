<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Email;
use App\Models\GasCylinder;
use App\Models\Profile;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(3)->create();
        // Profile::factory(10)->create();
        GasCylinder::factory(3)->create();
        // GasCylinder::factory(10)->create();

        // User::factory()->count(10)->create();


        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);



        // Crear un nuevo correo electrónico
        // $email = Email::create([
        //     'profile_id' => 1, // ID del perfil
        //     'email' => 'example@example.com',
        //     'is_primary' => true,
        // ]);


          // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

          // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

         // \App\Models\User::factory(10)->create();



          // \App\Models\User::factory(10)->create();
        //Blog::factory(10)->create();
        //Blog::factory()->count(10)->create();


        $this->call([

            //CONTRIES STATES CITIES
            // CountryCodeSeeder::class,
            OperatorCodeSeeder::class,
            CountriesSeeder::class,
            StatesSeeder::class,
            CitiesSeeder::class,
            GasSuppliersSeeder::class,
            StationsSeeder::class,
            //VEHICULE DATA
            // VehicleTypesSeeder::class,
            // MarksSeeder::class,
            // YearsSeeder::class,
            // CarModelsSeeder::class,



            //PermissionSeeder::class,
            //UserSeeder::class,
            //FRONT
            //EventsSeeder::class,
            //SermonSeeder::class,
            //TestimonioSeeder::class,
            //VideoheroSeeder::class,
            //HistoryaboutSeeder::class,

            //BLOG
            //AuthorSeeder::class,
            //CategorySeeder::class,
            //TagSeeder::class,
            // PostsSeeder::class,

         ]);



    }
}
