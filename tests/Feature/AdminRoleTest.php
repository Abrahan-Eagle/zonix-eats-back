<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_commerces_and_orders()
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        // Crear comercios y órdenes
        $commerces = Commerce::factory()->count(2)->create();
        $orders = Order::factory()->count(2)->create();

        // Listar comercios (simulación de endpoint admin)
        $response = $this->getJson('/api/admin/commerces');
        $response->assertStatus(200);

        // Listar órdenes (simulación de endpoint admin)
        $response = $this->getJson('/api/admin/orders');
        $response->assertStatus(200);
    }

    public function test_admin_orders_returns_canonical_envelope_with_legacy_aliases()
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        Order::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/orders?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items',
                    'data',
                    'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);
    }

    public function test_admin_can_update_order_status()
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $order = Order::factory()->create(['status' => 'paid']);

        // Cambiar estado de la orden (simulación de endpoint admin)
        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'processing']);
        $response->assertStatus(200);
    }

    public function test_admin_system_health_returns_real_fields_not_placeholders()
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/system-health');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'server_status',
                    'database_status',
                    'database_ping_ms',
                    'memory_usage_mb',
                    'php_version',
                    'laravel_version',
                ],
            ]);
    }

    public function test_admin_realtime_metrics_snapshot_returns_expected_keys()
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/realtime-metrics');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'metrics:realtime:notification_broadcast_emitted_total',
                    'metrics:realtime:broadcast_auth_success_total',
                ],
            ]);
    }
}
