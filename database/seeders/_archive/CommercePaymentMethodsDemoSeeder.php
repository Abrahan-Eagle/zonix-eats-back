<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea métodos de pago de ejemplo para el comercio del usuario indicado (tabla users).
 * Usa el user_id para obtener perfil → comercio principal y asociar los 4 métodos demo:
 * - Pago móvil - Personal (04121234567)
 * - Transferencia Bancaria (Banesco •••• 5678)
 * - Billetera Digital (Saldo disponible: $45.00)
 * - Visa Termina en 4242 (Expira 12/26)
 *
 * ID de usuario: PAYMENT_DEMO_USER_ID en .env, o 1 por defecto.
 * Ejecutar: php artisan db:seed --class=CommercePaymentMethodsDemoSeeder
 */
class CommercePaymentMethodsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userId = (int) (env('PAYMENT_DEMO_USER_ID') ?? 1);
        $user = User::find($userId);
        if (!$user) {
            $this->command->warn("Usuario con id {$userId} no existe en la tabla users.");
            return;
        }

        $profile = $user->profile;
        if (!$profile) {
            $this->command->warn("El usuario id {$userId} no tiene perfil. Crea el perfil primero.");
            return;
        }

        $commerce = $profile->getPrimaryCommerce();
        if (!$commerce) {
            $this->command->warn("El usuario id {$userId} no tiene comercio asociado (perfil sin comercio).");
            return;
        }

        $banesco = Bank::where('name', 'like', '%Banesco%')->first();
        $mercantil = Bank::where('name', 'like', '%Mercantil%')->first();

        $demoMethods = [
            [
                'type' => 'mobile_payment',
                'phone' => '04121234567',
                'owner_name' => 'Juan Pérez',
                'owner_id' => 'V-12.345.678',
                'bank_id' => $mercantil?->id,
                'is_default' => true,
                'is_active' => true,
                'reference_info' => [
                    'alias' => 'Pago móvil - Personal',
                    'bank' => $mercantil?->name ?? 'Banco Mercantil',
                    'currency' => 'VES',
                ],
            ],
            [
                'type' => 'bank_transfer',
                'account_number' => '01050000000000005678',
                'owner_name' => 'Inversiones Zonix C.A.',
                'owner_id' => 'J-123456789',
                'bank_id' => $banesco?->id,
                'is_default' => false,
                'is_active' => true,
                'reference_info' => [
                    'alias' => 'Transferencia Bancaria',
                    'bank' => $banesco?->name ?? 'Banesco',
                    'currency' => 'VES',
                ],
            ],
            [
                'type' => 'other',
                'email' => 'cuenta@paypal.com',
                'owner_name' => 'Juan Alberto Pérez',
                'is_default' => false,
                'is_active' => true,
                'reference_info' => [
                    'alias' => 'Billetera Digital',
                    'display_type' => 'digital_wallet',
                    'platform' => 'PayPal',
                    'email' => 'cuenta@paypal.com',
                    'currency' => 'USD',
                    'notes' => 'Saldo disponible: $45.00',
                ],
            ],
            [
                'type' => 'card',
                'brand' => 'Visa',
                'last4' => '4242',
                'exp_month' => 12,
                'exp_year' => 2026,
                'cardholder_name' => 'JUAN PÉREZ',
                'owner_name' => 'Juan Pérez',
                'is_default' => false,
                'is_active' => true,
                'reference_info' => [
                    'alias' => 'Visa Termina en 4242',
                    'exp' => '12/26',
                    'holder' => 'JUAN PÉREZ',
                ],
            ],
        ];

        $commerce->paymentMethods()->delete();

        foreach ($demoMethods as $data) {
            $commerce->paymentMethods()->create($data);
        }

        $this->command->info(
            'CommercePaymentMethodsDemoSeeder: 4 métodos de pago demo creados para usuario id ' . $userId
            . ' → comercio id ' . $commerce->id . ' (' . $commerce->business_name . ').'
        );
    }
}
