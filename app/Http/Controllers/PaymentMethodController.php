<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Determinar la entidad dueña de los métodos de pago según el rol actual.
     * - users             → User (comprador)
     * - commerce         → Commerce (vendedor)
     * - delivery_agent   → DeliveryAgent (repartidor)
     * - delivery         → DeliveryAgent (repartidor autónomo)
     * - delivery_company → DeliveryCompany (empresa de repartidores)
     * - otros            → User por defecto
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

            if ($role === 'commerce' && $profile) {
                $commerceId = request()->query('commerce_id') ?? request()->header('X-Commerce-Id') ?? request()->input('commerce_id');
                if ($commerceId) {
                    $commerce = $profile->commerces()->find($commerceId);
                    if ($commerce) {
                        return $commerce;
                    }
                }
                return $profile->getPrimaryCommerce();
            }

            // Motorizados (delivery_agent o delivery autónomo): métodos de pago del repartidor
            if (in_array($role, ['delivery', 'delivery_agent'], true) && $profile && $profile->deliveryAgent) {
                return $profile->deliveryAgent;
            }

            // Empresa de delivery: métodos de pago de la empresa
            if ($role === 'delivery_company' && $profile && $profile->deliveryCompany) {
                return $profile->deliveryCompany;
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
            if (!$owner || !method_exists($owner, 'paymentMethods')) {
                return response()->json(['success' => true, 'data' => []]);
            }
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
    public function store(StorePaymentMethodRequest $request)
    {
        try {
            $owner = $this->getPayableOwner();
            if (!$owner || !method_exists($owner, 'paymentMethods')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar el propietario de los métodos de pago (perfil o comercio).'
                ], 422);
            }

            $data = $request->validated();

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
    public function update(UpdatePaymentMethodRequest $request, $id)
    {
        try {
            $owner = $this->getPayableOwner();
            if (!$owner || !method_exists($owner, 'paymentMethods')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar el propietario de los métodos de pago.'
                ], 422);
            }
            $method = $owner->paymentMethods()->findOrFail($id);

            $data = $request->validated();

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