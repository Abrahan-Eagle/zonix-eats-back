---
name: zonix-payments
description: Sistema de pagos híbridos, métodos de pago polimórficos, bancos, y reglas de comisiones para Zonix Eats.
trigger: Cuando se toque Checkout, Pasarelas, Tasas, Binance, Stripe, payment_methods, o datos bancarios.
scope: app/Services/Payment, app/Models/PaymentMethod.php, app/Models/Bank.php
version: 2.0
---

# 💸 Sistema de Pagos Zonix (Venezuela/Global)

## 1. Moneda y Conversión

- **Moneda Base:** El sistema calcula todo internamente en **USD**.
- **Visualización:** El cliente ve precios en USD y VES (Bolívares).
- **Tasa de Cambio:** Se usa la tasa del BCV almacenada en DB o Caché. NUNCA usar tasas estáticas en código.

## 2. Pasarelas Activas

### A. Binance Pay (Cripto)

- **Prioridad:** Alta.
- **Flujo:** Automático (Webhook).
- **Lógica:** Generar QR de cobro en USDT. La orden pasa a `paid` SOLO cuando el Webhook de Binance confirma.

### B. Stripe (Tarjetas Internacionales)

- **Uso:** Solo para tarjetas de crédito/débito internacionales.
- **Regla:** El monto se envía en centavos de USD.

### C. Pago Móvil / Zelle (Manual)

- **Flujo:** Semi-Manual.
- **Requisito:** El usuario DEBE subir comprobante (foto/referencia).
- **Estado:** La orden queda en `pending_payment` hasta que el Comercio o Admin valide.

## 3. Modelo `payment_methods` (Polimórfico Unificado)

**Una sola tabla** sirve para los 3 roles vía relación polimórfica:

| payable_type                  | payable_id   | Uso                     |
| ----------------------------- | ------------ | ----------------------- |
| `App\Models\User`             | user_id      | Comprador paga con esto |
| `App\Models\Commerce`        | commerce_id  | Comercio recibe aquí    |
| `App\Models\DeliveryAgent`    | agent_id     | Delivery recibe aquí    |
| `App\Models\DeliveryCompany`  | company_id   | Empresa delivery recibe aquí |

### Campos de `payment_methods`:

| Campo             | Tipo   | Uso                                                                                           |
| ----------------- | ------ | --------------------------------------------------------------------------------------------- |
| `payable_type/id` | morph  | Dueño (User/Commerce/DeliveryAgent)                                                           |
| `bank_id`         | FK     | Ref a `banks` (pago móvil, transferencia)                                                     |
| `type`            | enum   | card, mobile_payment, cash, paypal, stripe, mercadopago, digital_wallet, bank_transfer, other |
| `brand`           | string | Visa, Mastercard (solo tarjetas)                                                              |
| `last4`           | string | Últimos 4 dígitos (tarjetas)                                                                  |
| `account_number`  | string | Cuenta bancaria                                                                               |
| `phone`           | string | Teléfono (pago móvil)                                                                         |
| `email`           | string | Billetera digital, PayPal                                                                     |
| `owner_name`      | string | Titular de la cuenta                                                                          |
| `owner_id`        | string | Cédula/RIF del titular                                                                        |
| `reference_info`  | json   | Info extra (referencia, etc.)                                                                 |
| `is_default`      | bool   | Método principal                                                                              |
| `is_active`       | bool   | Activo/inactivo                                                                               |

### Cómo se usa en código:

```php
$user->paymentMethods()            // → métodos para PAGAR (comprador)
$commerce->paymentMethods()        // → métodos para RECIBIR (comercio)
$deliveryAgent->paymentMethods()   // → métodos para RECIBIR (repartidor)
$deliveryCompany->paymentMethods() // → métodos para RECIBIR (empresa delivery)
```
`PaymentMethodController::getPayableOwner()` resuelve el dueño según rol (Commerce, DeliveryAgent, DeliveryCompany, User). Flujo completo por rol: ver [docs/logica-pagos-por-rol.md](../../docs/logica-pagos-por-rol.md).

### API:

```
GET/POST .../buyer/payment/methods     → Comprador
GET/POST/PUT/DELETE .../payment-methods → Genérico
```

## 4. Tabla `banks` (Catálogo)

| Campo        | Uso                                 |
| ------------ | ----------------------------------- |
| `name`       | Nombre del banco (ej. Banesco)      |
| `code`       | Código bancario único (ej. 0134)    |
| `type`       | Público, privado, internacional     |
| `swift_code` | Para transferencias internacionales |
| `is_active`  | Banco activo/inactivo               |

## 5. Comisiones y Facturación

### Orden — campos financieros:

| Campo                     | Descripción                            |
| ------------------------- | -------------------------------------- |
| `delivery_fee`            | Costo de delivery que paga el cliente  |
| `delivery_payment_amount` | 100% del delivery_fee → va al delivery |
| `commission_amount`       | Comisión de Zonix sobre la venta       |
| `cancellation_penalty`    | Penalidad si cancela después de `paid` |

### Commerce — membresía:

| Campo                    | Descripción                |
| ------------------------ | -------------------------- |
| `membership_type`        | basic, premium, enterprise |
| `membership_monthly_fee` | Cuota mensual              |
| `commission_percentage`  | % de comisión sobre ventas |

### Tabla `commerce_invoices`:

Facturas mensuales = `membership_fee` + `commission_amount` = `total`.

## 6. Reglas de Validación

- No se permiten pagos mixtos en el MVP.
- El Delivery Fee siempre se cobra completo junto con la orden.
- Las tablas antiguas `user_payment_methods` y `delivery_payment_methods` fueron eliminadas; todo está en `payment_methods`.

## 7. Evento PaymentValidated

Cuando el Commerce valida (o rechaza) un comprobante de pago, se dispara:

```php
event(new PaymentValidated($order, $isValid, $profileId));
```

- Si `$isValid = true`: `pending_payment → paid`
- Si `$isValid = false`: `pending_payment → cancelled`
- **Ver `zonix-realtime-events` § 3 para payload completo del evento**

## 8. Cross-references

- **Estados de orden:** `zonix-order-lifecycle` § 1-2
- **Eventos broadcast:** `zonix-realtime-events` § 3 (PaymentValidated, OrderStatusChanged)
- **Campo `profiles.phone` deprecado** — se lee vía accessor desde tabla `phones` (ver `zonix-onboarding` § 5.7)
- **Delivery fee en UI:** `zonix-ui-design` § 4 (Checkout layout)
- **Flujo por rol (Commerce, Delivery, DeliveryCompany, comprador):** [docs/logica-pagos-por-rol.md](../../docs/logica-pagos-por-rol.md) — quién configura métodos, quién los usa, diagramas Mermaid.
