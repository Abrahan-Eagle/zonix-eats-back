<?php

namespace App\Http\Controllers\DeliveryCompany;

use App\Events\OrderStatusChanged;
use App\Events\PaymentValidated;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\OperatorCode;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Phone;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\DeliveryFeeService;
use App\Services\DeliveryObservabilityService;
use App\Services\OrderStateMachineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    private function getAuthCompany(): ?DeliveryCompany
    {
        $user = Auth::user();
        $profile = $user?->profile;
        if (! $profile) {
            return null;
        }

        return DeliveryCompany::where('profile_id', $profile->id)->first();
    }

    private function getAgentIds(DeliveryCompany $company): array
    {
        return DeliveryAgent::where('company_id', $company->id)->pluck('id')->toArray();
    }

    /**
     * GET /api/delivery-company/dashboard
     */
    public function dashboard()
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes una empresa de delivery registrada.',
                ], 404);
            }

            $agentIds = $this->getAgentIds($company);
            $agents = DeliveryAgent::whereIn('id', $agentIds)->get();

            $activeAgents = $agents->where('working', true)->count();

            $deliveredQuery = fn ($period) => OrderDelivery::whereIn('agent_id', $agentIds)
                ->where('status', 'delivered');

            $todayDeliveries = (clone $deliveredQuery(null))->whereDate('updated_at', today())->count();
            $weekDeliveries = (clone $deliveredQuery(null))->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $monthDeliveries = (clone $deliveredQuery(null))->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count();

            $todayEarnings = (clone $deliveredQuery(null))->whereDate('updated_at', today())->sum('delivery_fee');
            $weekEarnings = (clone $deliveredQuery(null))->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('delivery_fee');
            $monthEarnings = (clone $deliveredQuery(null))->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('delivery_fee');

            $avgRating = Review::where('reviewable_type', DeliveryAgent::class)
                ->whereIn('reviewable_id', $agentIds)
                ->avg('rating') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'image' => $company->image,
                        'active' => (bool) $company->active,
                        'open' => (bool) $company->open,
                        'default_payout_percentage' => (float) ($company->default_payout_percentage ?? 70),
                        'headquarters' => (function () use ($company) {
                            $addr = $company->profile?->addresses()
                                ->whereNotNull('latitude')->whereNotNull('longitude')
                                ->first();

                            return $addr ? [
                                'latitude' => (float) $addr->latitude,
                                'longitude' => (float) $addr->longitude,
                                'address' => $addr->address ?? $company->address,
                            ] : null;
                        })(),
                    ],
                    'agents_count' => count($agentIds),
                    'active_agents' => $activeAgents,
                    'today_deliveries' => $todayDeliveries,
                    'week_deliveries' => $weekDeliveries,
                    'month_deliveries' => $monthDeliveries,
                    'today_earnings' => round($todayEarnings, 2),
                    'week_earnings' => round($weekEarnings, 2),
                    'month_earnings' => round($monthEarnings, 2),
                    'average_rating' => round($avgRating, 1),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] dashboard error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al cargar dashboard'], 500);
        }
    }

    /**
     * GET /api/delivery-company/agents
     */
    public function agents(Request $request)
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            $query = DeliveryAgent::where('company_id', $company->id)
                ->with(['profile.user', 'profile.phones'])
                ->withCount(['orderDeliveries as total_deliveries' => function ($q) {
                    $q->where('status', 'delivered');
                }])
                ->withAvg(['reviews' => function ($q) {
                    $q->where('reviewable_type', DeliveryAgent::class);
                }], 'rating');

            if ($request->boolean('active_only', false)) {
                $query->where('status', 'activo');
            }

            $agentIds = (clone $query)->pluck('id');
            $activeDeliveries = OrderDelivery::whereIn('agent_id', $agentIds)
                ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
                ->with('order:id,status,delivery_latitude,delivery_longitude,delivery_address')
                ->get()
                ->keyBy('agent_id');

            $agents = $query->get()->map(function ($agent) use ($activeDeliveries) {
                $profile = $agent->profile;
                $user = $profile?->user;
                $phone = $profile?->phones?->first();

                $totalDeliveries = (int) ($agent->total_deliveries ?? 0);
                $avgRating = (float) ($agent->reviews_avg_rating ?? 0);

                $activeDelivery = $activeDeliveries->get($agent->id);

                $destination = null;
                if ($activeDelivery?->order) {
                    $o = $activeDelivery->order;
                    if ($o->delivery_latitude && $o->delivery_longitude) {
                        $destination = [
                            'latitude' => (float) $o->delivery_latitude,
                            'longitude' => (float) $o->delivery_longitude,
                            'address' => $o->delivery_address ?? '',
                        ];
                    }
                }

                return [
                    'id' => $agent->id,
                    'name' => trim(($profile->firstName ?? '').' '.($profile->lastName ?? '')),
                    'photo' => $user->photo_users ?? null,
                    'phone' => $phone?->phone_number ?? null,
                    'status' => $agent->status,
                    'working' => (bool) $agent->working,
                    'is_active' => $agent->status === 'activo',
                    'vehicle_type' => $agent->vehicle_type,
                    'license_number' => $agent->license_number,
                    'rating' => round($avgRating, 1),
                    'total_deliveries' => $totalDeliveries,
                    'current_latitude' => $agent->current_latitude,
                    'current_longitude' => $agent->current_longitude,
                    'last_location_update' => $agent->last_location_update,
                    'payout_percentage' => (float) ($agent->payout_percentage ?? 70),
                    'current_order_id' => $activeDelivery?->order_id,
                    'current_order_status' => $activeDelivery?->order?->status,
                    'is_busy' => $activeDelivery !== null,
                    'destination' => $destination,
                ];
            });

            $statusFilter = $request->query('status');
            if ($statusFilter === 'available') {
                $agents = $agents->where('is_busy', false)->values();
            } elseif ($statusFilter === 'busy') {
                $agents = $agents->where('is_busy', true)->values();
            }

            return response()->json(['success' => true, 'data' => $agents]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] agents error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al listar agentes'], 500);
        }
    }

    /**
     * POST /api/delivery-company/agents — Crear cuenta de delivery_agent (la empresa crea al repartidor).
     */
    public function storeAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'vehicle_type' => 'required|string|max:100',
            'license_number' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        try {
            $data = DB::transaction(function () use ($request, $company) {
                $name = trim($request->firstName.' '.$request->lastName);
                $user = User::create([
                    'name' => $name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'delivery_agent',
                    'completed_onboarding' => false,
                ]);

                $profile = Profile::create([
                    'user_id' => $user->id,
                    'firstName' => $request->firstName,
                    'lastName' => $request->lastName,
                    'middleName' => $request->middleName ?? '',
                    'secondLastName' => $request->secondLastName ?? '',
                    'status' => 'notverified',
                ]);

                $this->createPhoneForProfile($profile, $request->phone);

                $defaultPayout = $company->default_payout_percentage ?? 70;
                $agent = DeliveryAgent::create([
                    'profile_id' => $profile->id,
                    'company_id' => $company->id,
                    'vehicle_type' => $request->vehicle_type,
                    'license_number' => $request->license_number,
                    'status' => 'activo',
                    'working' => false,
                    'payout_percentage' => $defaultPayout,
                ]);

                return ['user' => $user, 'profile' => $profile, 'agent' => $agent];
            });

            return response()->json([
                'success' => true,
                'message' => 'Agente creado correctamente. El repartidor puede iniciar sesión con su email y contraseña.',
                'data' => [
                    'id' => $data['agent']->id,
                    'email' => $data['user']->email,
                    'name' => trim($data['profile']->firstName.' '.$data['profile']->lastName),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] storeAgent error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al crear agente'], 500);
        }
    }

    /**
     * PATCH /api/delivery-company/agents/{id} — Actualizar estado del agente (activo/inactivo/suspendido).
     */
    public function updateAgentStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:activo,inactivo,suspendido',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Estado inválido', 'errors' => $validator->errors()], 422);
        }

        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        $agent = DeliveryAgent::where('id', $id)->where('company_id', $company->id)->first();
        if (! $agent) {
            return response()->json(['success' => false, 'message' => 'Agente no pertenece a tu empresa'], 403);
        }

        $agent->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => ['id' => $agent->id, 'status' => $agent->status],
        ]);
    }

    /**
     * PATCH /api/delivery-company/agents/{id}/payout — Cambiar porcentaje de pago del agente.
     */
    public function updateAgentPayout(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payout_percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Porcentaje inválido (0-100)', 'errors' => $validator->errors()], 422);
        }

        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        $agent = DeliveryAgent::where('id', $id)->where('company_id', $company->id)->first();
        if (! $agent) {
            return response()->json(['success' => false, 'message' => 'Agente no pertenece a tu empresa'], 403);
        }

        $agent->update(['payout_percentage' => $request->payout_percentage]);

        return response()->json([
            'success' => true,
            'message' => 'Porcentaje actualizado',
            'data' => ['id' => $agent->id, 'payout_percentage' => (float) $agent->payout_percentage],
        ]);
    }

    /**
     * PATCH /api/delivery-company/settings — Actualizar configuración de la empresa (ej. % default).
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_payout_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        if ($request->has('default_payout_percentage')) {
            $company->update(['default_payout_percentage' => $request->default_payout_percentage]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'default_payout_percentage' => (float) ($company->default_payout_percentage ?? 70),
            ],
        ]);
    }

    /**
     * GET /api/delivery-company/orders/{id}/available-agents — Agentes de la empresa ordenados por distancia al comercio.
     */
    public function availableAgentsForOrder($orderId)
    {
        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        $order = Order::with(['commerce.addresses'])->find($orderId);
        if (! $order || $order->status !== 'shipped') {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada o no disponible para asignar'], 404);
        }
        if ($order->delivery_company_id !== $company->id) {
            return response()->json(['success' => false, 'message' => 'La orden no pertenece a tu empresa'], 403);
        }
        if ($order->orderDelivery) {
            return response()->json(['success' => false, 'message' => 'La orden ya tiene un repartidor asignado'], 400);
        }

        $commerce = $order->commerce;
        $commerceAddress = $commerce->addresses()->whereNotNull('latitude')->whereNotNull('longitude')->first();
        $commerceLat = (float) ($commerceAddress?->latitude ?? config('zonix.default_commerce_lat', 10.1620));
        $commerceLng = (float) ($commerceAddress?->longitude ?? config('zonix.default_commerce_lng', -68.0074));

        $agentIds = $this->getAgentIds($company);
        $agents = DeliveryAgent::whereIn('id', $agentIds)
            ->where('working', true)
            ->with(['profile'])
            ->get()
            ->filter(function ($agent) {
                $hasActive = OrderDelivery::where('agent_id', $agent->id)
                    ->whereHas('order', fn ($q) => $q->whereIn('status', ['shipped']))
                    ->exists();

                return ! $hasActive;
            })
            ->map(function ($agent) use ($commerceLat, $commerceLng) {
                $lat = $agent->current_latitude ?? $commerceLat;
                $lng = $agent->current_longitude ?? $commerceLng;
                $distanceKm = DeliveryFeeService::distanceKm(
                    (float) $commerceLat,
                    (float) $commerceLng,
                    (float) $lat,
                    (float) $lng
                );
                $profile = $agent->profile;
                $totalDeliveries = OrderDelivery::where('agent_id', $agent->id)->where('status', 'delivered')->count();
                $avgRating = Review::where('reviewable_type', DeliveryAgent::class)->where('reviewable_id', $agent->id)->avg('rating') ?? 0;

                return [
                    'id' => $agent->id,
                    'name' => trim(($profile->firstName ?? '').' '.($profile->lastName ?? '')),
                    'distance_km' => round($distanceKm, 2),
                    'vehicle_type' => $agent->vehicle_type,
                    'rating' => round($avgRating, 1),
                    'total_deliveries' => $totalDeliveries,
                ];
            })
            ->sortBy('distance_km')
            ->values()
            ->toArray();

        return response()->json(['success' => true, 'data' => $agents]);
    }

    /**
     * POST /api/delivery-company/orders/{id}/assign — Asignar orden a un agente de la empresa.
     */
    public function assignOrder(Request $request, $orderId)
    {
        $request->validate(['agent_id' => 'required|exists:delivery_agents,id']);

        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        $agent = DeliveryAgent::where('id', $request->agent_id)->where('company_id', $company->id)->first();
        if (! $agent) {
            return response()->json(['success' => false, 'message' => 'Agente no pertenece a tu empresa'], 403);
        }

        $result = DB::transaction(function () use ($orderId, $company, $agent) {
            $order = Order::where('id', $orderId)->lockForUpdate()->first();
            if (! $order || $order->status !== 'shipped') {
                return response()->json(['success' => false, 'message' => 'Orden no encontrada o no disponible'], 404);
            }
            if ((int) $order->delivery_company_id !== (int) $company->id) {
                return response()->json(['success' => false, 'message' => 'La orden no pertenece a tu empresa'], 403);
            }

            $existingAssignment = OrderDelivery::where('order_id', $order->id)->lockForUpdate()->first();
            if ($existingAssignment) {
                return response()->json(['success' => false, 'message' => 'La orden ya tiene un repartidor asignado'], 409);
            }

            OrderDelivery::create([
                'order_id' => $order->id,
                'agent_id' => $agent->id,
                'status' => 'assigned',
                'delivery_fee' => $order->delivery_fee ?? 0,
                'notes' => '',
            ]);

            event(new OrderStatusChanged($order->fresh()));

            return response()->json([
                'success' => true,
                'message' => 'Orden asignada al repartidor',
                'data' => $order->fresh()->load(['commerce', 'profile.user', 'orderItems.product', 'orderDelivery']),
            ]);
        });

        return $result;
    }

    public function observabilitySummary(DeliveryObservabilityService $observabilityService)
    {
        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        try {
            $data = $observabilityService->getSummary((int) $company->id, [
                'window_hours' => request()->query('window_hours'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Resumen de observabilidad obtenido correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error observabilitySummary company: '.$e->getMessage(), ['company_id' => $company->id]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo resumen de observabilidad',
            ], 500);
        }
    }

    public function observabilityIncidents(Request $request, DeliveryObservabilityService $observabilityService)
    {
        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        try {
            $incidents = $observabilityService->getIncidents((int) $company->id, [
                'type' => $request->query('type'),
                'priority' => $request->query('priority'),
                'window_hours' => $request->query('window_hours'),
                'page' => $request->query('page', 1),
                'per_page' => $request->query('per_page', 20),
            ]);

            return response()->json([
                'success' => true,
                'data' => $incidents,
                'message' => 'Incidentes de observabilidad obtenidos correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error observabilityIncidents company: '.$e->getMessage(), ['company_id' => $company->id]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo incidentes de observabilidad',
            ], 500);
        }
    }

    public function observabilityIncidentOrders(Request $request, DeliveryObservabilityService $observabilityService)
    {
        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        try {
            $orders = $observabilityService->getIncidentOrders((int) $company->id, [
                'type' => $request->query('type'),
                'window_hours' => $request->query('window_hours'),
                'page' => $request->query('page', 1),
                'per_page' => $request->query('per_page', 20),
            ]);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'Ordenes de incidentes obtenidas correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error observabilityIncidentOrders company: '.$e->getMessage(), ['company_id' => $company->id]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo ordenes de incidentes',
            ], 500);
        }
    }

    public function observabilityRunbooks(DeliveryObservabilityService $observabilityService)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $observabilityService->getRunbooks(),
            ],
            'message' => 'Runbooks de observabilidad obtenidos correctamente',
        ]);
    }

    public function observabilityHistory(Request $request, DeliveryObservabilityService $observabilityService)
    {
        $company = $this->getAuthCompany();
        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        try {
            $history = $observabilityService->getHistory(
                (int) $company->id,
                (int) $request->query('page', 1),
                (int) $request->query('per_page', 24),
                $request->query('window_hours') !== null ? (int) $request->query('window_hours') : null
            );

            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => 'Historico de observabilidad obtenido correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error observabilityHistory company: '.$e->getMessage(), ['company_id' => $company->id]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo historico de observabilidad',
            ], 500);
        }
    }

    private function createPhoneForProfile(Profile $profile, string $phoneString): void
    {
        $digits = preg_replace('/\D/', '', $phoneString);
        if (strlen($digits) < 7) {
            return;
        }
        $number = substr($digits, -7);
        $code4 = substr($digits, 0, 4);
        $code3 = ltrim($code4, '0');
        $operatorCode = OperatorCode::where('code', $code4)->orWhere('code', $code3)->first()
            ?? OperatorCode::first();
        if (! $operatorCode) {
            return;
        }
        Phone::create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => $number,
            'is_primary' => true,
            'status' => true,
        ]);
    }

    /**
     * GET /api/delivery-company/agents/{id}
     */
    public function agentDetail($id)
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            $agent = DeliveryAgent::where('id', $id)
                ->where('company_id', $company->id)
                ->with(['profile.user', 'profile.phones'])
                ->first();

            if (! $agent) {
                return response()->json(['success' => false, 'message' => 'Agente no pertenece a tu empresa'], 403);
            }

            $profile = $agent->profile;
            $user = $profile?->user;

            $totalDeliveries = OrderDelivery::where('agent_id', $agent->id)->where('status', 'delivered')->count();
            $totalEarnings = OrderDelivery::where('agent_id', $agent->id)->where('status', 'delivered')->sum('delivery_fee');
            $avgRating = Review::where('reviewable_type', DeliveryAgent::class)
                ->where('reviewable_id', $agent->id)->avg('rating') ?? 0;

            $recentOrders = Order::with(['commerce'])
                ->whereHas('orderDelivery', fn ($q) => $q->where('agent_id', $agent->id))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'commerce_name' => $o->commerce->business_name ?? '',
                    'status' => $o->status,
                    'total' => $o->total,
                    'delivery_fee' => $o->delivery_fee,
                    'created_at' => $o->created_at->toIso8601String(),
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $agent->id,
                    'name' => trim(($profile->firstName ?? '').' '.($profile->lastName ?? '')),
                    'photo' => $user->photo_users ?? null,
                    'phone' => $profile?->phones?->first()?->phone_number,
                    'status' => $agent->status,
                    'working' => (bool) $agent->working,
                    'vehicle_type' => $agent->vehicle_type,
                    'license_number' => $agent->license_number,
                    'rating' => round($avgRating, 1),
                    'total_deliveries' => $totalDeliveries,
                    'total_earnings' => round($totalEarnings, 2),
                    'payout_percentage' => (float) ($agent->payout_percentage ?? 70),
                    'recent_orders' => $recentOrders,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] agentDetail error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al obtener detalle del agente'], 500);
        }
    }

    /**
     * GET /api/delivery-company/orders?status=shipped,delivered
     */
    public function orders(Request $request)
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            $agentIds = $this->getAgentIds($company);
            if (empty($agentIds)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $query = Order::with(['commerce', 'orderItems.product', 'orderDelivery.agent.profile'])
                ->whereHas('orderDelivery', fn ($q) => $q->whereIn('agent_id', $agentIds));

            if ($request->filled('status')) {
                $statuses = explode(',', $request->input('status'));
                $query->whereIn('status', $statuses);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate(20);

            return response()->json(['success' => true, 'data' => $orders]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] orders error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al listar órdenes'], 500);
        }
    }

    /**
     * GET /api/delivery-company/orders/pending — Órdenes en estado shipped sin repartidor asignado (para asignación manual).
     */
    public function pendingOrders()
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            // Debe coincidir con availableAgentsForOrder / assignOrder: solo shipped sin repartidor.
            $orders = Order::with(['commerce', 'orderItems.product'])
                ->where('delivery_company_id', $company->id)
                ->where('status', 'shipped')
                ->whereDoesntHave('orderDelivery')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json(['success' => true, 'data' => $orders]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] pendingOrders error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al listar órdenes pendientes'], 500);
        }
    }

    /**
     * GET /api/delivery-company/earnings?period=today|week|month
     */
    public function earnings(Request $request)
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            $agentIds = $this->getAgentIds($company);

            $baseQuery = fn () => OrderDelivery::whereIn('agent_id', $agentIds)->where('status', 'delivered');

            $todayEarnings = (clone $baseQuery())->whereDate('updated_at', today())->sum('delivery_fee');
            $weekEarnings = (clone $baseQuery())->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('delivery_fee');
            $monthEarnings = (clone $baseQuery())->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('delivery_fee');
            $totalEarnings = (clone $baseQuery())->sum('delivery_fee');

            $agentBreakdown = DeliveryAgent::whereIn('id', $agentIds)
                ->with('profile')
                ->get()
                ->map(function ($agent) {
                    $profile = $agent->profile;
                    $delivered = OrderDelivery::where('agent_id', $agent->id)->where('status', 'delivered');

                    return [
                        'agent_id' => $agent->id,
                        'name' => trim(($profile->firstName ?? '').' '.($profile->lastName ?? '')),
                        'deliveries' => (clone $delivered)->count(),
                        'earnings' => round((clone $delivered)->sum('delivery_fee'), 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'today_earnings' => round($todayEarnings, 2),
                    'week_earnings' => round($weekEarnings, 2),
                    'month_earnings' => round($monthEarnings, 2),
                    'total_earnings' => round($totalEarnings, 2),
                    'agents_breakdown' => $agentBreakdown,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] earnings error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al obtener ganancias'], 500);
        }
    }

    /**
     * GET /api/delivery-company/orders/pending-payment — Órdenes con comprobante de envío subido, pendiente de validar.
     */
    public function pendingPaymentOrders()
    {
        try {
            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            $orders = Order::with(['commerce', 'orderItems.product', 'orderPayments'])
                ->where('delivery_company_id', $company->id)
                ->where('status', 'pending_payment')
                ->whereHas('orderPayments', function ($q) {
                    $q->where('type', 'delivery')
                        ->whereNotNull('payment_proof')
                        ->whereNull('validated_at')
                        ->whereNull('rejected_at');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json(['success' => true, 'data' => $orders]);
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] pendingPaymentOrders error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al listar órdenes'], 500);
        }
    }

    /**
     * POST /api/delivery-company/orders/{id}/validate-delivery-payment — Validar o rechazar pago de envío.
     */
    public function validateDeliveryPayment(Request $request, $orderId)
    {
        try {
            $request->validate([
                'is_valid' => 'required|boolean',
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            $company = $this->getAuthCompany();
            if (! $company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
            }

            return DB::transaction(function () use ($request, $orderId, $company) {
                $order = Order::whereKey($orderId)->lockForUpdate()->first();
                if (! $order || $order->delivery_company_id !== $company->id) {
                    return response()->json(['success' => false, 'message' => 'Orden no encontrada o no pertenece a tu empresa'], 404);
                }
                if ($order->status !== 'pending_payment') {
                    return response()->json(['success' => false, 'message' => 'Solo se puede validar pago de órdenes pendientes'], 400);
                }

                $deliveryPayment = $order->deliveryPayment;
                if (! $deliveryPayment || ! $deliveryPayment->payment_proof) {
                    return response()->json(['success' => false, 'message' => 'No hay comprobante de envío para validar'], 400);
                }

                $profile = Auth::user()->profile;

                if ($request->is_valid) {
                    $deliveryPayment->update([
                        'validated_at' => now(),
                        'validated_by' => $profile->id,
                        'rejected_at' => null,
                        'rejection_reason' => null,
                    ]);

                    $order->refresh();
                    $order->load(['foodPayment', 'deliveryPayment']);
                    if ($order->allPaymentsValidated()) {
                        $decision = app(OrderStateMachineService::class)->applyTransition(
                            $order,
                            'delivery_company',
                            'paid',
                            $profile->id,
                            'delivery_company_payment_validation',
                            'Todos los pagos validados'
                        );
                        if (! ($decision['allowed'] ?? false)) {
                            return response()->json([
                                'success' => false,
                                'message' => $decision['message'] ?? 'No se pudo actualizar el estado de la orden',
                                'error_code' => $decision['error_code'] ?? 'ORDER_INVALID_TRANSITION',
                            ], (int) ($decision['http_status'] ?? 409));
                        }
                        $order->update(['payment_validated_at' => now()]);
                        $message = 'Todos los pagos validados. Orden lista para preparar.';
                    } else {
                        $message = 'Pago de envío validado. Pendiente: pago de comida.';
                    }

                    event(new PaymentValidated($order->fresh(), true, $profile->id));
                } else {
                    $deliveryPayment->update([
                        'rejected_at' => now(),
                        'rejection_reason' => $request->rejection_reason ?? 'Pago de envío rechazado',
                    ]);
                    $message = 'Pago de envío rechazado. El comprador puede re-subir el comprobante.';
                    event(new OrderStatusChanged($order->fresh()));
                }

                return response()->json(['success' => true, 'message' => $message]);
            });
        } catch (\Exception $e) {
            Log::error('[DeliveryCompanyAPI] validateDeliveryPayment error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al validar pago'], 500);
        }
    }
}
