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
 * Flota de delivery en Carabobo (Valencia y alrededores).
 * - 1 empresa de delivery
 * - 10 agentes repartidos cerca de establecimientos en el estado
 * - 1 agente asignado a la orden 4 para simular pedido activo (con ubicación actual)
 */
class DeliveryCaraboboSeeder extends Seeder
{
    /** Coordenadas aproximadas en Carabobo (Valencia, Naguanagua, San Diego, etc.) */
    private const CARABOBO_SPOTS = [
        [10.1620, -68.0074], // Valencia centro
        [10.1734, -68.0012], // Naguanagua
        [10.1555, -68.0130], // San Blas
        [10.1680, -67.9950], // La Viña
        [10.1580, -68.0200], // El Socorro
        [10.1800, -68.0100], // San Diego
        [10.1500, -67.9900], // Guacara
        [10.1720, -68.0180], // Los Guayos
        [10.1650, -67.9850], // Campo Carabobo
        [10.1600, -68.0050], // Valencia (otro punto)
    ];

    public function run(): void
    {
        $company = $this->ensureCompany();
        $agents = $this->createAgents($company);
        $this->assignOrder4($agents);
    }

    private function ensureCompany(): DeliveryCompany
    {
        $company = DeliveryCompany::first();
        if ($company) {
            $this->command->info('Usando empresa de delivery existente: ' . ($company->name ?? $company->id));
            return $company;
        }
        $profile = Profile::factory()->create();
        $profile->user->update(['role' => 'delivery_company']);
        $company = DeliveryCompany::factory()->create([
            'profile_id' => $profile->id,
            'name' => 'Delivery Carabobo Express',
        ]);
        $this->command->info('Creada empresa: ' . $company->name);
        return $company;
    }

    /** @return array<int, DeliveryAgent> */
    private function createAgents(DeliveryCompany $company): array
    {
        $agents = [];
        for ($i = 0; $i < 10; $i++) {
            [$lat, $lng] = self::CARABOBO_SPOTS[$i];
            $profile = Profile::factory()->create();
            /** @var User $user */
            $user = $profile->user;
            $user->update(['role' => 'delivery_agent']);
            $agent = DeliveryAgent::create([
                'company_id' => $company->id,
                'profile_id' => $profile->id,
                'status' => 'activo',
                'working' => true,
                'rating' => round(3.5 + (rand(0, 15) / 10), 1),
                'vehicle_type' => ['motorcycle', 'motorcycle', 'car', 'bicycle'][$i % 4],
                'license_number' => 'LIC-' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'current_latitude' => $lat + (rand(-50, 50) / 10000),
                'current_longitude' => $lng + (rand(-50, 50) / 10000),
                'last_location_update' => now(),
                'rejection_count' => 0,
            ]);
            $agents[$agent->id] = $agent;
        }
        $this->command->info('Creados 10 agentes de delivery en Carabobo.');
        return $agents;
    }

    private function demoOrderId(): int
    {
        return (int) config('zonix.seeder.demo_order_id', 4);
    }

    /** @param array<int, DeliveryAgent> $agents */
    private function assignOrder4(array $agents): void
    {
        $demoOrderId = $this->demoOrderId();
        $order = Order::find($demoOrderId);
        if (!$order) {
            $this->command->warn("Orden {$demoOrderId} no existe. No se asigna repartidor de prueba (configurar ZONIX_SEEDER_DEMO_ORDER_ID si aplica).");
            return;
        }
        OrderDelivery::where('order_id', $demoOrderId)->delete();
        $agent = array_values($agents)[0];
        $agent->update([
            'current_latitude' => 10.1625,
            'current_longitude' => -68.0080,
            'last_location_update' => now(),
        ]);
        OrderDelivery::create([
            'order_id' => $demoOrderId,
            'agent_id' => $agent->id,
            'status' => 'assigned',
            'delivery_fee' => $order->delivery_fee ?? config('zonix.seeder.default_delivery_fee', 5.00),
            'notes' => null,
        ]);
        $orderUpdates = [];
        if ($order->delivery_type === 'delivery') {
            $orderUpdates['delivery_latitude'] = 10.125277;
            $orderUpdates['delivery_longitude'] = -68.051191;
        }
        if ($order->status !== 'shipped' && $order->status !== 'out_for_delivery') {
            $orderUpdates['status'] = 'shipped';
        }
        if (!empty($orderUpdates)) {
            $order->update($orderUpdates);
        }
        $this->command->info("Orden {$demoOrderId} asignada al agente " . $agent->id . ' (repartidor simulado para mapa/ETA).');
    }
}
