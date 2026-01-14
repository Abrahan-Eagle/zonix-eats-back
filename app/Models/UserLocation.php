<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    use HasFactory;

    protected $table = 'user_locations';

    protected $fillable = [
        'profile_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'speed',
        'heading',
        'address',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /**
     * Relación con Profile
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
