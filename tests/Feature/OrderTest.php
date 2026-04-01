<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\Commerce;
use App\Models\Coupon;
use App\Models\OperatorCode;
use App\Models\Phone;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_can_create_upload_comprobante_and_cancel_order()
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Cliente',
            'lastName' => 'Test',
            'address' => 'Calle 123',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
        ]);
        $operatorCode = OperatorCode::firstOrCreate(
            ['code' => 412],
            ['name' => '0412']
        );
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => '1234567',
            'is_primary' => true,
            'status' => true,
        ]);
        $this->actingAs($user, 'sanctum');

        // Crear orden
        $response = $this->postJson('/api/buyer/orders', [
            'commerce_id' => $commerce->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 2]
            ],
            'delivery_type' => 'pickup',
            'total' => $product->price * 2,
            'notes' => 'Sin cebolla',
            'delivery_address' => 'Calle 123'
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $orderId = $response->json('data.id');

        // Subir comprobante
        $file = UploadedFile::fake()->image('comprobante.jpg');
        $response = $this->postJson("/api/buyer/orders/{$orderId}/payment-proof", [
            'payment_proof' => $file,
            'payment_method' => 'mobile_payment',
            'reference_number' => '123456'
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        Storage::disk('public')->assertExists('payment_proofs/' . $file->hashName());

        // Cancelar orden
        $response = $this->postJson("/api/buyer/orders/{$orderId}/cancel", [
            'reason' => 'Cambio de planes'
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_user_cannot_create_order_when_stock_is_insufficient()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Cliente',
            'lastName' => 'Test',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 1,
        ]);
        $operatorCode = OperatorCode::firstOrCreate(
            ['code' => 412],
            ['name' => '0412']
        );
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => '1234567',
            'is_primary' => true,
            'status' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/buyer/orders', [
            'commerce_id' => $commerce->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 2]
            ],
            'delivery_type' => 'pickup',
            'total' => $product->price * 2,
            'delivery_address' => 'Calle 123'
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_orders_index_returns_canonical_envelope()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Cliente',
            'lastName' => 'Test',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);

        \App\Models\Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/buyer/orders?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'data',
                    'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);
    }

    public function test_create_order_is_idempotent_with_same_key()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Cliente',
            'lastName' => 'Test',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 10,
        ]);
        $operatorCode = OperatorCode::firstOrCreate(['code' => 412], ['name' => '0412']);
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => '1234567',
            'is_primary' => true,
            'status' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $payload = [
            'commerce_id' => $commerce->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 1],
            ],
            'delivery_type' => 'pickup',
            'total' => $product->price,
            'delivery_fee' => 0,
            'delivery_address' => 'Calle 123',
        ];

        $first = $this->withHeaders(['Idempotency-Key' => 'idem-order-1'])
            ->postJson('/api/buyer/orders', $payload);
        $first->assertStatus(201)->assertJsonPath('success', true);

        $second = $this->withHeaders(['Idempotency-Key' => 'idem-order-1'])
            ->postJson('/api/buyer/orders', $payload);
        $second->assertStatus(201)->assertJsonPath('success', true);

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertEquals(1, DB::table('orders')->count());
    }

    public function test_create_order_rejects_reused_idempotency_key_with_different_payload()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Cliente',
            'lastName' => 'Test',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 10,
        ]);
        $operatorCode = OperatorCode::firstOrCreate(['code' => 412], ['name' => '0412']);
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => '1234567',
            'is_primary' => true,
            'status' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $firstPayload = [
            'commerce_id' => $commerce->id,
            'products' => [['id' => $product->id, 'quantity' => 1]],
            'delivery_type' => 'pickup',
            'total' => $product->price,
            'delivery_fee' => 0,
            'delivery_address' => 'Calle 123',
        ];
        $secondPayload = [
            'commerce_id' => $commerce->id,
            'products' => [['id' => $product->id, 'quantity' => 2]],
            'delivery_type' => 'pickup',
            'total' => $product->price * 2,
            'delivery_fee' => 0,
            'delivery_address' => 'Calle 123',
        ];

        $this->withHeaders(['Idempotency-Key' => 'idem-order-2'])
            ->postJson('/api/buyer/orders', $firstPayload)
            ->assertStatus(201);

        $this->withHeaders(['Idempotency-Key' => 'idem-order-2'])
            ->postJson('/api/buyer/orders', $secondPayload)
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'ORDER_IDEMPOTENCY_CONFLICT');
    }

    public function test_create_order_applies_coupon_atomically()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Cliente',
            'lastName' => 'Test',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 10,
            'price' => 100,
        ]);
        $coupon = Coupon::factory()->public()->create([
            'code' => 'DESC10',
            'is_active' => true,
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'minimum_order' => 50,
            'usage_limit' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $operatorCode = OperatorCode::firstOrCreate(['code' => 412], ['name' => '0412']);
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => '1234567',
            'is_primary' => true,
            'status' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/buyer/orders', [
            'commerce_id' => $commerce->id,
            'products' => [['id' => $product->id, 'quantity' => 1]],
            'delivery_type' => 'pickup',
            'total' => 100,
            'delivery_fee' => 0,
            'coupon_code' => $coupon->code,
            'delivery_address' => 'Calle 123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('pricing_breakdown.coupon_discount', 10)
            ->assertJsonPath('pricing_breakdown.final_total', 90);

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'profile_id' => $profile->id,
            'order_id' => $orderId,
        ]);
    }
} 