<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private function getDeliveryAgent()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (! $profile) {
            return null;
        }
        if ($profile->deliveryAgent) {
            return $profile->deliveryAgent;
        }
        if ($user->role === 'delivery_company') {
            $company = DeliveryCompany::where('profile_id', $profile->id)->first();
            if ($company) {
                return DeliveryAgent::where('company_id', $company->id)->first();
            }
        }

        return null;
    }

    private function getAgentIdsForQuery()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (! $profile) {
            return [];
        }
        if ($user->role === 'delivery_company') {
            $company = DeliveryCompany::where('profile_id', $profile->id)->first();
            if ($company) {
                return DeliveryAgent::where('company_id', $company->id)->pluck('id')->toArray();
            }

            return [];
        }
        $agent = $profile->deliveryAgent;

        return $agent ? [$agent->id] : [];
    }

    /**
     * Contexto para laravel.log al depurar delivery_company / agentes.
     */
    private function orderDeliveryLogContext(array $agentIds, array $extra = []): array
    {
        $user = Auth::user();

        return array_merge([
            'auth_user_id' => $user?->id,
            'role' => $user?->role,
            'profile_id' => $user?->profile?->id,
            'agent_ids_for_query' => $agentIds,
            'agent_ids_count' => count($agentIds),
        ], $extra);
    }

    public function index()
    {
        try {
            $agentIds = $this->getAgentIdsForQuery();
            Log::debug('[DeliveryAPI] OrderController@index entrada', $this->orderDeliveryLogContext($agentIds));

            // 200 + lista vacía: no es "prohibido", es estado de cuenta (ej. empresa sin repartidores aún).
            if (empty($agentIds)) {
                Log::warning('[DeliveryAPI] OrderController@index sin agent_ids (empresa sin repartidores o perfil sin company)', $this->orderDeliveryLogContext($agentIds));

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No se encontraron agentes de delivery vinculados a tu cuenta',
                ]);
            }

            $orders = Order::whereHas('orderDelivery', function ($query) use ($agentIds) {
                $query->whereIn('agent_id', $agentIds);
            })
                ->with(['commerce', 'profile.user', 'orderItems.product', 'orderDelivery'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('[DeliveryAPI] OrderController@index OK', $this->orderDeliveryLogContext($agentIds, [
                'orders_returned' => $orders->count(),
            ]));

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] OrderController@index excepción: '.$e->getMessage(), [
                'auth_user_id' => Auth::id(),
                'role' => Auth::user()?->role,
            ]);

            return response()->json(['success' => false, 'message' => 'Error interno al listar órdenes'], 500);
        }
    }

    public function show($id)
    {
        try {
            $agentIds = $this->getAgentIdsForQuery();
            if (empty($agentIds)) {
                return response()->json(['success' => false, 'message' => 'No se encontraron agentes de delivery'], 404);
            }

            $order = Order::whereHas('orderDelivery', function ($query) use ($agentIds) {
                $query->whereIn('agent_id', $agentIds);
            })
                ->with(['commerce', 'profile.user', 'orderItems.product', 'orderDelivery'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] OrderController@show excepción: '.$e->getMessage(), [
                'order_id' => $id,
                'auth_user_id' => Auth::id(),
            ]);

            return response()->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

    public function acceptOrder($orderId)
    {
        try {
            $agent = $this->getDeliveryAgent();
            if (! $agent) {
                return response()->json(['success' => false, 'message' => 'User is not a delivery agent'], 422);
            }

            $order = Order::with('orderDelivery')->findOrFail($orderId);

            if ($order->status !== 'shipped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aceptar órdenes en estado shipped',
                ], 400);
            }

            if ($order->orderDelivery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta orden ya fue asignada a otro repartidor',
                ], 400);
            }

            $order->orderDelivery()->create([
                'agent_id' => $agent->id,
                'status' => 'assigned',
            ]);

            event(new \App\Events\OrderStatusChanged($order->fresh()));

            return response()->json([
                'success' => true,
                'message' => 'Orden aceptada exitosamente',
                'data' => $order->load(['commerce', 'profile.user', 'orderItems.product', 'orderDelivery']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] OrderController@acceptOrder excepción: '.$e->getMessage(), ['auth_user_id' => Auth::id()]);

            return response()->json(['success' => false, 'message' => 'Error interno al aceptar orden'], 500);
        }
    }

    public function updateStatus($id, Request $request)
    {
        try {
            $agentIds = $this->getAgentIdsForQuery();
            if (empty($agentIds)) {
                return response()->json(['success' => false, 'message' => 'No se encontraron agentes de delivery'], 422);
            }

            $order = Order::whereHas('orderDelivery', function ($query) use ($agentIds) {
                $query->whereIn('agent_id', $agentIds);
            })->findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|in:delivered',
            ]);

            $order->update(['status' => $validated['status']]);

            if ($order->orderDelivery) {
                $order->orderDelivery->update(['status' => $validated['status']]);
            }

            event(new \App\Events\OrderStatusChanged($order));

            return response()->json([
                'success' => true,
                'message' => 'Pedido marcado como entregado',
                'data' => $order->load(['commerce', 'profile.user', 'orderDelivery']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada o no asignada a ti'], 404);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] OrderController@updateStatus excepción: '.$e->getMessage(), ['auth_user_id' => Auth::id()]);

            return response()->json(['success' => false, 'message' => 'Error interno al actualizar estado'], 500);
        }
    }
}
