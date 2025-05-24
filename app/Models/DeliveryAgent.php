<?php

namespace App\Models\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAgent extends Model
{
    use HasFactory;

      protected $fillable = ['company_id', 'user_id', 'estado'];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class, 'agent_id');
    }
}
