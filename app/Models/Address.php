<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'street',
        'house_number',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'profile_id',
        'city_id',
        'is_default',
        'role',
        'commerce_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Definir la relación con el modelo Profile
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }


    // Definir la relación con el modelo City
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Cuando role = 'commerce', la dirección pertenece a un comercio
    public function commerce()
    {
        return $this->belongsTo(\App\Models\Commerce::class);
    }
}
