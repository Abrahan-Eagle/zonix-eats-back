<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@zonix-glasses.local'],
            [
                'name' => 'Admin Zonix Glasses',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'completed_onboarding' => true,
            ]
        );

        Profile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'firstName' => 'Admin',
                'lastName' => 'Glasses',
                'date_of_birth' => '1990-01-01',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'status' => 'completeData',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@zonix-glasses.local'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'completed_onboarding' => true,
            ]
        );

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'firstName' => 'Demo',
                'lastName' => 'User',
                'date_of_birth' => '1995-06-15',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'status' => 'completeData',
            ]
        );
    }
}
