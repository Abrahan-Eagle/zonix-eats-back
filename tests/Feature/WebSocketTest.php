<?php

namespace Tests\Feature;

use App\Events\DeliveryLocationUpdated;
use App\Events\NotificationCreated;
use App\Events\OrderCreated;
use App\Events\OrderPendingAssignment;
use App\Events\OrderStatusChanged;
use App\Events\PaymentValidated;
use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * La suite usa BROADCAST_DRIVER=null en phpunit.xml para no llamar a Pusher al emitir eventos.
 * Los tests de /api/broadcasting/auth activan el driver pusher solo aquí para firmar canales.
 */
class WebSocketTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected $commerce;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario y perfil para las pruebas
        $this->user = User::factory()->create([
            'role' => 'users',
            'google_id' => 'test_google_id_123',
        ]);

        // Crear perfil para el usuario
        Profile::factory()->create(['user_id' => $this->user->id]);
        $this->user->refresh();

        $this->commerce = Commerce::factory()->create([
            'business_name' => 'Test Restaurant',
            'address' => 'Test Address',
            'open' => true,
        ]);

        $this->product = Product::factory()->create([
            'commerce_id' => $this->commerce->id,
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 10.00,
            'available' => true,
        ]);
    }

    /**
     * Firma local de canales privados (sin HTTP a Pusher Cloud).
     */
    protected function configureBroadcastingAuthForTests(): void
    {
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => 'test-app-id',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
        ]);

        // En phpunit el driver por defecto es `null`; los canales se registraron en ese broadcaster.
        // Para firmar con Pusher hay que purgar y volver a registrar rutas de canal en el driver pusher.
        app(BroadcastManager::class)->purge();
        require base_path('routes/channels.php');
    }

    /** @return array<string, string> */
    protected function broadcastingAuthHeaders(User $user): array
    {
        $token = $user->createToken('broadcasting-test')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }

    /** @test */
    public function it_can_broadcast_order_created_event()
    {
        Event::fake();

        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'pending_payment',
            'total' => 25.50,
        ]);

        // Disparar evento
        event(new OrderCreated($order));

        // Verificar que el evento fue disparado
        Event::assertDispatched(OrderCreated::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });

        $channels = (new OrderCreated($order))->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertSame('private-commerce.'.$this->commerce->id, $channels[0]->name);
        $payload = (new OrderCreated($order))->broadcastWith();
        $this->assertArrayHasKey('event_id', $payload);
        $this->assertArrayHasKey('schema_version', $payload);
        $this->assertArrayHasKey('occurred_at', $payload);
    }

    /** @test */
    public function it_can_broadcast_payment_validated_event()
    {
        Event::fake();

        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'pending_payment',
        ]);

        // Disparar evento de pago validado
        event(new PaymentValidated($order, true, 'Test Commerce'));

        // Verificar que el evento fue disparado
        Event::assertDispatched(PaymentValidated::class, function ($event) use ($order) {
            return $event->order->id === $order->id && $event->isValidated === true;
        });
        $payload = (new PaymentValidated($order, true, 'Test Commerce'))->broadcastWith();
        $this->assertArrayHasKey('event_id', $payload);
        $this->assertArrayHasKey('schema_version', $payload);
        $this->assertArrayHasKey('occurred_at', $payload);
    }

    /** @test */
    public function it_can_broadcast_order_status_changed_event()
    {
        Event::fake();

        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'pending_payment',
        ]);

        event(new OrderStatusChanged($order));

        // Verificar que el evento fue disparado
        Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
        $payload = (new OrderStatusChanged($order))->broadcastWith();
        $this->assertArrayHasKey('event_id', $payload);
        $this->assertArrayHasKey('schema_version', $payload);
        $this->assertArrayHasKey('occurred_at', $payload);
    }

    /** @test */
    public function it_can_broadcast_delivery_location_updated_event()
    {
        Event::fake();

        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'shipped',
        ]);

        // Disparar evento de actualización de ubicación
        event(new DeliveryLocationUpdated(
            $order->id,
            1, // delivery_agent_id
            -12.3456, // latitude
            -78.9012, // longitude
            now()->addMinutes(30) // estimated_arrival
        ));

        // Verificar que el evento fue disparado
        Event::assertDispatched(DeliveryLocationUpdated::class, function ($event) use ($order) {
            return $event->orderId === $order->id &&
                   $event->latitude === -12.3456 &&
                   $event->longitude === -78.9012;
        });
        $payload = (new DeliveryLocationUpdated(
            $order->id,
            1,
            -12.3456,
            -78.9012,
            now()->addMinutes(30)
        ))->broadcastWith();
        $this->assertArrayHasKey('event_id', $payload);
        $this->assertArrayHasKey('schema_version', $payload);
        $this->assertArrayHasKey('occurred_at', $payload);
    }

    /** @test */
    public function it_includes_pending_assignment_payload_for_company_ui()
    {
        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'processing',
            'delivery_fee' => 3.25,
            'delivery_address' => 'Av Principal, Valencia',
        ]);

        $event = new OrderPendingAssignment($order);
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('order_id', $payload);
        $this->assertArrayHasKey('order_number', $payload);
        $this->assertArrayHasKey('commerce_name', $payload);
        $this->assertArrayHasKey('delivery_address', $payload);
        $this->assertArrayHasKey('delivery_fee', $payload);
        $this->assertArrayHasKey('event_id', $payload);
        $this->assertArrayHasKey('schema_version', $payload);
        $this->assertArrayHasKey('occurred_at', $payload);
    }

    /** @test */
    public function it_includes_notification_contract_metadata()
    {
        $notification = Notification::create([
            'profile_id' => $this->user->profile->id,
            'title' => 'Test',
            'body' => 'Body',
            'type' => 'system',
            'data' => ['order_id' => '1'],
        ]);

        $payload = (new NotificationCreated($notification))->broadcastWith();
        $this->assertArrayHasKey('event_id', $payload);
        $this->assertArrayHasKey('schema_version', $payload);
        $this->assertArrayHasKey('occurred_at', $payload);
    }

    /** @test */
    public function it_can_access_broadcasting_channels()
    {
        $this->configureBroadcastingAuthForTests();

        // Verificar acceso al canal de usuario (Echo/Pusher: prefijo private-)
        $response = $this->withHeaders($this->broadcastingAuthHeaders($this->user))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-App.Models.User.'.$this->user->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_order_channels()
    {
        $this->configureBroadcastingAuthForTests();
        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
        ]);

        // Verificar acceso al canal de orden
        $response = $this->withHeaders($this->broadcastingAuthHeaders($this->user))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-orders.'.$order->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_denies_access_to_unauthorized_channels()
    {
        $this->configureBroadcastingAuthForTests();
        $otherUser = User::factory()->create();

        // Intentar acceder al canal de otro usuario
        $response = $this->withHeaders($this->broadcastingAuthHeaders($this->user))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-App.Models.User.'.$otherUser->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_access_commerce_channels()
    {
        $this->configureBroadcastingAuthForTests();

        $commerceUser = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $commerceUser->id]);
        $commerce = Commerce::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => true,
        ]);

        $response = $this->withHeaders($this->broadcastingAuthHeaders($commerceUser))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-commerce.'.$commerce->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_denies_commerce_channel_to_non_owner()
    {
        $this->configureBroadcastingAuthForTests();

        $commerceUser = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $commerceUser->id]);
        Commerce::factory()->create(['profile_id' => $profile->id, 'is_primary' => true]);

        $otherCommerce = Commerce::factory()->create();

        $response = $this->withHeaders($this->broadcastingAuthHeaders($commerceUser))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-commerce.'.$otherCommerce->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_access_delivery_agent_channels()
    {
        $this->configureBroadcastingAuthForTests();

        $deliveryUser = User::factory()->create(['role' => 'delivery_agent']);
        $profile = Profile::factory()->create(['user_id' => $deliveryUser->id]);
        $agent = DeliveryAgent::factory()->create(['profile_id' => $profile->id]);

        $response = $this->withHeaders($this->broadcastingAuthHeaders($deliveryUser))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-delivery.'.$agent->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_company_channels()
    {
        $this->configureBroadcastingAuthForTests();

        $companyUser = User::factory()->create(['role' => 'delivery_company']);
        $profile = Profile::factory()->create(['user_id' => $companyUser->id]);
        $company = DeliveryCompany::factory()->create(['profile_id' => $profile->id]);

        $response = $this->withHeaders($this->broadcastingAuthHeaders($companyUser))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-company.'.$company->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_denies_company_channel_to_non_owner()
    {
        $this->configureBroadcastingAuthForTests();

        $companyUser = User::factory()->create(['role' => 'delivery_company']);
        $profile = Profile::factory()->create(['user_id' => $companyUser->id]);
        DeliveryCompany::factory()->create(['profile_id' => $profile->id]);

        $otherCompany = DeliveryCompany::factory()->create();

        $response = $this->withHeaders($this->broadcastingAuthHeaders($companyUser))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-company.'.$otherCompany->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_access_user_alias_channel()
    {
        $this->configureBroadcastingAuthForTests();

        $response = $this->withHeaders($this->broadcastingAuthHeaders($this->user))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-user.'.$this->user->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_general_orders_channel()
    {
        $this->configureBroadcastingAuthForTests();

        $response = $this->withHeaders($this->broadcastingAuthHeaders($this->user))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-orders',
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_presence_chat_channel()
    {
        $this->configureBroadcastingAuthForTests();

        $order = Order::factory()->create([
            'profile_id' => $this->user->profile->id,
            'commerce_id' => $this->commerce->id,
        ]);

        $response = $this->withHeaders($this->broadcastingAuthHeaders($this->user))
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'presence-presence-chat.'.$order->id,
                'socket_id' => '123.456',
            ]);

        $response->assertStatus(200);
    }
}
