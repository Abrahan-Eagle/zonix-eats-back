<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Database\Seeder;

/**
 * SOLO PARA TESTING / DEMO.
 *
 * Sobrescribe direcciones y posición del repartidor con coordenadas fijas para que
 * el mapa de "Detalle del Pedido" se vea bien en desarrollo. En producción NO se
 * debe usar para usuarios reales: la ubicación del repartidor viene del GPS del
 * celular (app envía actualizaciones y se guardan en delivery_agents.current_*),
 * y la dirección del cliente viene de la dirección que el usuario guardó en la app
 * (desde GPS o mapa), almacenada en addresses con latitude/longitude.
 *
 * Aplica solo a órdenes de demo (por defecto ids 4 y 5). Ejecutar después de
 * OrderSeeder, AddressSeeder, OrdersForUserSeeder, DeliveryCaraboboOrder4Seeder.
 */
class FixDemoOrderTrackingSeeder extends Seeder
{
    /** El Socorro - C. las Torres, Valencia 2001, Carabobo (destino del usuario) */
    private const EL_SOCORRO_LAT = 10.125277;
    private const EL_SOCORRO_LNG = -68.051191;

    /** Repartidor - Av. Bolívar Sur, Valencia 2001, Carabobo */
    private const DELIVERY_LAT = 10.159739;
    private const DELIVERY_LNG = -68.000354;

    /** IDs de órdenes de demo a corregir (env: ZONIX_SEEDER_DEMO_ORDER_IDS = 4,5) */
    private function demoOrderIds(): array
    {
        $ids = env('ZONIX_SEEDER_DEMO_ORDER_IDS', '4,5');
        return array_map('intval', array_filter(explode(',', $ids)));
    }

    public function run(): void
    {
        $orderIds = $this->demoOrderIds();
        if (empty($orderIds)) {
            return;
        }

        $orders = Order::whereIn('id', $orderIds)
            ->where('delivery_type', 'delivery')
            ->with(['profile', 'orderDelivery.agent'])
            ->get();

        if ($orders->isEmpty()) {
            $this->command->warn('FixDemoOrderTrackingSeeder: no hay órdenes de demo con delivery (ids: ' . implode(', ', $orderIds) . '). El mapa usará los datos que tenga cada orden.');
            return;
        }

        foreach ($orders as $order) {
            $this->fixCustomerAddress($order);
            $this->fixDeliveryAgentPosition($order);
            $this->fixOrderDeliveryCoords($order);
        }

        $this->command->info('FixDemoOrderTrackingSeeder: destino C. las Torres (El Socorro) y repartidor en Av. Bolívar Sur aplicados a órdenes ' . implode(', ', $orders->pluck('id')->toArray()) . '.');
    }

    private function fixCustomerAddress(Order $order): void
    {
        $profile = $order->profile;
        if (!$profile) {
            return;
        }

        $defaultAddr = $profile->addresses()->where('is_default', true)->first()
            ?? $profile->addresses()->first();

        if ($defaultAddr) {
            $defaultAddr->update([
                'street' => 'C. las Torres, Valencia 2001, Carabobo',
                'latitude' => self::EL_SOCORRO_LAT,
                'longitude' => self::EL_SOCORRO_LNG,
            ]);
        } else {
            Address::create([
                'profile_id' => $profile->id,
                'street' => 'C. las Torres, Valencia 2001, Carabobo',
                'house_number' => '1',
                'latitude' => self::EL_SOCORRO_LAT,
                'longitude' => self::EL_SOCORRO_LNG,
                'is_default' => true,
                'status' => 'completeData',
                'city_id' => City::where('name', 'like', '%Valencia%')->first()?->id ?? City::first()?->id ?? 1,
            ]);
        }
    }

    private function fixDeliveryAgentPosition(Order $order): void
    {
        $agent = $order->orderDelivery?->agent;
        if (!$agent) {
            return;
        }

        $agent->update([
            'current_latitude' => self::DELIVERY_LAT,
            'current_longitude' => self::DELIVERY_LNG,
            'last_location_update' => now(),
        ]);
    }

    private function fixOrderDeliveryCoords(Order $order): void
    {
        $order->update([
            'delivery_latitude' => self::EL_SOCORRO_LAT,
            'delivery_longitude' => self::EL_SOCORRO_LNG,
        ]);
    }
}
