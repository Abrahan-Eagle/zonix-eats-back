<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bank;
use App\Models\UserPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_crud_payment_methods()
    {
        $user = User::factory()->create(['role' => 'users']);
        $bank = Bank::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Crear método de pago
        $response = $this->postJson('/api/user/payment-methods', [
            'type' => 'mobile_payment',
            'bank_id' => $bank->id,
            'account_number' => '01021234567890123456',
            'phone' => '04141234567',
            'owner_name' => 'Juan Pérez',
            'owner_id' => '12345678',
            'is_default' => true
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $id = $response->json('data.id');

        // Listar métodos de pago
        $response = $this->getJson('/api/user/payment-methods');
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('data'));

        // Actualizar método de pago
        $response = $this->putJson("/api/user/payment-methods/$id", [
            'phone' => '04140000000',
            'is_default' => false
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('04140000000', $response->json('data.phone'));

        // Eliminar método de pago
        $response = $this->deleteJson("/api/user/payment-methods/$id");
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('user_payment_methods', ['id' => $id]);
    }
} 