<?php

namespace Database\Seeders;

use App\Models\Commerce;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use App\Models\DeliveryAgent;
use Illuminate\Database\Seeder;

/**
 * Crea órdenes (activas + historial) para el usuario comprador id 1 (demo).
 * Para verlas en la app: inicia sesión con el usuario 1 (ej. ing.pulido.abrahan@gmail.com).
 *
 * - 1 orden "En camino" (shipped) con delivery_type = delivery y repartidor asignado (OrderDelivery).
 *   El chat de la orden estará vacío en pruebas porque el repartidor es seed; para chat real prueba con dos dispositivos.
 *
 * Ejecutar con el resto del seed:
 *   php artisan migrate:fresh --seed
 *
 * O solo órdenes para usuario 1 (requiere User1Seeder + Commerce + Productos + DeliveryAgentSeeder):
 *   php artisan db:seed --class=OrdersForUserSeeder
 *
 * Requisitos: usuario 1 con role 'users', perfil y teléfono; al menos un comercio con productos.
 */
class OrdersForUserSeeder extends Seeder
{
    /** ID del usuario comprador (1 = demo). Si no existe, se usa el primer user con role 'users'. */
    protected int $targetUserId = 1;

    public function run(): void
    {
        $user = User::where('id', $this->targetUserId)->where('role', 'users')->first()
            ?? User::where('role', 'users')->first();

        if (!$user) {
            $this->command->warn('No hay ningún usuario con role "users". Crea uno antes de ejecutar este seeder.');
            return;
        }

        $profile = $user->profile;
        if (!$profile) {
            $this->command->warn("El usuario {$user->id} no tiene perfil. Completa el perfil antes.");
            return;
        }

        $commerce = Commerce::where('open', true)->has('products')->first()
            ?? Commerce::first();

        if (!$commerce) {
            $this->command->warn('No hay comercios. Ejecuta CommerceSeeder y ProductSeeder primero.');
            return;
        }

        $products = Product::where('commerce_id', $commerce->id)->where('available', true)->get();
        if ($products->isEmpty()) {
            $this->command->warn("El comercio {$commerce->id} no tiene productos disponibles. Crea productos primero.");
            return;
        }

        $this->command->info("Creando órdenes para usuario {$user->id} (perfil {$profile->id}), comercio {$commerce->id}.");

        // 1 orden activa "En camino" (siempre con reparto y repartidor asignado para probar chat/tracking)
        $shippedOrder = $this->createOrder($profile->id, $commerce->id, $products, 'shipped', 1, now(), true);
        if ($shippedOrder) {
            $agent = DeliveryAgent::where('working', true)->first();
            if ($agent) {
                OrderDelivery::create([
                    'order_id' => $shippedOrder->id,
                    'agent_id' => $agent->id,
                    'status' => 'in_transit',
                    'delivery_fee' => $shippedOrder->delivery_fee ?? 0,
                ]);
                $this->command->info("Orden shipped #{$shippedOrder->id}: repartidor asignado (agent_id {$agent->id}). Chat vacío en pruebas (seed).");
            }
        }

        // 3 órdenes entregadas (historial) con repartidor para que ReviewSeeder pueda crear reseñas
        $agents = DeliveryAgent::where('working', true)->get();
        foreach ([2, 5, 8] as $daysAgo) {
            $deliveredOrder = $this->createOrder($profile->id, $commerce->id, $products, 'delivered', 1, now()->subDays($daysAgo), true);
            if ($deliveredOrder && $agents->isNotEmpty()) {
                $agent = $agents->random();
                OrderDelivery::create([
                    'order_id' => $deliveredOrder->id,
                    'agent_id' => $agent->id,
                    'status' => 'delivered',
                    'delivery_fee' => $deliveredOrder->delivery_fee ?? 0,
                ]);
            }
        }
        // 1 cancelada
        $this->createOrder($profile->id, $commerce->id, $products, 'cancelled', 1, now()->subDays(10), null);

        $this->command->info('OrdersForUserSeeder: órdenes creadas para tu cuenta. Refresca la vista Órdenes en la app.');
    }

    /**
     * @param bool|null $forceDelivery True = delivery_type delivery, false = pickup, null = aleatorio
     * @return Order|null La orden creada (para la primera del loop cuando count=1)
     */
    private function createOrder(int $profileId, int $commerceId, $products, string $status, int $count = 1, ?\DateTimeInterface $createdAt = null, ?bool $forceDelivery = null): ?Order
    {
        $createdAt = $createdAt ?? now();
        $created = null;
        for ($i = 0; $i < $count; $i++) {
            $deliveryType = $forceDelivery === true ? 'delivery' : ($forceDelivery === false ? 'pickup' : ['pickup', 'delivery'][array_rand(['pickup', 'delivery'])]);
            $deliveryFee = $deliveryType === 'delivery' ? round(rand(150, 500) / 100, 2) : 0;

            $deliveryLat = $deliveryType === 'delivery' ? 10.125277 : null;
            $deliveryLng = $deliveryType === 'delivery' ? -68.051191 : null;

            $order = Order::create([
                'profile_id' => $profileId,
                'commerce_id' => $commerceId,
                'delivery_type' => $deliveryType,
                'status' => $status,
                'total' => 0,
                'delivery_fee' => $deliveryFee,
                'delivery_payment_amount' => $deliveryType === 'delivery' && in_array($status, ['shipped', 'delivered']) ? $deliveryFee : null,
                'commission_amount' => 0,
                'cancellation_penalty' => 0,
                'cancelled_by' => $status === 'cancelled' ? 'user_id' : null,
                'estimated_delivery_time' => $deliveryType === 'delivery' ? rand(15, 45) : null,
                'payment_method' => $status !== 'pending_payment' ? 'cash' : null,
                'reference_number' => $status !== 'pending_payment' ? 'REF' . rand(10000, 99999) : null,
                'payment_validated_at' => in_array($status, ['paid', 'processing', 'shipped', 'delivered']) ? $createdAt : null,
                'delivery_address' => $deliveryType === 'delivery' ? 'Casa, El Socorro, Valencia' : null,
                'delivery_latitude' => $deliveryLat,
                'delivery_longitude' => $deliveryLng,
                'cancellation_reason' => $status === 'cancelled' ? 'Solicitud del cliente' : null,
            ]);
            $order->created_at = $createdAt;
            $order->updated_at = $createdAt;
            $order->saveQuietly();
            $created = $order;

            $selected = $products->random(min(3, $products->count()));
            $subtotal = 0;
            foreach ($selected as $product) {
                $qty = rand(1, 2);
                $unitPrice = (float) $product->price;
                $subtotal += $unitPrice * $qty;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                ]);
            }

            $order->update(['total' => round($subtotal + $deliveryFee, 2)]);
        }
        return $created;
    }
}
