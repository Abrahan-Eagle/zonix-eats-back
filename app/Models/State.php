<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    protected $fillable = ['name', 'countries_id'];

    // Un estado tiene muchas ciudades
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    // Un estado pertenece a un país (FK: countries_id)
    public function country()
    {
        return $this->belongsTo(Country::class, 'countries_id');
    }
}
