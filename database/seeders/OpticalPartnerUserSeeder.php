<?php

namespace Database\Seeders;

use App\Models\OpticalPartner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OpticalPartnerUserSeeder extends Seeder
{
    public function run(): void
    {
        $partner = OpticalPartner::where('slug', 'zonix-direct')->first();
        if (! $partner) {
            return;
        }

        $user = User::updateOrCreate(
            ['email' => 'partner@zonix-glasses.local'],
            [
                'name' => 'Partner Demo',
                'password' => Hash::make('password'),
                'role' => 'optical_partner',
                'completed_onboarding' => true,
            ]
        );

        $user->opticalPartners()->syncWithoutDetaching([$partner->id]);
    }
}
