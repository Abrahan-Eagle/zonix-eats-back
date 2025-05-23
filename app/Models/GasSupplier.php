<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasSupplier extends Model
{
    use HasFactory;


    protected $table = 'gas_suppliers';


    protected $fillable = [
        'name',
        'contact_info',
        'address',
        'status',
    ];

    // Relación con otras tablas (si existe)
    // Ejemplo: Un proveedor de gas puede tener muchos tickets de gas
    public function gasTickets()
    {
        return $this->hasMany(GasTicket::class);
    }

    public function gasSuppliers()
    {
        return $this->hasOne(Profile::class);
    }


}

