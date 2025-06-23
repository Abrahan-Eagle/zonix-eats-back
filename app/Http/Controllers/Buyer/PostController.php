<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar posts desde el lado del comprador.
 *
 * Métodos principales:
 * - index(): Listar todos los posts.
 * - show(): Mostrar detalles de un post específico.
 */
class PostController extends Controller
{
    /**
     * Servicio de posts.
     * @var PostService
     */
    protected $postService;

    /**
     * Inyecta el servicio de posts.
     * @param PostService $postService
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Listar todos los posts.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = $this->postService->getAllPosts();
        return response()->json($posts);
    }

    /**
     * Mostrar detalles de un post específico.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $post = $this->postService->getPostById($id);
        if (!$post) {
            return response()->json(['message' => 'Post no encontrado'], 404);
        }
        return response()->json($post);
    }
}
