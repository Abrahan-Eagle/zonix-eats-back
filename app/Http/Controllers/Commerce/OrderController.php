<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PaymentValidated;
use App\Events\OrderStatusChanged;

class OrderController extends Controller
{
  public function index(Request $request)
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            $commerce = $profile->commerce;
            
            // Verificar si se solicita un commerce_id específico
            if ($request->has('commerce_id') && $request->commerce_id != $commerce->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $perPage = $request->input('per_page', 15);
            $status = $request->input('status');
            
            $query = Order::where('commerce_id', $commerce->id)
                ->with(['profile.user', 'orderItems.product']);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json($orders);
        } catch (\Exception $e) {
            \Log::error('Error al listar órdenes de comercio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al listar órdenes'], 500);
        }
    }

    public function show(Order $order)
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            // Verificar que la orden pertenece al comercio del usuario
            if ($order->commerce_id !== $profile->commerce->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($order->load(['profile.user', 'orderItems.product', 'orderDelivery']));
        } catch (\Exception $e) {
            \Log::error('Error al mostrar orden de comercio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al mostrar orden'], 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        try {
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;
            
            if (!$profile || !$profile->commerce) {
                return response()->json(['error' => 'User is not associated with a commerce'], 403);
            }

            // Verificar que la orden pertenece al comercio del usuario
            if ($order->commerce_id !== $profile->commerce->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'status' => 'required|in:pending_payment,paid,processing,shipped,delivered,cancelled'
            ]);

            // Validar transiciones de estado
            $validTransitions = [
                'paid' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
            ];

            if (isset($validTransitions[$order->status])) {
                if (!in_array($request->status, $validTransitions[$order->status])) {
                    return response()->json([
                        'success' => false,
                        'message' => "No se puede cambiar de '{$order->status}' a '{$request->status}'"
                    ], 400);
                }
            } else {
                // Estados que no permiten cambios
                if (in_array($order->status, ['pending_payment', 'delivered', 'cancelled'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "No se puede cambiar el estado de una orden en '{$order->status}'"
                    ], 400);
                }
            }

            $order->update(['status' => $request->status]);

            // Emitir evento de cambio de estado
            event(new \App\Events\OrderStatusChanged($order));

            return response()->json(['success' => true, 'message' => 'Estado de la orden actualizado']);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar estado de orden de comercio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al actualizar estado de orden'], 500);
        }
    }

    /**
     * Validar o rechazar comprobante de pago de una orden.
     * 
     * Este método consolida validarComprobante() y validatePayment().
     * Usa el campo 'status' en lugar de 'estado' para mantener consistencia.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validatePayment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'is_valid' => 'required|boolean',
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            $order = Order::findOrFail($id);
            $user = Auth::user()->load('profile.commerce');
            $profile = $user->profile;

            // Verificar que el usuario es el dueño del comercio
            if (!$profile || !$profile->commerce || $order->commerce_id !== $profile->commerce->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para validar esta orden'
                ], 403);
            }

            if ($validated['is_valid']) {
                $order->update([
                    'status' => 'paid',
                    'payment_validated_at' => now(),
                    'cancellation_reason' => null
                ]);
                
                $message = 'Pago validado correctamente';
                
                // Emitir evento de pago validado
                event(new PaymentValidated($order, true, $profile->id));
            } else {
                $order->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $validated['rejection_reason'] ?? 'Pago rechazado por el comercio',
                    'payment_validated_at' => null
                ]);
                
                $message = 'Pago rechazado';
                
                // Emitir evento de cambio de estado
                event(new OrderStatusChanged($order));
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al validar el comprobante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al validar el comprobante: ' . $e->getMessage()
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
}
