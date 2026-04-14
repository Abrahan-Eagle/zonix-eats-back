<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\RestaurantService;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar restaurantes (comercios).
 *
 * Métodos principales:
 * - index(): Listar todos los restaurantes.
 * - show(): Mostrar detalles de un restaurante específico.
 */
class RestaurantController extends Controller
{
    /**
     * Servicio de restaurantes.
     *
     * @var RestaurantService
     */
    protected $restaurantService;

    /**
     * Inyecta el servicio de restaurantes.
     */
    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    /**
     * Listar todos los restaurantes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $restaurantsPaginator = $this->restaurantService->getAllRestaurants($perPage);
        $restaurants = $restaurantsPaginator->items();

        return response()->json([
            'success' => true,
            'data' => [
                // Canonico v2
                'items' => $restaurants,
                // Canonico actual
                'restaurants' => $restaurants,
                // Legacy compatibility
                'data' => $restaurants,
                'pagination' => [
                    'current_page' => $restaurantsPaginator->currentPage(),
                    'last_page' => $restaurantsPaginator->lastPage(),
                    'per_page' => $restaurantsPaginator->perPage(),
                    'total' => $restaurantsPaginator->total(),
                ],
            ],
            'message' => 'Restaurantes obtenidos exitosamente',
        ]);
    }

    /**
     * Mostrar detalles de un restaurante específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $restaurant = $this->restaurantService->getCatalogVisibleRestaurantById($id);
        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Restaurante no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $restaurant,
            'message' => 'Restaurante encontrado',
        ]);
    }
}
