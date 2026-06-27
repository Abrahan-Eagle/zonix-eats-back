<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_payment_methods(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $bank = Bank::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/payment-methods', [
            'type' => 'mobile_payment',
            'bank_id' => $bank->id,
            'phone' => '04141234567',
            'owner_name' => 'Juan Pérez',
            'owner_id' => '12345678',
            'is_default' => true,
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $id = $response->json('data.id');

        $this->getJson('/api/payment-methods')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->putJson("/api/payment-methods/$id", [
            'phone' => '04140000000',
        ])->assertStatus(200);

        $this->assertDatabaseHas('payment_methods', [
            'payable_type' => User::class,
            'payable_id' => $user->id,
        ]);
    }
}
