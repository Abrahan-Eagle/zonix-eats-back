# Runbook Operativo - Ciclo de Vida de Ordenes

## Objetivo

Operar y diagnosticar transiciones del ciclo de vida de ordenes sin romper flujo de negocio.

## Senales y alertas recomendadas

- `order_transition_rejected` con `error_code=ORDER_INVALID_TRANSITION` mayor a 10 en 5 minutos.
- `ORDER_ALREADY_ASSIGNED` mayor a 5 en 5 minutos.
- Ordenes en `processing` por mas de 45 minutos sin pasar a `shipped`.
- Ordenes en `shipped` por mas de 90 minutos sin pasar a `delivered`.

## Consultas rapidas

```sql
-- Ultimas transiciones por orden
SELECT order_id, from_status, to_status, actor_role, source, occurred_at
FROM order_status_history
ORDER BY occurred_at DESC
LIMIT 100;
```

```sql
-- Ordenes atascadas en processing
SELECT id, status, updated_at
FROM orders
WHERE status = 'processing'
  AND updated_at < (NOW() - INTERVAL 45 MINUTE);
```

```sql
-- Ordenes atascadas en shipped
SELECT id, status, updated_at
FROM orders
WHERE status = 'shipped'
  AND updated_at < (NOW() - INTERVAL 90 MINUTE);
```

## Respuesta a incidente

1. Verificar eventos `order_transition_rejected` y `order_transition_applied`.
2. Correlacionar `order_id` con `order_status_history`.
3. Si hubo doble aceptacion delivery, confirmar presencia de `ORDER_ALREADY_ASSIGNED` y revisar agentes involucrados.
4. Ejecutar reintento controlado solo desde endpoint autorizado por rol.
5. Si hay regresion funcional, rollback de deploy y validar nuevamente transiciones criticas:
   - `paid -> processing`
   - `processing -> shipped`
   - `shipped -> delivered`
   - `* -> cancelled` (segun reglas por rol)
