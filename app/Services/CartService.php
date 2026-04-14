<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public const ERR_UNAUTHENTICATED = 1001;

    public const ERR_PROFILE_REQUIRED = 1002;

    public const ERR_INVALID_QUANTITY = 1003;

    public const ERR_PRODUCT_UNAVAILABLE = 1004;

    public const ERR_COMMERCE_CLOSED = 1005;

    public const ERR_OUT_OF_STOCK = 1006;

    public const ERR_LINE_NOT_FOUND = 1007;

    /**
     * Obtener o crear el carrito del perfil del usuario autenticado.
     * El carrito está asociado al perfil (no al user); solo profiles y user_roles en users.
     *
     * @return Cart
     */
    private function getOrCreateCart()
    {
        $user = Auth::user();
        if (! $user) {
            throw new \RuntimeException('Usuario no autenticado', self::ERR_UNAUTHENTICATED);
        }
        $profile = $user->profile;
        if (! $profile) {
            throw new \RuntimeException('Debe completar su perfil para usar el carrito', self::ERR_PROFILE_REQUIRED);
        }

        return Cart::getOrCreateForProfile($profile->id);
    }

    /**
     * Agregar un producto al carrito.
     *
     * @return array
     */
    public function addToCart(array $productData)
    {
        $cart = $this->getOrCreateCart();
        $productId = $productData['product_id'];
        $quantity = $productData['quantity'] ?? 1;

        // Validar cantidad
        if ($quantity < 1 || $quantity > 100) {
            throw new \RuntimeException('La cantidad debe estar entre 1 y 100', self::ERR_INVALID_QUANTITY);
        }

        // Verificar que el producto existe
        $product = Product::with('commerce')->findOrFail($productId);

        // Validar que producto está disponible
        if (! $product->available) {
            throw new \RuntimeException('El producto no está disponible', self::ERR_PRODUCT_UNAVAILABLE);
        }

        // Validar que commerce está activo
        if (! $product->commerce || ! $product->commerce->open) {
            throw new \RuntimeException('El comercio no está disponible', self::ERR_COMMERCE_CLOSED);
        }

        // Si el producto maneja stock, validar cantidad solicitada
        if ($product->stock_quantity !== null && $quantity > $product->stock_quantity) {
            throw new \RuntimeException("Stock insuficiente. Solo hay {$product->stock_quantity} unidades disponibles", self::ERR_OUT_OF_STOCK);
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

        $notes = isset($productData['notes']) ? trim((string) $productData['notes']) : '';
        $lineId = $this->makeLineId($productId, $notes, $productData['line_id'] ?? null);

        // Buscar si la misma línea lógica ya existe en el carrito
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('line_id', $lineId)
            ->first();

        if ($cartItem) {
            // Actualizar cantidad si ya existe
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > 100) {
                throw new \RuntimeException('La cantidad máxima permitida es 100', self::ERR_INVALID_QUANTITY);
            }
            if ($product->stock_quantity !== null && $newQuantity > $product->stock_quantity) {
                throw new \RuntimeException("Stock insuficiente. Solo hay {$product->stock_quantity} unidades disponibles", self::ERR_OUT_OF_STOCK);
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->notes = $notes !== '' ? $notes : null;
            $cartItem->save();
        } else {
            // Crear nuevo item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'line_id' => $lineId,
                'quantity' => $quantity,
                'notes' => $notes !== '' ? $notes : null,
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
     * @param  int  $productId
     * @param  int  $quantity
     * @return array
     */
    public function updateQuantity($productId, $quantity, $lineId = null)
    {
        // Validar cantidad
        if ($quantity < 1 || $quantity > 100) {
            throw new \RuntimeException('La cantidad debe estar entre 1 y 100', self::ERR_INVALID_QUANTITY);
        }

        $cart = $this->getOrCreateCart();

        $query = CartItem::where('cart_id', $cart->id)->with('product');
        if (! empty($lineId)) {
            $query->where('line_id', $lineId);
        } else {
            $query->where('product_id', $productId);
        }

        $cartItem = $query->first();
        if (! $cartItem) {
            throw new \RuntimeException('Línea de carrito no encontrada', self::ERR_LINE_NOT_FOUND);
        }

        // Validar que producto sigue disponible (available Y stock_quantity)
        if (! $cartItem->product->available) {
            throw new \RuntimeException('El producto ya no está disponible', self::ERR_PRODUCT_UNAVAILABLE);
        }

        // Si tiene stock_quantity, validar que hay suficiente cantidad
        if ($cartItem->product->stock_quantity !== null) {
            if ($cartItem->product->stock_quantity < $quantity) {
                throw new \RuntimeException("Stock insuficiente. Solo hay {$cartItem->product->stock_quantity} unidades disponibles", self::ERR_OUT_OF_STOCK);
            }
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return $this->formatCartResponse($cart);
    }

    /**
     * Remover un producto del carrito.
     *
     * @param  int  $productId
     * @return array
     */
    public function removeFromCart($productId, $lineId = null)
    {
        $cart = $this->getOrCreateCart();

        $query = CartItem::where('cart_id', $cart->id);
        if (! empty($lineId)) {
            $query->where('line_id', $lineId);
        } else {
            $query->where('product_id', $productId);
        }
        $query->delete();

        return $this->formatCartResponse($cart);
    }

    /**
     * Agregar notas al carrito.
     *
     * @param  string  $notes
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
     * @return array
     */
    private function formatCartResponse(Cart $cart)
    {
        $items = $cart->items()->with(['product.commerce', 'product.category'])->get();

        // Validar que productos sigan disponibles y remover los que no
        $validItems = $items->filter(function ($item) {
            return $item->product && $item->product->available && $item->product->commerce && $item->product->commerce->open;
        });

        // Eliminar items con productos no disponibles
        $invalidItems = $items->diff($validItems);
        foreach ($invalidItems as $invalidItem) {
            $invalidItem->delete();
        }

        $formattedItems = $validItems->map(function ($item) {
            return $this->formatCartItem($item);
        })->values()->toArray();

        return [
            'items' => $formattedItems,
            'notes' => $cart->notes,
        ];
    }

    private function formatCartItem(CartItem $item): array
    {
        $product = $item->product;

        return [
            'id' => $product->id,
            'product_id' => $product->id,
            'line_id' => $item->line_id,
            'nombre' => $product->name,
            'precio' => (float) $product->price,
            'quantity' => (int) $item->quantity,
            'imagen' => $product->image,
            'image' => $product->image,
            'stock' => $product->stock_quantity,
            'stock_quantity' => $product->stock_quantity,
            'category' => $product->category?->name,
            'commerce_id' => $product->commerce_id,
            'notes' => $item->notes,
        ];
    }

    private function makeLineId(int $productId, string $notes, $explicitLineId = null): string
    {
        $candidate = trim((string) ($explicitLineId ?? ''));
        if ($candidate !== '') {
            return substr($candidate, 0, 120);
        }

        $normalizedNotes = mb_strtolower(trim(preg_replace('/\s+/', ' ', $notes)));

        return substr('p'.$productId.'-'.hash('sha256', $productId.'|'.$normalizedNotes), 0, 120);
    }
}
