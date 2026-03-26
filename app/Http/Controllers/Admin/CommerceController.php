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
            'open' => 'required|boolean',
        ]);

        $commerce = Commerce::findOrFail($id);
        $commerce->open = $request->boolean('open');
        $commerce->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado del comercio actualizado.',
            'data' => $commerce,
        ]);
    }
}
