<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar el carrito de compras del comprador.
 *
 * Métodos principales:
 * - add(): Agregar un producto al carrito.
 * - show(): Mostrar el contenido del carrito.
 */
class CartController extends Controller
{
    /**
     * Servicio de carrito.
     *
     * @var CartService
     */
    protected $cartService;

    /**
     * Inyecta el servicio de carrito.
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Agregar un producto al carrito.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'line_id' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:500',
        ]);
        try {
            $cart = $this->cartService->addToCart($validated);

            return response()->json([
                'success' => true,
                'data' => $cart,
                'message' => 'Producto agregado al carrito',
            ]);
        } catch (\Throwable $e) {
            return $this->businessErrorResponse($e);
        }
    }

    /**
     * Mostrar el contenido del carrito.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        try {
            $cart = $this->cartService->getCart();

            return response()->json([
                'success' => true,
                'data' => $cart,
                'message' => 'Carrito recuperado exitosamente',
            ]);
        } catch (\Throwable $e) {
            return $this->businessErrorResponse($e);
        }
    }

    /**
     * Actualizar cantidad de un producto en el carrito.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'line_id' => 'nullable|string|max:120',
        ]);

        try {
            $cart = $this->cartService->updateQuantity(
                $validated['product_id'],
                $validated['quantity'],
                $validated['line_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $cart,
                'message' => 'Cantidad actualizada',
            ]);
        } catch (\Throwable $e) {
            return $this->businessErrorResponse($e);
        }
    }

    /**
     * Remover un producto del carrito.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, $productId)
    {
        try {
            $lineId = $request->query('line_id');
            $cart = $this->cartService->removeFromCart($productId, $lineId);

            return response()->json([
                'success' => true,
                'data' => $cart,
                'message' => 'Producto removido del carrito',
            ]);
        } catch (\Throwable $e) {
            return $this->businessErrorResponse($e);
        }
    }

    /**
     * Agregar notas al carrito.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNotes(Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $cart = $this->cartService->addNotes($validated['notes']);

            return response()->json([
                'success' => true,
                'data' => $cart,
                'message' => 'Notas agregadas',
            ]);
        } catch (\Throwable $e) {
            return $this->businessErrorResponse($e);
        }
    }

    /**
     * Limpiar el carrito completo.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        try {
            $cart = $this->cartService->clearCart();

            return response()->json([
                'success' => true,
                'data' => $cart,
                'message' => 'Carrito limpiado',
            ]);
        } catch (\Throwable $e) {
            return $this->businessErrorResponse($e);
        }
    }

    private function businessErrorResponse(\Throwable $e)
    {
        $message = $e->getMessage() ?: 'Error de carrito';
        $errorCode = 'CART_ERROR';
        $status = 422;

        switch ($e->getCode()) {
            case CartService::ERR_OUT_OF_STOCK:
                $errorCode = 'OUT_OF_STOCK';
                break;
            case CartService::ERR_COMMERCE_CLOSED:
                $errorCode = 'COMMERCE_CLOSED';
                break;
            case CartService::ERR_PRODUCT_UNAVAILABLE:
                $errorCode = 'PRODUCT_UNAVAILABLE';
                break;
            case CartService::ERR_INVALID_QUANTITY:
                $errorCode = 'INVALID_QUANTITY';
                break;
            case CartService::ERR_UNAUTHENTICATED:
                $errorCode = 'UNAUTHENTICATED';
                $status = 401;
                break;
            case CartService::ERR_PROFILE_REQUIRED:
                $errorCode = 'PROFILE_REQUIRED';
                break;
            case CartService::ERR_LINE_NOT_FOUND:
                $errorCode = 'CART_LINE_NOT_FOUND';
                break;
            default:
                $errorCode = 'CART_ERROR';
        }

        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
            'error_code' => $errorCode,
        ], $status);
    }
}
