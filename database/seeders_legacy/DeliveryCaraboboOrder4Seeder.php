<?php

namespace Database\Seeders;

use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea una flotilla de delivery en Carabobo (Valencia y alrededores) y asigna
 * un repartidor a la orden de demo para simular el pedido activo con ubicación y ruta.
 * ID de orden de demo: config('zonix.seeder.demo_order_id') o env ZONIX_SEEDER_DEMO_ORDER_ID.
 */
class DeliveryCaraboboOrder4Seeder extends Seeder
{
    /** Coordenadas aproximadas Carabobo/Valencia (demo) */
    private const VALENCIA_LAT = 10.1620;
    private const VALENCIA_LNG = -68.0074;

    /** Radio en grados para repartir agentes (~10 km) */
    private const RADIUS = 0.05;

    private function demoOrderId(): int
    {
        return (int) config('zonix.seeder.demo_order_id', 4);
    }

    public function run(): void
    {
        $company = DeliveryCompany::first();
        if (!$company) {
            $profileCompany = Profile::factory()->create();
            $profileCompany->user->update(['role' => 'delivery_company']);
            $company = DeliveryCompany::factory()->create(['profile_id' => $profileCompany->id]);
        }

        $agents = [];
        for ($i = 1; $i <= 10; $i++) {
            $profile = Profile::factory()->create();
            /** @var User $user */
            $user = $profile->user;
            $user->update(['role' => 'delivery_agent']);

            $lat = self::VALENCIA_LAT + (random_int(-100, 100) / 1000.0) * self::RADIUS;
            $lng = self::VALENCIA_LNG + (random_int(-100, 100) / 1000.0) * self::RADIUS;

            $agents[] = DeliveryAgent::create([
                'company_id' => $company->id,
                'profile_id' => $profile->id,
                'status' => 'activo',
                'working' => true,
                'rating' => round(3.5 + (random_int(0, 15) / 10.0), 1),
                'vehicle_type' => ['motorcycle', 'motorcycle', 'car', 'bicycle'][$i % 4],
                'license_number' => 'LIC-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'current_latitude' => $lat,
                'current_longitude' => $lng,
                'last_location_update' => now(),
            ]);
        }

        $demoOrderId = $this->demoOrderId();
        $order = Order::find($demoOrderId);
        if (!$order) {
            $this->command->warn("Orden {$demoOrderId} no existe. Crear una orden con id {$demoOrderId} (o configurar ZONIX_SEEDER_DEMO_ORDER_ID) y volver a ejecutar este seeder.");
            return;
        }

        $order->update([
            'status' => 'shipped',
            'delivery_type' => 'delivery',
            'estimated_delivery_time' => (int) config('zonix.default_preparation_time_minutes', 12),
            'delivery_latitude' => 10.125277,
            'delivery_longitude' => -68.051191,
        ]);

        OrderDelivery::where('order_id', $demoOrderId)->delete();

        $agentForOrder4 = $agents[0];
        $agentForOrder4->update([
            'current_latitude' => self::VALENCIA_LAT + 0.015,
            'current_longitude' => self::VALENCIA_LNG - 0.005,
            'last_location_update' => now(),
        ]);

        OrderDelivery::create([
            'order_id' => $demoOrderId,
            'agent_id' => $agentForOrder4->id,
            'status' => 'assigned',
            'delivery_fee' => $order->delivery_fee ?? config('zonix.seeder.default_delivery_fee', 5.00),
        ]);

        $this->command->info("DeliveryCaraboboOrder4Seeder: 1 compañía, 10 agentes en Carabobo, orden {$demoOrderId} asignada al agente " . $agentForOrder4->id . '.');
    }
}
