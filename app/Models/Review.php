<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Review: almacena reseñas y calificaciones para comercios, productos, etc.
 * Usa morphTo para soportar múltiples entidades calificables.
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'reviewable_id',
        'reviewable_type',
        'rating',
        'comentario'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

     public function reviewable()
    {
        return $this->morphTo();
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->profile->user();
    }
}
