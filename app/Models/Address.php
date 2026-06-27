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
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
