<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrador del sistema',
                'permissions' => [
                    'manage_users',
                    'view_reports',
                    'manage_system',
                ],
            ],
            [
                'name' => 'user',
                'description' => 'Usuario estándar',
                'permissions' => [
                    'manage_profile',
                ],
            ],
            [
                'name' => 'optical_partner',
                'description' => 'Óptica aliada — pacientes y fórmulas',
                'permissions' => [
                    'manage_partner_patients',
                    'manage_prescriptions',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
