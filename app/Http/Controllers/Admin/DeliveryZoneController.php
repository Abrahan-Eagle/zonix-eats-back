<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $perPage = $perPage > 0 ? $perPage : 15;

        $query = DeliveryZone::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->orderBy('name')->paginate($perPage);

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'center_latitude' => 'required|numeric|between:-90,90',
            'center_longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:0.01',
            'delivery_fee' => 'required|numeric|min:0',
            'delivery_time' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $zone = DeliveryZone::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Zona de delivery creada.',
            'data' => $zone,
        ], 201);
    }

    public function show($id)
    {
        $zone = DeliveryZone::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $zone,
        ]);
    }

    public function update(Request $request, $id)
    {
        $zone = DeliveryZone::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'center_latitude' => 'sometimes|required|numeric|between:-90,90',
            'center_longitude' => 'sometimes|required|numeric|between:-180,180',
            'radius' => 'sometimes|required|numeric|min:0.01',
            'delivery_fee' => 'sometimes|required|numeric|min:0',
            'delivery_time' => 'sometimes|required|integer|min:1',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $zone->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Zona de delivery actualizada.',
            'data' => $zone,
        ]);
    }

    public function destroy($id)
    {
        $zone = DeliveryZone::findOrFail($id);
        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Zona de delivery eliminada.',
        ]);
    }
}
