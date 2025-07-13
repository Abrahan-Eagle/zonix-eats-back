<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Order;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    /**
     * Crear una nueva calificación.
     *
     * @param array $data
     * @return array
     */
    public function createReview($data)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        if (!$profile) {
            return ['success' => false, 'message' => 'Perfil no encontrado'];
        }

        // Validar que el usuario tenga pedidos entregados
        if (!$this->canUserReview($user->id, $data['order_id'])) {
            return [
                'success' => false, 
                'message' => 'Solo puedes calificar después de recibir tu pedido'
            ];
        }

        // Verificar si ya existe una calificación para este tipo
        $existingReview = Review::where('order_id', $data['order_id'])
                               ->where('type', $data['type'])
                               ->first();

        if ($existingReview) {
            return [
                'success' => false, 
                'message' => 'Ya has calificado este elemento'
            ];
        }

        // Preparar datos para la nueva estructura
        $reviewData = [
            'order_id' => $data['order_id'],
            'profile_id' => $profile->id,
            'type' => $data['type'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        // Agregar campos específicos según el tipo
        if ($data['type'] === 'restaurant') {
            $order = Order::find($data['order_id']);
            $reviewData['commerce_id'] = $order->commerce_id;
        } elseif ($data['type'] === 'delivery_agent') {
            $order = Order::find($data['order_id']);
            $reviewData['delivery_agent_id'] = $order->delivery_agent_id;
        }

        // Crear la calificación
        $review = Review::create($reviewData);

        return [
            'success' => true,
            'message' => 'Calificación creada exitosamente',
            'review' => $review
        ];
    }

    /**
     * Verificar si un usuario puede calificar.
     *
     * @param int $userId
     * @param int $orderId
     * @return bool
     */
    public function canUserReview($userId, $orderId)
    {
        $profile = Profile::where('user_id', $userId)->first();
        if (!$profile) return false;

        // Verificar que el pedido existe, pertenece al usuario y está entregado
        $order = Order::where('id', $orderId)
                     ->where('profile_id', $profile->id)
                     ->where('status', 'delivered')
                     ->first();

        return $order !== null;
    }

    /**
     * Obtener calificaciones de un restaurante.
     *
     * @param int $commerceId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRestaurantReviews($commerceId)
    {
        return Review::where('commerce_id', $commerceId)
                    ->where('type', 'restaurant')
                    ->with('profile.user')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Obtener calificaciones de un repartidor.
     *
     * @param int $deliveryAgentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDeliveryAgentReviews($deliveryAgentId)
    {
        return Review::where('delivery_agent_id', $deliveryAgentId)
                    ->where('type', 'delivery_agent')
                    ->with('profile.user')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Calcular rating promedio de un restaurante.
     *
     * @param int $commerceId
     * @return float
     */
    public function getRestaurantAverageRating($commerceId)
    {
        $reviews = Review::where('commerce_id', $commerceId)
                        ->where('type', 'restaurant')
                        ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        return round($reviews->avg('rating'), 1);
    }

    /**
     * Calcular rating promedio de un repartidor.
     *
     * @param int $deliveryAgentId
     * @return float
     */
    public function getDeliveryAgentAverageRating($deliveryAgentId)
    {
        $reviews = Review::where('delivery_agent_id', $deliveryAgentId)
                        ->where('type', 'delivery_agent')
                        ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        return round($reviews->avg('rating'), 1);
    }

    /**
     * Actualizar una calificación existente.
     *
     * @param int $reviewId
     * @param array $data
     * @return array
     */
    public function updateReview($reviewId, $data)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        $review = Review::where('id', $reviewId)
                       ->where('profile_id', $profile->id)
                       ->first();

        if (!$review) {
            return ['success' => false, 'message' => 'Calificación no encontrada'];
        }

        $review->update([
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? $review->comment,
        ]);

        return [
            'success' => true,
            'message' => 'Calificación actualizada',
            'review' => $review
        ];
    }

    /**
     * Eliminar una calificación.
     *
     * @param int $reviewId
     * @return array
     */
    public function deleteReview($reviewId)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        $review = Review::where('id', $reviewId)
                       ->where('profile_id', $profile->id)
                       ->first();

        if (!$review) {
            return ['success' => false, 'message' => 'Calificación no encontrada'];
        }

        $review->delete();

        return [
            'success' => true,
            'message' => 'Calificación eliminada'
        ];
    }
} 