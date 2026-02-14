<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Review;
use App\Models\DeliveryAgent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
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
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::findOrFail($request->order_id);
            
            // Verificar que el pedido pertenece al usuario
            if ($order->profile_id !== auth()->user()->profile->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para calificar este pedido'
                ], 403);
            }

            // Verificar que el pedido está entregado
            if ($order->status !== 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes calificar pedidos entregados'
                ], 400);
            }

            // Verificar que no se haya calificado antes (schema: reviewable_type, reviewable_id)
            $existingReview = Review::where('order_id', $order->id)
                ->where('reviewable_type', 'App\\Models\\Commerce')
                ->where('reviewable_id', $order->commerce_id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has calificado este restaurante'
                ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Calificación enviada exitosamente',
                'data' => [
                    'review_id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'photos' => []
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error rating restaurant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la calificación'
            ], 500);
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
            'comment' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::with('deliveryAgent')->findOrFail($request->order_id);
            
            // Verificar que el pedido pertenece al usuario
            if ($order->profile_id !== auth()->user()->profile->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para calificar este pedido'
                ], 403);
            }

            // Verificar que el pedido está entregado
            if ($order->status !== 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes calificar pedidos entregados'
                ], 400);
            }

            // Verificar que hay un repartidor asignado
            if (!$order->deliveryAgent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay repartidor asignado a este pedido'
                ], 400);
            }

            // Verificar que no se haya calificado antes (schema: reviewable_type, reviewable_id)
            $existingReview = Review::where('order_id', $order->id)
                ->where('reviewable_type', 'App\\Models\\DeliveryAgent')
                ->where('reviewable_id', $order->delivery_agent_id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has calificado este repartidor'
                ], 400);
            }

            // Crear la reseña (schema: profile_id, order_id, reviewable_type, reviewable_id, rating, comment)
            $review = Review::create([
                'order_id' => $order->id,
                'profile_id' => $order->profile_id,
                'reviewable_type' => 'App\\Models\\DeliveryAgent',
                'reviewable_id' => $order->delivery_agent_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            $this->updateDeliveryAgentRating($order->delivery_agent_id);

            return response()->json([
                'success' => true,
                'message' => 'Calificación enviada exitosamente',
                'data' => [
                    'review_id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error rating delivery agent: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la calificación'
            ], 500);
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
                    ? trim(($profile->firstName ?? '') . ' ' . ($profile->lastName ?? ''))
                    : 'Cliente';
                $customerName = $customerName !== '' ? $customerName : 'Cliente';

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'photos' => [],
                    'customer_name' => $customerName,
                    'customer_avatar' => $profile?->photo_users,
                    'created_at' => $review->created_at->format('d/m/Y H:i')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'reviews' => $reviewsData,
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'last_page' => $reviews->lastPage(),
                        'per_page' => $reviews->perPage(),
                        'total' => $reviews->total()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting restaurant reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reseñas'
            ], 500);
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
                    ? trim(($profile->firstName ?? '') . ' ' . ($profile->lastName ?? ''))
                    : 'Cliente';
                $customerName = $customerName !== '' ? $customerName : 'Cliente';

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'customer_name' => $customerName,
                    'customer_avatar' => $profile?->photo_users,
                    'created_at' => $review->created_at->format('d/m/Y H:i')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'reviews' => $reviewsData,
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'last_page' => $reviews->lastPage(),
                        'per_page' => $reviews->perPage(),
                        'total' => $reviews->total()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting delivery agent reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reseñas'
            ], 500);
        }
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