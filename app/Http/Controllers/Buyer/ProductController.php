<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar productos desde el lado del comprador.
 *
 * Métodos principales:
 * - show(): Mostrar detalles de un producto específico.
 */
class ProductController extends Controller
{
    /**
     * Servicio de productos.
     *
     * @var ProductService
     */
    protected $productService;

    /**
     * Inyecta el servicio de productos.
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Mostrar detalles de un producto específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $product = $this->productService->getCatalogVisibleProductById($id);
            if (! $product) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Producto encontrado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener el producto',
            ], 500);
        }
    }

    /**
     * Listar productos disponibles para el comprador.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'nullable|integer|exists:categories,id',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;
            $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

            $products = $this->productService->searchAvailableProducts(
                null,
                $categoryId,
                $perPage
            );

            $productsData = $products->items();

            return response()->json([
                'success' => true,
                'data' => [
                    // Canonico v2
                    'items' => $productsData,
                    // Canonico actual
                    'products' => $productsData,
                    // Legacy compatibility
                    'data' => $productsData,
                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                ],
                'message' => 'Productos obtenidos exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener productos',
            ], 500);
        }
    }
}
