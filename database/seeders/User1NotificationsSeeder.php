<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Profile;
use Illuminate\Database\Seeder;

/**
 * Crea notificaciones de prueba para el perfil del usuario 1 (demo)
 * para simular una cuenta ya en uso.
 */
class User1NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $profile = Profile::where('user_id', 1)->first();
        if (!$profile) {
            $this->command->warn('No existe perfil para usuario 1. Ejecuta User1Seeder primero.');
            return;
        }

        $titles = [
            'Tu pedido está en camino',
            'Pago confirmado',
            '¡Oferta especial! 20% de descuento',
            'Tu pedido ha sido entregado',
            'Repartidor asignado',
            'Nuevo restaurante disponible',
            'Cupón de descuento disponible',
            'Tu pedido está siendo preparado',
            'Actualización de la aplicación',
            'Factura generada',
        ];

        $bodies = [
            'Tu pedido #1234 está en camino. Llegará en unos 15 minutos.',
            'Tu pago de $25.50 ha sido confirmado. Gracias por tu compra.',
            'Aprovecha 20% de descuento con el código ZONIX20 en tu próxima orden.',
            'Tu pedido #1234 ha sido entregado. ¡Disfruta tu comida!',
            'Se ha asignado un repartidor a tu pedido. Recibirás actualizaciones en tiempo real.',
            'Un nuevo restaurante se ha unido a Zonix Eats. ¡Explora su menú!',
            'Tienes un cupón de descuento disponible. Revisa tu perfil.',
            'Tu pedido está siendo preparado por el restaurante.',
            'Se ha lanzado una nueva versión con mejoras.',
            'Tu factura está disponible en tu perfil.',
        ];

        $types = ['order_status', 'payment_confirmation', 'promotion', 'order_status', 'delivery_update', 'promotion', 'promotion', 'order_status', 'system', 'payment_confirmation'];

        for ($i = 0; $i < 10; $i++) {
            Notification::create([
                'profile_id' => $profile->id,
                'title' => $titles[$i],
                'body' => $bodies[$i],
                'type' => $types[$i],
                'read_at' => $i >= 3 ? now()->subDays(rand(1, 5)) : null,
                'data' => [
                    'order_id' => $i % 2 === 0 ? 1000 + $i : null,
                    'amount' => $i === 1 ? 25.50 : null,
                ],
            ]);
        }

        $this->command->info('Notificaciones para usuario 1 (perfil ' . $profile->id . ') creadas.');
    }
}
