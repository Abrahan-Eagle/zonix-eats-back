<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Crear una nueva calificación.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'type' => 'required|string|in:restaurant,delivery',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $review = $this->reviewService->createReview($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'review_id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                ],
                'message' => 'Calificación creada exitosamente',
            ], 201);
        } catch (QueryException $e) {
            $isDuplicated = ($e->getCode() ?? '') === '23000';

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $isDuplicated ? 'Ya has calificado este elemento' : 'No se pudo crear la calificación',
                'error_code' => $isDuplicated ? 'REVIEWS_DUPLICATE_REVIEW' : 'REVIEWS_CREATE_ERROR',
            ], $isDuplicated ? 409 : 400);
        } catch (\Exception $e) {
            $isDuplicated = str_contains(strtolower($e->getMessage()), 'ya has calificado');

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'error_code' => $isDuplicated ? 'REVIEWS_DUPLICATE_REVIEW' : 'REVIEWS_CREATE_ERROR',
            ], $isDuplicated ? 409 : 400);
        }
    }

    /**
     * Obtener calificaciones de un elemento.
     *
     * @param  int  $reviewableId
     * @param  string  $reviewableType
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($reviewableId, $reviewableType)
    {
        if ($reviewableType === 'App\\Models\\Commerce') {
            $reviews = $this->reviewService->getRestaurantReviews($reviewableId);
            $averageRating = $this->reviewService->getRestaurantAverageRating($reviewableId);
        } elseif ($reviewableType === 'App\\Models\\DeliveryAgent') {
            $reviews = $this->reviewService->getDeliveryAgentReviews($reviewableId);
            $averageRating = $this->reviewService->getDeliveryAgentAverageRating($reviewableId);
        } else {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Tipo de review no soportado',
                'error_code' => 'REVIEWS_INVALID_TYPE',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'average_rating' => $averageRating,
                'total_reviews' => $reviews->count(),
            ],
            'message' => 'Reseñas obtenidas exitosamente',
        ]);
    }

    /**
     * Actualizar una calificación.
     *
     * @param  int  $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $reviewId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        $result = $this->reviewService->updateReview($reviewId, $validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Eliminar una calificación.
     *
     * @param  int  $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($reviewId)
    {
        $result = $this->reviewService->deleteReview($reviewId);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Verificar si un usuario puede calificar.
     *
     * @param  int  $reviewableId
     * @param  string  $reviewableType
     * @return \Illuminate\Http\JsonResponse
     */
    public function canReview($reviewableId, $reviewableType)
    {
        $user = auth()->user();
        $orderId = (int) $reviewableId;
        $canReview = $this->reviewService->canUserReview($orderId, $user->id);

        return response()->json([
            'success' => true,
            'data' => ['can_review' => $canReview],
            'message' => 'Elegibilidad obtenida exitosamente',
        ]);
    }
}
