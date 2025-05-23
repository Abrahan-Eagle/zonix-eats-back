<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    // Si el nombre de la tabla no sigue la convención plural, puedes especificarlo aquí:
    protected $table = 'stations';

    // Definir los campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'location',
        'latitude',
        'longitude',
        'contact_number',
        'responsible_person',
        'days_available',
        'opening_time',
        'closing_time',
        'active',
        'code',
    ];



    // public function profile()
    // {
    //     return $this->belongsTo(Profile::class, 'profile_id');
    // }

    // public function gasTicket()
    // {
    //     return $this->belongsTo(GasTicket::class);
    // }

    // Si los campos created_at y updated_at no son necesarios o son personalizados, puedes deshabilitarlos
    // public $timestamps = false;

    // También puedes agregar relaciones si las necesitas, por ejemplo, si una estación tiene muchos registros
    // public function records()
    // {
    //     return $this->hasMany(Record::class);
    // }

}
