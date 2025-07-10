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

        // Validar que el usuario tenga pedidos entregados con este comercio
        if (!$this->canUserReview($user->id, $data['reviewable_id'], $data['reviewable_type'])) {
            return [
                'success' => false, 
                'message' => 'Solo puedes calificar después de recibir tu pedido'
            ];
        }

        // Verificar si ya existe una calificación
        $existingReview = Review::where('profile_id', $profile->id)
                               ->where('reviewable_id', $data['reviewable_id'])
                               ->where('reviewable_type', $data['reviewable_type'])
                               ->first();

        if ($existingReview) {
            return [
                'success' => false, 
                'message' => 'Ya has calificado este elemento'
            ];
        }

        // Crear la calificación
        $review = Review::create([
            'profile_id' => $profile->id,
            'reviewable_id' => $data['reviewable_id'],
            'reviewable_type' => $data['reviewable_type'],
            'rating' => $data['rating'],
            'comentario' => $data['comentario'] ?? null,
        ]);

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
     * @param int $reviewableId
     * @param string $reviewableType
     * @return bool
     */
    public function canUserReview($userId, $reviewableId, $reviewableType)
    {
        // Buscar pedidos entregados del usuario con este comercio
        $deliveredOrders = Order::where('user_id', $userId)
                               ->where('estado', 'entregado')
                               ->get();

        foreach ($deliveredOrders as $order) {
            if ($reviewableType === 'App\Models\Commerce') {
                if ($order->commerce_id == $reviewableId) {
                    return true;
                }
            } elseif ($reviewableType === 'App\Models\Product') {
                // Verificar si el pedido contiene este producto
                $hasProduct = $order->items()->where('product_id', $reviewableId)->exists();
                if ($hasProduct) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Obtener calificaciones de un elemento.
     *
     * @param int $reviewableId
     * @param string $reviewableType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReviews($reviewableId, $reviewableType)
    {
        return Review::where('reviewable_id', $reviewableId)
                    ->where('reviewable_type', $reviewableType)
                    ->with('profile.user')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Calcular rating promedio.
     *
     * @param int $reviewableId
     * @param string $reviewableType
     * @return float
     */
    public function getAverageRating($reviewableId, $reviewableType)
    {
        $reviews = Review::where('reviewable_id', $reviewableId)
                        ->where('reviewable_type', $reviewableType)
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
            'comentario' => $data['comentario'] ?? $review->comentario,
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