<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Phone;
use App\Models\OperatorCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea/actualiza el usuario id 1 (Abrahan Pulido) con datos reales y su perfil/teléfono.
 * Debe ejecutarse ANTES de UserSeeder para que el usuario 1 sea este demo.
 * El resto de tablas (addresses, cart, orders, notifications, etc.) se llenan con los
 * seeders posteriores que ya incluyen a todos los perfiles (p. ej. AddressSeeder, CartSeeder).
 *
 * Ejecutar solo este seeder:
 *   php artisan db:seed --class=User1Seeder
 */
class User1Seeder extends Seeder
{
    public function run(): void
    {
        $userData = [
            'name' => 'abrahan pulido',
            'email' => 'ing.pulido.abrahan@gmail.com',
            'email_verified_at' => null,
            'password' => null,
            'google_id' => '111890855875234910207',
            'given_name' => 'abrahan',
            'family_name' => 'pulido',
            'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocIuLGJWAUiZXz3X-UKcCtla9yqtb8nK0sTu_33NkIv2O1x5d5-E=s96-c',
            'AccessToken' => null,
            'completed_onboarding' => 1,
            'role' => 'users',
            'remember_token' => null,
            'light' => '1',
            'created_at' => '2026-03-01 12:10:41',
            'updated_at' => '2026-03-01 12:11:21',
        ];

        DB::table('users')->updateOrInsert(
            ['id' => 1],
            array_merge($userData, ['id' => 1])
        );

        $this->command->info('Usuario 1 (abrahan pulido) creado/actualizado.');

        $profile = Profile::updateOrCreate(
            ['user_id' => 1],
            [
                'firstName' => 'Abrahan',
                'middleName' => '',
                'lastName' => 'Pulido',
                'secondLastName' => '',
                'photo_users' => $userData['profile_pic'],
                'date_of_birth' => '1990-01-15',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'status' => 'completeData',
                'address' => null,
            ]
        );

        $this->command->info('Perfil para usuario 1 verificado.');

        $operatorCode = OperatorCode::first();
        if ($operatorCode && !Phone::where('profile_id', $profile->id)->exists()) {
            Phone::create([
                'profile_id' => $profile->id,
                'operator_code_id' => $operatorCode->id,
                'number' => '4241234',
                'is_primary' => true,
                'status' => true,
                'approved' => true,
            ]);
            $this->command->info('Teléfono para perfil 1 creado.');
        }
    }
}
