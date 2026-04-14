<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear cupones públicos
        Coupon::factory()->count(10)->public()->create();

        // Crear cupones privados (asignados a perfiles)
        $profiles = Profile::whereHas('user', function ($query) {
            $query->where('role', 'users');
        })->take(5)->get();

        foreach ($profiles as $profile) {
            Coupon::factory()->count(2)->private()->create([
                'assigned_to_profile_id' => $profile->id,
            ]);
        }

        $this->command->info('CouponSeeder ejecutado exitosamente.');
    }
}
