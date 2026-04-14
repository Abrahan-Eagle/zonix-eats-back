<?php

namespace App\Http\Controllers\Commerce;

use App\Events\OrderStatusChanged;
use App\Events\PaymentValidated;
use App\Http\Controllers\Controller;
use App\Jobs\AutoAssignDeliveryJob;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Services\OrderStateMachineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            $user->load('profile.commerces');
            $profile = $user->profile;
            $commerce = $profile?->getPrimaryCommerce();

            if (! $profile || ! $commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            // Si se solicita commerce_id específico, debe pertenecer al perfil; si no, 403
            if ($request->has('commerce_id')) {
                $requested = $profile->commerces()->find($request->commerce_id);
                if (! $requested) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                $commerce = $requested;
            }

            $perPage = max(1, min((int) $request->input('per_page', 15), 100));
            $status = $request->input('status');

            $query = Order::where('commerce_id', $commerce->id)
                ->with(['profile.user', 'orderItems.product']);

            if ($status) {
                $query->where('status', $status);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Órdenes obtenidas correctamente',
                'data' => [
                    'items' => $orders->items(),
                    // Compatibilidad temporal para clientes que leen data como lista directa.
                    'data' => $orders->items(),
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar órdenes de comercio: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error interno al listar órdenes'], 500);
        }
    }

    public function show(Order $order)
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            $user->load('profile.commerces');
            $profile = $user->profile;

            if (! $profile || ! $profile->commerces()->exists()) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            if (! $profile->commerces()->where('id', $order->commerce_id)->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $loadedOrder = $order->load(['profile.user', 'orderItems.product', 'orderDelivery', 'orderPayments']);

            return response()->json([
                'success' => true,
                'message' => 'Orden obtenida correctamente',
                'data' => $loadedOrder,
                // Compatibilidad temporal para clientes legacy.
                'order' => $loadedOrder,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al mostrar orden de comercio: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error interno al mostrar orden'], 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            $user->load('profile.commerces');
            $profile = $user->profile;

            if (! $profile || ! $profile->commerces()->exists()) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            // Verificar que la orden pertenece al comercio del usuario
            if (! $profile->commerces()->where('id', $order->commerce_id)->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'status' => 'required|string',
            ]);

            $stateMachine = app(OrderStateMachineService::class);
            $targetStatus = $stateMachine->normalizeStatus((string) $request->status);
            $decision = $stateMachine->applyTransition(
                $order,
                'commerce',
                $targetStatus,
                $profile->id,
                'commerce_api'
            );

            if (! $decision['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $decision['message'],
                    'error_code' => $decision['error_code'],
                    'data' => [
                        'from_status' => $decision['from'],
                        'to_status' => $decision['to'],
                    ],
                ], $decision['http_status']);
            }

            // Si pasa a processing y es delivery, asignar empresa y disparar auto-asignación
            if ($targetStatus === 'processing' && $order->delivery_type === 'delivery') {
                $order->refresh();
                if (! $order->delivery_company_id) {
                    $company = DeliveryCompany::where('active', true)->first();
                    if ($company) {
                        $order->update(['delivery_company_id' => $company->id]);
                    }
                }
                if ($order->delivery_company_id && ! $order->orderDelivery) {
                    AutoAssignDeliveryJob::dispatch($order->id);
                }
            }

            $this->broadcastOrderStatusChanged($order);

            return response()->json(['success' => true, 'message' => 'Estado de la orden actualizado']);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de orden de comercio: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error interno al actualizar estado de orden'], 500);
        }
    }

    /**
     * Validar o rechazar comprobante de pago de una orden.
     *
     * Este método consolida validarComprobante() y validatePayment().
     * Usa el campo 'status' en lugar de 'estado' para mantener consistencia.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validatePayment(Request $request, $id)
    {
        try {
            $stateMachine = app(OrderStateMachineService::class);
            $validated = $request->validate([
                'is_valid' => 'required|boolean',
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            if ($validated['is_valid'] === false && empty(trim((string) ($validated['rejection_reason'] ?? '')))) {
                return response()->json([
                    'success' => false,
                    'message' => 'El motivo de rechazo es obligatorio cuando el pago es rechazado.',
                    'error_code' => 'PAYMENT_REJECTION_REASON_REQUIRED',
                    'errors' => [
                        'rejection_reason' => ['El motivo de rechazo es obligatorio cuando el pago es rechazado.'],
                    ],
                ], 422);
            }

            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            return DB::transaction(function () use ($validated, $stateMachine, $id, $user) {
                $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
                $user->loadMissing('profile.commerces');
                $profile = $user->profile;

                // Verificar que el usuario es dueño del comercio de la orden
                if (! $profile || ! $profile->commerces()->where('id', $order->commerce_id)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No autorizado para validar esta orden',
                        'error_code' => 'ORDER_FORBIDDEN',
                    ], 403);
                }

                // Solo se puede validar pago si la orden está pendiente de pago
                $currentStatus = $stateMachine->normalizeStatus((string) $order->status);
                if ($currentStatus !== 'pending_payment') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo se puede validar el pago de órdenes pendientes de pago',
                        'error_code' => 'ORDER_INVALID_STATE_FOR_PAYMENT_VALIDATION',
                    ], 409);
                }

                // Requiere que el comercio haya aprobado previamente la orden para pago
                if (! $order->approved_for_payment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes aprobar la orden para pago antes de validar el comprobante',
                        'error_code' => 'ORDER_NOT_APPROVED_FOR_PAYMENT',
                    ], 409);
                }

                $foodPayment = $order->foodPayment;
                if (! $foodPayment || ! $foodPayment->payment_proof) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay comprobante de pago de comida para validar.',
                        'error_code' => 'PAYMENT_PROOF_NOT_FOUND',
                    ], 422);
                }

                if ($foodPayment->validated_at) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El comprobante ya fue validado previamente.',
                        'error_code' => 'PAYMENT_ALREADY_VALIDATED',
                    ], 409);
                }

                if ($foodPayment->rejected_at && $validated['is_valid'] === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El comprobante ya fue rechazado previamente.',
                        'error_code' => 'PAYMENT_ALREADY_REJECTED',
                    ], 409);
                }

                if ($validated['is_valid']) {
                    // Marcar pago food como validado en order_payments
                    if ($foodPayment) {
                        $foodPayment->update([
                            'validated_at' => now(),
                            'validated_by' => $profile->id,
                            'rejected_at' => null,
                            'rejection_reason' => null,
                        ]);
                    }

                    // Compatibilidad legacy
                    $order->update([
                        'payment_validated_at' => now(),
                        'cancellation_reason' => null,
                    ]);

                    // Si todos los pagos están validados → paid; si no, sigue pending_payment
                    $order->refresh();
                    $order->load(['foodPayment', 'deliveryPayment']);
                    if ($order->allPaymentsValidated()) {
                        $decision = $stateMachine->applyTransition(
                            $order,
                            'commerce',
                            'paid',
                            $profile->id,
                            'commerce_payment_validation',
                            'Todos los pagos validados'
                        );

                        if (! $decision['allowed']) {
                            return response()->json([
                                'success' => false,
                                'message' => $decision['message'],
                                'error_code' => $decision['error_code'],
                                'data' => [
                                    'from_status' => $decision['from'],
                                    'to_status' => $decision['to'],
                                ],
                            ], $decision['http_status']);
                        }

                        $message = 'Todos los pagos validados. Orden lista para preparar.';
                    } else {
                        $message = 'Pago de comida validado. Pendiente: pago de envío.';
                    }

                    $this->broadcastPaymentValidated($order, true, $profile->id);
                    Log::info('payment_food_validated', [
                        'order_id' => $order->id,
                        'validated_by' => $profile->id,
                    ]);
                } else {
                    if ($foodPayment) {
                        $foodPayment->update([
                            'rejected_at' => now(),
                            'rejection_reason' => $validated['rejection_reason'] ?? 'Pago rechazado por el comercio',
                        ]);
                    }

                    $rejectionReason = $validated['rejection_reason'] ?? 'Pago rechazado por el comercio';
                    $order->update([
                        'cancellation_reason' => $rejectionReason,
                        'payment_validated_at' => null,
                    ]);

                    $decision = $stateMachine->applyTransition(
                        $order,
                        'commerce',
                        'cancelled',
                        $profile->id,
                        'commerce_payment_validation',
                        $rejectionReason
                    );

                    if (! $decision['allowed']) {
                        return response()->json([
                            'success' => false,
                            'message' => $decision['message'],
                            'error_code' => $decision['error_code'],
                            'data' => [
                                'from_status' => $decision['from'],
                                'to_status' => $decision['to'],
                            ],
                        ], $decision['http_status']);
                    }

                    $message = 'Pago rechazado';
                    $this->broadcastOrderStatusChanged($order);
                    Log::warning('payment_food_rejected', [
                        'order_id' => $order->id,
                        'validated_by' => $profile->id,
                        'reason' => $validated['rejection_reason'] ?? null,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'error_code' => null,
                    'all_payments_validated' => $order->allPaymentsValidated(),
                    'order' => $order, // alias legacy
                    'data' => [
                        'order' => $order,
                        'all_payments_validated' => $order->allPaymentsValidated(),
                    ],
                ]);

            });

        } catch (\Exception $e) {
            Log::error('Error al validar el comprobante: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo validar el comprobante. Intenta de nuevo o contacta soporte.',
                'error_code' => 'PAYMENT_VALIDATION_FAILED',
            ], 500);
        }
    }

    /**
     * Alias para mantener compatibilidad con rutas existentes.
     * Redirige a validatePayment().
     *
     * @deprecated Usar validatePayment() en su lugar
     */
    public function validarComprobante(Request $request, $id)
    {
        return $this->validatePayment($request, $id);
    }

    /**
     * Aprobar una orden para que el comprador pueda proceder al pago.
     *
     * Reglas:
     * - Solo el comercio dueño de la orden puede aprobar.
     * - La orden debe estar en estado pending_payment.
     * - Puede aprobarse aunque el comprador ya haya subido comprobante.
     */
    public function approveForPayment($id)
    {
        try {
            $order = Order::findOrFail($id);
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            $user->load('profile.commerces');
            $profile = $user->profile;

            if (! $profile || ! $profile->commerces()->where('id', $order->commerce_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para aprobar esta orden',
                ], 403);
            }

            if ($order->status !== 'pending_payment') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar órdenes pendientes de pago',
                ], 400);
            }

            if ($order->approved_for_payment) {
                return response()->json([
                    'success' => true,
                    'message' => 'La orden ya estaba aprobada para pago',
                ]);
            }

            // Permitir aprobar aunque ya haya comprobante (ej. comprador subió primero)
            $order->update([
                'approved_for_payment' => true,
                'approved_for_payment_at' => now(),
            ]);

            $this->broadcastOrderStatusChanged($order);

            return response()->json([
                'success' => true,
                'message' => 'Orden aprobada para pago',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al aprobar orden para pago: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar orden para pago: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rechazar (cancelar) una orden en pending_payment.
     * El comercio rechaza la orden cuando no puede atenderla o no hay acuerdo con el cliente.
     */
    public function rejectOrder(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $order = Order::findOrFail($id);
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            $user->load('profile.commerces');
            $profile = $user->profile;

            if (! $profile || ! $profile->commerces()->where('id', $order->commerce_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para rechazar esta orden',
                    'error_code' => 'ORDER_FORBIDDEN',
                ], 403);
            }

            $stateMachine = app(OrderStateMachineService::class);
            if ($stateMachine->normalizeStatus((string) $order->status) !== 'pending_payment') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede rechazar una orden en estado pendiente de pago',
                    'error_code' => 'ORDER_INVALID_STATE_FOR_REJECTION',
                ], 409);
            }

            $reason = $validated['reason'] ?? 'Orden rechazada por el comercio';
            $order->update([
                'cancellation_reason' => $reason,
            ]);

            $decision = $stateMachine->applyTransition(
                $order,
                'commerce',
                'cancelled',
                $profile->id,
                'commerce_reject_order',
                $reason
            );

            if (! $decision['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $decision['message'],
                    'error_code' => $decision['error_code'],
                    'data' => [
                        'from_status' => $decision['from'],
                        'to_status' => $decision['to'],
                    ],
                ], $decision['http_status']);
            }

            $this->broadcastOrderStatusChanged($order);

            return response()->json([
                'success' => true,
                'message' => 'Orden rechazada',
                'order' => $order->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al rechazar orden: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la orden: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/commerce/orders/{id}/pickup-qr — QR para que el repartidor escanee al recoger.
     */
    public function pickupQr($id)
    {
        try {
            $order = Order::findOrFail($id);
            $user = Auth::user();
            $profile = $user->profile;
            if (! $profile || ! $profile->commerces()->where('id', $order->commerce_id)->exists()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            if ($order->status !== 'processing') {
                return response()->json(['success' => false, 'message' => 'QR solo disponible cuando la orden está en preparación'], 400);
            }

            if (! $order->pickup_token) {
                $token = substr(hash_hmac('sha256', "order:{$order->id}:pickup:".now()->timestamp, config('app.key')), 0, 16);
                $order->update(['pickup_token' => $token]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'token' => $order->pickup_token,
                    'qr_payload' => "zonix://pickup/{$order->id}/{$order->pickup_token}",
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error generando QR de recogida: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

    private function broadcastOrderStatusChanged(Order $order): void
    {
        try {
            event(new OrderStatusChanged($order->fresh()));
        } catch (\Throwable $e) {
            Log::warning('commerce_order_broadcast_failed', [
                'event' => 'OrderStatusChanged',
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function broadcastPaymentValidated(Order $order, bool $isValidated, $validatedBy): void
    {
        try {
            event(new PaymentValidated($order->fresh(), $isValidated, $validatedBy));
        } catch (\Throwable $e) {
            Log::warning('commerce_order_broadcast_failed', [
                'event' => 'PaymentValidated',
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
