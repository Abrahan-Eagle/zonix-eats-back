<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Commerce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class DeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $deliveryUser;
    protected $deliveryAgent;
    protected $profile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->deliveryUser = User::factory()->create(['role' => 'delivery']);
        $this->profile = Profile::factory()->create(['user_id' => $this->deliveryUser->id]);
        $this->deliveryAgent = DeliveryAgent::factory()->create(['profile_id' => $this->profile->id]);
        
        Sanctum::actingAs($this->deliveryUser);
    }

    public function test_get_available_orders()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create(['status' => 'paid', 'commerce_id' => $commerce->id]);
        $order2 = Order::factory()->create(['status' => 'preparing', 'commerce_id' => $commerce->id]);
        $order3 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);

        $response = $this->getJson('/api/delivery/available-orders');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Solo paid y preparing
    }

    public function test_get_assigned_orders()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create(['status' => 'on_way', 'commerce_id' => $commerce->id]);
        $order2 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order1->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'assigned'
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order2->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered'
        ]);

        $response = $this->getJson("/api/delivery/assigned-orders/{$this->deliveryAgent->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_accept_order()
    {
        $commerce = Commerce::factory()->create();
        $order = Order::factory()->create(['status' => 'paid', 'commerce_id' => $commerce->id]);

        $response = $this->postJson("/api/delivery/orders/{$order->id}/accept", [
            'notes' => 'Test notes'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Orden aceptada']);

        $this->assertDatabaseHas('order_delivery', [
            'order_id' => $order->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'assigned'
        ]);

        $order->refresh();
        $this->assertEquals('on_way', $order->status);
    }

    public function test_update_location()
    {
        $response = $this->postJson('/api/delivery/location/update', [
            'latitude' => -12.0464,
            'longitude' => -77.0428
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->deliveryAgent->refresh();
        $this->assertEquals(-12.0464, $this->deliveryAgent->current_latitude);
        $this->assertEquals(-77.0428, $this->deliveryAgent->current_longitude);
    }

    public function test_get_statistics()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);
        $order2 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order1->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered',
            'costo_envio' => 10.50
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order2->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered',
            'costo_envio' => 15.00
        ]);

        $response = $this->getJson("/api/delivery/statistics/{$this->deliveryAgent->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data' => [
                     'total_deliveries',
                     'completed_deliveries',
                     'cancelled_deliveries',
                     'total_earnings'
                 ]]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_deliveries']);
        $this->assertEquals(2, $data['completed_deliveries']);
        $this->assertEquals(25.50, $data['total_earnings']);
    }

    public function test_get_history()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);
        $order2 = Order::factory()->create(['status' => 'cancelled', 'commerce_id' => $commerce->id]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order1->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered'
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order2->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'cancelled'
        ]);

        $response = $this->getJson("/api/delivery/history/{$this->deliveryAgent->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_get_history_with_date_filters()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create([
            'status' => 'delivered',
            'commerce_id' => $commerce->id,
            'created_at' => now()->subDays(5)
        ]);
        $order2 = Order::factory()->create([
            'status' => 'delivered',
            'commerce_id' => $commerce->id,
            'created_at' => now()->subDays(10)
        ]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order1->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered'
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order2->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered'
        ]);

        $startDate = now()->subDays(7)->toIso8601String();
        $endDate = now()->toIso8601String();

        $response = $this->getJson("/api/delivery/history/{$this->deliveryAgent->id}?" . http_build_query([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data); // Solo order1 está en el rango
    }

    public function test_get_earnings()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);
        $order2 = Order::factory()->create(['status' => 'delivered', 'commerce_id' => $commerce->id]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order1->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered',
            'costo_envio' => 10.00,
            'updated_at' => now()
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order2->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'delivered',
            'costo_envio' => 15.00,
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/delivery/earnings/{$this->deliveryAgent->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data' => [
                     'total_earnings',
                     'total_deliveries',
                     'average_delivery_time',
                     'today_earnings',
                     'weekly_earnings',
                     'monthly_earnings'
                 ]]);

        $data = $response->json('data');
        $this->assertEquals(25.00, $data['total_earnings']);
        $this->assertEquals(2, $data['total_deliveries']);
    }

    public function test_get_routes()
    {
        $commerce = Commerce::factory()->create();
        $order1 = Order::factory()->create(['status' => 'on_way', 'commerce_id' => $commerce->id]);
        $order2 = Order::factory()->create(['status' => 'on_way', 'commerce_id' => $commerce->id]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order1->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'on_way'
        ]);
        OrderDelivery::factory()->create([
            'order_id' => $order2->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'assigned'
        ]);

        $response = $this->getJson("/api/delivery/routes/{$this->deliveryAgent->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_report_issue()
    {
        $commerce = Commerce::factory()->create();
        $order = Order::factory()->create(['status' => 'on_way', 'commerce_id' => $commerce->id]);
        
        OrderDelivery::factory()->create([
            'order_id' => $order->id,
            'agent_id' => $this->deliveryAgent->id,
            'status' => 'on_way'
        ]);

        $response = $this->postJson("/api/delivery/orders/{$order->id}/report-issue", [
            'issue' => 'Customer not available',
            'description' => 'Customer did not answer the door'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Issue reported successfully']);
    }

    public function test_cannot_accept_already_assigned_order()
    {
        $commerce = Commerce::factory()->create();
        $order = Order::factory()->create(['status' => 'paid', 'commerce_id' => $commerce->id]);
        
        // Assign order to another agent
        $otherAgent = DeliveryAgent::factory()->create();
        OrderDelivery::factory()->create([
            'order_id' => $order->id,
            'agent_id' => $otherAgent->id,
            'status' => 'assigned'
        ]);

        $response = $this->postJson("/api/delivery/orders/{$order->id}/accept", [
            'notes' => 'Test'
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'message' => 'Order already assigned']);
    }
}
