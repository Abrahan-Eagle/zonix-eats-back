<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Obtener o crear el carrito del usuario autenticado
     *
     * @return Cart
     */
    private function getOrCreateCart()
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }
        return Cart::getOrCreateForUser($user->id);
    }

    /**
     * Agregar un producto al carrito.
     *
     * @param array $productData
     * @return array
     */
    public function addToCart(array $productData)
    {
        $cart = $this->getOrCreateCart();
        $productId = $productData['product_id'];
        $quantity = $productData['quantity'] ?? 1;

        // Verificar que el producto existe
        $product = Product::findOrFail($productId);

        // Buscar si el producto ya existe en el carrito
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            // Actualizar cantidad si ya existe
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Crear nuevo item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $this->formatCartResponse($cart);
    }

    /**
     * Obtener el contenido del carrito.
     *
     * @return array
     */
    public function getCart()
    {
        $cart = $this->getOrCreateCart();
        return $this->formatCartResponse($cart);
    }

    /**
     * Actualizar cantidad de un producto.
     *
     * @param int $productId
     * @param int $quantity
     * @return array
     */
    public function updateQuantity($productId, $quantity)
    {
        $cart = $this->getOrCreateCart();
        
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->firstOrFail();

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return $this->formatCartResponse($cart);
    }

    /**
     * Remover un producto del carrito.
     *
     * @param int $productId
     * @return array
     */
    public function removeFromCart($productId)
    {
        $cart = $this->getOrCreateCart();
        
        CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->delete();

        return $this->formatCartResponse($cart);
    }

    /**
     * Agregar notas al carrito.
     *
     * @param string $notes
     * @return array
     */
    public function addNotes($notes)
    {
        $cart = $this->getOrCreateCart();
        $cart->notes = $notes;
        $cart->save();

        return $this->formatCartResponse($cart);
    }

    /**
     * Limpiar el carrito.
     *
     * @return array
     */
    public function clearCart()
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        $cart->notes = null;
        $cart->save();

        return $this->formatCartResponse($cart);
    }

    /**
     * Formatear la respuesta del carrito en el formato esperado por el frontend
     * Compatible con el formato anterior basado en Session
     *
     * @param Cart $cart
     * @return array
     */
    private function formatCartResponse(Cart $cart)
    {
        $items = $cart->items()->with('product')->get();
        
        // Formatear items en el formato esperado (array indexado numéricamente)
        $formattedItems = $items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ];
        })->values()->toArray(); // values() reindexa el array numéricamente

        // Si hay notas, agregarlas como clave separada (compatible con formato anterior)
        if ($cart->notes) {
            $formattedItems['notes'] = $cart->notes;
        }

        return $formattedItems;
    }
}
