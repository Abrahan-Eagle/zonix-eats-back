<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_position',
        'qr_code',
        'reserved_date',
        'appointment_date',
        'expiry_date',
        'date',
        'status',
        'asistio',
        'profile_id',
        'gas_cylinders_id',
        'station_id',
    ];

    // Relación con el perfil
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    // Relación con cilindros de gas
    public function gasCylinder()
    {
        return $this->belongsTo(GasCylinder::class, 'gas_cylinders_id');
    }

    // Relación con el usuario (a través del perfil)
    public function user()
    {
        return $this->hasOneThrough(User::class, Profile::class);
    }

    // Relación con direcciones (a través del perfil)
    public function addresses()
    {
        return $this->hasManyThrough(Address::class, Profile::class);
    }

    // Relación con teléfonos (a través del perfil)
    public function phones()
    {
        return $this->hasManyThrough(Phone::class, Profile::class);
    }

    // Relación con correos electrónicos (a través del perfil)
    public function emails()
    {
        return $this->hasManyThrough(Email::class, Profile::class);
    }

    // Relación con documentos (a través del perfil)
    public function documents()
    {
        return $this->hasManyThrough(Document::class, Profile::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

}

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class GasTicket extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'queue_position',
//         'qr_code',
//         'reserved_date',
//         'appointment_date',
//         'expiry_date',
//         'date',
//         'status',
//         'asistio',
//         'profile_id',
//         'gas_cylinders_id',
//     ];

//     // Relación con el perfil
//     public function profile()
//     {
//         return $this->belongsTo(Profile::class);
//     }

//     public function gasCylinder()
//     {
//         return $this->belongsTo(GasCylinder::class);
//     }

// }
