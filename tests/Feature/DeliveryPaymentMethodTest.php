<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bank;
use App\Models\Profile;
use App\Models\DeliveryAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_can_crud_payment_methods_and_no_duplicates()
    {
        $user = User::factory()->create(['role' => 'delivery']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $delivery = DeliveryAgent::factory()->create(['profile_id' => $profile->id]);
        $bank = Bank::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Crear método de pago
        $response = $this->postJson('/api/delivery/payment-methods', [
            'type' => 'mobile_payment',
            'bank_id' => $bank->id,
            'account_number' => '01021234567890123456',
            'phone' => '04141234567',
            'owner_name' => 'Pedro Delivery',
            'owner_id' => '87654321',
            'is_default' => true
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $id = $response->json('data.id');

        // Listar métodos de pago
        $response = $this->getJson('/api/delivery/payment-methods');
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('data'));

        // Intentar duplicado
        $response = $this->postJson('/api/delivery/payment-methods', [
            'type' => 'mobile_payment',
            'bank_id' => $bank->id,
            'account_number' => '01021234567890123456',
            'phone' => '04141234567',
            'owner_name' => 'Pedro Delivery',
            'owner_id' => '87654321',
            'is_default' => false
        ]);
        $response->assertStatus(422)->assertJson(['success' => false]);

        // Actualizar método de pago
        $response = $this->putJson("/api/delivery/payment-methods/$id", [
            'phone' => '04140000000',
            'is_default' => false
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('04140000000', $response->json('data.phone'));

        // Eliminar método de pago
        $response = $this->deleteJson("/api/delivery/payment-methods/$id");
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('delivery_payment_methods', ['id' => $id]);
    }
} 