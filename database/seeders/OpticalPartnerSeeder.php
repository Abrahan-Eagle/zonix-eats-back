<?php

namespace Database\Seeders;

use App\Models\OpticalPartner;
use Illuminate\Database\Seeder;

class OpticalPartnerSeeder extends Seeder
{
    public function run(): void
    {
        OpticalPartner::updateOrCreate(
            ['slug' => 'zonix-direct'],
            [
                'name' => 'Zonix Direct',
                'is_zonix_direct' => true,
                'commission_rate' => 0,
                'status' => 'active',
            ]
        );
    }
}
