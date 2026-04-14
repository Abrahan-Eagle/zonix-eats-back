<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use Illuminate\Http\Request;

class CommerceController extends Controller
{
    /**
     * Listar comercios con paginación.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $perPage = $perPage > 0 ? $perPage : 15;

        $query = Commerce::with('profile.user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('business_name', 'like', "%$search%");
        }

        if ($request->has('open')) {
            $query->where('open', $request->boolean('open'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $commerce = Commerce::with(['profile.user', 'profile.phones', 'profile.addresses'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $commerce,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_review,approved,rejected,suspended',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $commerce = Commerce::findOrFail($id);
        $commerce->status = $request->status;

        if ($request->status === 'rejected' && $request->filled('rejection_reason')) {
            $commerce->rejection_reason = $request->rejection_reason;
        }

        if ($request->status === 'approved') {
            $commerce->rejection_reason = null;
        }

        $commerce->save();

        if (in_array($request->status, ['approved', 'rejected'])) {
            try {
                $profileId = $commerce->profile_id;
                $statusText = $request->status === 'approved' ? 'aprobado' : 'rechazado';
                app(\App\Services\NotificationService::class)->notify(
                    $profileId,
                    'Actualización de tu comercio',
                    "Tu comercio \"{$commerce->business_name}\" ha sido {$statusText}.",
                    'commerce_status',
                    ['commerce_id' => (string) $commerce->id, 'status' => $request->status]
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo notificar al commerce: '.$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado del comercio actualizado.',
            'data' => $commerce,
        ]);
    }

    public function toggleOpen(Request $request, $id)
    {
        $request->validate([
            'open' => 'required|boolean',
        ]);

        $commerce = Commerce::findOrFail($id);
        $commerce->open = $request->boolean('open');
        $commerce->save();

        return response()->json([
            'success' => true,
            'message' => $commerce->open ? 'Comercio abierto.' : 'Comercio cerrado.',
            'data' => $commerce,
        ]);
    }
}
