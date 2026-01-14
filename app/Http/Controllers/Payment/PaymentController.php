<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Get payment methods for user/commerce
     */
    public function getPaymentMethods()
    {
        try {
            $user = Auth::user();
            
            // Obtener métodos de pago del usuario directamente
            $userPaymentMethods = $user->paymentMethods()
                ->where('is_active', true)
                ->with('bank')
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Si el usuario tiene un comercio, también obtener sus métodos de pago
            $commercePaymentMethods = collect();
            if ($user->profile && $user->profile->commerce) {
                $commercePaymentMethods = $user->profile->commerce->paymentMethods()
                    ->where('is_active', true)
                    ->with('bank')
                    ->orderBy('is_default', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            
            // Combinar y formatear métodos de pago
            $paymentMethods = $userPaymentMethods->concat($commercePaymentMethods)->map(function ($method) {
                return [
                    'id' => $method->id,
                    'type' => $method->type,
                    'brand' => $method->brand,
                    'last4' => $method->last4,
                    'exp_month' => $method->exp_month,
                    'exp_year' => $method->exp_year,
                    'cardholder_name' => $method->cardholder_name,
                    'account_number' => $method->account_number ? substr($method->account_number, -4) : null,
                    'phone' => $method->phone,
                    'email' => $method->email,
                    'owner_name' => $method->owner_name,
                    'bank' => $method->bank ? [
                        'id' => $method->bank->id,
                        'name' => $method->bank->name,
                    ] : null,
                    'is_default' => $method->is_default,
                    'is_active' => $method->is_active,
                    'created_at' => $method->created_at->toIso8601String(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment methods'
            ], 500);
        }
    }

    /**
     * Add new payment method
     */
    public function addPaymentMethod(Request $request)
    {
        try {
            $user = Auth::user();
            $commerce = $user->profile->commerce ?? null;
            if (!$commerce) {
                return response()->json(['success' => false, 'message' => 'No es comercio'], 403);
            }
            $request->validate([
                'type' => 'required|string',
                'bank_id' => 'nullable|exists:banks,id',
                'brand' => 'nullable|string',
                'account_number' => 'nullable|string',
                'phone' => 'nullable|string',
                'owner_name' => 'nullable|string',
                'owner_id' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
            ]);
            $exists = $commerce->paymentMethods()
                ->where('type', $request->type)
                ->where('bank_id', $request->bank_id)
                ->where('account_number', $request->account_number)
                ->where('phone', $request->phone)
                ->where('owner_name', $request->owner_name)
                ->where('owner_id', $request->owner_id)
                ->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ya existe un método de pago igual registrado.'], 422);
            }
            // Guardar realmente el método de pago
            $paymentMethod = $commerce->paymentMethods()->create([
                'type' => $request->type,
                'bank_id' => $request->bank_id,
                'brand' => $request->brand,
                'account_number' => $request->account_number,
                'phone' => $request->phone,
                'owner_name' => $request->owner_name,
                'owner_id' => $request->owner_id,
                'is_default' => $request->input('is_default', false),
                'is_active' => $request->input('is_active', true),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully',
                'data' => $paymentMethod
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear método de pago de comercio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al crear método de pago'], 500);
        }
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|size:3',
                'payment_method_id' => 'required|integer|exists:payment_methods,id',
                'order_id' => 'required|integer|exists:orders,id',
                'description' => 'required|string|max:255'
            ]);

            $user = Auth::user();
            $order = Order::findOrFail($request->order_id);
            
            // Verificar que la orden pertenece al usuario
            if ($order->profile_id !== $user->profile->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para pagar esta orden'
                ], 403);
            }

            // Verificar que la orden no esté ya pagada
            if ($order->payment_proof) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden ya ha sido pagada'
                ], 422);
            }

            // Verificar que el monto coincida
            if (abs($order->total - $request->amount) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto no coincide con el total de la orden'
                ], 422);
            }

            // Obtener método de pago
            $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
            
            // Generar ID de transacción único
            $transactionId = 'txn_' . time() . '_' . strtoupper(substr(md5($order->id . $user->id), 0, 8));

            DB::beginTransaction();
            try {
                // Actualizar orden con información de pago
                $order->update([
                    'payment_method' => $paymentMethod->type,
                    'payment_proof' => $transactionId,
                    'payment_validated_at' => now(),
                    'status' => 'paid'
                ]);

                DB::commit();

                $transaction = [
                    'id' => $order->id,
                    'amount' => $order->total,
                    'currency' => $request->currency,
                    'status' => 'completed',
                    'type' => 'payment',
                    'payment_method_id' => $paymentMethod->id,
                    'payment_method_type' => $paymentMethod->type,
                    'order_id' => $order->id,
                    'description' => $request->description,
                    'transaction_id' => $transactionId,
                    'created_at' => $order->created_at->toIso8601String(),
                    'processed_at' => $order->payment_validated_at->toIso8601String(),
                ];

                Log::info('Payment processed successfully', [
                    'transaction_id' => $transactionId,
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'user_id' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => $transaction
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $profileId = $user->profile->id ?? null;
            
            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $query = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->with(['commerce', 'profile'])
                ->orderBy('payment_validated_at', 'desc');

            // Aplicar filtros
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date')) {
                $query->whereDate('payment_validated_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('payment_validated_at', '<=', $request->end_date);
            }

            if ($request->has('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            $perPage = $request->input('per_page', 15);
            $orders = $query->paginate($perPage);

            $transactions = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'amount' => $order->total,
                    'currency' => 'PEN', // Por defecto, puede ajustarse según necesidad
                    'status' => $order->status === 'delivered' ? 'completed' : ($order->status === 'cancelled' ? 'cancelled' : 'pending'),
                    'type' => 'payment',
                    'description' => 'Order #' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'transaction_id' => $order->payment_proof,
                    'payment_method' => $order->payment_method,
                    'order_id' => $order->id,
                    'commerce' => $order->commerce ? [
                        'id' => $order->commerce->id,
                        'name' => $order->commerce->business_name,
                    ] : null,
                    'created_at' => $order->payment_validated_at ? $order->payment_validated_at->toIso8601String() : $order->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching transaction history'
            ], 500);
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(Request $request, $transactionId)
    {
        try {
            $request->validate([
                'amount' => 'numeric|min:0.01',
                'reason' => 'required|string|max:500'
            ]);

            $user = Auth::user();
            
            // Buscar orden por transaction_id (payment_proof)
            $order = Order::where('payment_proof', $transactionId)
                ->where('profile_id', $user->profile->id)
                ->firstOrFail();

            // Verificar que la orden esté pagada
            if (!$order->payment_proof || !$order->payment_validated_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden no ha sido pagada'
                ], 422);
            }

            // Verificar que la orden no esté ya cancelada o reembolsada
            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden ya ha sido cancelada'
                ], 422);
            }

            // Verificar límite de tiempo (24 horas desde el pago)
            $hoursSincePayment = now()->diffInHours($order->payment_validated_at);
            if ($hoursSincePayment > 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden solicitar reembolsos dentro de las 24 horas posteriores al pago'
                ], 422);
            }

            $refundAmount = $request->input('amount', $order->total);
            
            // Verificar que el monto no exceda el total
            if ($refundAmount > $order->total) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto del reembolso no puede exceder el total de la orden'
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Generar ID de reembolso único
                $refundTransactionId = 'ref_' . time() . '_' . strtoupper(substr(md5($order->id . $user->id), 0, 8));

                // Actualizar orden con información de reembolso
                $order->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Refund: ' . $request->reason,
                    'payment_proof' => $refundTransactionId, // Guardar ID de reembolso
                ]);

                DB::commit();

                $refund = [
                    'id' => $order->id,
                    'original_transaction_id' => $transactionId,
                    'amount' => $refundAmount,
                    'currency' => 'PEN',
                    'status' => 'completed',
                    'type' => 'refund',
                    'order_id' => $order->id,
                    'created_at' => now()->toIso8601String(),
                    'transaction_id' => $refundTransactionId,
                    'description' => 'Refund for transaction #' . $transactionId,
                    'reason' => $request->reason,
                    'estimated_processing_time' => '3-5 días hábiles'
                ];

                Log::info('Refund processed successfully', [
                    'refund_transaction_id' => $refundTransactionId,
                    'original_transaction_id' => $transactionId,
                    'order_id' => $order->id,
                    'amount' => $refundAmount,
                    'user_id' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'data' => $refund
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing refund: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            $profileId = $user->profile->id ?? null;
            
            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $period = $request->input('period', 'month');
            
            // Calcular fechas según período
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subMonth(),
            };

            // Estadísticas generales
            $totalTransactions = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->where('payment_validated_at', '>=', $startDate)
                ->count();

            $totalRevenue = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->where('status', 'delivered')
                ->where('payment_validated_at', '>=', $startDate)
                ->sum('total') ?? 0;

            $averageTransactionValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

            $successfulPayments = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->whereIn('status', ['paid', 'preparing', 'on_way', 'delivered'])
                ->where('payment_validated_at', '>=', $startDate)
                ->count();

            $failedPayments = Order::where('profile_id', $profileId)
                ->where('status', 'cancelled')
                ->whereNotNull('payment_proof')
                ->where('payment_validated_at', '>=', $startDate)
                ->count();

            $refundedPayments = Order::where('profile_id', $profileId)
                ->where('status', 'cancelled')
                ->whereNotNull('cancellation_reason')
                ->where('cancellation_reason', 'like', 'Refund:%')
                ->where('payment_validated_at', '>=', $startDate)
                ->count();

            $paymentSuccessRate = ($successfulPayments + $failedPayments) > 0 
                ? round(($successfulPayments / ($successfulPayments + $failedPayments)) * 100, 2)
                : 0;

            // Crecimiento mensual
            $currentMonthRevenue = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->where('status', 'delivered')
                ->whereMonth('payment_validated_at', now()->month)
                ->whereYear('payment_validated_at', now()->year)
                ->sum('total') ?? 0;

            $lastMonthRevenue = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->where('status', 'delivered')
                ->whereMonth('payment_validated_at', now()->subMonth()->month)
                ->whereYear('payment_validated_at', now()->subMonth()->year)
                ->sum('total') ?? 0;

            $monthlyGrowth = $lastMonthRevenue > 0 
                ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
                : 0;

            // Métodos de pago más usados
            $topPaymentMethods = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->whereNotNull('payment_method')
                ->where('payment_validated_at', '>=', $startDate)
                ->select('payment_method', DB::raw('COUNT(*) as usage'), DB::raw('SUM(total) as revenue'))
                ->groupBy('payment_method')
                ->orderBy('usage', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) use ($totalTransactions) {
                    return [
                        'method' => ucfirst(str_replace('_', ' ', $item->payment_method)),
                        'usage' => round(($item->usage / max($totalTransactions, 1)) * 100, 1),
                        'revenue' => round($item->revenue, 2),
                    ];
                })
                ->toArray();

            // Transacciones recientes
            $recentTransactions = Order::where('profile_id', $profileId)
                ->whereNotNull('payment_proof')
                ->orderBy('payment_validated_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'amount' => $order->total,
                        'status' => $order->status === 'delivered' ? 'completed' : ($order->status === 'cancelled' ? 'cancelled' : 'pending'),
                        'date' => $order->payment_validated_at ? $order->payment_validated_at->toIso8601String() : $order->created_at->toIso8601String(),
                    ];
                })
                ->toArray();

            $statistics = [
                'total_revenue' => round($totalRevenue, 2),
                'total_transactions' => $totalTransactions,
                'average_transaction_value' => round($averageTransactionValue, 2),
                'successful_payments' => $successfulPayments,
                'failed_payments' => $failedPayments,
                'refunded_payments' => $refundedPayments,
                'payment_success_rate' => $paymentSuccessRate,
                'monthly_growth' => $monthlyGrowth,
                'top_payment_methods' => $topPaymentMethods,
                'recent_transactions' => $recentTransactions,
                'period' => $period,
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment statistics'
            ], 500);
        }
    }
} 