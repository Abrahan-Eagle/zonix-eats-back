<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryCompanyAssignOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_order_returns_conflict_when_order_already_assigned(): void
    {
        $companyUser = User::factory()->create(['role' => 'delivery_company']);
        $companyProfile = Profile::factory()->create(['user_id' => $companyUser->id]);
        $company = DeliveryCompany::factory()->create(['profile_id' => $companyProfile->id]);

        $agentA = DeliveryAgent::factory()->create(['company_id' => $company->id]);
        $agentB = DeliveryAgent::factory()->create(['company_id' => $company->id]);

        $commerce = Commerce::factory()->create(['open' => true]);
        $order = Order::factory()->create([
            'status' => 'shipped',
            'commerce_id' => $commerce->id,
            'delivery_company_id' => $company->id,
        ]);

        Sanctum::actingAs($companyUser);

        $firstResponse = $this->postJson("/api/delivery-company/orders/{$order->id}/assign", [
            'agent_id' => $agentA->id,
        ]);
        $firstResponse->assertStatus(200);

        $secondResponse = $this->postJson("/api/delivery-company/orders/{$order->id}/assign", [
            'agent_id' => $agentB->id,
        ]);

        $secondResponse->assertStatus(409)
            ->assertJsonPath('success', false);
    }
}
