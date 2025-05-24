<?php

namespace App\Models;

use App\Models\Models\Commerce;
use App\Models\Models\DeliveryAgent;
use App\Models\Models\DeliveryCompany;
use App\Models\Models\Order;
use App\Models\Models\PostLike;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',        // ID único proporcionado por Google
        'given_name',       // Nombre de pila
        'family_name',      // Apellido
        'profile_pic',      // URL de la imagen de perfil de Google
        'AccessToken',
        'role',  // Rol del usuario (admin, cliente, etc.
        'completed_onboarding'
    ];

    /**
     * Atributos que deberían ocultarse para arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deberían ser tratados como fechas.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relación para obtener los roles del usuario.
     * Si quieres manejar varios roles por usuario, puedes usar una tabla pivot.
     */



       public function commerce()
    {
        return $this->hasOne(Commerce::class);
    }

    public function deliveryCompany()
    {
        return $this->hasOne(DeliveryCompany::class);
    }

    public function deliveryAgent()
    {
        return $this->hasOne(DeliveryAgent::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }


}
