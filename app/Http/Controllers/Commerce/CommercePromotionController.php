<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommercePromotionController extends Controller
{
    /**
     * Listar promociones del comercio (o globales si commerce_id es null).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $commerce = $user->profile?->commerce;
        $commerceId = $commerce?->id;

        $query = Promotion::query();
        if ($commerceId) {
            $query->where(function ($q) use ($commerceId) {
                $q->where('commerce_id', $commerceId)->orWhereNull('commerce_id');
            });
        } else {
            $query->whereNull('commerce_id');
        }

        $query->orderByDesc('created_at');
        $promotions = $query->get();

        return response()->json($promotions);
    }

    /**
     * Mostrar una promoción.
     */
    public function show($id)
    {
        $promotion = Promotion::findOrFail($id);
        $user = Auth::user();
        $commerceId = $user->profile?->commerce?->id;

        if ($promotion->commerce_id !== null && $promotion->commerce_id !== $commerceId) {
            abort(403, 'No tienes acceso a esta promoción');
        }

        return response()->json($promotion);
    }

    /**
     * Crear promoción.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'terms_conditions' => 'nullable|string|max:1000',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $commerce = $user->profile?->commerce;

        $data = $request->only([
            'title', 'description', 'discount_type', 'discount_value',
            'minimum_order', 'maximum_discount', 'start_date', 'end_date',
            'terms_conditions', 'priority',
        ]);
        $data['minimum_order'] = $data['minimum_order'] ?? 0;
        $data['priority'] = $data['priority'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['commerce_id'] = $commerce?->id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('promotions', 'public');
            $baseUrl = config('app.env') === 'production'
                ? config('app.url_production')
                : config('app.url_local');
            $data['image_url'] = $baseUrl . '/storage/' . $path;
        }

        $promotion = Promotion::create($data);

        return response()->json([
            'success' => true,
            'data' => $promotion,
        ], 201);
    }

    /**
     * Actualizar promoción.
     */
    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $user = Auth::user();
        $commerceId = $user->profile?->commerce?->id;

        if ($promotion->commerce_id !== null && $promotion->commerce_id !== $commerceId) {
            abort(403, 'No tienes acceso a esta promoción');
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'terms_conditions' => 'nullable|string|max:1000',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'title', 'description', 'discount_type', 'discount_value',
            'minimum_order', 'maximum_discount', 'start_date', 'end_date',
            'terms_conditions', 'priority',
        ]);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }
        $data = array_filter($data, fn($v) => $v !== null);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('promotions', 'public');
            $baseUrl = config('app.env') === 'production'
                ? config('app.url_production')
                : config('app.url_local');
            $data['image_url'] = $baseUrl . '/storage/' . $path;
        }

        $promotion->update($data);

        return response()->json([
            'success' => true,
            'data' => $promotion->fresh(),
        ]);
    }

    /**
     * Eliminar promoción.
     */
    public function destroy($id)
    {
        $promotion = Promotion::findOrFail($id);
        $user = Auth::user();
        $commerceId = $user->profile?->commerce?->id;

        if ($promotion->commerce_id !== null && $promotion->commerce_id !== $commerceId) {
            abort(403, 'No tienes acceso a esta promoción');
        }

        $promotion->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Activar/desactivar promoción.
     */
    public function toggle($id)
    {
        $promotion = Promotion::findOrFail($id);
        $user = Auth::user();
        $commerceId = $user->profile?->commerce?->id;

        if ($promotion->commerce_id !== null && $promotion->commerce_id !== $commerceId) {
            abort(403, 'No tienes acceso a esta promoción');
        }

        $promotion->update(['is_active' => !$promotion->is_active]);
        return response()->json([
            'success' => true,
            'data' => $promotion->fresh(),
        ]);
    }
}
