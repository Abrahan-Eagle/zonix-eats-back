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
     * @var CartService
     */
    protected $cartService;

    /**
     * Inyecta el servicio de carrito.
     * @param CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Agregar un producto al carrito.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = $this->cartService->addToCart($validated);
        return response()->json(['message' => 'Producto agregado al carrito', 'cart' => $cart]);
    }

    /**
     * Mostrar el contenido del carrito.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $cart = $this->cartService->getCart();
        return response()->json(['cart' => $cart]);
    }
}
