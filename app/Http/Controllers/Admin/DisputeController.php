<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispute::with(['order', 'reportedBy', 'reportedAgainst']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $disputes = $query->orderBy('created_at', 'desc')
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
        $dispute = Dispute::with(['order', 'reportedBy', 'reportedAgainst'])->find($id);

        if (!$dispute) {
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

        $dispute = Dispute::find($id);

        if (!$dispute) {
            return response()->json(['success' => false, 'message' => 'Disputa no encontrada'], 404);
        }

        if ($dispute->status === 'closed') {
            return response()->json(['success' => false, 'message' => 'La disputa ya está cerrada'], 422);
        }

        $dispute->update([
            'status' => $request->resolution === 'closed' ? 'closed' : 'resolved',
            'admin_notes' => $request->admin_notes,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $dispute->fresh(['order', 'reportedBy', 'reportedAgainst']),
            'message' => 'Disputa resuelta exitosamente',
        ]);
    }

    public function stats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => Dispute::count(),
                'open' => Dispute::where('status', 'open')->count(),
                'in_progress' => Dispute::where('status', 'in_progress')->count(),
                'resolved' => Dispute::where('status', 'resolved')->count(),
                'closed' => Dispute::where('status', 'closed')->count(),
            ],
        ]);
    }
}
