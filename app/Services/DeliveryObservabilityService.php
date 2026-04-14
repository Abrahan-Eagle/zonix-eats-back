<?php

namespace App\Services;

use App\Models\DeliveryAssignmentTimeout;
use App\Models\DeliveryCompany;
use App\Models\DeliveryObservabilitySnapshot;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeliveryObservabilityService
{
    public function getSummary(?int $companyId = null, array $filters = []): array
    {
        $correlationId = (string) Str::uuid();
        $windowHours = max((int) ($filters['window_hours'] ?? config('zonix.observability.window_hours', 24)), 1);
        $assignmentThresholdMinutes = (int) config('zonix.observability.unassigned_threshold_minutes', 5);
        $trackingFreezeMinutes = (int) config('zonix.observability.tracking_frozen_minutes', 5);
        $since = now()->subHours($windowHours);

        $baseOrders = Order::query()
            ->where('delivery_type', 'delivery')
            ->where('created_at', '>=', $since);

        if ($companyId) {
            $baseOrders->where('delivery_company_id', $companyId);
        }

        $ordersTotal = (clone $baseOrders)->count();
        $deliveredOrders = (clone $baseOrders)->where('status', 'delivered')->count();
        $cancelledOrders = (clone $baseOrders)->where('status', 'cancelled')->count();

        $assignmentRows = DB::table('orders')
            ->join('order_delivery', 'order_delivery.order_id', '=', 'orders.id')
            ->when($companyId, fn ($q) => $q->where('orders.delivery_company_id', $companyId))
            ->where('orders.delivery_type', 'delivery')
            ->where('orders.created_at', '>=', $since)
            ->get(['orders.created_at as order_created_at', 'order_delivery.created_at as assignment_created_at']);

        $avgAssignmentMinutes = (float) $assignmentRows
            ->map(function ($row) {
                $orderAt = $row->order_created_at ? Carbon::parse($row->order_created_at) : null;
                $assignedAt = $row->assignment_created_at ? Carbon::parse($row->assignment_created_at) : null;
                if (! $orderAt || ! $assignedAt) {
                    return null;
                }

                return max(0, $orderAt->diffInMinutes($assignedAt));
            })
            ->filter(fn ($value) => $value !== null)
            ->avg();
        $assignmentCount = $assignmentRows->count();
        $assignmentDurations = $assignmentRows
            ->map(function ($row) {
                $orderAt = $row->order_created_at ? Carbon::parse($row->order_created_at) : null;
                $assignedAt = $row->assignment_created_at ? Carbon::parse($row->assignment_created_at) : null;
                if (! $orderAt || ! $assignedAt) {
                    return null;
                }

                return max(0, $orderAt->diffInMinutes($assignedAt));
            })
            ->filter(fn ($value) => $value !== null)
            ->values();

        $deliveryRows = DB::table('orders')
            ->join('order_delivery', 'order_delivery.order_id', '=', 'orders.id')
            ->when($companyId, fn ($q) => $q->where('orders.delivery_company_id', $companyId))
            ->where('orders.delivery_type', 'delivery')
            ->where('orders.created_at', '>=', $since)
            ->where('order_delivery.status', 'delivered')
            ->get(['orders.created_at as order_created_at', 'order_delivery.updated_at as delivered_at']);

        $avgDeliveryMinutes = (float) $deliveryRows
            ->map(function ($row) {
                $orderAt = $row->order_created_at ? Carbon::parse($row->order_created_at) : null;
                $deliveredAt = $row->delivered_at ? Carbon::parse($row->delivered_at) : null;
                if (! $orderAt || ! $deliveredAt) {
                    return null;
                }

                return max(0, $orderAt->diffInMinutes($deliveredAt));
            })
            ->filter(fn ($value) => $value !== null)
            ->avg();
        $deliveryDurations = $deliveryRows
            ->map(function ($row) {
                $orderAt = $row->order_created_at ? Carbon::parse($row->order_created_at) : null;
                $deliveredAt = $row->delivered_at ? Carbon::parse($row->delivered_at) : null;
                if (! $orderAt || ! $deliveredAt) {
                    return null;
                }

                return max(0, $orderAt->diffInMinutes($deliveredAt));
            })
            ->filter(fn ($value) => $value !== null)
            ->values();

        $timeoutCount = (int) DeliveryAssignmentTimeout::query()
            ->where('occurred_at', '>=', $since)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->count();

        $unassignedOverThreshold = (int) Order::query()
            ->where('delivery_type', 'delivery')
            ->whereIn('status', ['processing', 'shipped'])
            ->where('created_at', '<=', now()->subMinutes($assignmentThresholdMinutes))
            ->when($companyId, fn ($q) => $q->where('delivery_company_id', $companyId))
            ->whereDoesntHave('orderDelivery')
            ->count();

        $frozenTracking = (int) OrderDelivery::query()
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
            ->whereHas('order', function ($q) use ($companyId) {
                $q->where('delivery_type', 'delivery');
                if ($companyId) {
                    $q->where('delivery_company_id', $companyId);
                }
            })
            ->whereHas('agent', function ($q) use ($trackingFreezeMinutes) {
                $q->where(function ($sub) use ($trackingFreezeMinutes) {
                    $sub->whereNull('last_location_update')
                        ->orWhere('last_location_update', '<=', now()->subMinutes($trackingFreezeMinutes));
                });
            })
            ->count();

        $timeoutRatio = $ordersTotal > 0 ? round(($timeoutCount / $ordersTotal) * 100, 2) : 0.0;
        $successRatio = $ordersTotal > 0 ? round(($deliveredOrders / $ordersTotal) * 100, 2) : 0.0;
        $cancelledRatio = $ordersTotal > 0 ? round(($cancelledOrders / $ordersTotal) * 100, 2) : 0.0;
        $agentNoResponseRatio = ($assignmentCount + $timeoutCount) > 0
            ? round(($timeoutCount / ($assignmentCount + $timeoutCount)) * 100, 2)
            : 0.0;
        $schedulerHealth = $this->getSchedulerHealth();
        $incidentLatency = $this->getIncidentDetectionLatencyP95($companyId, $windowHours);

        return [
            'correlation_id' => $correlationId,
            'window_hours' => $windowHours,
            'kpi' => [
                'orders_total' => $ordersTotal,
                'avg_assignment_minutes' => round($avgAssignmentMinutes, 2),
                'avg_delivery_minutes' => round($avgDeliveryMinutes, 2),
                'assignment_percentiles' => [
                    'p50' => $this->percentile($assignmentDurations->all(), 50),
                    'p95' => $this->percentile($assignmentDurations->all(), 95),
                ],
                'delivery_percentiles' => [
                    'p50' => $this->percentile($deliveryDurations->all(), 50),
                    'p95' => $this->percentile($deliveryDurations->all(), 95),
                ],
                'timeout_count' => $timeoutCount,
                'timeout_ratio_percent' => $timeoutRatio,
                'agent_no_response_ratio_percent' => $agentNoResponseRatio,
                'success_ratio_percent' => $successRatio,
                'cancelled_ratio_percent' => $cancelledRatio,
                'unassigned_over_threshold' => $unassignedOverThreshold,
                'frozen_tracking_count' => $frozenTracking,
                'incident_detection_latency_p95_seconds' => $incidentLatency,
                'scheduler_health' => $schedulerHealth,
                'thresholds' => [
                    'unassigned_minutes' => $assignmentThresholdMinutes,
                    'tracking_frozen_minutes' => $trackingFreezeMinutes,
                ],
            ],
        ];
    }

    public function getRunbooks(): array
    {
        return [
            [
                'incident_type' => 'unassigned_order',
                'title' => 'Orden sin asignacion',
                'severity' => 'high',
                'steps' => [
                    'Verificar disponibilidad de agentes y estado working.',
                    'Reintentar asignacion manual desde panel company.',
                    'Escalar a admin si supera 10 minutos.',
                ],
            ],
            [
                'incident_type' => 'frozen_tracking',
                'title' => 'Tracking congelado',
                'severity' => 'medium',
                'steps' => [
                    'Validar conectividad del agente y permisos de ubicacion.',
                    'Solicitar refresh de app del delivery.',
                    'Si persiste >5 min, reasignar orden.',
                ],
            ],
        ];
    }

    public function persistSnapshot(?int $companyId = null): array
    {
        $summary = $this->getSummary($companyId);
        $kpi = $summary['kpi'] ?? [];

        return DeliveryObservabilitySnapshot::create([
            'company_id' => $companyId,
            'window_hours' => (int) ($summary['window_hours'] ?? 24),
            'orders_total' => (int) ($kpi['orders_total'] ?? 0),
            'avg_assignment_minutes' => (float) ($kpi['avg_assignment_minutes'] ?? 0),
            'avg_delivery_minutes' => (float) ($kpi['avg_delivery_minutes'] ?? 0),
            'assignment_p50_minutes' => (float) (($kpi['assignment_percentiles']['p50'] ?? 0)),
            'assignment_p95_minutes' => (float) (($kpi['assignment_percentiles']['p95'] ?? 0)),
            'delivery_p50_minutes' => (float) (($kpi['delivery_percentiles']['p50'] ?? 0)),
            'delivery_p95_minutes' => (float) (($kpi['delivery_percentiles']['p95'] ?? 0)),
            'timeout_count' => (int) ($kpi['timeout_count'] ?? 0),
            'timeout_ratio_percent' => (float) ($kpi['timeout_ratio_percent'] ?? 0),
            'agent_no_response_ratio_percent' => (float) ($kpi['agent_no_response_ratio_percent'] ?? 0),
            'success_ratio_percent' => (float) ($kpi['success_ratio_percent'] ?? 0),
            'cancelled_ratio_percent' => (float) ($kpi['cancelled_ratio_percent'] ?? 0),
            'unassigned_over_threshold' => (int) ($kpi['unassigned_over_threshold'] ?? 0),
            'frozen_tracking_count' => (int) ($kpi['frozen_tracking_count'] ?? 0),
        ])->toArray();
    }

    public function getHistory(?int $companyId = null, int $page = 1, int $perPage = 24, ?int $windowHours = null): array
    {
        $since = $windowHours ? now()->subHours(max($windowHours, 1)) : null;
        $paginator = DeliveryObservabilitySnapshot::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->orderByDesc('created_at')
            ->paginate(min(max($perPage, 1), 100), ['*'], 'page', max($page, 1));

        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function getIncidents(?int $companyId = null, array $filters = []): array
    {
        $correlationId = (string) Str::uuid();
        $windowHours = max((int) ($filters['window_hours'] ?? config('zonix.observability.window_hours', 24)), 1);
        $assignmentThresholdMinutes = (int) config('zonix.observability.unassigned_threshold_minutes', 5);
        $trackingFreezeMinutes = (int) config('zonix.observability.tracking_frozen_minutes', 5);
        $since = now()->subHours($windowHours);
        $type = isset($filters['type']) ? (string) $filters['type'] : null;
        $priority = isset($filters['priority']) ? (string) $filters['priority'] : null;
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 100);

        $incidents = [];

        $unassignedOrders = Order::query()
            ->where('delivery_type', 'delivery')
            ->whereIn('status', ['processing', 'shipped'])
            ->where('created_at', '>=', $since)
            ->where('created_at', '<=', now()->subMinutes($assignmentThresholdMinutes))
            ->when($companyId, fn ($q) => $q->where('delivery_company_id', $companyId))
            ->whereDoesntHave('orderDelivery')
            ->orderBy('created_at')
            ->get(['id', 'delivery_company_id', 'created_at', 'status']);

        foreach ($unassignedOrders as $order) {
            $incidents[] = [
                'type' => 'unassigned_order',
                'priority' => 'high',
                'event_code' => 'DELIVERY_UNASSIGNED_THRESHOLD',
                'order_id' => $order->id,
                'company_id' => $order->delivery_company_id,
                'agent_id' => null,
                'occurred_at' => optional($order->created_at)->toISOString(),
                'correlation_id' => $correlationId,
                'meta' => [
                    'status' => $order->status,
                    'threshold_minutes' => $assignmentThresholdMinutes,
                ],
            ];
        }

        $frozenTrackings = OrderDelivery::query()
            ->with(['order:id,delivery_company_id', 'agent:id,last_location_update'])
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
            ->whereHas('order', function ($q) use ($companyId, $since) {
                $q->where('delivery_type', 'delivery');
                $q->where('created_at', '>=', $since);
                if ($companyId) {
                    $q->where('delivery_company_id', $companyId);
                }
            })
            ->whereHas('agent', function ($q) use ($trackingFreezeMinutes) {
                $q->where(function ($sub) use ($trackingFreezeMinutes) {
                    $sub->whereNull('last_location_update')
                        ->orWhere('last_location_update', '<=', now()->subMinutes($trackingFreezeMinutes));
                });
            })
            ->get();

        foreach ($frozenTrackings as $delivery) {
            $incidents[] = [
                'type' => 'frozen_tracking',
                'priority' => 'medium',
                'event_code' => 'DELIVERY_TRACKING_FROZEN',
                'order_id' => $delivery->order_id,
                'company_id' => $delivery->order?->delivery_company_id,
                'agent_id' => $delivery->agent_id,
                'occurred_at' => optional($delivery->agent?->last_location_update)->toISOString(),
                'correlation_id' => $correlationId,
                'meta' => [
                    'delivery_status' => $delivery->status,
                    'threshold_minutes' => $trackingFreezeMinutes,
                ],
            ];
        }

        $collection = collect($incidents);
        if ($type) {
            $collection = $collection->where('type', $type);
        }
        if ($priority) {
            $collection = $collection->where('priority', $priority);
        }

        $collection = $collection
            ->sortByDesc(fn (array $incident) => $incident['occurred_at'] ?? '')
            ->values();

        $total = $collection->count();
        $items = $collection
            ->forPage($page, $perPage)
            ->values()
            ->all();

        return [
            'correlation_id' => $correlationId,
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'window_hours' => $windowHours,
        ];
    }

    public function getIncidentOrders(?int $companyId = null, array $filters = []): array
    {
        $type = isset($filters['type']) ? (string) $filters['type'] : null;
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 100);

        $incidentData = $this->getIncidents($companyId, [
            'type' => $type,
            'window_hours' => $filters['window_hours'] ?? null,
            'page' => 1,
            'per_page' => 500,
        ]);
        $orderIds = collect($incidentData['items'])
            ->pluck('order_id')
            ->filter()
            ->unique()
            ->values();

        $orders = Order::query()
            ->with(['commerce:id,business_name', 'orderDelivery:id,order_id,agent_id,status'])
            ->whereIn('id', $orderIds)
            ->when($companyId, fn ($q) => $q->where('delivery_company_id', $companyId))
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $orders->items(),
            'total' => $orders->total(),
            'page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'last_page' => $orders->lastPage(),
        ];
    }

    public function emitOperationalAlerts(?int $companyId = null): void
    {
        $incidentData = $this->getIncidents($companyId, ['page' => 1, 'per_page' => 200]);
        $incidents = collect($incidentData['items']);
        if ($incidents->isEmpty()) {
            return;
        }

        foreach ($incidents as $incident) {
            Log::warning('Delivery observability incident', $incident);
        }

        $incidentsArray = $incidents->all();
        $this->notifyAdmins($incidentsArray);
        if ($companyId) {
            $this->notifyCompany($companyId, $incidentsArray);
        }
    }

    private function notifyAdmins(array $incidents): void
    {
        $dedupeMinutes = (int) config('zonix.observability.alert_dedupe_minutes', 30);
        $cacheKey = 'obs:delivery:admin:alert:'.md5(json_encode(array_column($incidents, 'event_code')));
        if (Cache::has($cacheKey)) {
            return;
        }

        $adminProfiles = Profile::whereHas('user', fn ($q) => $q->where('role', 'admin'))->pluck('id');
        if ($adminProfiles->isEmpty()) {
            return;
        }

        $notificationService = app(NotificationService::class);
        foreach ($adminProfiles as $profileId) {
            $notificationService->notify(
                (int) $profileId,
                'Alerta operativa delivery',
                'Se detectaron incidentes de asignación/tracking. Revisa el panel de observabilidad.',
                'system',
                ['module' => 'delivery_observability', 'incidents_count' => count($incidents)]
            );
        }

        Cache::put($cacheKey, true, now()->addMinutes($dedupeMinutes));
    }

    private function notifyCompany(int $companyId, array $incidents): void
    {
        $company = DeliveryCompany::find($companyId);
        if (! $company?->profile_id) {
            return;
        }

        $dedupeMinutes = (int) config('zonix.observability.alert_dedupe_minutes', 30);
        $cacheKey = "obs:delivery:company:{$companyId}:alert:".md5(json_encode(array_column($incidents, 'event_code')));
        if (Cache::has($cacheKey)) {
            return;
        }

        app(NotificationService::class)->notify(
            (int) $company->profile_id,
            'Alerta operativa delivery',
            'Hay ordenes sin asignar o tracking congelado en tu operacion.',
            'order',
            ['module' => 'delivery_observability', 'incidents_count' => count($incidents)]
        );

        Cache::put($cacheKey, true, now()->addMinutes($dedupeMinutes));
    }

    private function percentile(array $values, int $percent): float
    {
        if (empty($values)) {
            return 0.0;
        }
        sort($values);
        $index = (int) ceil(($percent / 100) * count($values)) - 1;
        $index = max(0, min($index, count($values) - 1));

        return round((float) $values[$index], 2);
    }

    private function getSchedulerHealth(): array
    {
        $now = now();
        $alertsLastRun = Cache::get('obs:delivery:heartbeat:alerts_last_run_at');
        $snapshotsLastRun = Cache::get('obs:delivery:heartbeat:snapshots_last_run_at');

        $alertsAgo = $alertsLastRun ? Carbon::parse($alertsLastRun)->diffInSeconds($now) : null;
        $snapshotsAgo = $snapshotsLastRun ? Carbon::parse($snapshotsLastRun)->diffInSeconds($now) : null;

        return [
            'alerts_last_run_at' => $alertsLastRun,
            'snapshots_last_run_at' => $snapshotsLastRun,
            'alerts_seconds_ago' => $alertsAgo,
            'snapshots_seconds_ago' => $snapshotsAgo,
            'alerts_healthy' => $alertsAgo !== null && $alertsAgo <= 600,
            'snapshots_healthy' => $snapshotsAgo !== null && $snapshotsAgo <= 5400,
        ];
    }

    private function getIncidentDetectionLatencyP95(?int $companyId, int $windowHours): float
    {
        $incidentData = $this->getIncidents($companyId, [
            'window_hours' => $windowHours,
            'page' => 1,
            'per_page' => 1000,
        ]);
        $latencies = collect($incidentData['items'] ?? [])
            ->map(function ($incident) {
                $occurredAt = $incident['occurred_at'] ?? null;
                if (! $occurredAt) {
                    return null;
                }

                return Carbon::parse($occurredAt)->diffInSeconds(now());
            })
            ->filter(fn ($v) => $v !== null)
            ->values()
            ->all();

        return $this->percentile($latencies, 95);
    }
}
