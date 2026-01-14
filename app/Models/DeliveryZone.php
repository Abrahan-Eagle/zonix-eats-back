<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $table = 'delivery_zones';

    protected $fillable = [
        'name',
        'center_latitude',
        'center_longitude',
        'radius',
        'delivery_fee',
        'delivery_time',
        'is_active',
        'description',
    ];

    protected $casts = [
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'radius' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'delivery_time' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Verificar si una ubicación está dentro de esta zona
     */
    public function containsLocation(float $latitude, float $longitude): bool
    {
        $distance = $this->calculateDistance(
            $this->center_latitude,
            $this->center_longitude,
            $latitude,
            $longitude
        );

        return $distance <= $this->radius;
    }

    /**
     * Calcular distancia usando fórmula Haversine
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radio de la Tierra en km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Scope para zonas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
