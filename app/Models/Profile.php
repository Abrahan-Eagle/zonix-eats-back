<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

    // Definir los campos que se pueden llenar de forma masiva
    protected $fillable = [
        'user_id', // Asegúrate de que esta columna exista en tu tabla
        'firstName',
        'middleName',
        'lastName',
        'secondLastName',
        'photo_users',
        'date_of_birth',
        'maritalStatus',
        'sex',
        // 'status',
    ];

    // Relación uno a uno con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación uno a muchos con el modelo GasTicket
    public function gasTickets()
    {
        return $this->hasMany(GasTicket::class);
    }

    // Relación uno a muchos con el modelo GasCylinder
    public function gasCylinders()
    {
        return $this->hasMany(GasCylinder::class, 'profile_id'); // La clave foránea debe coincidir con la columna en la tabla gas_cylinders
    }

    public function gasSuppliers()
    {
        return $this->belongsTo(GasSupplier::class); // La clave foránea debe coincidir con la columna en la tabla gas_cylinders
    }

    // Relación uno a muchos con el modelo Phone
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    // Relación uno a muchos con el modelo Email
    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    // Relación uno a muchos con el modelo Document
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Relación uno a muchos con el modelo Address
    public function addresses() {
        return $this->hasMany(Address::class);
    }
}
