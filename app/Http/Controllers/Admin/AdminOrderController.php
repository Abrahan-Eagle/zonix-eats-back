<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use App\Models\Order;
use App\Services\OrderStateMachineService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');
        $commerceId = $request->input('commerce_id');

        $query = Order::with(['profile', 'commerce', 'items', 'orderDelivery.agent']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($commerceId) {
            $query->where('commerce_id', $commerceId);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $orders->items(),
                // Legacy compatibility for existing clients
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ],
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $stateMachine = app(OrderStateMachineService::class);
        $targetStatus = $stateMachine->normalizeStatus((string) $request->input('status'));
        $adminId = auth()->id();

        $decision = $stateMachine->applyTransition(
            $order,
            'admin',
            $targetStatus,
            $adminId,
            'admin_api',
            (string) $request->input('reason', '')
        );

        if (! $decision['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $decision['message'],
                'error_code' => $decision['error_code'],
                'data' => [
                    'from_status' => $decision['from'],
                    'to_status' => $decision['to'],
                ],
            ], $decision['http_status']);
        }

        event(new \App\Events\OrderStatusChanged($order->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'order' => $order->fresh(),
        ]);
    }

    public function commerces(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $commerces = Commerce::with('profile')->paginate($perPage);

        return response()->json($commerces);
    }
}
