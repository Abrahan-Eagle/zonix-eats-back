<?php

namespace App\Services\Routing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cascada: ORS (si hay API key) → Valhalla → OSRM FOSSGIS → OSRM demo → interpolado local.
 */
class RouteCalculationService
{
    public function calculateBetween(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
        string $mode = 'driving'
    ): array {
        return $this->calculateViaPoints([
            ['lat' => $originLat, 'lng' => $originLng],
            ['lat' => $destLat, 'lng' => $destLng],
        ], $mode);
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $points  al menos 2 puntos
     */
    public function calculateViaPoints(array $points, string $mode = 'driving'): array
    {
        if (count($points) < 2) {
            return $this->fallbackFromPoints($points, $mode, 'not_enough_points');
        }

        $timeout = max(1, (int) config('zonix.routing_http_timeout', 5));

        $orsKey = (string) config('zonix.ors_api_key', '');
        if ($orsKey !== '') {
            $result = $this->tryOpenRouteService($points, $mode, $orsKey, $timeout);
            if ($result !== null) {
                Log::debug('RouteCalculationService: provider=ors', ['points' => count($points)]);

                return $result;
            }
        }

        $result = $this->tryValhalla($points, $mode, $timeout);
        if ($result !== null) {
            Log::debug('RouteCalculationService: provider=valhalla', ['points' => count($points)]);

            return $result;
        }

        $foss = (string) config('zonix.osrm_fossgis_base_url', '');
        if ($foss !== '') {
            $result = $this->tryOsrm($foss, $points, $mode, $timeout);
            if ($result !== null) {
                Log::debug('RouteCalculationService: provider=osrm_fossgis', ['points' => count($points)]);

                return $result;
            }
        }

        $demo = (string) config('zonix.osrm_demo_base_url', '');
        if ($demo !== '') {
            $result = $this->tryOsrm($demo, $points, $mode, $timeout);
            if ($result !== null) {
                Log::debug('RouteCalculationService: provider=osrm_demo', ['points' => count($points)]);

                return $result;
            }
        }

        Log::warning('RouteCalculationService: all external providers failed, using interpolated fallback');

        return $this->fallbackFromPoints($points, $mode, 'interpolated');
    }

    /**
     * @return array{distance: float, duration: int, polyline: array<int, array{lat: float, lng: float}>, provider: string, note?: string}|null
     */
    private function tryOpenRouteService(array $points, string $mode, string $apiKey, int $timeout): ?array
    {
        $profile = $this->orsProfile($mode);
        $url = config('zonix.ors_directions_base').'/'.$profile.'/geojson';
        $coordinates = [];
        foreach ($points as $p) {
            $coordinates[] = [(float) $p['lng'], (float) $p['lat']];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($timeout)->post($url, [
                'coordinates' => $coordinates,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $features = $data['features'] ?? null;
            if (! is_array($features) || empty($features[0])) {
                return null;
            }

            $feature = $features[0];
            $geometry = $feature['geometry'] ?? [];
            $coords = $geometry['coordinates'] ?? null;
            if (! is_array($coords) || empty($coords)) {
                return null;
            }

            $polyline = [];
            foreach ($coords as $c) {
                if (count($c) >= 2) {
                    $polyline[] = ['lat' => (float) $c[1], 'lng' => (float) $c[0]];
                }
            }

            if (count($polyline) < 2) {
                return null;
            }

            $summary = $feature['properties']['summary'] ?? [];
            $distanceM = (float) ($summary['distance'] ?? 0);
            $durationS = (float) ($summary['duration'] ?? 0);

            if ($distanceM <= 0) {
                $distanceM = $this->sumSegmentDistanceMeters($polyline);
            }
            if ($durationS <= 0) {
                $durationS = max(60, $distanceM / 1000 * 120);
            }

            return [
                'distance' => round($distanceM / 1000, 2),
                'duration' => max(1, (int) round($durationS / 60)),
                'polyline' => $polyline,
                'provider' => 'ors',
            ];
        } catch (\Throwable $e) {
            Log::debug('ORS routing failed: '.$e->getMessage());

            return null;
        }
    }

    private function tryValhalla(array $points, string $mode, int $timeout): ?array
    {
        $url = (string) config('zonix.valhalla_route_url', '');
        if ($url === '') {
            return null;
        }

        $costing = $this->valhallaCosting($mode);
        $locations = [];
        foreach ($points as $p) {
            $locations[] = [
                'lat' => (float) $p['lat'],
                'lon' => (float) $p['lng'],
            ];
        }

        try {
            $response = Http::timeout($timeout)->post($url, [
                'locations' => $locations,
                'costing' => $costing,
                'directions_options' => ['units' => 'kilometers'],
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $trip = $data['trip'] ?? null;
            if (! is_array($trip)) {
                return null;
            }

            $legs = $trip['legs'] ?? [];
            if (! is_array($legs) || empty($legs[0])) {
                return null;
            }

            $leg = $legs[0];
            $shape = $leg['shape'] ?? '';
            if (! is_string($shape) || $shape === '') {
                return null;
            }

            $decoded = $this->decodePolyline($shape, 6);
            if (count($decoded) < 2) {
                return null;
            }

            $polyline = [];
            foreach ($decoded as $pt) {
                $polyline[] = ['lat' => $pt['lat'], 'lng' => $pt['lng']];
            }

            $summary = $trip['summary'] ?? $leg['summary'] ?? [];
            $lengthKm = (float) ($summary['length'] ?? 0);
            $timeS = (float) ($summary['time'] ?? 0);

            if ($lengthKm <= 0) {
                $lengthKm = $this->sumSegmentDistanceMeters($polyline) / 1000;
            }
            if ($timeS <= 0) {
                $timeS = max(60, $lengthKm * 120);
            }

            return [
                'distance' => round($lengthKm, 2),
                'duration' => max(1, (int) round($timeS / 60)),
                'polyline' => $polyline,
                'provider' => 'valhalla',
            ];
        } catch (\Throwable $e) {
            Log::debug('Valhalla routing failed: '.$e->getMessage());

            return null;
        }
    }

    private function tryOsrm(string $base, array $points, string $mode, int $timeout): ?array
    {
        $profile = $this->osrmProfile($mode);
        $parts = [];
        foreach ($points as $p) {
            $parts[] = ((float) $p['lng']).','.((float) $p['lat']);
        }
        $coordPath = implode(';', $parts);
        $osrmUrl = rtrim($base, '/').'/route/v1/'.$profile.'/'.$coordPath;

        try {
            $response = Http::timeout($timeout)->get($osrmUrl, [
                'overview' => 'full',
                'geometries' => 'geojson',
                'steps' => 'false',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (empty($data['routes'][0])) {
                return null;
            }

            $routeData = $data['routes'][0];
            $distance = ((float) $routeData['distance']) / 1000;
            $duration = (int) round(((float) $routeData['duration']) / 60);

            $polyline = [];
            if (! empty($routeData['geometry']['coordinates'])) {
                foreach ($routeData['geometry']['coordinates'] as $coord) {
                    $polyline[] = ['lat' => (float) $coord[1], 'lng' => (float) $coord[0]];
                }
            }

            if (count($polyline) < 2) {
                return null;
            }

            return [
                'distance' => round($distance, 2),
                'duration' => max(1, $duration),
                'polyline' => $polyline,
                'provider' => 'osrm',
            ];
        } catch (\Throwable $e) {
            Log::debug('OSRM routing failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $points
     */
    private function fallbackFromPoints(array $points, string $mode, string $reason): array
    {
        $polyline = [];
        $totalKm = 0.0;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $a = $points[$i];
            $b = $points[$i + 1];
            $seg = $this->interpolateSegment(
                (float) $a['lat'],
                (float) $a['lng'],
                (float) $b['lat'],
                (float) $b['lng']
            );
            if ($i === 0) {
                $polyline = array_merge($polyline, $seg);
            } else {
                $polyline = array_merge($polyline, array_slice($seg, 1));
            }
            $totalKm += $this->haversineKm(
                (float) $a['lat'],
                (float) $a['lng'],
                (float) $b['lat'],
                (float) $b['lng']
            );
        }

        $duration = max(1, (int) round($totalKm * 2));

        return [
            'distance' => round($totalKm, 2),
            'duration' => $duration,
            'polyline' => $polyline,
            'provider' => 'fallback',
            'note' => 'Route calculated using interpolated fallback'.($reason !== 'interpolated' ? ' ('.$reason.')' : ''),
        ];
    }

    private function interpolateSegment(float $fromLat, float $fromLng, float $toLat, float $toLng, int $segments = 12): array
    {
        $points = [['lat' => $fromLat, 'lng' => $fromLng]];
        $dist = $this->haversineKm($fromLat, $fromLng, $toLat, $toLng);
        $offset = $dist * 0.008;

        for ($i = 1; $i < $segments; $i++) {
            $t = $i / $segments;
            $lat = $fromLat + ($toLat - $fromLat) * $t;
            $lng = $fromLng + ($toLng - $fromLng) * $t;
            $perpLat = -($toLng - $fromLng);
            $perpLng = $toLat - $fromLat;
            $norm = sqrt($perpLat * $perpLat + $perpLng * $perpLng) ?: 1;
            $curve = sin(M_PI * $t) * $offset;
            $lat += ($perpLat / $norm) * $curve;
            $lng += ($perpLng / $norm) * $curve;
            $points[] = ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
        }
        $points[] = ['lat' => $toLat, 'lng' => $toLng];

        return $points;
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Longitud aproximada de la polilínea en metros (suma Haversine por tramo).
     *
     * @param  array<int, array{lat: float, lng: float}>  $polyline
     */
    private function sumSegmentDistanceMeters(array $polyline): float
    {
        if (count($polyline) < 2) {
            return 0.0;
        }
        $km = 0.0;
        for ($i = 0; $i < count($polyline) - 1; $i++) {
            $km += $this->haversineKm(
                (float) $polyline[$i]['lat'],
                (float) $polyline[$i]['lng'],
                (float) $polyline[$i + 1]['lat'],
                (float) $polyline[$i + 1]['lng']
            );
        }

        return $km * 1000;
    }

    /**
     * @return array<int, array{lat: float, lng: float}>
     */
    private function decodePolyline(string $encoded, int $precision = 6): array
    {
        $points = [];
        $index = 0;
        $len = strlen($encoded);
        $lat = 0;
        $lng = 0;
        $factor = pow(10, $precision);

        while ($index < $len) {
            $result = 0;
            $shift = 0;
            $b = 0;
            do {
                if ($index >= $len) {
                    break 2;
                }
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1F) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $result = 0;
            $shift = 0;
            do {
                if ($index >= $len) {
                    break 2;
                }
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1F) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = ['lat' => $lat / $factor, 'lng' => $lng / $factor];
        }

        return $points;
    }

    private function orsProfile(string $mode): string
    {
        return match ($mode) {
            'walking' => 'foot-walking',
            'bicycling' => 'cycling-regular',
            default => 'driving-car',
        };
    }

    private function valhallaCosting(string $mode): string
    {
        return match ($mode) {
            'walking' => 'pedestrian',
            'bicycling' => 'bicycle',
            default => 'auto',
        };
    }

    private function osrmProfile(string $mode): string
    {
        return match ($mode) {
            'walking' => 'walking',
            'bicycling' => 'cycling',
            default => 'driving',
        };
    }
}
