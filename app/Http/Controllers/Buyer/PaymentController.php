<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Obtener métodos de pago disponibles
     */
    public function getPaymentMethods(): JsonResponse
    {
        $paymentMethods = [
            [
                'id' => 'credit_card',
                'name' => 'Tarjeta de Crédito/Débito',
                'icon' => 'credit_card',
                'description' => 'Visa, MasterCard, American Express',
                'enabled' => true
            ],
            [
                'id' => 'cash',
                'name' => 'Efectivo',
                'icon' => 'money',
                'description' => 'Pago al momento de la entrega',
                'enabled' => true
            ],
            [
                'id' => 'digital_wallet',
                'name' => 'Billetera Digital',
                'icon' => 'account_balance_wallet',
                'description' => 'PayPal, Apple Pay, Google Pay',
                'enabled' => false // Por implementar
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    /**
     * Procesar pago con tarjeta
     */
    public function processCardPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'card_number' => 'required|string|min:13|max:19',
            'card_holder' => 'required|string|max:100',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|min:3|max:4',
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de tarjeta inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::findOrFail($request->order_id);
            
            // Simular procesamiento de pago
            $paymentResult = $this->simulateCardPayment($request->all());
            
            if ($paymentResult['success']) {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'credit_card',
                    'payment_proof' => $paymentResult['transaction_id'],
                    'paid_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago procesado exitosamente',
                    'data' => [
                        'transaction_id' => $paymentResult['transaction_id'],
                        'amount' => $request->amount,
                        'order_id' => $order->id
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el pago: ' . $paymentResult['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error processing card payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Confirmar pago en efectivo
     */
    public function confirmCashPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01'
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
            
            $order->update([
                'payment_status' => 'pending_cash',
                'payment_method' => 'cash',
                'total_amount' => $request->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pago en efectivo confirmado',
                'data' => [
                    'order_id' => $order->id,
                    'amount' => $request->amount,
                    'payment_status' => 'pending_cash'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error confirming cash payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener comprobante de pago
     */
    public function getPaymentReceipt($orderId): JsonResponse
    {
        try {
            $order = Order::with(['profile', 'commerce', 'items.product'])
                ->findOrFail($orderId);

            $receipt = [
                'receipt_number' => 'RCP-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'date' => $order->created_at->format('d/m/Y H:i'),
                'customer' => [
                    'name' => $order->profile->full_name ?? 'Cliente',
                    'email' => $order->profile->email ?? 'N/A',
                    'phone' => $order->profile->phone ?? 'N/A'
                ],
                'restaurant' => [
                    'name' => $order->commerce->name ?? 'Restaurante',
                    'address' => $order->commerce->address ?? 'N/A'
                ],
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Producto',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total_price
                    ];
                }),
                'subtotal' => $order->subtotal,
                'tax' => $order->tax_amount ?? 0,
                'delivery_fee' => $order->delivery_fee ?? 0,
                'total' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'transaction_id' => $order->payment_proof
            ];

            return response()->json([
                'success' => true,
                'data' => $receipt
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating receipt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el comprobante'
            ], 500);
        }
    }

    /**
     * Simular procesamiento de pago con tarjeta
     */
    private function simulateCardPayment(array $cardData): array
    {
        // Simular validación de tarjeta
        $cardNumber = $cardData['card_number'];
        $lastDigit = substr($cardNumber, -1);
        
        // Simular rechazo de tarjetas que terminan en 0
        if ($lastDigit === '0') {
            return [
                'success' => false,
                'message' => 'Tarjeta rechazada por el banco',
                'transaction_id' => null
            ];
        }

        // Simular tarjetas que terminan en 5 como sin fondos
        if ($lastDigit === '5') {
            return [
                'success' => false,
                'message' => 'Fondos insuficientes',
                'transaction_id' => null
            ];
        }

        // Simular pago exitoso
        $transactionId = 'TXN-' . time() . '-' . rand(1000, 9999);
        
        return [
            'success' => true,
            'message' => 'Pago procesado exitosamente',
            'transaction_id' => $transactionId
        ];
    }
} 