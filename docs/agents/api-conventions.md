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
    'data' => $orders->items(),
    'pagination' => [
        'current_page' => $orders->currentPage(),
        'per_page' => $orders->perPage(),
        'total' => $orders->total(),
        'last_page' => $orders->lastPage(),
    ]
]);
```
