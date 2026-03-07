<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['id' => '1', 'sortname' => 'VE', 'name' => 'Venezuela', 'phonecode' => '58'],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['id' => $country['id']],
                $country
            );
        }
    }
}
