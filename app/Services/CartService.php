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

        // Validar cantidad
        if ($quantity < 1 || $quantity > 100) {
            throw new \Exception('La cantidad debe estar entre 1 y 100');
        }

        // Verificar que el producto existe
        $product = Product::with('commerce')->findOrFail($productId);

        // Validar que producto está disponible
        if (!$product->available) {
            throw new \Exception('El producto no está disponible');
        }

        // Validar que commerce está activo
        if (!$product->commerce || !$product->commerce->open) {
            throw new \Exception('El comercio no está disponible');
        }

        // Validar que todos los productos del carrito sean del mismo commerce
        $existingItems = CartItem::where('cart_id', $cart->id)
            ->with('product')
            ->get();

        if ($existingItems->isNotEmpty()) {
            $existingCommerceId = $existingItems->first()->product->commerce_id;
            if ($existingCommerceId !== $product->commerce_id) {
                // Limpiar carrito y agregar nuevo producto
                CartItem::where('cart_id', $cart->id)->delete();
            }
        }

        // Buscar si el producto ya existe en el carrito
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            // Actualizar cantidad si ya existe
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > 100) {
                throw new \Exception('La cantidad máxima permitida es 100');
            }
            $cartItem->quantity = $newQuantity;
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
        // Validar cantidad
        if ($quantity < 1 || $quantity > 100) {
            throw new \Exception('La cantidad debe estar entre 1 y 100');
        }

        $cart = $this->getOrCreateCart();
        
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->with('product')
            ->firstOrFail();

        // Validar que producto sigue disponible (available Y stock_quantity)
        if (!$cartItem->product->available) {
            throw new \Exception('El producto ya no está disponible');
        }

        // Si tiene stock_quantity, validar que hay suficiente cantidad
        if ($cartItem->product->stock_quantity !== null) {
            if ($cartItem->product->stock_quantity < $quantity) {
                throw new \Exception("Stock insuficiente. Solo hay {$cartItem->product->stock_quantity} unidades disponibles");
            }
        }

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
        $items = $cart->items()->with('product.commerce')->get();
        
        // Validar que productos sigan disponibles y remover los que no
        $validItems = $items->filter(function ($item) {
            return $item->product && $item->product->available && $item->product->commerce && $item->product->commerce->open;
        });

        // Eliminar items con productos no disponibles
        $invalidItems = $items->diff($validItems);
        foreach ($invalidItems as $invalidItem) {
            $invalidItem->delete();
        }

        // Formatear items en el formato esperado (array indexado numéricamente)
        $formattedItems = $validItems->map(function ($item) {
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
