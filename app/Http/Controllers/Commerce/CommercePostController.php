<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Posts/publicaciones del commerce (de todos sus comercios).
 */
class CommercePostController extends Controller
{
    /**
     * Listar posts de los comercios del perfil.
     * GET /api/commerce/posts
     */
    public function index(): JsonResponse
    {
        $profile = Auth::user()->profile;
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado',
            ], 404);
        }

        $commerceIds = $profile->commerces()->pluck('id');
        $posts = Post::whereIn('commerce_id', $commerceIds)
            ->with('commerce:id,business_name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }
}
