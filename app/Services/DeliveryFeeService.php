<?php

namespace App\Services;

use App\Models\DeliverySetting;
use App\Models\DeliveryZone;

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
     * Primary entry point: calculates delivery fee.
     *
     * 1) If delivery lat/lng provided, checks active DeliveryZones (Haversine < radius).
     *    If a zone matches, returns zone-based fee.
     * 2) Otherwise falls back to global formula (DB settings → config/zonix.php).
     */
    public static function calculate(float $distanceKm, ?float $deliveryLat = null, ?float $deliveryLng = null): array
    {
        if ($deliveryLat !== null && $deliveryLng !== null) {
            $zone = static::findZone($deliveryLat, $deliveryLng);
            if ($zone) {
                return [
                    'fee' => round((float) $zone->delivery_fee, 2),
                    'delivery_time_minutes' => (int) $zone->delivery_time,
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->name,
                    'distance_km' => $distanceKm,
                ];
            }
        }

        return static::calculateGlobal($distanceKm);
    }

    /**
     * Global formula: base + billable_km * per_km, clamped between min and max.
     * Reads from delivery_settings table first; falls back to config/zonix.php.
     */
    private static function calculateGlobal(float $distanceKm): array
    {
        try {
            $cfg = DeliverySetting::getConfig();
            $base = $cfg->base_cost;
            $perKm = $cfg->cost_per_km;
            $freeKm = $cfg->free_km;
            $min = $cfg->fee_min;
            $max = $cfg->fee_max;
        } catch (\Throwable $e) {
            $base = (float) config('zonix.delivery_fee_base', 1.50);
            $perKm = (float) config('zonix.delivery_fee_per_km', 0.50);
            $freeKm = 0.0;
            $min = (float) config('zonix.delivery_fee_min', 2.00);
            $max = (float) config('zonix.delivery_fee_max', 15.00);
        }

        $billableKm = max(0, $distanceKm - $freeKm);
        $fee = $base + ($billableKm * $perKm);
        $fee = max($min, min($max, $fee));

        $estimatedMinutes = max(5, (int) round($distanceKm * 3.5 + 10));

        return [
            'fee' => round($fee, 2),
            'delivery_time_minutes' => $estimatedMinutes,
            'zone_id' => null,
            'zone_name' => null,
            'distance_km' => $distanceKm,
        ];
    }

    /**
     * Finds the first active DeliveryZone that contains the given point.
     */
    private static function findZone(float $lat, float $lng): ?DeliveryZone
    {
        $zones = DeliveryZone::active()->get();

        foreach ($zones as $zone) {
            if ($zone->containsLocation($lat, $lng)) {
                return $zone;
            }
        }

        return null;
    }
}
