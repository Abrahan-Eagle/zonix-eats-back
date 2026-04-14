<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Jobs\AutoAssignDeliveryJob;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Review;
use App\Services\OrderStateMachineService;
use App\Services\Routing\RouteCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    private function getAuthAgent()
    {
        $user = Auth::user();
        $user->loadMissing('profile');
        $profile = $user->profile;
        if (! $profile) {
            return null;
        }

        $agent = DeliveryAgent::where('profile_id', $profile->id)->first();
        if ($agent) {
            return $agent;
        }

        // delivery_company no actúa como agente; solo usa /api/delivery-company/* para monitorear y asignar
        return null;
    }

    private function getCompanyAgentIds(): ?array
    {
        $user = Auth::user();
        if ($user->role !== 'delivery_company') {
            return null;
        }
        $profile = $user->profile;
        if (! $profile) {
            return null;
        }
        $company = DeliveryCompany::where('profile_id', $profile->id)->first();
        if (! $company) {
            return null;
        }

        return DeliveryAgent::where('company_id', $company->id)->pluck('id')->toArray();
    }

    private function canAccessAgent(int $deliveryAgentId): bool
    {
        $agent = $this->getAuthAgent();
        if ($agent && $agent->id == $deliveryAgentId) {
            return true;
        }
        $companyIds = $this->getCompanyAgentIds();
        if ($companyIds && in_array($deliveryAgentId, $companyIds)) {
            return true;
        }

        return false;
    }

    /**
     * Contexto unificado para logs (delivery_company vs delivery_agent / delivery).
     */
    private function deliveryLogContext(array $extra = []): array
    {
        $user = Auth::user();
        $profile = $user?->profile;
        $agent = $this->getAuthAgent();
        $companyAgentIds = $this->getCompanyAgentIds();

        return array_merge([
            'auth_user_id' => $user?->id,
            'role' => $user?->role,
            'profile_id' => $profile?->id,
            'resolved_delivery_agent_id' => $agent?->id,
            'resolved_company_id' => $agent?->company_id,
            'company_team_agent_ids' => $companyAgentIds,
        ], $extra);
    }

    /**
     * Get available orders for delivery
     */
    public function getAvailableOrders()
    {
        try {
            $perPage = max(1, min((int) request()->input('per_page', 15), 100));
            Log::debug('[DeliveryAPI] getAvailableOrders entrada', $this->deliveryLogContext());

            $deliveryAgent = $this->getAuthAgent();
            if (! $deliveryAgent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found',
                ], 404);
            }

            $availableOrdersQuery = Order::with(['commerce', 'profile.user', 'orderItems.product'])
                ->whereIn('status', ['processing', 'shipped'])
                ->whereDoesntHave('orderDelivery')
                ->orderBy('created_at', 'desc');

            if ($deliveryAgent->company_id) {
                $availableOrdersQuery->where('delivery_company_id', $deliveryAgent->company_id);
            } else {
                $availableOrdersQuery->whereNull('delivery_company_id');
            }

            $availableOrders = $availableOrdersQuery->paginate($perPage);

            Log::info('[DeliveryAPI] getAvailableOrders OK', $this->deliveryLogContext([
                'count' => $availableOrders->count(),
            ]));

            return response()->json([
                'success' => true,
                'data' => $availableOrders->items(),
                'pagination' => [
                    'current_page' => $availableOrders->currentPage(),
                    'per_page' => $availableOrders->perPage(),
                    'total' => $availableOrders->total(),
                    'last_page' => $availableOrders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching available orders: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching available orders',
            ], 500);
        }
    }

    /**
     * List orders assigned to the authenticated delivery agent.
     */
    public function index()
    {
        try {
            $perPage = max(1, min((int) request()->input('per_page', 15), 100));
            $agent = $this->getAuthAgent();
            if (! $agent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found',
                ], 404);
            }

            $orders = Order::whereHas('orderDelivery', function ($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })
                ->with(['commerce.addresses', 'profile.user', 'orderItems.product', 'orderDelivery'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] index excepción: '.$e->getMessage(), $this->deliveryLogContext());

            return response()->json(['success' => false, 'message' => 'Error interno al listar órdenes'], 500);
        }
    }

    /**
     * Show a single order assigned to the authenticated delivery agent.
     */
    public function show($id)
    {
        try {
            $agent = $this->getAuthAgent();
            if (! $agent) {
                return response()->json(['success' => false, 'message' => 'Delivery agent not found'], 404);
            }

            $order = Order::whereHas('orderDelivery', function ($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })
                ->with(['commerce.addresses', 'profile.user', 'orderItems.product', 'orderDelivery'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] show excepción: '.$e->getMessage(), ['order_id' => $id]);

            return response()->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

    /**
     * Update order status to delivered (only for assigned agent).
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $agent = $this->getAuthAgent();
            if (! $agent) {
                return response()->json(['success' => false, 'message' => 'Delivery agent not found'], 422);
            }

            $order = Order::whereHas('orderDelivery', function ($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })->findOrFail($id);

            $request->validate([
                'status' => 'required|in:delivered',
            ]);

            $stateMachine = app(OrderStateMachineService::class);
            $decision = $stateMachine->applyTransition(
                $order,
                'delivery',
                'delivered',
                $agent->id,
                'delivery_api'
            );
            if (! $decision['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $decision['message'],
                    'error_code' => $decision['error_code'],
                ], $decision['http_status']);
            }

            if ($order->orderDelivery) {
                $order->orderDelivery->update(['status' => 'delivered']);
            }

            event(new \App\Events\OrderStatusChanged($order->fresh()));

            return response()->json([
                'success' => true,
                'message' => 'Pedido marcado como entregado',
                'data' => $order->load(['commerce', 'profile.user', 'orderDelivery']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada o no asignada a ti'], 404);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] updateStatus excepción: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error interno al actualizar estado'], 500);
        }
    }

    public function me()
    {
        $agent = $this->getAuthAgent();
        if (! $agent) {
            $user = Auth::user();
            $msg = $user->role === 'delivery_company'
                ? 'No hay repartidores vinculados a tu empresa. Registra al menos un repartidor.'
                : 'Delivery agent not found';
            Log::warning('[DeliveryAPI] me() 404 — no hay DeliveryAgent resuelto', $this->deliveryLogContext());

            return response()->json(['success' => false, 'message' => $msg], 404);
        }

        Log::debug('[DeliveryAPI] me() OK', $this->deliveryLogContext([
            'effective_agent_id' => $agent->id,
            'working' => (bool) $agent->working,
        ]));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $agent->id,
                'profile_id' => $agent->profile_id,
                'working' => (bool) $agent->working,
                'company_id' => $agent->company_id,
            ],
        ]);
    }

    /**
     * Get orders assigned to delivery agent
     */
    public function getAssignedOrders($deliveryAgentId)
    {
        try {
            $perPage = max(1, min((int) request()->input('per_page', 15), 100));
            if (! $this->canAccessAgent($deliveryAgentId)) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $assignedOrders = Order::with(['commerce', 'profile.user', 'orderItems.product', 'orderDelivery'])
                ->whereHas('orderDelivery', function ($query) use ($deliveryAgentId) {
                    $query->where('agent_id', $deliveryAgentId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $assignedOrders->items(),
                'pagination' => [
                    'current_page' => $assignedOrders->currentPage(),
                    'per_page' => $assignedOrders->perPage(),
                    'total' => $assignedOrders->total(),
                    'last_page' => $assignedOrders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching assigned orders: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error fetching assigned orders'], 500);
        }
    }

    /**
     * Accept order for delivery
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $deliveryAgent = $this->getAuthAgent();
            if (! $deliveryAgent) {
                Log::warning('[DeliveryAPI] acceptOrder 404 — sin agente resuelto', $this->deliveryLogContext(['order_id' => $orderId]));

                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found',
                ], 404);
            }

            $accepted = DB::transaction(function () use ($orderId, $deliveryAgent, $request) {
                $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

                if (! in_array($order->status, ['processing', 'shipped'], true)) {
                    return ['ok' => false, 'http_status' => 409, 'error_code' => 'ORDER_ACCEPT_INVALID_STATUS', 'message' => 'Solo se pueden aceptar órdenes en estado processing o shipped'];
                }

                if (OrderDelivery::where('order_id', $orderId)->exists()) {
                    return ['ok' => false, 'http_status' => 409, 'error_code' => 'ORDER_ALREADY_ASSIGNED', 'message' => 'La orden ya fue asignada a otro repartidor.'];
                }

                try {
                    OrderDelivery::create([
                        'order_id' => $orderId,
                        'agent_id' => $deliveryAgent->id,
                        'status' => 'assigned',
                        'delivery_fee' => $order->delivery_fee ?? 0,
                        'notes' => $request->input('notes', ''),
                    ]);
                } catch (\Throwable $e) {
                    if (str_contains($e->getMessage(), 'order_delivery_order_id_unique')) {
                        return ['ok' => false, 'http_status' => 409, 'error_code' => 'ORDER_ALREADY_ASSIGNED', 'message' => 'La orden ya fue asignada a otro repartidor.'];
                    }
                    throw $e;
                }

                $order->update(['agent_accepted_at' => now()]);

                DB::table('order_status_history')->insert([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => $order->status,
                    'actor_role' => 'delivery',
                    'actor_id' => $deliveryAgent->id,
                    'source' => 'delivery_accept',
                    'reason' => 'delivery_agent_assigned',
                    'occurred_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return ['ok' => true];
            });

            if (! $accepted['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $accepted['message'],
                    'error_code' => $accepted['error_code'],
                ], $accepted['http_status']);
            }

            $freshOrder = Order::with(['commerce.addresses', 'profile.user', 'orderItems.product', 'orderDelivery'])->findOrFail($orderId);
            event(new \App\Events\OrderStatusChanged($freshOrder));

            $commerce = $freshOrder->commerce;
            if ($commerce && $commerce->profile_id) {
                app(\App\Services\NotificationService::class)->notify(
                    $commerce->profile_id,
                    'Repartidor asignado',
                    "Un repartidor aceptó la orden #{$freshOrder->order_number}.",
                    'commerce_order',
                    ['order_id' => (string) $freshOrder->id]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden aceptada exitosamente',
                'data' => $freshOrder,
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting order: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error accepting order',
            ], 500);
        }
    }

    /**
     * POST /api/delivery/orders/{orderId}/reject — Rechazar una orden asignada.
     */
    public function rejectOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if (! in_array($order->status, ['processing', 'shipped'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden rechazar órdenes en estado processing o shipped',
                ], 400);
            }

            $deliveryAgent = $this->getAuthAgent();
            if (! $deliveryAgent) {
                return response()->json(['success' => false, 'message' => 'Delivery agent not found'], 404);
            }

            if (! $order->orderDelivery || $order->orderDelivery->agent_id !== $deliveryAgent->id) {
                return response()->json(['success' => false, 'message' => 'No estás asignado a esta orden'], 403);
            }

            Log::info('[DeliveryAPI] rejectOrder', $this->deliveryLogContext([
                'order_id' => $orderId,
                'agent_id' => $deliveryAgent->id,
            ]));

            $order->orderDelivery->delete();
            AutoAssignDeliveryJob::dispatch($order->id);

            return response()->json([
                'success' => true,
                'message' => 'Orden rechazada. Se buscará otro repartidor.',
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] rejectOrder error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al rechazar orden'], 500);
        }
    }

    /**
     * POST /api/delivery/orders/{orderId}/scan-pickup — Escanear QR de recogida en el comercio.
     */
    public function scanPickup(Request $request, $orderId, RouteCalculationService $routing)
    {
        try {
            $request->validate(['token' => 'required|string']);
            $order = Order::findOrFail($orderId);

            if ($order->status !== 'processing') {
                return response()->json(['success' => false, 'message' => 'La orden no está en preparación'], 400);
            }

            $agent = $this->getAuthAgent();
            if (! $agent || ! $order->orderDelivery || $order->orderDelivery->agent_id !== $agent->id) {
                return response()->json(['success' => false, 'message' => 'No estás asignado a esta orden'], 403);
            }

            if (! $order->pickup_token || $request->token !== $order->pickup_token) {
                return response()->json(['success' => false, 'message' => 'Código QR inválido'], 400);
            }

            $stateMachine = app(OrderStateMachineService::class);
            $decision = $stateMachine->applyTransition(
                $order,
                'delivery',
                'shipped',
                $agent->id,
                'delivery_qr_pickup'
            );
            if (! $decision['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $decision['message'],
                    'error_code' => $decision['error_code'],
                ], $decision['http_status']);
            }
            if ($order->orderDelivery) {
                $order->orderDelivery->update(['status' => 'picked_up']);
            }

            // Recalculate ETA (cascada routing) con ubicación real del agente
            try {
                if ($agent->current_latitude && $agent->current_longitude
                    && $order->delivery_latitude && $order->delivery_longitude) {
                    $routeResult = $routing->calculateBetween(
                        (float) $agent->current_latitude,
                        (float) $agent->current_longitude,
                        (float) $order->delivery_latitude,
                        (float) $order->delivery_longitude,
                        'driving'
                    );
                    $etaMinutes = max(1, (int) $routeResult['duration']);
                    $order->update(['estimated_delivery_time' => $etaMinutes]);
                }
            } catch (\Exception $e) {
                Log::warning('ETA recalculation on pickup failed, keeping existing ETA', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if (! $order->delivery_token) {
                $deliveryToken = substr(hash_hmac('sha256', "order:{$order->id}:delivery:".now()->timestamp, config('app.key')), 0, 16);
                $order->update(['delivery_token' => $deliveryToken]);
            }

            \App\Models\ChatMessage::create([
                'order_id' => $order->id,
                'sender_id' => $agent->profile_id,
                'sender_type' => 'delivery_agent',
                'recipient_type' => 'customer',
                'content' => 'El repartidor ya recogió tu pedido y va en camino.',
                'type' => 'system',
            ]);

            $freshOrder = $order->fresh();
            event(new \App\Events\OrderStatusChanged($freshOrder));

            $commerce = $freshOrder->commerce;
            if ($commerce && $commerce->profile_id) {
                app(\App\Services\NotificationService::class)->notify(
                    $commerce->profile_id,
                    'Pedido recogido',
                    "El repartidor recogió la orden #{$freshOrder->order_number} y va en camino al cliente.",
                    'commerce_order',
                    ['order_id' => (string) $freshOrder->id, 'status' => 'shipped']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto recogido. En camino al cliente.',
                'data' => $freshOrder->load(['commerce.addresses', 'profile.user', 'orderDelivery']),
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] scanPickup error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al verificar recogida'], 500);
        }
    }

    /**
     * POST /api/delivery/orders/{orderId}/scan-delivery — Escanear QR de entrega al buyer.
     */
    public function scanDelivery(Request $request, $orderId)
    {
        try {
            $request->validate(['token' => 'required|string']);
            $order = Order::findOrFail($orderId);

            if ($order->status !== 'shipped') {
                return response()->json(['success' => false, 'message' => 'La orden no está en camino'], 400);
            }

            $agent = $this->getAuthAgent();
            if (! $agent || ! $order->orderDelivery || $order->orderDelivery->agent_id !== $agent->id) {
                return response()->json(['success' => false, 'message' => 'No estás asignado a esta orden'], 403);
            }

            if (! $order->delivery_token || $request->token !== $order->delivery_token) {
                return response()->json(['success' => false, 'message' => 'Código QR inválido'], 400);
            }

            $stateMachine = app(OrderStateMachineService::class);
            $decision = $stateMachine->applyTransition(
                $order,
                'delivery',
                'delivered',
                $agent->id,
                'delivery_qr_dropoff'
            );
            if (! $decision['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $decision['message'],
                    'error_code' => $decision['error_code'],
                ], $decision['http_status']);
            }
            if ($order->orderDelivery) {
                $order->orderDelivery->update(['status' => 'delivered']);
            }

            $freshOrder = $order->fresh();
            event(new \App\Events\OrderStatusChanged($freshOrder));

            $commerce = $freshOrder->commerce;
            if ($commerce && $commerce->profile_id) {
                app(\App\Services\NotificationService::class)->notify(
                    $commerce->profile_id,
                    'Pedido entregado',
                    "La orden #{$freshOrder->order_number} fue entregada al cliente.",
                    'commerce_order',
                    ['order_id' => (string) $freshOrder->id, 'status' => 'delivered']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido entregado exitosamente.',
                'data' => $order->load(['commerce', 'profile.user', 'orderDelivery']),
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] scanDelivery error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al verificar entrega'], 500);
        }
    }

    /**
     * POST /api/delivery/orders/{orderId}/arrived — El agente notifica que llegó al destino.
     * Dispara evento Pusher + notificación FCM al buyer para que muestre el QR de entrega.
     */
    public function arrived($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            if ($order->status !== 'shipped') {
                return response()->json(['success' => false, 'message' => 'La orden no está en camino'], 400);
            }
            $agent = $this->getAuthAgent();
            if (! $agent || ! $order->orderDelivery || $order->orderDelivery->agent_id !== $agent->id) {
                return response()->json(['success' => false, 'message' => 'No estás asignado a esta orden'], 403);
            }

            if (! $order->delivery_token) {
                $token = substr(hash_hmac('sha256', "order:{$order->id}:delivery:".now()->timestamp, config('app.key')), 0, 16);
                $order->update(['delivery_token' => $token]);
            }

            \App\Models\ChatMessage::create([
                'order_id' => $order->id,
                'sender_id' => $agent->profile_id,
                'sender_type' => 'delivery_agent',
                'recipient_type' => 'customer',
                'content' => 'El repartidor llegó a tu ubicación. Muestra tu código QR para confirmar la entrega.',
                'type' => 'system',
            ]);

            $buyerProfile = $order->profile;
            if ($buyerProfile) {
                app(\App\Services\NotificationService::class)->notify(
                    $buyerProfile->id,
                    'Repartidor en tu ubicación',
                    'Tu repartidor llegó. Muestra el QR para confirmar la entrega.',
                    'delivery_arrived',
                    ['order_id' => $order->id, 'action' => 'show_delivery_qr']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Notificación enviada al cliente.',
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] arrived error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al notificar llegada'], 500);
        }
    }

    /**
     * Get current delivery agent status (working) for the authenticated user.
     */
    public function getStatus()
    {
        try {
            $deliveryAgent = $this->getAuthAgent();

            if (! $deliveryAgent) {
                Log::warning('[DeliveryAPI] getStatus 404 — sin agente (antes fallaba delivery_company al buscar por profile_id del dueño)', $this->deliveryLogContext());

                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found',
                ], 404);
            }

            Log::debug('[DeliveryAPI] getStatus OK', $this->deliveryLogContext([
                'agent_id' => $deliveryAgent->id,
                'working' => (bool) $deliveryAgent->working,
            ]));

            return response()->json([
                'success' => true,
                'data' => [
                    'working' => (bool) $deliveryAgent->working,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryAPI] getStatus excepción: '.$e->getMessage(), $this->deliveryLogContext([
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Error getting delivery status',
            ], 500);
        }
    }

    /**
     * Update delivery agent working status (available for orders).
     */
    public function updateWorking(Request $request)
    {
        try {
            $request->validate([
                'working' => 'required|boolean',
            ]);

            $deliveryAgent = $this->getAuthAgent();

            if (! $deliveryAgent) {
                Log::warning('[DeliveryAPI] updateWorking 404 — sin agente resuelto', $this->deliveryLogContext([
                    'requested_working' => $request->boolean('working'),
                ]));

                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found',
                ], 404);
            }

            $deliveryAgent->update(['working' => $request->boolean('working')]);

            Log::info('[DeliveryAPI] updateWorking OK', $this->deliveryLogContext([
                'agent_id' => $deliveryAgent->id,
                'working' => (bool) $deliveryAgent->working,
            ]));

            return response()->json([
                'success' => true,
                'data' => ['working' => $deliveryAgent->working],
                'message' => $deliveryAgent->working ? 'Disponible para recibir pedidos' : 'No disponible',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating working status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating working status',
            ], 500);
        }
    }

    /**
     * Update delivery location
     */
    public function updateLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $deliveryAgent = $this->getAuthAgent();

            if (! $deliveryAgent) {
                Log::warning('[DeliveryAPI] updateLocation 404 — sin agente resuelto', $this->deliveryLogContext());

                return response()->json([
                    'success' => false,
                    'message' => 'Delivery agent not found',
                ], 404);
            }

            $deliveryAgent->update([
                'current_latitude' => $request->latitude,
                'current_longitude' => $request->longitude,
                'last_location_update' => now(),
            ]);

            $activeDelivery = OrderDelivery::where('agent_id', $deliveryAgent->id)
                ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
                ->first();

            event(new \App\Events\DeliveryLocationUpdated(
                $activeDelivery?->order_id,
                $deliveryAgent->id,
                $request->latitude,
                $request->longitude,
                null,
                $deliveryAgent->company_id,
            ));

            Log::debug('[DeliveryAPI] updateLocation OK', $this->deliveryLogContext([
                'agent_id' => $deliveryAgent->id,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'event_code' => 'DELIVERY_LOCATION_UPDATE_OK',
                'occurred_at' => now()->toISOString(),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de ubicacion invalidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating location: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating location',
            ], 500);
        }
    }

    /**
     * Get delivery statistics
     */
    public function getStatistics($deliveryAgentId)
    {
        try {
            if (! $this->canAccessAgent($deliveryAgentId)) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $totalDeliveries = OrderDelivery::where('agent_id', $deliveryAgentId)->count();
            $completedDeliveries = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')->count();
            $cancelledDeliveries = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'cancelled')->count();

            $totalEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->sum('delivery_fee') ?? 0;

            // Calcular average_rating desde reviews
            $averageRating = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $deliveryAgentId)
                ->avg('rating') ?? 0;

            // Contar total_reviews
            $totalReviews = Review::where('reviewable_type', 'App\Models\DeliveryAgent')
                ->where('reviewable_id', $deliveryAgentId)
                ->count();

            // Calcular average_delivery_time y on_time_deliveries (compatible MySQL y SQLite)
            $deliveredOrders = Order::whereHas('orderDelivery', function ($q) use ($deliveryAgentId) {
                $q->where('agent_id', $deliveryAgentId);
            })
                ->where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->get(['id', 'created_at', 'updated_at']);

            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            if (strtolower($driver) === 'mysql') {
                $avgMinutes = Order::whereHas('orderDelivery', function ($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId);
                })
                    ->where('status', 'delivered')
                    ->whereDate('created_at', '>=', now()->subDays(30))
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
                    ->value('avg_minutes') ?? 0;
                $onTimeDeliveries = Order::whereHas('orderDelivery', function ($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId);
                })
                    ->where('status', 'delivered')
                    ->whereDate('created_at', '>=', now()->subDays(30))
                    ->selectRaw('TIMESTAMPDIFF(MINUTE, created_at, updated_at) as delivery_minutes')
                    ->get()
                    ->filter(fn ($o) => ($o->delivery_minutes ?? 0) <= 45)
                    ->count();
            } else {
                $minutes = $deliveredOrders->map(fn ($o) => $o->created_at->diffInMinutes($o->updated_at));
                $avgMinutes = $minutes->isEmpty() ? 0 : $minutes->avg();
                $onTimeDeliveries = $minutes->filter(fn ($m) => $m <= 45)->count();
            }

            $averageDeliveryTime = round($avgMinutes ?? 0, 1);
            $lateDeliveries = $completedDeliveries - $onTimeDeliveries;

            // Calcular customer_satisfaction desde ratings
            $customerSatisfaction = $totalReviews > 0
                ? round(($averageRating / 5) * 100, 1)
                : 0;

            $statistics = [
                'total_deliveries' => $totalDeliveries,
                'completed_deliveries' => $completedDeliveries,
                'cancelled_deliveries' => $cancelledDeliveries,
                'total_earnings' => round($totalEarnings, 2),
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
                'on_time_deliveries' => $onTimeDeliveries,
                'late_deliveries' => max(0, $lateDeliveries),
                'average_delivery_time' => $averageDeliveryTime,
                'total_distance' => 0, // Requiere tracking GPS real
                'fuel_efficiency' => 0, // Requiere datos de vehículo
                'customer_satisfaction' => $customerSatisfaction,
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery statistics: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery statistics',
            ], 500);
        }
    }

    /**
     * Report delivery issue
     */
    public function reportIssue(Request $request, $orderId)
    {
        try {
            $request->validate([
                'issue' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
            ]);

            $order = Order::findOrFail($orderId);

            $deliveryAgent = $this->getAuthAgent();
            if (! $deliveryAgent) {
                return response()->json(['success' => false, 'message' => 'Delivery agent not found'], 404);
            }

            $isAssignedToAgent = OrderDelivery::where('order_id', $order->id)
                ->where('agent_id', $deliveryAgent->id)
                ->exists();

            if (! $isAssignedToAgent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para reportar incidencias de esta orden',
                ], 403);
            }

            Dispute::create([
                'order_id' => $orderId,
                'reported_by_type' => 'App\\Models\\DeliveryAgent',
                'reported_by_id' => $deliveryAgent->id,
                'reported_against_type' => 'App\\Models\\Commerce',
                'reported_against_id' => $order->commerce_id,
                'type' => 'other', // enum: quality_issue|delivery_problem|payment_issue|other
                'description' => $request->issue.': '.$request->description,
                'status' => 'pending', // enum: pending|in_review|resolved|closed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Issue reported successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error reporting delivery issue: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error reporting issue',
            ], 500);
        }
    }

    /**
     * Get delivery history for a delivery agent
     */
    public function getHistory($deliveryAgentId, Request $request)
    {
        try {
            if (! $this->canAccessAgent($deliveryAgentId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                    'error_code' => 'ORDER_FORBIDDEN',
                    'data' => null,
                ], 403);
            }

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $perPage = max(1, min((int) $request->input('per_page', 15), 100));

            $query = Order::with(['commerce', 'profile.user', 'orderItems.product', 'orderDelivery'])
                ->whereHas('orderDelivery', function ($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId);
                })
                ->whereIn('status', ['delivered', 'cancelled']);

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Historial obtenido correctamente',
                'error_code' => null,
                'data' => [
                    'items' => $orders->items(),
                    // Compatibilidad: algunos clientes consumen data como lista directa.
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
            Log::error('Error fetching delivery history: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery history',
                'error_code' => 'DELIVERY_HISTORY_FETCH_FAILED',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get delivery earnings for a delivery agent
     */
    public function getEarnings($deliveryAgentId, Request $request)
    {
        try {
            if (! $this->canAccessAgent($deliveryAgentId)) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $perPage = max(1, min((int) $request->input('per_page', 50), 100));

            $query = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered');

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $deliveries = $query->with('order')->paginate($perPage);
            $deliveriesCollection = collect($deliveries->items());

            $totalEarnings = $deliveriesCollection->sum('delivery_fee');
            $totalDeliveries = $deliveriesCollection->count();

            $deliveryTimes = [];
            foreach ($deliveriesCollection as $delivery) {
                if ($delivery->order && $delivery->order->created_at && $delivery->updated_at) {
                    $deliveryTimes[] = $delivery->updated_at->diffInMinutes($delivery->order->created_at);
                }
            }
            $averageDeliveryTime = count($deliveryTimes) > 0
                ? array_sum($deliveryTimes) / count($deliveryTimes)
                : 0;

            // Calculate today's earnings
            $todayEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->whereDate('updated_at', today())
                ->sum('delivery_fee');

            // Calculate weekly earnings
            $weeklyEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('delivery_fee');

            // Calculate monthly earnings
            $monthlyEarnings = OrderDelivery::where('agent_id', $deliveryAgentId)
                ->where('status', 'delivered')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('delivery_fee');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_earnings' => $totalEarnings,
                    'total_deliveries' => $totalDeliveries,
                    'average_delivery_time' => round($averageDeliveryTime, 2),
                    'today_earnings' => $todayEarnings,
                    'weekly_earnings' => $weeklyEarnings,
                    'monthly_earnings' => $monthlyEarnings,
                    'delivery_fees' => $deliveriesCollection->pluck('delivery_fee')->toArray(),
                    'delivery_dates' => $deliveriesCollection->pluck('updated_at')->map(function ($date) {
                        return $date->toIso8601String();
                    })->toArray(),
                    'pagination' => [
                        'current_page' => $deliveries->currentPage(),
                        'per_page' => $deliveries->perPage(),
                        'total' => $deliveries->total(),
                        'last_page' => $deliveries->lastPage(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery earnings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching delivery earnings',
            ], 500);
        }
    }

    /**
     * Get delivery routes for a delivery agent
     */
    public function getRoutes($deliveryAgentId, RouteCalculationService $routing, Request $request)
    {
        try {
            $perPage = max(1, min((int) $request->input('per_page', 20), 100));
            if (! $this->canAccessAgent($deliveryAgentId)) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $assignedOrders = Order::with(['commerce.addresses', 'profile.user', 'orderDelivery'])
                ->whereHas('orderDelivery', function ($q) use ($deliveryAgentId) {
                    $q->where('agent_id', $deliveryAgentId)
                        ->whereIn('status', ['assigned', 'picked_up', 'in_transit']);
                })
                ->paginate($perPage);

            $routes = [];
            foreach ($assignedOrders->items() as $index => $order) {
                $commerceLat = $order->commerce?->latitude;
                $commerceLng = $order->commerce?->longitude;
                $customerLat = $order->delivery_latitude;
                $customerLng = $order->delivery_longitude;

                $startLat = (float) ($commerceLat ?? $customerLat ?? 10.1579);
                $startLng = (float) ($commerceLng ?? $customerLng ?? -67.9972);
                $endLat = (float) ($customerLat ?? $startLat);
                $endLng = (float) ($customerLng ?? $startLng);

                $distance = 5.0;
                $estimatedTime = 30;

                try {
                    $routeResult = $routing->calculateBetween($startLat, $startLng, $endLat, $endLng, 'driving');
                    $distance = $routeResult['distance'];
                    $estimatedTime = $routeResult['duration'];
                } catch (\Exception $e) {
                    Log::warning('Error calculando ruta: '.$e->getMessage());
                }

                $routes[] = [
                    'id' => $index + 1,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'commerce_name' => $order->commerce->business_name ?? 'Comercio',
                    'commerce_address' => $order->commerce->address ?? '',
                    'commerce_latitude' => $commerceLat,
                    'commerce_longitude' => $commerceLng,
                    'delivery_address' => $order->delivery_address ?? $order->shipping_address ?? '',
                    'delivery_latitude' => $customerLat,
                    'delivery_longitude' => $customerLng,
                    'estimated_time' => $estimatedTime,
                    'total_distance' => $distance,
                    'status' => $order->orderDelivery->status ?? 'assigned',
                    'total' => $order->total,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $routes,
                'pagination' => [
                    'current_page' => $assignedOrders->currentPage(),
                    'per_page' => $assignedOrders->perPage(),
                    'total' => $assignedOrders->total(),
                    'last_page' => $assignedOrders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery routes: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error fetching delivery routes'], 500);
        }
    }
}
