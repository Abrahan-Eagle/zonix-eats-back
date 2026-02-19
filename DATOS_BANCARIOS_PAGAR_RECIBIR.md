# Datos bancarios: pagar (usuario) y recibir (comercio / delivery)

**Respuesta corta:** Sí hay tablas para eso. No hace falta crear nuevas; se usan **banks** y **payment_methods**.

---

## 1. Tablas que ya existen

### `banks` (catálogo de bancos)
- **name** – Nombre del banco (ej. Banesco)
- **code** – Código bancario (ej. 0134), único
- **type** – Público, privado, internacional (opcional)
- **swift_code** – Para transferencias internacionales (opcional)
- **is_active**

Usada para asociar un método de pago a un banco (pago móvil, transferencia, etc.).

---

### `payment_methods` (unificada, relación polimórfica)

**Un solo modelo** sirve para:

1. **Usuario/comprador:** métodos con los que **paga** (tarjeta, pago móvil, transferencia, efectivo, etc.).
2. **Comercio:** datos donde **recibe** el dinero (cuenta bancaria, teléfono pago móvil, etc.).
3. **Delivery:** datos donde **recibe** el dinero (cuenta, teléfono pago móvil, etc.).

Se distingue por la relación polimórfica:

- **payable_type** + **payable_id**:
  - `App\Models\User` + user_id → comprador (para pagar)
  - `App\Models\Commerce` + commerce_id → comercio (para recibir)
  - `App\Models\DeliveryAgent` + delivery_agent_id → repartidor (para recibir)

**Campos de la tabla:**

| Campo | Uso |
|------|-----|
| payable_type, payable_id | Quién es el dueño (User / Commerce / DeliveryAgent) |
| bank_id | FK a `banks` (pago móvil, transferencia) |
| type | card, mobile_payment, cash, paypal, stripe, mercadopago, digital_wallet, bank_transfer, other |
| brand | Visa, Mastercard, etc. (tarjetas) |
| last4, exp_month, exp_year, cardholder_name | Tarjetas |
| account_number | Cuenta bancaria (transferencia / pago móvil) |
| phone | Teléfono (pago móvil) |
| email | Billetera digital, PayPal |
| owner_name, owner_id | Titular y cédula/RIF |
| reference_info | JSON extra (referencia, etc.) |
| is_default, is_active | Principal y activo |

---

## 2. Cómo se usa en el código

- **User** (comprador): `$user->paymentMethods()` → métodos para **pagar**.
- **Commerce**: `$commerce->paymentMethods()` → métodos para **recibir**.
- **DeliveryAgent**: `$deliveryAgent->paymentMethods()` → métodos para **recibir**.

Rutas de API (entre otras):

- Comprador: `GET/POST .../payment/methods` (Buyer PaymentController).
- Genéricas: `GET/POST/PUT/DELETE .../payment-methods` (PaymentMethodController).

Las tablas antiguas `user_payment_methods` y `delivery_payment_methods` ya se eliminaron; todo está en **payment_methods** con `payable_type` / `payable_id`.

---

## 3. Resumen

| Necesidad | Tabla / modelo | Notas |
|-----------|----------------|-------|
| Banco (catálogo) | **banks** | Nombre, código, tipo, swift |
| Usuario puede pagar | **payment_methods** (payable = User) | Tarjeta, pago móvil, transferencia, etc. |
| Comercio puede recibir | **payment_methods** (payable = Commerce) | Cuenta, teléfono pago móvil, etc. |
| Delivery puede recibir | **payment_methods** (payable = DeliveryAgent) | Cuenta, teléfono, etc. |

No hace falta una tabla nueva para “datos personales del banco del usuario para pagar” ni para “datos donde comercio y delivery reciben”; eso se guarda en **payment_methods** (y **banks** para el catálogo). Si en el futuro quisieras que el comprador tenga métodos de pago ligados al **perfil** en lugar de al **user**, se podría usar `payable_type = Profile` y `payable_id = profile_id`; el esquema actual con User es válido y coherente con la API actual.
