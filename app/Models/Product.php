<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'commerce_id', 'nombre', 'descripcion', 'precio', 'imagen', 'disponible'
    ];

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }
}
