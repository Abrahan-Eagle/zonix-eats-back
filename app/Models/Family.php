<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = ['profile_id', 'related_profile_id', 'relationship', 'is_family_leader'];

    // Relación con el perfil principal
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    // Relación con el perfil relacionado (miembro de la familia)
    public function relatedProfile()
    {
        return $this->belongsTo(Profile::class, 'related_profile_id');
    }

}
