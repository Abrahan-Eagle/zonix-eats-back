<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Coupon;
use App\Models\OperatorCode;
use App\Models\Order;
use App\Models\Phone;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
            // Stock explícito: el factory usa stock aleatorio (0–100 o null); con qty 2 puede devolver 400 por stock.
            'stock_quantity' => 100,
            'price' => 12.50,
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

        $product->refresh();
        $expectedTotal = round((float) $product->price * 2, 2);

        // Crear orden (pickup: delivery_fee 0 explícito, mismo patrón que el resto de OrderTest)
        $response = $this->postJson('/api/buyer/orders', [
            'commerce_id' => $commerce->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
            'delivery_type' => 'pickup',
            'delivery_fee' => 0,
            'total' => $expectedTotal,
            'notes' => 'Sin cebolla',
            'delivery_address' => 'Calle 123',
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $orderId = $response->json('data.id');

        // Subir comprobante
        $file = UploadedFile::fake()->image('comprobante.jpg');
        $response = $this->postJson("/api/buyer/orders/{$orderId}/payment-proof", [
            'payment_proof' => $file,
            'payment_method' => 'mobile_payment',
            'reference_number' => '123456',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        Storage::disk('public')->assertExists('payment_proofs/'.$file->hashName());

        // Cancelar orden
        $response = $this->postJson("/api/buyer/orders/{$orderId}/cancel", [
            'reason' => 'Cambio de planes',
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
                ['id' => $product->id, 'quantity' => 2],
            ],
            'delivery_type' => 'pickup',
            'total' => $product->price * 2,
            'delivery_address' => 'Calle 123',
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

        Order::factory()->create([
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

    public function test_buyer_cannot_create_order_when_max_concurrent_open_reached(): void
    {
        config(['zonix.buyer_max_concurrent_open_orders' => 2]);

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

        Order::factory()->count(2)->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'processing',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/buyer/orders', [
            'commerce_id' => $commerce->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 1],
            ],
            'delivery_type' => 'pickup',
            'total' => $product->price,
            'delivery_fee' => 0,
            'delivery_address' => 'Calle 123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'ORDER_MAX_CONCURRENT_OPEN')
            ->assertJsonPath('max_open_orders', 2)
            ->assertJsonPath('current_open_orders', 2);
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

    public function test_create_order_returns_in_progress_when_same_key_is_processing()
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
            'products' => [['id' => $product->id, 'quantity' => 1]],
            'delivery_type' => 'pickup',
            'total' => $product->price,
            'delivery_fee' => 0,
            'delivery_address' => 'Calle 123',
        ];

        $requestFingerprint = hash('sha256', json_encode([
            'commerce_id' => $payload['commerce_id'],
            'products' => $payload['products'],
            'delivery_type' => $payload['delivery_type'],
            'delivery_fee' => $payload['delivery_fee'],
            'coupon_code' => '',
            'delivery_address' => $payload['delivery_address'],
            'delivery_latitude' => null,
            'delivery_longitude' => null,
        ]));

        DB::table('order_idempotency_keys')->insert([
            'profile_id' => $profile->id,
            'idempotency_key' => 'idem-order-processing',
            'request_fingerprint' => $requestFingerprint,
            'order_id' => null,
            'response_payload' => null,
            'status_code' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeaders(['Idempotency-Key' => 'idem-order-processing'])
            ->postJson('/api/buyer/orders', $payload)
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'ORDER_IDEMPOTENCY_IN_PROGRESS');
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

    public function test_buyer_cannot_track_another_users_order(): void
    {
        $buyerA = User::factory()->create(['role' => 'users']);
        $profileA = Profile::factory()->create(['user_id' => $buyerA->id]);
        $buyerB = User::factory()->create(['role' => 'users']);
        $profileB = Profile::factory()->create(['user_id' => $buyerB->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profileA->id, 'open' => true]);

        $orderOfB = \App\Models\Order::factory()->create([
            'profile_id' => $profileB->id,
            'commerce_id' => $commerce->id,
            'status' => 'processing',
        ]);

        $this->actingAs($buyerA, 'sanctum');
        $this->getJson("/api/buyer/tracking/order/{$orderOfB->id}")
            ->assertStatus(404);
    }

    public function test_buyer_cancel_order_registers_status_history(): void
    {
        $buyer = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $buyer->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $order = \App\Models\Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($buyer, 'sanctum');
        $response = $this->postJson("/api/buyer/orders/{$order->id}/cancel", [
            'reason' => 'Cambio de planes',
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'from_status' => 'pending_payment',
            'to_status' => 'cancelled',
            'actor_role' => 'buyer',
            'actor_id' => $profile->id,
            'source' => 'buyer_api_cancel',
        ]);
    }

    public function test_buyer_tracking_endpoint_includes_timeline(): void
    {
        $buyer = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $buyer->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $order = \App\Models\Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'processing',
        ]);

        DB::table('order_status_history')->insert([
            [
                'order_id' => $order->id,
                'from_status' => 'pending_payment',
                'to_status' => 'paid',
                'actor_role' => 'commerce',
                'actor_id' => $profile->id,
                'source' => 'test',
                'reason' => null,
                'occurred_at' => now()->subMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'from_status' => 'paid',
                'to_status' => 'processing',
                'actor_role' => 'commerce',
                'actor_id' => $profile->id,
                'source' => 'test',
                'reason' => null,
                'occurred_at' => now()->subMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($buyer, 'sanctum');
        $response = $this->getJson("/api/buyer/orders/{$order->id}/tracking");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'timeline',
                ],
            ]);
    }
}
