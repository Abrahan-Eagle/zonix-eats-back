# Runbook Operativo: Checkout (Idempotencia + Cupones)

## Objetivo
Reducir impacto operativo ante fallos de checkout, duplicados de orden o inconsistencias de cupón sin interrumpir operación.

## Señales a monitorear
- `checkout_total_mismatch` (warning): total enviado no coincide con recálculo backend.
- `coupon_validation_failed` (info): fallo de validación por cupón inválido, límite o mínimo.
- Respuestas `409` con `ORDER_IDEMPOTENCY_CONFLICT`.

## Checklist de despliegue
1. Ejecutar migración `create_order_idempotency_keys_table`.
2. Validar `POST /api/buyer/orders` con y sin header `Idempotency-Key`.
3. Verificar creación de orden con `coupon_code` válido.
4. Verificar rechazo de `coupon_code` inválido con `error_code` adecuado.
5. Revisar logs estructurados en `storage/logs/laravel.log`.

## Smoke tests post-deploy
1. Crear orden pickup sin cupón.
2. Repetir exactamente la misma solicitud con mismo `Idempotency-Key` y confirmar que retorna la misma orden.
3. Reusar el mismo `Idempotency-Key` con payload diferente y confirmar `409 ORDER_IDEMPOTENCY_CONFLICT`.
4. Crear orden con cupón y confirmar:
   - descuento aplicado en `pricing_breakdown`,
   - registro en `coupon_usages`.

## Rollback por fases

### Rollback Fase Idempotencia
- Mantener endpoint activo.
- Si hay anomalías, instruir clientes a no enviar `Idempotency-Key` temporalmente.
- Validar que el flujo sin clave sigue funcional (comportamiento legacy).

### Rollback Fase Cupón atómico
- Dejar de enviar `coupon_code` desde frontend.
- Mantener `validate-coupon` para cálculo informativo.
- Si se necesita contingencia completa, deshabilitar UI de cupón y operar checkout sin descuento.

## Consultas rápidas de diagnóstico
- Duplicados potenciales por clave:
  - `select profile_id, idempotency_key, count(*) from order_idempotency_keys group by profile_id, idempotency_key having count(*) > 1;`
- Órdenes con uso de cupón:
  - `select order_id, coupon_id, discount_amount, used_at from coupon_usages order by used_at desc limit 50;`

## Criterio de salida estable
- 0 errores 500 relacionados a checkout en ventana de 24h.
- 0 conflictos de schema en cupones.
- 100% de reintentos con misma clave devuelven respuesta consistente.
