<?php

namespace App\Services;

class DeliveryFeeService
{
    /**
     * Distancia en km entre dos puntos (Haversine).
     */
    public static function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Calcula tarifa de delivery: base + (distancia_km * por_km), acotada entre min y max.
     */
    public static function calculate(float $distanceKm): float
    {
        $base = (float) config('zonix.delivery_fee_base', 1.50);
        $perKm = (float) config('zonix.delivery_fee_per_km', 0.50);
        $min = (float) config('zonix.delivery_fee_min', 2.00);
        $max = (float) config('zonix.delivery_fee_max', 15.00);

        $fee = $base + ($distanceKm * $perKm);
        $fee = max($min, min($max, $fee));
        return round($fee, 2);
    }
}
