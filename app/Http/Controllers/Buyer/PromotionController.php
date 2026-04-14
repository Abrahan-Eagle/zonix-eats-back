<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    /**
     * Obtener promociones activas
     */
    public function getActivePromotions(): JsonResponse
    {
        try {
            $promotions = Promotion::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->orderBy('priority', 'desc')
                ->get();

            $promotionsData = $promotions->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'title' => $promotion->title,
                    'description' => $promotion->description,
                    'discount_type' => $promotion->discount_type, // percentage, fixed
                    'discount_value' => $promotion->discount_value,
                    'minimum_order' => $promotion->minimum_order,
                    'maximum_discount' => $promotion->maximum_discount,
                    'image_url' => $promotion->image_url,
                    'banner_url' => $promotion->banner_url,
                    'start_date' => $promotion->start_date->format('Y-m-d'),
                    'end_date' => $promotion->end_date->format('Y-m-d'),
                    'terms_conditions' => $promotion->terms_conditions,
                    'is_applicable' => $this->isPromotionApplicable($promotion),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $promotionsData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting active promotions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las promociones',
            ], 500);
        }
    }

    /**
     * Obtener cupones disponibles
     */
    public function getAvailableCoupons(): JsonResponse
    {
        try {
            $profile = auth()->user()->profile;

            $coupons = Coupon::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where(function ($query) use ($profile) {
                    $query->where('is_public', true)
                        ->orWhere('assigned_to_profile_id', $profile->id);
                })
                ->get();

            $couponsData = $coupons->map(function ($coupon) use ($profile) {
                $usageCount = $coupon->usages()
                    ->where('profile_id', $profile->id)
                    ->count();

                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'title' => $coupon->title,
                    'description' => $coupon->description,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'minimum_order' => $coupon->minimum_order,
                    'maximum_discount' => $coupon->maximum_discount,
                    'usage_limit' => $coupon->usage_limit,
                    'usage_count' => $usageCount,
                    'can_use' => $usageCount < $coupon->usage_limit,
                    'start_date' => $coupon->start_date->format('Y-m-d'),
                    'end_date' => $coupon->end_date->format('Y-m-d'),
                    'terms_conditions' => $coupon->terms_conditions,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $couponsData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available coupons: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los cupones',
            ], 500);
        }
    }

    /**
     * Validar cupón
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
            'order_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $profile = auth()->user()->profile;
            $code = strtoupper($request->code);
            $orderAmount = $request->order_amount;

            $coupon = Coupon::where('code', $code)
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where(function ($query) use ($profile) {
                    $query->where('is_public', true)
                        ->orWhere('assigned_to_profile_id', $profile->id);
                })
                ->first();

            if (! $coupon) {
                Log::info('coupon_validation_failed', [
                    'reason' => 'invalid_or_expired',
                    'profile_id' => $profile->id,
                    'code' => $code,
                    'order_amount' => $orderAmount,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no válido o expirado',
                ], 404);
            }

            // Verificar límite de uso
            $usageCount = $coupon->usages()
                ->where('profile_id', $profile->id)
                ->count();

            if ($usageCount >= $coupon->usage_limit) {
                Log::info('coupon_validation_failed', [
                    'reason' => 'usage_limit',
                    'profile_id' => $profile->id,
                    'coupon_id' => $coupon->id,
                    'usage_count' => $usageCount,
                    'usage_limit' => (int) $coupon->usage_limit,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Has alcanzado el límite de uso de este cupón',
                ], 400);
            }

            // Verificar monto mínimo
            if ($orderAmount < $coupon->minimum_order) {
                Log::info('coupon_validation_failed', [
                    'reason' => 'minimum_order',
                    'profile_id' => $profile->id,
                    'coupon_id' => $coupon->id,
                    'order_amount' => $orderAmount,
                    'minimum_order' => (float) $coupon->minimum_order,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Monto mínimo requerido: $'.number_format($coupon->minimum_order, 2),
                ], 400);
            }

            // Calcular descuento
            $discount = $this->calculateDiscount($coupon, $orderAmount);

            return response()->json([
                'success' => true,
                'message' => 'Cupón válido',
                'data' => [
                    'coupon_id' => $coupon->id,
                    'code' => $coupon->code,
                    'title' => $coupon->title,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'discount_amount' => $discount,
                    'final_amount' => $orderAmount - $discount,
                    'terms_conditions' => $coupon->terms_conditions,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating coupon: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al validar el cupón',
            ], 500);
        }
    }

    /**
     * Aplicar cupón a un pedido
     */
    public function applyCouponToOrder(Request $request): JsonResponse
    {
        $legacyEnabled = filter_var(env('ENABLE_LEGACY_APPLY_COUPON', false), FILTER_VALIDATE_BOOL);
        if (! $legacyEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'Este flujo está deprecado. Usa coupon_code en el checkout.',
                'error_code' => 'COUPON_FLOW_DEPRECATED',
            ], 410);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'coupon_id' => 'required|exists:coupons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            Log::warning('legacy_apply_coupon_used', [
                'profile_id' => auth()->user()?->profile?->id,
                'order_id' => $request->order_id,
                'coupon_id' => $request->coupon_id,
            ]);

            $order = Order::findOrFail($request->order_id);
            $coupon = Coupon::findOrFail($request->coupon_id);

            // Verificar que el pedido pertenece al usuario
            if ($order->profile_id !== auth()->user()->profile->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar este pedido',
                ], 403);
            }

            // Verificar que el cupón no se haya aplicado ya para esta orden
            if (DB::table('coupon_usages')->where('order_id', $order->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pedido ya tiene un cupón aplicado',
                ], 400);
            }

            // Validar cupón
            $validationResult = $this->validateCouponForOrder($coupon, $order);
            if (! $validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message'],
                ], 400);
            }

            // Calcular descuento
            $baseAmount = (float) $order->total;
            $discount = $this->calculateDiscount($coupon, $baseAmount);
            $newTotal = max(0, $baseAmount - $discount);

            // Aplicar cupón al pedido usando el campo canónico `total`
            $order->update([
                'total' => $newTotal,
            ]);

            // Registrar uso del cupón
            $coupon->usages()->create([
                'profile_id' => auth()->user()->profile->id,
                'order_id' => $order->id,
                'discount_amount' => $discount,
                'used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cupón aplicado exitosamente',
                'data' => [
                    'order_id' => $order->id,
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $discount,
                    'new_total' => $order->total,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error applying coupon to order: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar el cupón',
            ], 500);
        }
    }

    /**
     * Obtener historial de cupones usados
     */
    public function getCouponHistory(): JsonResponse
    {
        try {
            $profile = auth()->user()->profile;

            $couponHistory = DB::table('coupon_usages')
                ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
                ->join('orders', 'coupon_usages.order_id', '=', 'orders.id')
                ->where('coupon_usages.profile_id', $profile->id)
                ->select([
                    'coupons.code',
                    'coupons.title',
                    'coupon_usages.discount_amount',
                    'coupon_usages.used_at',
                    'orders.id as order_id',
                    'orders.total as order_total',
                ])
                ->orderBy('coupon_usages.used_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $couponHistory->items(),
                    'pagination' => [
                        'current_page' => $couponHistory->currentPage(),
                        'last_page' => $couponHistory->lastPage(),
                        'per_page' => $couponHistory->perPage(),
                        'total' => $couponHistory->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting coupon history: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de cupones',
            ], 500);
        }
    }

    /**
     * Verificar si una promoción es aplicable
     */
    private function isPromotionApplicable(Promotion $promotion): bool
    {
        // Aquí se pueden agregar más validaciones específicas
        return true;
    }

    /**
     * Validar cupón para un pedido específico
     */
    private function validateCouponForOrder(Coupon $coupon, Order $order): array
    {
        $profile = auth()->user()->profile;

        // Verificar límite de uso
        $usageCount = $coupon->usages()
            ->where('profile_id', $profile->id)
            ->count();

        if ($usageCount >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'message' => 'Has alcanzado el límite de uso de este cupón',
            ];
        }

        // Verificar monto mínimo
        if ((float) $order->total < (float) $coupon->minimum_order) {
            return [
                'valid' => false,
                'message' => 'Monto mínimo requerido: $'.number_format($coupon->minimum_order, 2),
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Calcular descuento
     */
    private function calculateDiscount($coupon, float $amount): float
    {
        if ($coupon->discount_type === 'percentage') {
            $discount = ($amount * $coupon->discount_value) / 100;

            return min($discount, $coupon->maximum_discount ?? $discount);
        } else {
            return min($coupon->discount_value, $amount);
        }
    }
}
