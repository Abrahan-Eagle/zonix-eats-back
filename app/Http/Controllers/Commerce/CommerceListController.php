<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommerceListController extends Controller
{
    /**
     * Crear un nuevo comercio (para usuarios con varios restaurantes).
     * POST /api/commerce/commerces
     */
    public function store(Request $request)
    {
        $profile = Auth::user()->profile;
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'tax_id' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'open' => 'nullable|boolean',
            'schedule' => 'nullable|string|max:500',
        ]);

        $scheduleValue = null;
        if (!empty($data['schedule'])) {
            $decoded = json_decode($data['schedule'], true);
            $scheduleValue = is_array($decoded) ? $decoded : ['raw' => (string) $data['schedule']];
        }

        $isFirst = $profile->commerces()->count() === 0;

        $commerce = Commerce::create([
            'profile_id' => $profile->id,
            'is_primary' => $isFirst,
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'],
            'tax_id' => $data['tax_id'],
            'address' => $data['address'],
            'open' => (bool) ($data['open'] ?? false),
            'schedule' => $scheduleValue,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Restaurante creado correctamente',
            'data' => $commerce,
        ], 201);
    }

    /**
     * Listar todos los comercios del perfil (multi-restaurante).
     * GET /api/commerce/commerces
     */
    public function index()
    {
        $profile = Auth::user()->profile;
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $commerces = $profile->commerces()
            ->orderByRaw('is_primary DESC, id ASC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $commerces,
        ]);
    }

    /**
     * Establecer un comercio como principal.
     * PUT /api/commerce/commerces/{id}/set-primary
     */
    public function setPrimary(Commerce $commerce)
    {
        $profile = Auth::user()->profile;
        if (!$profile || !$profile->commerces()->where('id', $commerce->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Comercio no encontrado o no pertenece al perfil',
            ], 404);
        }

        DB::transaction(function () use ($profile, $commerce) {
            $profile->commerces()->update(['is_primary' => false]);
            $commerce->update(['is_primary' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Comercio principal actualizado',
            'data' => $commerce->fresh(),
        ]);
    }
}
