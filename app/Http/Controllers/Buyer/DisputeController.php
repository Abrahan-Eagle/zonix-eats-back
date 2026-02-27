<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $disputes = Dispute::where('reported_by_type', 'App\\Models\\Profile')
            ->where('reported_by_id', $profile->id)
            ->with(['order'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

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
        $user = Auth::user();
        $profile = $user->profile;

        $dispute = Dispute::where('reported_by_type', 'App\\Models\\Profile')
            ->where('reported_by_id', $profile->id)
            ->with(['order'])
            ->find($id);

        if (!$dispute) {
            return response()->json(['success' => false, 'message' => 'Disputa no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $dispute]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:quality,delivery,payment,other',
            'description' => 'required|string|min:10|max:1000',
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrado'], 404);
        }

        $order = Order::where('id', $request->order_id)
            ->where('profile_id', $profile->id)
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'paid'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada o no elegible para disputa',
            ], 422);
        }

        $existing = Dispute::where('order_id', $order->id)
            ->where('reported_by_type', 'App\\Models\\Profile')
            ->where('reported_by_id', $profile->id)
            ->where('status', '!=', 'closed')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una disputa abierta para esta orden',
            ], 422);
        }

        $dispute = Dispute::create([
            'order_id' => $order->id,
            'reported_by_type' => 'App\\Models\\Profile',
            'reported_by_id' => $profile->id,
            'reported_against_type' => 'App\\Models\\Commerce',
            'reported_against_id' => $order->commerce_id,
            'type' => $request->type,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'data' => $dispute->load('order'),
            'message' => 'Disputa creada exitosamente',
        ], 201);
    }
}
