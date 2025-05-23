<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasCylinder extends Model
{
    use HasFactory;

    protected $table = 'gas_cylinders';

    protected $fillable = [
        'gas_cylinder_code',
        'cylinder_quantity',
        'cylinder_type',
        'cylinder_weight',
        'approved',
        'photo_gas_cylinder',
        'manufacturing_date',
        'profile_id',
        'company_supplier_id',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function gasTicket()
    {
        return $this->belongsTo(GasTicket::class);
    }


    public function gasSupplier()
    {
    return $this->belongsTo(GasSupplier::class, 'company_supplier_id');
    }



}
