# Real-time Events — Zonix Eats Backend

**Firebase Cloud Messaging (FCM) + Pusher. NO WebSocket.**

## Events

| Evento                    | Descripción                       |
| ------------------------- | --------------------------------- |
| `OrderCreated`            | Nueva orden creada                |
| `OrderStatusChanged`      | Estado de orden cambiado          |
| `PaymentValidated`        | Pago validado                     |
| `NewMessage`              | Nuevo mensaje de chat             |
| `DeliveryLocationUpdated` | Ubicación de delivery actualizada |
| `NotificationCreated`     | Nueva notificación                |

## Channels (Pusher)

- `private-user.{userId}` — Notificaciones de usuario
- `private-order.{orderId}` — Actualizaciones de orden
- `private-chat.{orderId}` — Chat de orden
- `private-commerce.{commerceId}` — Notificaciones de comercio
