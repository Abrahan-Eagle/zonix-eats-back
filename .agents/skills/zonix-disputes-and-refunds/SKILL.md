---
name: zonix-disputes-and-refunds
description: Sistema de quejas/disputas y reembolsos manuales en Zonix Eats. Penalizaciones por cancelación y reglas de resolución.
trigger: Cuando se trabaje con disputas, quejas, reembolsos, penalizaciones por cancelación o soporte post-venta.
scope: app/Models/Dispute.php, app/Http/Controllers/*/DisputeController.php, app/Services/Payment/RefundService.php
author: Zonix Team
version: 1.0
---

# ⚖️ Disputas y Reembolsos - Zonix Eats

## 1. Conceptos Clave

- **Disputa**: queja formal asociada a una orden (producto equivocado, pedido frío, no llegó, etc.).
- **Reembolso**: devolución parcial o total del monto pagado, **siempre manual**, nunca automático.
- **Penalización**: monto cargado al comercio cuando cancela fuera de las reglas o por mala práctica.

## 2. Modelo `disputes`

Campos recomendados:

| Campo            | Descripción                          |
| ---------------- | ------------------------------------ |
| `order_id`       | Orden asociada                       |
| `opened_by`      | buyer|commerce|delivery|admin        |
| `reason`         | Motivo principal                     |
| `details`        | Descripción extendida (texto largo)  |
| `status`         | open|in_review|resolved|rejected     |
| `resolution`     | Texto con la decisión tomada         |
| `refund_amount`  | Monto de reembolso (si aplica)       |
| `penalty_amount` | Penalización al comercio (si aplica) |

Relación:

```php
class Dispute extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
```

## 3. Flujo de Disputa

```
1. Buyer crea disputa sobre una orden entregada o cancelada.
2. Admin revisa evidencia (mensajes, fotos, historial de estado).
3. Admin decide:
   - Reembolso total/parcial al Buyer.
   - Penalización al Commerce.
   - Sin acción (disputa rechazada).
4. Se actualiza Dispute y, si aplica, se registra Refund manual.
```

## 4. Reembolsos (Manual)

- Los reembolsos **no** se ejecutan automáticamente desde Zonix.
- Debe quedar registro del reembolso efectuado fuera del sistema (transferencia, reverso de pago, etc.).

Modelo sugerido `refunds`:

| Campo          | Descripción                               |
| -------------- | ----------------------------------------- |
| `order_id`     | Orden asociada                            |
| `dispute_id`   | Disputa origen (nullable)                  |
| `amount`       | Monto devuelto                             |
| `method`       | Texto (transferencia, reverso tarjeta…)    |
| `processed_by` | Admin responsable                          |
| `notes`        | Detalles adicionales                       |

## 5. Penalizaciones por Cancelación

Reglas (ver README backend, sección penalizaciones):

- Comercio:
  - Puede cancelar en `paid` o `processing` con justificación.
  - Penalización si excede límite (ej. > 5 cancelaciones/30 días).
  - Puede aplicarse `cancellation_penalty` en la orden.
- Buyer:
  - Límite 5 minutos o hasta validación de pago.
  - Penalización de comportamiento (no necesariamente monetaria en MVP).

Campos en `orders` (ver `zonix-order-lifecycle` § 5):

```php
$order->update([
    'status' => 'cancelled',
    'cancelled_by' => 'buyer|commerce|admin',
    'cancellation_reason' => 'Razón',
    'cancellation_penalty' => 0.00, // Penalidad si aplica
]);
```

## 6. Endpoints Sugeridos

```text
GET    /api/buyer/disputes              → Ver mis disputas
POST   /api/buyer/disputes              → Crear disputa para una orden
GET    /api/admin/disputes              → Listar todas las disputas
GET    /api/admin/disputes/{id}         → Ver detalle
POST   /api/admin/disputes/{id}/resolve → Resolver (refund/penalty/close)
```

## 7. Reglas Importantes

1. **Nunca** cambiar retroactivamente estados de orden para “tapar” disputas; registrar resolución aparte.
2. Reembolsos y penalizaciones deben ser **explícitos** en campos dedicados.
3. Solo Admin puede resolver una disputa.
4. Buyer no puede abrir múltiples disputas simultáneas sobre la misma orden.

## 8. Cross-references

- **Estados y cancelaciones**: `zonix-order-lifecycle` § 3-5.
- **Campos financieros**: `zonix-payments` § 5-6.

