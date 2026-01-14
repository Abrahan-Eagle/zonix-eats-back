<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\Order;
use App\Models\ChatMessage;
use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\OrderDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $profile;
    protected $commerce;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'users']);
        $this->profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $this->commerce = Commerce::factory()->create();
        $this->order = Order::factory()->create([
            'profile_id' => $this->profile->id,
            'commerce_id' => $this->commerce->id,
            'status' => 'paid'
        ]);
        
        Sanctum::actingAs($this->user);
    }

    public function test_get_conversations()
    {
        // Crear mensajes de chat para la orden
        ChatMessage::factory()->create([
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
            'content' => 'Test message',
        ]);

        $response = $this->getJson('/api/chat/conversations');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_get_messages()
    {
        ChatMessage::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
        ]);

        $response = $this->getJson("/api/chat/conversations/{$this->order->id}/messages");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
    }

    public function test_send_message()
    {
        $response = $this->postJson("/api/chat/conversations/{$this->order->id}/messages", [
            'content' => 'Hello, this is a test message',
            'type' => 'text',
        ]);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertEquals('Hello, this is a test message', $data['content']);
        
        $this->assertDatabaseHas('chat_messages', [
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
            'content' => 'Hello, this is a test message',
        ]);
    }

    public function test_mark_messages_as_read()
    {
        // Crear mensajes no leídos de otro usuario
        $otherProfile = Profile::factory()->create();
        ChatMessage::factory()->count(2)->create([
            'order_id' => $this->order->id,
            'sender_id' => $otherProfile->id,
            'read_at' => null,
        ]);

        $response = $this->postJson("/api/chat/conversations/{$this->order->id}/read");

        $response->assertStatus(200);
        $response->assertJson(['marked' => true]);
        
        // Verificar que los mensajes fueron marcados como leídos
        $this->assertDatabaseMissing('chat_messages', [
            'order_id' => $this->order->id,
            'sender_id' => $otherProfile->id,
            'read_at' => null,
        ]);
    }

    public function test_create_conversation()
    {
        $response = $this->postJson('/api/chat/conversations', [
            'order_id' => $this->order->id,
        ]);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertEquals($this->order->id, $data['id']);
        $this->assertEquals('order', $data['type']);
    }

    public function test_delete_conversation()
    {
        // Crear mensajes del usuario actual
        ChatMessage::factory()->count(2)->create([
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
        ]);

        $response = $this->deleteJson("/api/chat/conversations/{$this->order->id}");

        $response->assertStatus(200);
        $response->assertJson(['deleted' => true]);
        
        // Verificar que los mensajes fueron eliminados
        $this->assertDatabaseMissing('chat_messages', [
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
        ]);
    }

    public function test_search_messages()
    {
        ChatMessage::factory()->create([
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
            'content' => 'Mensaje de prueba para búsqueda',
        ]);

        $response = $this->getJson('/api/chat/search?q=prueba');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_block_user()
    {
        $otherUser = User::factory()->create();

        $response = $this->postJson('/api/chat/block', [
            'user_id' => $otherUser->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['blocked' => true]);
    }

    public function test_unblock_user()
    {
        $otherUser = User::factory()->create();

        $response = $this->deleteJson("/api/chat/block/{$otherUser->id}");

        $response->assertStatus(200);
        $response->assertJson(['unblocked' => true]);
    }

    public function test_get_blocked_users()
    {
        $response = $this->getJson('/api/chat/blocked-users');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
    }

    public function test_cannot_access_other_user_order_messages()
    {
        $otherUser = User::factory()->create(['role' => 'users']);
        $otherProfile = Profile::factory()->create(['user_id' => $otherUser->id]);
        $otherOrder = Order::factory()->create([
            'profile_id' => $otherProfile->id,
            'commerce_id' => $this->commerce->id,
        ]);

        $response = $this->getJson("/api/chat/conversations/{$otherOrder->id}/messages");

        $response->assertStatus(403);
    }

    public function test_commerce_can_access_order_messages()
    {
        $commerceUser = User::factory()->create(['role' => 'commerce']);
        $commerceProfile = Profile::factory()->create(['user_id' => $commerceUser->id]);
        $this->commerce->update(['profile_id' => $commerceProfile->id]);
        
        Sanctum::actingAs($commerceUser);

        ChatMessage::factory()->create([
            'order_id' => $this->order->id,
            'sender_id' => $this->profile->id,
        ]);

        $response = $this->getJson("/api/chat/conversations/{$this->order->id}/messages");

        $response->assertStatus(200);
    }
}
