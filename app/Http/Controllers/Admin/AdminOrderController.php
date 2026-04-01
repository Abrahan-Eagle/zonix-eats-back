<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Commerce;
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
            'status' => 'required|in:pending_payment,paid,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();
        return response()->json(['message' => 'Estado actualizado', 'order' => $order]);
    }

    public function commerces(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $commerces = Commerce::with('profile')->paginate($perPage);
        return response()->json($commerces);
    }
}
