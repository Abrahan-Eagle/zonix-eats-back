# Business Rules (MVP) — Zonix Eats Backend

## Decisiones Clave

1. **Carrito:** NO puede haber productos de diferentes comercios (uni-commerce)
2. **Validación de Precio:** Recalcular y validar contra total enviado (margen 0.01 por redondeo)
3. **Stock:** AMBAS opciones (`available` Y `stock_quantity`) - Validar siempre available, si tiene stock_quantity validar cantidad
4. **Delivery:** Sistema completo (propio, empresas, independientes) + Asignación autónoma con expansión de área
5. **Eventos:** Firebase + Pusher (NO WebSocket)
6. **Perfiles:** Datos mínimos (USERS) vs completos (COMMERCE, DELIVERY)
7. **photo_users:** Required estricto (bloquea creación de orden)
8. **Geolocalización Comercios:** Búsqueda inicial 1-1.5km, expansión automática a 4-5km
9. **Asignación Delivery:** Autónoma con expansión automática de área (1-1.5km → 4-5km → continua)
10. **Cancelación:** Límite 5 minutos O hasta validación de pago
11. **Reembolsos:** Manual (no automático)

## Order States

```
pending_payment → paid → processing → shipped → delivered
                → cancelled (solo en pending_payment, paid, processing)
```

## 💰 Modelo de Negocio

**Costos y Precios:**

- **Costo Delivery:** Híbrido (Base fija $2.00 + $0.50/km después de 2 km) - Configurable por admin
- **Quién paga delivery:** Cliente (se agrega al total de la orden)
- **Membresía/Comisión:** Membresía mensual obligatoria (base) + Comisión % sobre ventas del mes (extra)
    - Ejemplo: $100/mes + 10% de ventas = $100 + $500 (si vendió $5,000) = $600 total
- **Mínimo pedido:** No hay mínimo
- **Tarifa servicio:** No hay (solo subtotal + delivery)
- **Propinas:** No permitidas

**Pagos:**

- **Métodos:** Todos (efectivo, transferencia, tarjeta, pago móvil, digitales)
- **Quién recibe:** Comercio directamente (con sus datos bancarios)
- **Manejo:** Tiempo real (validación manual de comprobante)
- **Pago a delivery:** Del comercio (después de recibir pago del cliente) → **Delivery recibe 100% del delivery_fee** (Opción A confirmada)

**Límites:**

- **Distancia máxima:** 60 minutos de tiempo estimado de viaje
- **Quejas/Disputas:** Sistema de tickets con admin (tabla `disputes`)

**Horarios:**

- **Comercios:** Definen horarios + campo `open` manual
- **Delivery:** 24/7 según disponibilidad (campo `working`)

## Penalizaciones y Tiempos Límite

**Cancelaciones:**

- **Comercio:** Puede cancelar en `paid`/`processing` con justificación. Penalización si excede límite (5 cancelaciones/30 días)
- **Cliente:** Solo en `pending_payment`, límite 5 minutos. Penalización si crea múltiples órdenes sin pagar
- **Comisión en cancelación:** Si comercio cancela después de `paid`, se cobra comisión como penalización adicional

**Delivery rechaza:**

- Debe justificar. Penalización si rechaza 3-5 órdenes seguidas sin justificación válida
- Ideal: Bajar switch `working = false` si no está disponible

**Tiempos límite:**

- Cliente sube comprobante: 5 minutos (cancelación automática si no sube)
- Comercio valida pago: 5 minutos (cancelación automática si no valida)
- Notificaciones automáticas antes de timeout

**Rating/Reviews:**

- Obligatorio después de orden `delivered`
- Comercio y delivery se califican por separado
- No editables ni eliminables

**Promociones:**

- Manual (comercio y admin pueden crear)
- Código promocional O automático según tipo

## Direcciones y Geolocalización

**Tabla `addresses`:**

- **Perfil (usuario):** `profile_id` + `role` (ej. usuario, comercio). Para USERS: 2 direcciones (casa `is_default=true` + entrega).
- **Establecimiento (comercio):** `commerce_id` sin `profile_id` (opción onboarding). Dirección del local vinculada solo al comercio.

**USERS tiene 2 direcciones:**

1. **Predeterminada (Casa):** `is_default = true` en tabla `addresses`
    - **Uso:** Base para búsqueda de comercios por geolocalización
    - **Ubicación:** GPS + inputs y selects para mayor precisión
2. **Entrega (Pedido):** Puede ser diferente, se guarda temporalmente o como nueva dirección
    - **Ubicación:** GPS + inputs y selects para mayor precisión

**Búsqueda de Comercios por Geolocalización:**

- **Rango inicial:** 1-1.5 km desde dirección predeterminada del usuario
- **Expansión automática:** Si no hay comercios abiertos, expande automáticamente a 4-5 km
- **Expansión manual:** Usuario puede ampliar rango si desea buscar más lejos
- **Cálculo:** Haversine para calcular distancia entre coordenadas GPS

## Campos Requeridos por Rol

**USERS:** firstName, lastName, phone, photo_users (required)
**COMMERCE:** 7 campos requeridos + 16 opcionales
**DELIVERY COMPANY:** 9 campos requeridos + campos opcionales (igual estructura que COMMERCE)
**DELIVERY AGENT:** 7 campos requeridos + campos opcionales

**IMPORTANTE:** Ver README.md sección completa "📋 DATOS REQUERIDOS POR ACCIÓN Y ROL" para detalles específicos de cada campo.
