<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
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

        $commerceIds = $commerces->pluck('id')->all();
        $productCounts = Product::whereIn('commerce_id', $commerceIds)
            ->selectRaw('commerce_id, count(*) as c')
            ->groupBy('commerce_id')
            ->pluck('c', 'commerce_id');
        $ventasCounts = Order::whereIn('commerce_id', $commerceIds)
            ->where('status', 'delivered')
            ->selectRaw('commerce_id, count(*) as c')
            ->groupBy('commerce_id')
            ->pluck('c', 'commerce_id');
        $ratings = Review::where('reviewable_type', Commerce::class)
            ->whereIn('reviewable_id', $commerceIds)
            ->selectRaw('reviewable_id, ROUND(AVG(rating), 1) as avg')
            ->groupBy('reviewable_id')
            ->pluck('avg', 'reviewable_id');

        $data = $commerces->map(function ($commerce) use ($productCounts, $ventasCounts, $ratings) {
            $arr = $commerce->toArray();
            $arr['stats'] = [
                'rating' => (float) ($ratings[$commerce->id] ?? 0),
                'ventas' => (int) ($ventasCounts[$commerce->id] ?? 0),
                'productos' => (int) ($productCounts[$commerce->id] ?? 0),
            ];
            return $arr;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
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
