<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Agregar un producto al carrito.
     *
     * @param array $productData
     * @return array
     */
    public function addToCart(array $productData)
    {
        $cart = Session::get('cart', []);
        $cart[] = $productData;
        Session::put('cart', $cart);
        return $cart;
    }

    /**
     * Obtener el contenido del carrito.
     *
     * @return array
     */
    public function getCart()
    {
        return Session::get('cart', []);
    }
}
