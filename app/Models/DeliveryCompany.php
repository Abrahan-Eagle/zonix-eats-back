<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'nombre',
        'ruc',
        'telefono',
        'direccion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->profile->user();
    }

    public function agents()
    {
        return $this->hasMany(DeliveryAgent::class, 'company_id');
    }
}
