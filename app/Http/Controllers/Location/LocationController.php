<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use App\Models\Commerce;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Actualizar ubicación del usuario
     * Almacena la ubicación en la base de datos y actualiza la ubicación actual del delivery agent si aplica
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
        ]);

        try {
            $user = Auth::user();
            $profile = $user->profile;

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found'
                ], 404);
            }

            // Obtener dirección usando Nominatim (geocodificación inversa)
            $address = $this->reverseGeocode($request->latitude, $request->longitude);

            // Guardar ubicación en historial
            $userLocation = UserLocation::create([
                'profile_id' => $profile->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'altitude' => $request->altitude,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'address' => $address,
                'recorded_at' => now(),
            ]);

            // Si es delivery agent, actualizar ubicación actual
            if ($profile->deliveryAgent) {
                $profile->deliveryAgent->update([
                    'current_latitude' => $request->latitude,
                    'current_longitude' => $request->longitude,
                    'last_location_update' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => [
                    'id' => $userLocation->id,
                    'latitude' => $userLocation->latitude,
                    'longitude' => $userLocation->longitude,
                    'address' => $address,
                    'timestamp' => $userLocation->recorded_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating location: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Geocodificación inversa usando Nominatim (OpenStreetMap)
     */
    private function reverseGeocode(float $latitude, float $longitude): ?string
    {
        try {
            $userAgent = config('app.name', 'ZonixEats') . ' App';
            
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? null;
                
                if ($address) {
                    $parts = [];
                    
                    // Construir dirección legible
                    if (!empty($address['house_number'])) {
                        $parts[] = $address['house_number'];
                    }
                    if (!empty($address['road'])) {
                        $parts[] = $address['road'];
                    }
                    if (!empty($address['suburb']) || !empty($address['neighbourhood'])) {
                        $parts[] = $address['suburb'] ?? $address['neighbourhood'];
                    }
                    if (!empty($address['city']) || !empty($address['town']) || !empty($address['village'])) {
                        $parts[] = $address['city'] ?? $address['town'] ?? $address['village'];
                    }
                    if (!empty($address['state'])) {
                        $parts[] = $address['state'];
                    }
                    if (!empty($address['country'])) {
                        $parts[] = $address['country'];
                    }
                    
                    if (!empty($parts)) {
                        return implode(', ', $parts);
                    }
                }
                
                // Fallback: usar display_name si está disponible
                if (!empty($data['display_name'])) {
                    return $data['display_name'];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error en geocodificación inversa: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Obtener lugares cercanos (restaurantes/comercios) usando cálculo de distancia Haversine
     */
    public function getNearbyPlaces(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:400', // km (1-400 como Facebook)
            'type' => 'nullable|string|in:restaurant,store,gas_station,pharmacy',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->get('radius', 5); // 5km por defecto

        try {
            // Buscar comercios cercanos usando Address (que tiene latitude/longitude)
            // Calcular distancia usando fórmula Haversine
            $earthRadius = 6371;

            $nearbyPlaces = Commerce::selectRaw("
                commerces.*,
                commerces.business_name,
                commerces.address,
                commerces.open,
                addresses.latitude,
                addresses.longitude,
                (
                    $earthRadius * acos(
                        cos(radians(?)) * 
                        cos(radians(addresses.latitude)) * 
                        cos(radians(addresses.longitude) - radians(?)) + 
                        sin(radians(?)) * 
                        sin(radians(addresses.latitude))
                    )
                ) AS distance
            ", [$latitude, $longitude, $latitude])
            ->leftJoin('addresses', 'addresses.profile_id', '=', 'commerces.profile_id')
            ->whereNotNull('addresses.latitude')
            ->whereNotNull('addresses.longitude')
            ->havingRaw("distance <= ?", [$radius])
            ->orderBy('distance', 'asc')
            ->limit(20)
            ->get()
            ->map(function ($commerce) {
                return [
                    'id' => $commerce->id,
                    'name' => $commerce->business_name ?? 'Comercio',
                    'type' => 'restaurant',
                    'latitude' => $commerce->latitude ?? null,
                    'longitude' => $commerce->longitude ?? null,
                    'distance' => round($commerce->distance ?? 0, 2), // km
                    'address' => $commerce->address ?? '',
                    'phone' => $commerce->phone ?? '',
                    'is_open' => $commerce->open ?? false,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $nearbyPlaces,
                'meta' => [
                    'center' => ['lat' => $latitude, 'lng' => $longitude],
                    'radius' => $radius,
                    'count' => $nearbyPlaces->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting nearby places: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting nearby places: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Calcular ruta entre dos puntos usando OSRM (Open Source Routing Machine)
     */
    public function calculateRoute(Request $request)
    {
        $request->validate([
            'origin_lat' => 'required|numeric|between:-90,90',
            'origin_lng' => 'required|numeric|between:-180,180',
            'destination_lat' => 'required|numeric|between:-90,90',
            'destination_lng' => 'required|numeric|between:-180,180',
            'mode' => 'nullable|string|in:driving,walking,bicycling,transit',
        ]);

        $originLat = $request->origin_lat;
        $originLng = $request->origin_lng;
        $destLat = $request->destination_lat;
        $destLng = $request->destination_lng;
        $mode = $request->get('mode', 'driving');

        try {
            // Mapear modo a perfil de OSRM
            $profile = 'driving'; // Por defecto
            if ($mode === 'walking') {
                $profile = 'walking';
            } elseif ($mode === 'bicycling') {
                $profile = 'cycling';
            }

            // OSRM Route Service API
            // Formato: /route/v1/{profile}/{coordinates}?overview=full&geometries=geojson
            $osrmUrl = "http://router.project-osrm.org/route/v1/$profile/$originLng,$originLat;$destLng,$destLat";
            
            $response = Http::timeout(10)->get($osrmUrl, [
                'overview' => 'full',
                'geometries' => 'geojson',
                'steps' => 'true',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['routes']) && !empty($data['routes'][0])) {
                    $routeData = $data['routes'][0];
                    $distance = $routeData['distance'] / 1000; // Convertir metros a km
                    $duration = round($routeData['duration'] / 60); // Convertir segundos a minutos
                    
                    // Extraer polyline de la geometría GeoJSON
                    $polyline = [];
                    if (!empty($routeData['geometry']['coordinates'])) {
                        foreach ($routeData['geometry']['coordinates'] as $coord) {
                            $polyline[] = [
                                'lat' => $coord[1],
                                'lng' => $coord[0],
                            ];
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'origin' => ['lat' => $originLat, 'lng' => $originLng],
                            'destination' => ['lat' => $destLat, 'lng' => $destLng],
                            'mode' => $mode,
                            'distance' => round($distance, 2), // km
                            'duration' => $duration, // minutos
                            'polyline' => $polyline,
                        ],
                    ]);
                }
            }

            // Fallback: calcular distancia usando Haversine si OSRM falla
            $distance = $this->calculateHaversineDistance($originLat, $originLng, $destLat, $destLng);
            $duration = round($distance * 2); // Estimación: 2 minutos por km

            return response()->json([
                'success' => true,
                'data' => [
                    'origin' => ['lat' => $originLat, 'lng' => $originLng],
                    'destination' => ['lat' => $destLat, 'lng' => $destLng],
                    'mode' => $mode,
                    'distance' => round($distance, 2),
                    'duration' => $duration,
                    'polyline' => [
                        ['lat' => $originLat, 'lng' => $originLng],
                        ['lat' => $destLat, 'lng' => $destLng],
                    ],
                    'note' => 'Route calculated using fallback method',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating route: ' . $e->getMessage());
            
            // Fallback: calcular distancia básica
            $distance = $this->calculateHaversineDistance($originLat, $originLng, $destLat, $destLng);
            $duration = round($distance * 2);

            return response()->json([
                'success' => true,
                'data' => [
                    'origin' => ['lat' => $originLat, 'lng' => $originLng],
                    'destination' => ['lat' => $destLat, 'lng' => $destLng],
                    'mode' => $mode,
                    'distance' => round($distance, 2),
                    'duration' => $duration,
                    'polyline' => [
                        ['lat' => $originLat, 'lng' => $originLng],
                        ['lat' => $destLat, 'lng' => $destLng],
                    ],
                    'note' => 'Route calculated using fallback method',
                ],
            ]);
        }
    }

    /**
     * Calcular distancia usando fórmula Haversine
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
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
     * Geocodificación: convertir dirección a coordenadas usando Nominatim (OpenStreetMap)
     */
    public function getCoordinatesFromAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
        ]);

        $address = $request->address;

        try {
            $userAgent = config('app.name', 'ZonixEats') . ' App';
            
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data) && is_array($data) && !empty($data[0])) {
                    $result = $data[0];
                    
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'latitude' => (float) $result['lat'],
                            'longitude' => (float) $result['lon'],
                            'formatted_address' => $result['display_name'] ?? $address,
                            'accuracy' => 'APPROXIMATE',
                        ],
                    ]);
                }
            }

            // Fallback: retornar coordenadas por defecto si no se encuentra
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
                'data' => [
                    'latitude' => -12.0464,
                    'longitude' => -77.0428,
                    'formatted_address' => 'Lima, Perú',
                    'accuracy' => 'APPROXIMATE',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error geocoding address: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error geocoding address: ' . $e->getMessage(),
                'data' => [
                    'latitude' => -12.0464,
                    'longitude' => -77.0428,
                    'formatted_address' => 'Lima, Perú',
                    'accuracy' => 'APPROXIMATE',
                ],
            ]);
        }
    }

    /**
     * Obtener zonas de entrega activas
     */
    public function getDeliveryZones()
    {
        try {
            $zones = DeliveryZone::active()
                ->get()
                ->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'center' => [
                            'lat' => (float) $zone->center_latitude,
                            'lng' => (float) $zone->center_longitude,
                        ],
                        'radius' => (float) $zone->radius,
                        'delivery_fee' => (float) $zone->delivery_fee,
                        'delivery_time' => $zone->delivery_time,
                        'is_active' => $zone->is_active,
                        'description' => $zone->description,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $zones,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting delivery zones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting delivery zones: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Obtener rutas de delivery
     */
    public function getDeliveryRoutes()
    {
        try {
            $user = Auth::user()->load('profile.deliveryAgent');
            $profile = $user->profile;

            // Obtener órdenes asignadas al usuario (si es delivery agent)
            $routes = [];
            
            if ($profile && $profile->deliveryAgent) {
                $orders = \App\Models\Order::whereHas('orderDelivery', function($q) use ($profile) {
                    $q->where('agent_id', $profile->deliveryAgent->id);
                })
                ->whereIn('status', ['shipped', 'processing'])
                ->with(['commerce.profile', 'profile.user', 'orderDelivery.agent.profile'])
                ->get();

                foreach ($orders as $order) {
                    $commerce = $order->commerce;
                    $customer = $order->profile;
                    
                    if ($commerce && $customer) {
                        // Obtener coordenadas del comercio desde Address
                        $commerceAddress = \App\Models\Address::where('profile_id', $commerce->profile_id)
                            ->first();
                        
                        // Obtener coordenadas del cliente desde Address
                        $customerAddress = \App\Models\Address::where('profile_id', $customer->id)
                            ->first();
                        
                        $commerceLat = $commerceAddress->latitude ?? -12.0464;
                        $commerceLng = $commerceAddress->longitude ?? -77.0428;
                        $customerLat = $customerAddress->latitude ?? -12.0470;
                        $customerLng = $customerAddress->longitude ?? -77.0435;
                        
                        $distance = $this->calculateHaversineDistance($commerceLat, $commerceLng, $customerLat, $customerLng);
                        
                        $commerceAddressStr = $commerceAddress 
                            ? "{$commerceAddress->street} {$commerceAddress->house_number}" 
                            : ($commerce->address ?? 'Restaurante');
                        $customerAddressStr = $customerAddress 
                            ? "{$customerAddress->street} {$customerAddress->house_number}" 
                            : ($customer->address ?? 'Dirección del cliente');
                        
                        $routes[] = [
                            'id' => $order->id,
                            'order_id' => $order->id,
                            'start_location' => [
                                'latitude' => $commerceLat,
                                'longitude' => $commerceLng,
                                'address' => $commerceAddressStr,
                            ],
                            'end_location' => [
                                'latitude' => $customerLat,
                                'longitude' => $customerLng,
                                'address' => $customerAddressStr,
                            ],
                            'distance' => round($distance, 2),
                            'estimated_time' => round($distance * 2), // Estimación: 2 minutos por km
                            'waypoints' => [],
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $routes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo delivery routes: ' . $e->getMessage()
            ], 500);
        }
    }
} 