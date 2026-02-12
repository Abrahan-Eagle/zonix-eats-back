<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Determinar la entidad dueña de los métodos de pago según el rol actual.
     * - users      → User (comprador)
     * - commerce   → Commerce (vendedor)
     * - delivery   → DeliveryAgent
     * - otros      → User por defecto
     */
    protected function getPayableOwner()
    {
        $user = Auth::user();
        if (!$user) {
            return $user;
        }

        try {
            $role = $user->role ?? null;
            $profile = $user->profile ?? null;

            if ($role === 'commerce' && $profile && $profile->commerce) {
                return $profile->commerce;
            }

            if ($role === 'delivery' && $profile && $profile->deliveryAgent) {
                return $profile->deliveryAgent;
            }
        } catch (\Throwable $e) {
            Log::warning('Error determinando payable owner para métodos de pago: ' . $e->getMessage());
        }

        // Fallback: usuario autenticado (rol users/admin)
        return $user;
    }
    /**
     * Obtener métodos de pago del usuario autenticado
     */
    public function index()
    {
        try {
            $owner = $this->getPayableOwner();
            $methods = $owner->paymentMethods()->with('bank')->active()->get();
            
            return response()->json([
                'success' => true,
                'data' => $methods
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener métodos de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métodos de pago'
            ], 500);
        }
    }

    /**
     * Crear nuevo método de pago
     */
    public function store(Request $request)
    {
        try {
            $owner = $this->getPayableOwner();
            
            $data = $request->validate([
                'type' => 'required|string|in:card,mobile_payment,cash,paypal,stripe,mercadopago,digital_wallet,bank_transfer,other',
                'bank_id' => 'nullable|exists:banks,id',
                'brand' => 'nullable|string',
                'last4' => 'nullable|string|max:4',
                'exp_month' => 'nullable|integer|between:1,12',
                'exp_year' => 'nullable|integer|min:' . (date('Y') - 1),
                'cardholder_name' => 'nullable|string',
                'account_number' => 'nullable|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'owner_name' => 'nullable|string',
                'owner_id' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'reference_info' => 'nullable|array',
            ]);

            // Validar duplicados
            $exists = $owner->paymentMethods()
                ->where('type', $data['type'])
                ->where('bank_id', $data['bank_id'] ?? null)
                ->where(function($q) use ($data) {
                    $q->where('account_number', $data['account_number'] ?? null)
                      ->orWhere('phone', $data['phone'] ?? null)
                      ->orWhere('email', $data['email'] ?? null);
                })->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un método de pago igual registrado.'
                ], 422);
            }

            // Si es método por defecto, desactivar otros
            if ($data['is_default'] ?? false) {
                $owner->paymentMethods()->update(['is_default' => false]);
            }

            $method = $owner->paymentMethods()->create($data);

            return response()->json([
                'success' => true,
                'data' => $method->load('bank')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear método de pago: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear método de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar método de pago
     */
    public function update(Request $request, $id)
    {
        try {
            $owner = $this->getPayableOwner();
            $method = $owner->paymentMethods()->findOrFail($id);

            $data = $request->validate([
                'type' => 'sometimes|string|in:card,mobile_payment,cash,paypal,stripe,mercadopago,digital_wallet,bank_transfer,other',
                'bank_id' => 'nullable|exists:banks,id',
                'brand' => 'nullable|string',
                'last4' => 'nullable|string|max:4',
                'exp_month' => 'nullable|integer|between:1,12',
                'exp_year' => 'nullable|integer|min:' . (date('Y') - 1),
                'cardholder_name' => 'nullable|string',
                'account_number' => 'nullable|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'owner_name' => 'nullable|string',
                'owner_id' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'reference_info' => 'nullable|array',
            ]);

            // Si es método por defecto, desactivar otros
            if ($data['is_default'] ?? false) {
                $owner->paymentMethods()->where('id', '!=', $id)->update(['is_default' => false]);
            }

            $method->update($data);

            return response()->json([
                'success' => true,
                'data' => $method->load('bank')
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar método de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar método de pago'
            ], 500);
        }
    }

    /**
     * Eliminar método de pago
     */
    public function destroy($id)
    {
        try {
            $owner = $this->getPayableOwner();
            $method = $owner->paymentMethods()->findOrFail($id);

            // No permitir desactivar el método por defecto si es el único activo
            if ($method->is_default && $owner->paymentMethods()->active()->count() === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar el único método de pago disponible.'
                ], 422);
            }

            // En lugar de eliminar físicamente, marcar como inactivo y no predeterminado
            $method->update([
                'is_active' => false,
                'is_default' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Método de pago desactivado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar método de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar método de pago'
            ], 500);
        }
    }

    /**
     * Establecer método de pago por defecto
     */
    public function setDefault($id)
    {
        try {
            $owner = $this->getPayableOwner();
            $method = $owner->paymentMethods()->active()->findOrFail($id);

            // Desactivar otros métodos por defecto
            $owner->paymentMethods()->update(['is_default' => false]);
            
            // Activar este método como por defecto
            $method->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Método de pago establecido como predeterminado'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al establecer método por defecto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer método por defecto'
            ], 500);
        }
    }

    /**
     * Obtener métodos de pago disponibles por tipo
     */
    public function getAvailableMethods()
    {
        try {
            $methods = [
                [
                    'type' => 'card',
                    'name' => 'Tarjeta de Crédito/Débito',
                    'description' => 'Visa, MasterCard, American Express',
                    'icon' => 'credit_card',
                    'enabled' => true
                ],
                [
                    'type' => 'mobile_payment',
                    'name' => 'Pago Móvil',
                    'description' => 'Pago a través de banca móvil',
                    'icon' => 'smartphone',
                    'enabled' => true
                ],
                [
                    'type' => 'cash',
                    'name' => 'Efectivo',
                    'description' => 'Pago al momento de la entrega',
                    'icon' => 'money',
                    'enabled' => true
                ],
                [
                    'type' => 'paypal',
                    'name' => 'PayPal',
                    'description' => 'Pago seguro con PayPal',
                    'icon' => 'paypal',
                    'enabled' => true
                ],
                [
                    'type' => 'digital_wallet',
                    'name' => 'Billetera Digital',
                    'description' => 'Apple Pay, Google Pay, etc.',
                    'icon' => 'account_balance_wallet',
                    'enabled' => true
                ],
                [
                    'type' => 'bank_transfer',
                    'name' => 'Transferencia Bancaria',
                    'description' => 'Transferencia directa a cuenta bancaria',
                    'icon' => 'account_balance',
                    'enabled' => true
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $methods
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métodos disponibles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métodos disponibles'
            ], 500);
        }
    }
} 