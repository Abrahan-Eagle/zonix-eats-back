<?php

namespace App\Services;

use App\Models\Commerce;

class RestaurantService
{
    /**
     * Obtener todos los restaurantes (comercios).
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllRestaurants($perPage = 15)
    {
        return Commerce::with(['profile', 'addresses', 'businessTypeRelation'])->paginate($perPage);
    }

    /**
     * Obtener un restaurante específico por ID.
     *
     * @param int $id
     * @return Commerce|null
     */
    public function getRestaurantById($id)
    {
        return Commerce::with(['profile', 'products.category', 'addresses', 'businessTypeRelation'])->find($id);
    }
}
