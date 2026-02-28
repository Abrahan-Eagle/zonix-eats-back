<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\Order;
use App\Models\Commerce;
use App\Models\Dispute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class DisputeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $buyerProfile;
    protected $commerce;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'users']);
        $this->buyerProfile = Profile::factory()->create(['user_id' => $this->buyer->id]);
        $this->commerce = Commerce::factory()->create();
        $this->order = Order::factory()->create([
            'profile_id' => $this->buyerProfile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function buyer_can_list_own_disputes()
    {
        Sanctum::actingAs($this->buyer);

        Dispute::factory()->create([
            'order_id' => $this->order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $this->buyerProfile->id,
            'reported_against_type' => 'App\\Models\\Commerce',
            'reported_against_id' => $this->commerce->id,
            'type' => 'other',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/buyer/disputes');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'pagination']);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    /** @test */
    public function buyer_can_create_dispute_for_eligible_order()
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson('/api/buyer/disputes', [
            'order_id' => $this->order->id,
            'type' => 'other',
            'description' => 'Descripción detallada del problema con al menos diez caracteres.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_id', $this->order->id)
            ->assertJsonPath('data.type', 'other')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('disputes', [
            'order_id' => $this->order->id,
            'reported_by_id' => $this->buyerProfile->id,
            'type' => 'other',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function buyer_cannot_create_second_open_dispute_for_same_order()
    {
        Sanctum::actingAs($this->buyer);

        Dispute::factory()->create([
            'order_id' => $this->order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $this->buyerProfile->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/buyer/disputes', [
            'order_id' => $this->order->id,
            'type' => 'other',
            'description' => 'Otra descripción con suficientes caracteres para la validación.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Ya existe una disputa abierta para esta orden');
    }

    /** @test */
    public function buyer_can_show_own_dispute()
    {
        Sanctum::actingAs($this->buyer);

        $dispute = Dispute::factory()->create([
            'order_id' => $this->order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $this->buyerProfile->id,
            'type' => 'other',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/buyer/disputes/{$dispute->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $dispute->id);
    }

    /** @test */
    public function buyer_cannot_see_other_user_dispute()
    {
        $otherUser = User::factory()->create(['role' => 'users']);
        $otherProfile = Profile::factory()->create(['user_id' => $otherUser->id]);
        $otherOrder = Order::factory()->create([
            'profile_id' => $otherProfile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'paid',
        ]);

        $dispute = Dispute::factory()->create([
            'order_id' => $otherOrder->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $otherProfile->id,
            'type' => 'other',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->buyer);

        $response = $this->getJson("/api/buyer/disputes/{$dispute->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_list_all_disputes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        Dispute::factory()->count(2)->create([
            'order_id' => $this->order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $this->buyerProfile->id,
            'type' => 'other',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/admin/disputes');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'pagination']);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    /** @test */
    public function admin_can_show_dispute()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $dispute = Dispute::factory()->create([
            'order_id' => $this->order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $this->buyerProfile->id,
            'type' => 'other',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/admin/disputes/{$dispute->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $dispute->id);
    }

    /** @test */
    public function admin_can_resolve_dispute()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $dispute = Dispute::factory()->create([
            'order_id' => $this->order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $this->buyerProfile->id,
            'type' => 'other',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/admin/disputes/{$dispute->id}/resolve", [
            'resolution' => 'closed',
            'admin_notes' => 'Resolución aplicada tras revisión.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'closed');

        $dispute->refresh();
        $this->assertEquals('closed', $dispute->status);
        $this->assertNotNull($dispute->resolved_at);
    }

    /** @test */
    public function admin_can_get_dispute_stats()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/disputes/stats');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['total', 'pending', 'in_review', 'resolved', 'closed']]);
    }

    /** @test */
    public function unauthenticated_cannot_access_buyer_disputes()
    {
        $this->getJson('/api/buyer/disputes')->assertStatus(401);
        $this->postJson('/api/buyer/disputes', [
            'order_id' => $this->order->id,
            'type' => 'other',
            'description' => 'Descripción válida de más de diez caracteres.',
        ])->assertStatus(401);
    }
}
