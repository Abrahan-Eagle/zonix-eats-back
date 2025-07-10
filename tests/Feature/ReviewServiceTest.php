<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Commerce;
use App\Models\Product;
use App\Models\Review;
use App\Models\Profile;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $reviewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewService = new ReviewService();
    }

    public function test_can_user_review_with_delivered_order()
    {
        $user = User::factory()->create();
        $commerce = Commerce::factory()->create();
        
        // Crear un pedido entregado
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'commerce_id' => $commerce->id,
            'estado' => 'entregado'
        ]);

        $canReview = $this->reviewService->canUserReview($user->id, $commerce->id, 'App\Models\Commerce');

        $this->assertTrue($canReview);
    }

    public function test_cannot_user_review_without_delivered_order()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        
        // Crear un pedido que no está entregado
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'estado' => 'pendiente_pago'
        ]);

        $canReview = $this->reviewService->canUserReview($user->id, $commerce->id, 'App\Models\Commerce');

        $this->assertFalse($canReview);
    }

    public function test_create_review_successfully()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        
        // Crear un pedido entregado
        Order::factory()->create([
            'user_id' => $user->id,
            'commerce_id' => $commerce->id,
            'estado' => 'entregado'
        ]);

        $this->actingAs($user);

        $data = [
            'reviewable_id' => $commerce->id,
            'reviewable_type' => 'App\Models\Commerce',
            'rating' => 5,
            'comentario' => 'Excelente servicio'
        ];

        $result = $this->reviewService->createReview($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Calificación creada exitosamente', $result['message']);
        $this->assertInstanceOf(Review::class, $result['review']);
    }

    public function test_cannot_create_duplicate_review()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create();
        
        // Crear un pedido entregado
        Order::factory()->create([
            'user_id' => $user->id,
            'commerce_id' => $commerce->id,
            'estado' => 'entregado'
        ]);

        $this->actingAs($user);

        $data = [
            'reviewable_id' => $commerce->id,
            'reviewable_type' => 'App\Models\Commerce',
            'rating' => 5,
            'comentario' => 'Excelente servicio'
        ];

        // Crear primera calificación
        $this->reviewService->createReview($data);

        // Intentar crear segunda calificación
        $result = $this->reviewService->createReview($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('Ya has calificado este elemento', $result['message']);
    }

    public function test_get_average_rating()
    {
        $commerce = Commerce::factory()->create();
        
        // Crear varias calificaciones
        Review::factory()->create([
            'reviewable_id' => $commerce->id,
            'reviewable_type' => 'App\Models\Commerce',
            'rating' => 5
        ]);
        
        Review::factory()->create([
            'reviewable_id' => $commerce->id,
            'reviewable_type' => 'App\Models\Commerce',
            'rating' => 3
        ]);
        
        Review::factory()->create([
            'reviewable_id' => $commerce->id,
            'reviewable_type' => 'App\Models\Commerce',
            'rating' => 4
        ]);

        $averageRating = $this->reviewService->getAverageRating($commerce->id, 'App\Models\Commerce');

        $this->assertEquals(4.0, $averageRating); // (5 + 3 + 4) / 3 = 4
    }

    public function test_get_reviews()
    {
        $commerce = Commerce::factory()->create();
        
        Review::factory()->count(3)->create([
            'reviewable_id' => $commerce->id,
            'reviewable_type' => 'App\Models\Commerce'
        ]);

        $reviews = $this->reviewService->getReviews($commerce->id, 'App\Models\Commerce');

        $this->assertCount(3, $reviews);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $reviews);
    }
} 