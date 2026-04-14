<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $reviewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewService = new ReviewService;
    }

    public function test_can_user_review_with_delivered_order()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();

        // Crear un pedido entregado
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        $canReview = $this->reviewService->canUserReview($order->id, $user->id);

        $this->assertTrue($canReview);
    }

    public function test_cannot_user_review_without_delivered_order()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();

        // Crear un pedido que no está entregado
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'pending_payment',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        $canReview = $this->reviewService->canUserReview($order->id, $user->id);

        $this->assertFalse($canReview);
    }

    public function test_create_review_successfully()
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);
        $this->actingAs($user);
        $data = [
            'order_id' => $order->id,
            'type' => 'restaurant',
            'rating' => 5,
            'comment' => 'Excelente servicio',
        ];

        $result = $this->reviewService->createReview($data + ['user_id' => $user->id]);

        $this->assertInstanceOf(Review::class, $result);
        $this->assertEquals(5, $result->rating);
        $this->assertEquals('Excelente servicio', $result->comment);
        $this->assertEquals($profile->id, $result->profile_id);
        $this->assertEquals('App\\Models\\Commerce', $result->reviewable_type);
        $this->assertEquals($commerce->id, $result->reviewable_id);
    }

    public function test_cannot_create_duplicate_review()
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);
        $this->actingAs($user);
        $data = [
            'order_id' => $order->id,
            'type' => 'restaurant',
            'rating' => 5,
            'comment' => 'Excelente',
        ];

        // Crear primera calificación
        $this->reviewService->createReview($data + ['user_id' => $user->id]);

        // Intentar crear segunda calificación - debería fallar
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ya has calificado este elemento');

        $this->reviewService->createReview($data + ['user_id' => $user->id]);
    }

    public function test_get_average_rating()
    {
        $commerce = Commerce::factory()->create();

        // Crear 3 usuarios diferentes para evitar conflictos de reviews duplicados
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $profile = Profile::factory()->create(['user_id' => $user->id]);
            $order = Order::create([
                'profile_id' => $profile->id,
                'commerce_id' => $commerce->id,
                'delivery_type' => 'pickup',
                'status' => 'delivered',
                'total' => 50.00,
                'notes' => 'Test order',
            ]);
            $this->actingAs($user);
            $data = [
                'order_id' => $order->id,
                'type' => 'restaurant',
                'rating' => $i === 0 ? 5 : ($i === 1 ? 3 : 4),
                'comment' => "Review {$i}",
                'user_id' => $user->id,
            ];
            $this->reviewService->createReview($data);
        }

        $averageRating = $this->reviewService->getRestaurantAverageRating($commerce->id);
        $this->assertEquals(4.0, $averageRating); // (5 + 3 + 4) / 3 = 4
    }

    public function test_get_reviews()
    {
        $commerce = Commerce::factory()->create();

        // Crear 3 usuarios diferentes para evitar conflictos de reviews duplicados
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $profile = Profile::factory()->create(['user_id' => $user->id]);
            $order = Order::create([
                'profile_id' => $profile->id,
                'commerce_id' => $commerce->id,
                'delivery_type' => 'pickup',
                'status' => 'delivered',
                'total' => 50.00,
                'notes' => 'Test order',
            ]);
            $this->actingAs($user);
            $data = [
                'order_id' => $order->id,
                'type' => 'restaurant',
                'rating' => 4,
                'comment' => "Review {$i}",
                'user_id' => $user->id,
            ];
            $this->reviewService->createReview($data);
        }

        $reviews = $this->reviewService->getRestaurantReviews($commerce->id);
        $this->assertCount(3, $reviews);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $reviews);
    }

    public function test_buyer_can_rate_restaurant_via_api()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/buyer/reviews/restaurant', [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Excelente',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'profile_id' => $profile->id,
            'reviewable_type' => 'App\\Models\\Commerce',
            'reviewable_id' => $commerce->id,
            'rating' => 5,
        ]);
    }

    public function test_cannot_rate_non_delivered_order_via_api()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'processing',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/buyer/reviews/restaurant', [
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Buen servicio',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_review_routes_require_users_role()
    {
        $user = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/buyer/reviews/restaurant', [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Excelente',
        ]);

        $response->assertStatus(403);
    }

    public function test_delivery_review_uses_order_delivery_agent_relation()
    {
        $buyer = User::factory()->create(['role' => 'users']);
        $buyerProfile = Profile::factory()->create(['user_id' => $buyer->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $buyerProfile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'delivery',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        $agentUser = User::factory()->create(['role' => 'delivery_agent']);
        $agentProfile = Profile::factory()->create(['user_id' => $agentUser->id]);
        $agent = DeliveryAgent::factory()->create(['profile_id' => $agentProfile->id]);
        OrderDelivery::factory()->create([
            'order_id' => $order->id,
            'agent_id' => $agent->id,
            'status' => 'delivered',
        ]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson('/api/buyer/reviews/delivery-agent', [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Muy atento',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'profile_id' => $buyerProfile->id,
            'reviewable_type' => 'App\\Models\\DeliveryAgent',
            'reviewable_id' => $agent->id,
            'rating' => 5,
        ]);
    }

    public function test_duplicate_restaurant_review_returns_409_conflict()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 50.00,
            'notes' => 'Test order',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/buyer/reviews/restaurant', [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Excelente',
        ])->assertStatus(200);

        $duplicate = $this->postJson('/api/buyer/reviews/restaurant', [
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Repetido',
        ]);

        $duplicate->assertStatus(409)
            ->assertJsonPath('error_code', 'REVIEWS_DUPLICATE_REVIEW');
    }

    public function test_buyer_can_report_review_for_moderation()
    {
        $reporter = User::factory()->create(['role' => 'users']);
        $reporterProfile = Profile::factory()->create(['user_id' => $reporter->id]);

        $author = User::factory()->create(['role' => 'users']);
        $authorProfile = Profile::factory()->create(['user_id' => $author->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $authorProfile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 40.00,
            'notes' => 'Order for report',
        ]);

        $review = Review::create([
            'profile_id' => $authorProfile->id,
            'order_id' => $order->id,
            'reviewable_type' => 'App\\Models\\Commerce',
            'reviewable_id' => $commerce->id,
            'rating' => 2,
            'comment' => 'Comentario ofensivo',
        ]);

        Sanctum::actingAs($reporter);

        $response = $this->postJson("/api/buyer/reviews/{$review->id}/report", [
            'reason' => 'Lenguaje ofensivo',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.moderation_status', 'reported');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'moderation_status' => 'reported',
            'reported_reason' => 'Lenguaje ofensivo',
            'reported_by_profile_id' => $reporterProfile->id,
        ]);
    }

    public function test_admin_can_list_reported_reviews()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'users']);
        $authorProfile = Profile::factory()->create(['user_id' => $author->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $authorProfile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 30.00,
            'notes' => 'Order for moderation listing',
        ]);

        $review = Review::create([
            'profile_id' => $authorProfile->id,
            'order_id' => $order->id,
            'reviewable_type' => 'App\\Models\\Commerce',
            'reviewable_id' => $commerce->id,
            'rating' => 1,
            'comment' => 'Mala experiencia',
            'moderation_status' => 'reported',
        ]);

        Sanctum::actingAs($admin);
        $response = $this->getJson('/api/admin/reviews/reported');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reviews.0.id', $review->id);
    }

    public function test_admin_can_moderate_reported_review()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'users']);
        $authorProfile = Profile::factory()->create(['user_id' => $author->id]);
        $commerce = Commerce::factory()->create();
        $order = Order::create([
            'profile_id' => $authorProfile->id,
            'commerce_id' => $commerce->id,
            'delivery_type' => 'pickup',
            'status' => 'delivered',
            'total' => 35.00,
            'notes' => 'Order for moderation action',
        ]);

        $review = Review::create([
            'profile_id' => $authorProfile->id,
            'order_id' => $order->id,
            'reviewable_type' => 'App\\Models\\Commerce',
            'reviewable_id' => $commerce->id,
            'rating' => 2,
            'comment' => 'Reporte pendiente',
            'moderation_status' => 'reported',
        ]);

        Sanctum::actingAs($admin);
        $response = $this->postJson("/api/admin/reviews/{$review->id}/moderate", [
            'action' => 'rejected',
            'reason' => 'Contenido inapropiado confirmado',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.moderation_status', 'rejected');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'moderation_status' => 'rejected',
        ]);
    }
}
