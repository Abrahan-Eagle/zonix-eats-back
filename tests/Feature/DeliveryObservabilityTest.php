<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\DeliveryAssignmentTimeout;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryObservabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_delivery_observability_summary_and_incidents(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Profile::factory()->create(['user_id' => $admin->id]);

        $company = DeliveryCompany::factory()->create();
        $commerce = Commerce::factory()->create(['open' => true]);
        $order = Order::factory()->create([
            'status' => 'shipped',
            'delivery_type' => 'delivery',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $company->id,
            'created_at' => now()->subMinutes(15),
        ]);
        DeliveryAssignmentTimeout::create([
            'order_id' => $order->id,
            'company_id' => $company->id,
            'occurred_at' => now()->subMinutes(10),
            'source' => 'test',
        ]);

        Sanctum::actingAs($admin);
        Cache::put('obs:delivery:heartbeat:alerts_last_run_at', now()->subMinutes(3)->toISOString(), now()->addHour());
        Cache::put('obs:delivery:heartbeat:snapshots_last_run_at', now()->subMinutes(30)->toISOString(), now()->addHour());

        $summary = $this->getJson('/api/admin/delivery/observability/summary');
        $summary->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpi.timeout_count', 1)
            ->assertJsonPath('data.kpi.scheduler_health.alerts_healthy', true)
            ->assertJsonPath('data.kpi.scheduler_health.snapshots_healthy', true)
            ->assertJsonStructure([
                'data' => [
                    'correlation_id',
                    'window_hours',
                    'kpi' => [
                        'orders_total',
                        'avg_assignment_minutes',
                        'avg_delivery_minutes',
                        'assignment_percentiles',
                        'delivery_percentiles',
                        'timeout_count',
                        'timeout_ratio_percent',
                        'agent_no_response_ratio_percent',
                        'success_ratio_percent',
                        'cancelled_ratio_percent',
                        'unassigned_over_threshold',
                        'frozen_tracking_count',
                        'incident_detection_latency_p95_seconds',
                        'scheduler_health',
                    ],
                ],
            ]);

        $incidents = $this->getJson('/api/admin/delivery/observability/incidents?page=1&per_page=10&type=unassigned_order');
        $incidents->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['items', 'total', 'page', 'per_page', 'correlation_id'],
            ]);

        $incidentOrders = $this->getJson('/api/admin/delivery/observability/incident-orders?type=unassigned_order');
        $incidentOrders->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['items', 'total', 'page', 'per_page', 'last_page'],
            ]);
    }

    public function test_delivery_company_can_get_scoped_observability_data(): void
    {
        $companyUser = User::factory()->create(['role' => 'delivery_company']);
        $companyProfile = Profile::factory()->create(['user_id' => $companyUser->id]);
        $company = DeliveryCompany::factory()->create(['profile_id' => $companyProfile->id]);

        $otherCompany = DeliveryCompany::factory()->create();
        $commerce = Commerce::factory()->create(['open' => true]);

        Order::factory()->create([
            'status' => 'shipped',
            'delivery_type' => 'delivery',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $company->id,
            'created_at' => now()->subMinutes(20),
        ]);

        $otherOrder = Order::factory()->create([
            'status' => 'shipped',
            'delivery_type' => 'delivery',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $otherCompany->id,
        ]);
        $otherAgent = DeliveryAgent::factory()->create(['company_id' => $otherCompany->id]);
        OrderDelivery::factory()->create([
            'order_id' => $otherOrder->id,
            'agent_id' => $otherAgent->id,
            'status' => 'in_transit',
        ]);

        Sanctum::actingAs($companyUser);

        $summary = $this->getJson('/api/delivery-company/observability/summary');
        $summary->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpi.unassigned_over_threshold', 1);

        $incidents = $this->getJson('/api/delivery-company/observability/incidents');
        $incidents->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 1);

        $runbooks = $this->getJson('/api/delivery-company/observability/runbooks');
        $runbooks->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['items'],
            ]);
    }
}
