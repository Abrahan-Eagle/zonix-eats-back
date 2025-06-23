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
     * @var ProductService
     */
    protected $productService;

    /**
     * Inyecta el servicio de productos.
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Mostrar detalles de un producto específico.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = $this->productService->getProductById($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($product);
    }
}
