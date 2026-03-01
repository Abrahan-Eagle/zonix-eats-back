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
        $paginator = Commerce::with('user')->orderBy('id', 'desc')->paginate($perPage);

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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'activo' => 'required|boolean',
        ]);

        $commerce = Commerce::findOrFail($id);
        $commerce->activo = $request->activo;
        $commerce->save();

        return response()->json(['message' => 'Estado del comercio actualizado']);
    }
}
