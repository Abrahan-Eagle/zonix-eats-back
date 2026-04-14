<?php

namespace App\Services;

use App\Models\Commerce;

class RestaurantService
{
    /**
     * Obtener todos los restaurantes (comercios).
     *
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllRestaurants($perPage = 15)
    {
        return Commerce::where('status', 'approved')
            ->where('open', true)
            ->with(['profile', 'addresses', 'businessTypeRelation', 'phones'])
            ->paginate($perPage);
    }

    /**
     * Obtener un restaurante específico por ID.
     *
     * @param  int  $id
     * @return Commerce|null
     */
    public function getRestaurantById($id)
    {
        return Commerce::with(['profile', 'products.category', 'addresses', 'businessTypeRelation', 'phones'])->find($id);
    }

    /**
     * Obtener restaurante visible en catalogo publico buyer.
     *
     * Reglas:
     * - Comercio aprobado
     * - Comercio abierto
     *
     * @param  int  $id
     * @return Commerce|null
     */
    public function getCatalogVisibleRestaurantById($id)
    {
        return Commerce::where('id', $id)
            ->where('status', 'approved')
            ->where('open', true)
            ->with(['profile', 'products.category', 'addresses', 'businessTypeRelation', 'phones'])
            ->first();
    }
}
