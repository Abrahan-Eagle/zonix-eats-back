# API Conventions — Zonix Eats Backend

## Response Format

```php
// Éxito
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operación exitosa'
], 200);

// Error
return response()->json([
    'success' => false,
    'message' => 'Mensaje de error',
    'errors' => $errors
], 400);
```

## Status Codes

`200` OK, `201` Created, `400` Bad Request, `401` Unauthorized, `403` Forbidden, `404` Not Found, `422` Validation Error, `500` Server Error

## Pagination

**CRÍTICO:** Siempre paginar endpoints de listado:

```php
$perPage = $request->get('per_page', 15);
$orders = Order::paginate($perPage);

return response()->json([
    'success' => true,
    'message' => 'Listado obtenido',
    'data' => [
        'items' => $orders->items(),
        // alias legacy temporal para clientes antiguos:
        'data' => $orders->items(),
        'pagination' => [
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
        ],
    ],
]);
```

## Contrato Catálogo Buyer v2 (transición)

- Formato canónico: `success`, `message`, `data.items`, `data.pagination`.
- Alias legacy transitorio: `data.data` (mismo arreglo que `items`) para no romper clientes existentes.
- Ventana de compatibilidad: mantener alias hasta **30-Jun-2026**.
- Fecha de retiro objetivo del alias legacy: **01-Jul-2026** (coordinado con frontend).

## Errores de carrito (códigos estables)

- `OUT_OF_STOCK`
- `PRODUCT_UNAVAILABLE`
- `COMMERCE_CLOSED`
- `INVALID_QUANTITY`
- `PROFILE_REQUIRED`
- `CART_LINE_NOT_FOUND`
- `UNAUTHENTICATED`

Se deben mapear desde códigos de dominio del servicio (no por parseo textual).

```php
return response()->json([
    'success' => false,
    'data' => null,
    'message' => 'Stock insuficiente',
    'error_code' => 'OUT_OF_STOCK',
], 422);
```

## Carrito por línea personalizada

- Cada item remoto debe incluir `line_id` estable.
- `line_id` representa producto + personalización (extras/preferencias/notas).
- Permite coexistir dos líneas del mismo `product_id` con personalización distinta.
- `DELETE /api/buyer/cart/{productId}` acepta `?line_id=...` para remover línea específica.
- `PUT /api/buyer/cart/update-quantity` acepta `line_id` opcional para actualizar la línea exacta.

```php
'pagination' => [
    'current_page' => $orders->currentPage(),
    'per_page' => $orders->perPage(),
    'total' => $orders->total(),
    'last_page' => $orders->lastPage(),
]
```
