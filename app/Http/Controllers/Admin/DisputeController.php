<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->input('per_page', 15), 100));
        $query = Dispute::with(['order', 'reportedBy', 'reportedAgainst']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $disputes = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $disputes->items(),
            'pagination' => [
                'current_page' => $disputes->currentPage(),
                'per_page' => $disputes->perPage(),
                'total' => $disputes->total(),
                'last_page' => $disputes->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $dispute = Dispute::with(['order', 'reportedBy', 'reportedAgainst'])->find($id);

        if (! $dispute) {
            return response()->json(['success' => false, 'message' => 'Disputa no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $dispute]);
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution' => 'required|in:refund,penalty,warning,closed',
            'admin_notes' => 'required|string|min:5|max:1000',
        ]);

        $resolvedStatus = $request->resolution === 'closed' ? 'closed' : 'resolved';
        $notificationPayload = null;
        $resolvedDispute = null;

        DB::transaction(function () use ($request, $id, $resolvedStatus, &$notificationPayload, &$resolvedDispute) {
            $dispute = Dispute::where('id', $id)->lockForUpdate()->first();

            if (! $dispute) {
                abort(response()->json(['success' => false, 'message' => 'Disputa no encontrada'], 404));
            }

            if ($dispute->status === 'closed') {
                abort(response()->json(['success' => false, 'message' => 'La disputa ya está cerrada'], 422));
            }

            if ($dispute->status === 'resolved') {
                abort(response()->json(['success' => false, 'message' => 'La disputa ya está resuelta'], 422));
            }

            $order = Order::where('id', $dispute->order_id)
                ->lockForUpdate()
                ->first();

            if ($request->resolution === 'refund') {
                if (! $order || ! $order->payment_proof || ! $order->payment_validated_at) {
                    abort(response()->json([
                        'success' => false,
                        'message' => 'La orden no tiene un pago validado para reembolso',
                    ], 422));
                }

                if ($order->status === 'cancelled') {
                    abort(response()->json([
                        'success' => false,
                        'message' => 'La orden ya se encuentra cancelada',
                    ], 422));
                }

                $order->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Refund(admin dispute #'.$dispute->id.'): '.$request->admin_notes,
                ]);
            }

            $dispute->update([
                'status' => $resolvedStatus,
                'resolution' => $request->resolution,
                'admin_notes' => $request->admin_notes,
                'resolved_by_user_id' => $request->user()?->id,
                'resolution_metadata' => [
                    'order_status_before_resolution' => $order?->status,
                    'resolved_at' => now()->toISOString(),
                ],
                'resolved_at' => now(),
            ]);

            $resolvedDispute = $dispute->fresh(['order', 'reportedBy', 'reportedAgainst', 'resolvedByUser']);

            if ($order && $order->profile_id) {
                $notificationPayload = [
                    'profile_id' => (int) $order->profile_id,
                    'order_id' => (int) $order->id,
                    'dispute_id' => (int) $dispute->id,
                    'new_status' => $resolvedStatus,
                    'resolution' => $request->resolution,
                    'admin_notes' => $request->admin_notes,
                ];
            }
        });

        if ($notificationPayload) {
            $this->notificationService->notify(
                $notificationPayload['profile_id'],
                'Actualizacion de disputa',
                "Tu disputa #{$notificationPayload['dispute_id']} fue actualizada: {$notificationPayload['new_status']}.",
                'dispute',
                [
                    'order_id' => $notificationPayload['order_id'],
                    'dispute_id' => $notificationPayload['dispute_id'],
                    'new_status' => $notificationPayload['new_status'],
                    'resolution' => $notificationPayload['resolution'],
                    'admin_notes' => $notificationPayload['admin_notes'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $resolvedDispute,
            'message' => 'Disputa resuelta exitosamente',
        ]);
    }

    public function stats()
    {
        $resolvedDisputes = Dispute::whereNotNull('resolved_at')
            ->whereNotNull('created_at')
            ->get(['created_at', 'resolved_at']);
        $resolutionDurations = $resolvedDisputes
            ->map(fn ($d) => max(0, $d->resolved_at->diffInMinutes($d->created_at)))
            ->values()
            ->all();

        $avgResolutionMinutes = $resolvedDisputes->isEmpty()
            ? null
            : (int) round($resolvedDisputes->avg(function ($d) {
                return $d->resolved_at->diffInMinutes($d->created_at);
            }));
        $p95ResolutionMinutes = $this->percentile($resolutionDurations, 95);
        $p99ResolutionMinutes = $this->percentile($resolutionDurations, 99);

        $pendingOlderThan24h = Dispute::whereIn('status', ['pending', 'in_review'])
            ->where('created_at', '<=', now()->subHours(24))
            ->count();

        $pendingOlderThan72h = Dispute::whereIn('status', ['pending', 'in_review'])
            ->where('created_at', '<=', now()->subHours(72))
            ->count();
        $pendingOlderThan6h = Dispute::whereIn('status', ['pending', 'in_review'])
            ->where('created_at', '<=', now()->subHours(6))
            ->count();
        $pendingOlderThan12h = Dispute::whereIn('status', ['pending', 'in_review'])
            ->where('created_at', '<=', now()->subHours(12))
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => Dispute::count(),
                'pending' => Dispute::where('status', 'pending')->count(),
                'in_review' => Dispute::where('status', 'in_review')->count(),
                'resolved' => Dispute::where('status', 'resolved')->count(),
                'closed' => Dispute::where('status', 'closed')->count(),
                'avg_resolution_minutes' => $avgResolutionMinutes,
                'p95_resolution_minutes' => $p95ResolutionMinutes,
                'p99_resolution_minutes' => $p99ResolutionMinutes,
                'pending_older_than_6h' => $pendingOlderThan6h,
                'pending_older_than_12h' => $pendingOlderThan12h,
                'pending_older_than_24h' => $pendingOlderThan24h,
                'pending_older_than_72h' => $pendingOlderThan72h,
            ],
        ]);
    }

    private function percentile(array $values, int $percent): ?int
    {
        if (empty($values)) {
            return null;
        }

        sort($values);
        $index = (int) ceil(($percent / 100) * count($values)) - 1;
        $index = max(0, min($index, count($values) - 1));

        return (int) round((float) $values[$index]);
    }
}
