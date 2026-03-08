---
name: zonix-delivery-system
description: Sistema de delivery de Zonix Eats. Asignación de entregas, cálculo de distancias Haversine, OSRM routing, zonas de entrega, y tracking.
trigger: Cuando se trabaje con delivery agents, cálculo de distancias, rutas, zonas de entrega, geolocalización, asignación de pedidos, o tarifa de delivery.
scope: app/Http/Controllers/Location/LocationController.php, app/Http/Controllers/Delivery/DeliveryController.php, app/Models/DeliveryZone.php, app/Services/TrackingService.php
author: Zonix Team
version: 2.0
---

**Módulo tarifa de delivery (futuro):** Si se va a implementar el módulo de tarifa (config Admin, CRUD zonas, cálculo en backend), leer y seguir [docs/PLAN_MODULO_TARIFA_DELIVERY.md](../../../docs/PLAN_MODULO_TARIFA_DELIVERY.md).

# 🛵 Sistema de Delivery - Zonix Eats

## Roles (Terminología Estándar)

| Nivel | Código en BD | Nombre Estándar | Alias aceptados            |
| ----- | ------------ | --------------- | -------------------------- |
| 0     | `users`      | **Buyer**       | Comprador, Cliente         |
| 1     | `commerce`   | **Commerce**    | Comercio, Restaurante      |
| 2     | `delivery`   | **Delivery**    | Delivery Agent, Repartidor |
| 3     | `admin`      | **Admin**       | Administrador              |

## 1. Fórmula Haversine

Usada en TODA la app para calcular distancias entre coordenadas (comercio↔cliente, delivery↔destino).

```php
// app/Http/Controllers/Location/LocationController.php
private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371; // Radio de la Tierra en km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // Resultado en km
}
```

> **REGLA:** La fórmula Haversine se usa como FALLBACK. Siempre intentar primero OSRM para distancias de ruta reales.

## 2. Búsqueda por Proximidad (Haversine SQL)

Para encontrar comercios/restaurantes cercanos al usuario:

```php
$earthRadius = 6371;
$nearbyPlaces = Commerce::selectRaw("
    commerces.*,
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
->get();
```

### Parámetros de búsqueda:

- **Radio por defecto:** 5 km
- **Radio máximo:** 400 km (como Facebook)
- **Tipos:** `restaurant`, `store`, `gas_station`, `pharmacy`
- **Límite:** 20 resultados

## 3. Routing con OSRM (Open Source Routing Machine)

**Prioridad:** OSRM → Fallback a Haversine

```php
// Calcular ruta real usando OSRM
$osrmUrl = "http://router.project-osrm.org/route/v1/$profile/$originLng,$originLat;$destLng,$destLat";

$response = Http::timeout(10)->get($osrmUrl, [
    'overview' => 'full',
    'geometries' => 'geojson',
    'steps' => 'true',
]);
```

### Perfiles OSRM:

| Modo Flutter | Perfil OSRM |
| ------------ | ----------- |
| `driving`    | `driving`   |
| `walking`    | `walking`   |
| `bicycling`  | `cycling`   |

### Respuesta de ruta:

```json
{
    "success": true,
    "data": {
        "origin": { "lat": 10.123, "lng": -67.456 },
        "destination": { "lat": 10.789, "lng": -67.012 },
        "mode": "driving",
        "distance": 3.52,
        "duration": 7,
        "polyline": [{ "lat": ..., "lng": ... }]
    }
}
```

### Estimación de tiempo:

```php
$duration = round($distance * 2); // 2 minutos por km (fallback)
```

## 4. Zonas de Entrega (DeliveryZone)

```php
// app/Models/DeliveryZone.php
// Cada zona tiene: name, center_latitude, center_longitude, radius, delivery_fee, delivery_time
$zones = DeliveryZone::active()->get();
```

| Campo              | Tipo    | Descripción                |
| ------------------ | ------- | -------------------------- |
| `name`             | string  | Nombre de la zona          |
| `center_latitude`  | float   | Centro de la zona          |
| `center_longitude` | float   | Centro de la zona          |
| `radius`           | float   | Radio en km                |
| `delivery_fee`     | decimal | Costo de delivery en USD   |
| `delivery_time`    | int     | Tiempo estimado en minutos |
| `is_active`        | boolean | Si la zona está activa     |

## 5. Geocodificación (Nominatim/OpenStreetMap)

### Geocodificación inversa (coordenadas → dirección):

```php
$response = Http::withHeaders([
    'User-Agent' => config('app.name') . ' App',
])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
    'format' => 'json',
    'lat' => $latitude,
    'lon' => $longitude,
    'zoom' => 18,
    'addressdetails' => 1,
]);
```

### Geocodificación directa (dirección → coordenadas):

```php
$response = Http::withHeaders([
    'User-Agent' => config('app.name') . ' App',
])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
    'q' => $address,
    'format' => 'json',
    'limit' => 1,
    'addressdetails' => 1,
]);
```

> **REGLA:** Siempre incluir `User-Agent` con nombre de la app. Nominatim lo requiere.

## 6. Tracking del Delivery Agent

```php
// Actualizar ubicación del delivery (LocationController)
if ($profile->deliveryAgent) {
    $profile->deliveryAgent->update([
        'current_latitude'  => $request->latitude,
        'current_longitude' => $request->longitude,
        'last_location_update' => now(),
    ]);
}
```

## 7. API Endpoints de Delivery

| Método | Ruta                           | Descripción                               |
| ------ | ------------------------------ | ----------------------------------------- |
| POST   | `/location/update`             | Actualizar ubicación del usuario/delivery |
| GET    | `/location/nearby-places`      | Comercios cercanos (Haversine)            |
| POST   | `/location/calculate-route`    | Calcular ruta (OSRM + fallback)           |
| POST   | `/location/geocode`            | Dirección → coordenadas                   |
| GET    | `/location/delivery-zones`     | Zonas de entrega activas                  |
| GET    | `/location/delivery-routes`    | Rutas del delivery agent                  |
| GET    | `/delivery/available-orders`   | Órdenes disponibles para delivery         |
| POST   | `/delivery/orders/{id}/accept` | Aceptar orden                             |
| PATCH  | `/delivery/orders/{id}/status` | Cambiar estado de entrega                 |
| POST   | `/delivery/location/update`    | Actualizar posición delivery              |
| GET    | `/delivery/earnings/{id}`      | Ganancias del delivery                    |
| GET    | `/delivery/statistics/{id}`    | Estadísticas del delivery                 |

## 8. Flujo de Asignación de Delivery

```
1. Orden cambia a 'processing' → Commerce solicita delivery
2. Sistema busca delivery agents disponibles en la zona
3. Delivery agent acepta → se crea OrderDelivery
4. Agent actualiza ubicación continuamente (POST /location/update)
5. Agent marca 'shipped' → Orden en camino
6. Agent marca 'delivered' → Orden completada
```

## 9. Cross-references

- **Estados de orden:** `zonix-order-lifecycle` § 1-2
- **Eventos broadcast:** `zonix-realtime-events` § 3 (OrderStatusChanged)
- **Comisión delivery_fee:** `zonix-payments` § 5
