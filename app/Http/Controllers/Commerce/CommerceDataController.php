<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommerceDataController extends Controller
{
    /**
     * Get current user's commerce data.
     * GET /api/commerce
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile || !$profile->commerce) {
            return response()->json([
                'success' => false,
                'message' => 'Comercio no encontrado para el usuario autenticado',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $profile->commerce,
        ]);
    }

    /**
     * Update commerce data.
     * PUT /api/commerce
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile || !$profile->commerce) {
            return response()->json([
                'success' => false,
                'message' => 'Comercio no encontrado para el usuario autenticado',
            ], 404);
        }

        $commerce = $profile->commerce;
        $data = $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'business_type' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:500',
            'open' => 'sometimes|boolean',
            'schedule' => 'nullable|string|max:1000',
        ]);

        if (isset($data['schedule']) && is_string($data['schedule'])) {
            $decoded = json_decode($data['schedule'], true);
            $data['schedule'] = $decoded ?? ['raw' => $data['schedule']];
        }

        $commerce->update(array_filter($data, fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'message' => 'Datos actualizados',
            'data' => $commerce->fresh(),
        ]);
    }

    /**
     * Upload commerce logo/image.
     * POST /api/commerce/logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile || !$profile->commerce) {
            return response()->json([
                'success' => false,
                'message' => 'Comercio no encontrado para el usuario autenticado',
            ], 404);
        }

        $commerce = $profile->commerce;

        // Delete previous image if exists
        if ($commerce->image) {
            $baseUrl = config('app.env') === 'production'
                ? config('app.url_production')
                : config('app.url_local');
            $oldPath = str_replace($baseUrl . '/storage/', '', $commerce->image);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Store new image
        $path = $request->file('image')->store('commerce_images', 'public');

        $baseUrl = config('app.env') === 'production'
            ? config('app.url_production')
            : config('app.url_local');

        $imageUrl = $baseUrl . '/storage/' . $path;

        $commerce->update(['image' => $imageUrl]);

        return response()->json([
            'success' => true,
            'message' => 'Logo subido correctamente',
            'data' => [
                'image' => $imageUrl,
                'url' => $imageUrl,
            ],
        ], 200);
    }
}
