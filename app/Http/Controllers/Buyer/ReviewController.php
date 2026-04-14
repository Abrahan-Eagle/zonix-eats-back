<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    private function successResponse(array $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function errorResponse(string $message, string $errorCode, int $status, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'data' => null,
            'message' => $message,
            'error_code' => $errorCode,
        ];
        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Calificar restaurante
     */
    public function rateRestaurant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Datos inválidos',
                'REVIEWS_VALIDATION_ERROR',
                422,
                $validator->errors()->toArray()
            );
        }

        try {
            $order = Order::findOrFail($request->order_id);

            // Verificar que el pedido pertenece al usuario
            if ($order->profile_id !== auth()->user()->profile->id) {
                return $this->errorResponse(
                    'No tienes permisos para calificar este pedido',
                    'REVIEWS_FORBIDDEN_ORDER',
                    403
                );
            }

            // Verificar que el pedido está entregado
            if ($order->status !== 'delivered') {
                return $this->errorResponse(
                    'Solo puedes calificar pedidos entregados',
                    'REVIEWS_ORDER_NOT_DELIVERED',
                    400
                );
            }

            // Verificar que no se haya calificado antes (schema: reviewable_type, reviewable_id)
            $existingReview = Review::where('order_id', $order->id)
                ->where('reviewable_type', 'App\\Models\\Commerce')
                ->where('reviewable_id', $order->commerce_id)
                ->first();

            if ($existingReview) {
                return $this->errorResponse(
                    'Ya has calificado este restaurante',
                    'REVIEWS_DUPLICATE_REVIEW',
                    409
                );
            }

            // Crear la reseña (schema: profile_id, order_id, reviewable_type, reviewable_id, rating, comment)
            $review = Review::create([
                'order_id' => $order->id,
                'profile_id' => $order->profile_id,
                'reviewable_type' => 'App\\Models\\Commerce',
                'reviewable_id' => $order->commerce_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return $this->successResponse([
                'review_id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'photos' => [],
                'moderation_status' => Schema::hasColumn('reviews', 'moderation_status') ? $review->moderation_status : 'approved',
            ], 'Calificación enviada exitosamente');
        } catch (QueryException $e) {
            if (($e->getCode() ?? '') === '23000') {
                return $this->errorResponse(
                    'Ya has calificado este restaurante',
                    'REVIEWS_DUPLICATE_REVIEW',
                    409
                );
            }
            Log::error('Error rating restaurant query: '.$e->getMessage());

            return $this->errorResponse(
                'Error al enviar la calificación',
                'REVIEWS_CREATE_ERROR',
                500
            );
        } catch (\Exception $e) {
            Log::error('Error rating restaurant: '.$e->getMessage());

            return $this->errorResponse(
                'Error al enviar la calificación',
                'REVIEWS_CREATE_ERROR',
                500
            );
        }
    }

    /**
     * Calificar repartidor
     */
    public function rateDeliveryAgent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Datos inválidos',
                'REVIEWS_VALIDATION_ERROR',
                422,
                $validator->errors()->toArray()
            );
        }

        try {
            $order = Order::with('orderDelivery')->findOrFail($request->order_id);

            // Verificar que el pedido pertenece al usuario
            if ($order->profile_id !== auth()->user()->profile->id) {
                return $this->errorResponse(
                    'No tienes permisos para calificar este pedido',
                    'REVIEWS_FORBIDDEN_ORDER',
                    403
                );
            }

            // Verificar que el pedido está entregado
            if ($order->status !== 'delivered') {
                return $this->errorResponse(
                    'Solo puedes calificar pedidos entregados',
                    'REVIEWS_ORDER_NOT_DELIVERED',
                    400
                );
            }

            // Verificar que hay un repartidor asignado
            if (! $order->orderDelivery) {
                return $this->errorResponse(
                    'No hay repartidor asignado a este pedido',
                    'REVIEWS_DELIVERY_AGENT_NOT_ASSIGNED',
                    400
                );
            }

            $deliveryAgentId = $order->orderDelivery?->agent_id;
            if (! $deliveryAgentId) {
                return $this->errorResponse(
                    'No se pudo determinar el repartidor asignado',
                    'REVIEWS_DELIVERY_AGENT_NOT_FOUND',
                    400
                );
            }

            // Verificar que no se haya calificado antes (schema: reviewable_type, reviewable_id)
            $existingReview = Review::where('order_id', $order->id)
                ->where('reviewable_type', 'App\\Models\\DeliveryAgent')
                ->where('reviewable_id', $deliveryAgentId)
                ->first();

            if ($existingReview) {
                return $this->errorResponse(
                    'Ya has calificado este repartidor',
                    'REVIEWS_DUPLICATE_REVIEW',
                    409
                );
            }

            // Crear la reseña (schema: profile_id, order_id, reviewable_type, reviewable_id, rating, comment)
            $review = Review::create([
                'order_id' => $order->id,
                'profile_id' => $order->profile_id,
                'reviewable_type' => 'App\\Models\\DeliveryAgent',
                'reviewable_id' => $deliveryAgentId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            $this->updateDeliveryAgentRating($deliveryAgentId);

            return $this->successResponse([
                'review_id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'moderation_status' => Schema::hasColumn('reviews', 'moderation_status') ? $review->moderation_status : 'approved',
            ], 'Calificación enviada exitosamente');
        } catch (QueryException $e) {
            if (($e->getCode() ?? '') === '23000') {
                return $this->errorResponse(
                    'Ya has calificado este repartidor',
                    'REVIEWS_DUPLICATE_REVIEW',
                    409
                );
            }
            Log::error('Error rating delivery agent query: '.$e->getMessage());

            return $this->errorResponse(
                'Error al enviar la calificación',
                'REVIEWS_CREATE_ERROR',
                500
            );
        } catch (\Exception $e) {
            Log::error('Error rating delivery agent: '.$e->getMessage());

            return $this->errorResponse(
                'Error al enviar la calificación',
                'REVIEWS_CREATE_ERROR',
                500
            );
        }
    }

    /**
     * Obtener reseñas de un restaurante
     */
    public function getRestaurantReviews($commerceId): JsonResponse
    {
        try {
            $reviews = Review::with(['profile'])
                ->where('reviewable_type', 'App\\Models\\Commerce')
                ->where('reviewable_id', $commerceId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $reviewsData = $reviews->map(function ($review) {
                $profile = $review->profile;
                $customerName = $profile
                    ? trim(($profile->firstName ?? '').' '.($profile->lastName ?? ''))
                    : 'Cliente';
                $customerName = $customerName !== '' ? $customerName : 'Cliente';

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'moderation_status' => Schema::hasColumn('reviews', 'moderation_status') ? $review->moderation_status : 'approved',
                    'photos' => [],
                    'customer_name' => $customerName,
                    'customer_avatar' => $profile?->photo_users,
                    'created_at' => $review->created_at->format('d/m/Y H:i'),
                ];
            });

            return $this->successResponse([
                'reviews' => $reviewsData,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ], 'Reseñas obtenidas exitosamente');
        } catch (\Exception $e) {
            Log::error('Error getting restaurant reviews: '.$e->getMessage());

            return $this->errorResponse(
                'Error al obtener las reseñas',
                'REVIEWS_LIST_ERROR',
                500
            );
        }
    }

    /**
     * Obtener reseñas de un repartidor
     */
    public function getDeliveryAgentReviews($agentId): JsonResponse
    {
        try {
            $reviews = Review::with(['profile'])
                ->where('reviewable_type', 'App\\Models\\DeliveryAgent')
                ->where('reviewable_id', $agentId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $reviewsData = $reviews->map(function ($review) {
                $profile = $review->profile;
                $customerName = $profile
                    ? trim(($profile->firstName ?? '').' '.($profile->lastName ?? ''))
                    : 'Cliente';
                $customerName = $customerName !== '' ? $customerName : 'Cliente';

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'moderation_status' => Schema::hasColumn('reviews', 'moderation_status') ? $review->moderation_status : 'approved',
                    'customer_name' => $customerName,
                    'customer_avatar' => $profile?->photo_users,
                    'created_at' => $review->created_at->format('d/m/Y H:i'),
                ];
            });

            return $this->successResponse([
                'reviews' => $reviewsData,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ], 'Reseñas obtenidas exitosamente');
        } catch (\Exception $e) {
            Log::error('Error getting delivery agent reviews: '.$e->getMessage());

            return $this->errorResponse(
                'Error al obtener las reseñas',
                'REVIEWS_LIST_ERROR',
                500
            );
        }
    }

    /**
     * Reportar una reseña para moderación (report-only, no oculta contenido).
     */
    public function reportReview(Request $request, int $reviewId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Datos inválidos',
                'REVIEWS_VALIDATION_ERROR',
                422,
                $validator->errors()->toArray()
            );
        }

        $review = Review::find($reviewId);
        if (! $review) {
            return $this->errorResponse(
                'Reseña no encontrada',
                'REVIEWS_NOT_FOUND',
                404
            );
        }

        $updatePayload = [];
        if (Schema::hasColumn('reviews', 'moderation_status')) {
            $updatePayload['moderation_status'] = 'reported';
        }
        if (Schema::hasColumn('reviews', 'reported_at')) {
            $updatePayload['reported_at'] = now();
        }
        if (Schema::hasColumn('reviews', 'reported_reason')) {
            $updatePayload['reported_reason'] = $request->input('reason');
        }
        if (Schema::hasColumn('reviews', 'reported_by_profile_id')) {
            $updatePayload['reported_by_profile_id'] = auth()->user()?->profile?->id;
        }
        if (! empty($updatePayload)) {
            $review->update($updatePayload);
        }

        return $this->successResponse([
            'review_id' => $review->id,
            'moderation_status' => Schema::hasColumn('reviews', 'moderation_status') ? $review->moderation_status : 'reported',
        ], 'Reseña reportada exitosamente');
    }

    /**
     * Actualizar calificación promedio del repartidor (campo rating en delivery_agents)
     */
    private function updateDeliveryAgentRating($agentId): void
    {
        $averageRating = Review::where('reviewable_type', 'App\\Models\\DeliveryAgent')
            ->where('reviewable_id', $agentId)
            ->avg('rating');

        DeliveryAgent::where('id', $agentId)->update([
            'rating' => round((float) $averageRating, 2),
        ]);
    }
}
