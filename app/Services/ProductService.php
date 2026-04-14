<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Servicio para la gestión de productos.
 * Permite obtener, listar y buscar productos de comercios.
 */
class ProductService
{
    /**
     * Obtener un producto por su ID.
     *
     * @param  int  $id
     * @return Product|null
     */
    public function getProductById($id)
    {
        return Product::with(['category', 'extras', 'preferences'])->find($id);
    }

    /**
     * Obtener un producto visible en el catalogo publico buyer.
     *
     * Reglas:
     * - Producto disponible
     * - Comercio abierto y aprobado
     *
     * @param  int  $id
     * @return Product|null
     */
    public function getCatalogVisibleProductById($id)
    {
        return Product::with(['category', 'extras', 'preferences'])
            ->where('id', $id)
            ->where('available', true)
            ->whereHas('commerce', function ($commerceQuery) {
                $commerceQuery->where('open', true)
                    ->where('status', 'approved');
            })
            ->first();
    }

    /**
     * Listar todos los productos de un comercio.
     *
     * @param  int  $commerceId
     * @return Collection<Product>
     */
    public function getProductsByCommerce($commerceId)
    {
        return Product::where('commerce_id', $commerceId)->with(['category', 'extras', 'preferences'])->get();
    }

    /**
     * Buscar productos disponibles (opcionalmente por nombre).
     *
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchAvailableProducts($search = null, ?int $categoryId = null, int $perPage = 20)
    {
        $query = Product::where('available', true)
            ->whereHas('commerce', function ($commerceQuery) {
                $commerceQuery->where('open', true)
                    ->where('status', 'approved');
            });

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        return $query
            ->with(['category', 'extras', 'preferences', 'commerce'])
            ->paginate($perPage);
    }
}
