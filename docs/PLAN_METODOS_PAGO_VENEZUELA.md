# Plan Estrategico: Metodos de Pago Venezuela - Zonix Eats

> **Fecha:** 27 Marzo 2026
> **Estado:** Investigacion completa, pendiente de implementacion.
> **Uso:** Leer este documento cuando se decida trabajar en metodos de pago.
> La IA debe consultar este archivo antes de modificar enum `payment_methods`, agregar nuevos tipos de pago, o tocar flujo de comprobantes.

---

## ALERTA REGULATORIA: Caso Yummy y Sudeban

**Yummy fue sancionada por Sudeban** (sept 2022) por operar como intermediario de pagos sin licencia fintech (ITFB). Le prohibieron abrir cuentas bancarias y recibir transferencias en bolivares.

**Que hizo mal Yummy:**
- Creo billeteras digitales donde los usuarios cargaban saldo en Bs
- Recibia el dinero del comprador, lo retenia, y luego pagaba al comercio
- Operaba como ITFB sin licencia de Sudeban (viola art. 15 Ley de Bancos)

**Reglas para Zonix Eats (OBLIGATORIAS):**
1. NUNCA recibir, retener ni redistribuir dinero del comprador
2. El pago siempre va DIRECTO del buyer al commerce
3. Zonix Eats es un CONECTOR PURO (conecta roles), NO un procesador de pagos
4. NUNCA crear wallets/billeteras dentro de la app
5. Zonix Eats solo cobra MEMBRESIA FIJA (suscripcion) a Commerce y Delivery Company. NO cobra comision % sobre ventas. La membresia se paga aparte del flujo de ordenes
6. Si se integra una pasarela automatizada (C2P, Binance API), el commerce es el merchant de la pasarela, no Zonix
7. La Delivery Company es una empresa INDEPENDIENTE que se registra en la plataforma. Zonix solo conecta, no gestiona sus operaciones

**Regulacion relevante:**
- **Sudeban:** Licencia ITFB obligatoria para cualquier empresa que procese pagos como intermediario (Resolucion Fintech 001.21)
- **Sunacrip:** Registro obligatorio para operar con criptoactivos (RISEC). Si Zonix NO procesa cripto directamente (solo muestra QR del commerce), no necesita registro
- Los bancos deben verificar que empresas con servicios de pago tengan autorizacion de Sudeban

---

## Modelo de Flujo de Pago (Seguro)

```
Buyer ---(paga directo)---> Commerce        (pago de la orden)
Commerce ---(paga directo)---> Delivery     (delivery_fee)
Commerce ---(membresia fija)---> Zonix Eats (suscripcion mensual, aparte)
Delivery Company ---(membresia fija)---> Zonix Eats (suscripcion mensual, aparte)
```

**Zonix Eats NUNCA toca el dinero de la transaccion entre buyer y commerce.**
Zonix solo cobra membresia fija por suscripcion. No cobra comision % sobre ventas.
Las Delivery Companies son empresas independientes; Zonix solo las conecta con commerces.

---

## Inventario de Metodos de Pago en Venezuela

### TIER 1 — Imprescindibles (cubren ~95% del mercado)

#### 1. Pago Movil P2P (Persona a Persona)

- **Que es:** Sistema de pago interbancario del BCV. Buyer envia a telefono/cedula/banco del commerce.
- **Permiso para Zonix:** NINGUNO. El commerce ya tiene su cuenta bancaria.
- **Comision:** ~0.30% (la cobra el banco al receptor, no a Zonix).
- **Costo para Zonix:** $0 — Solo UI para mostrar datos del commerce.
- **Limite diario:** 6,000 a 20,000 Bs segun banco.
- **Flujo:** Buyer ve datos del commerce -> paga desde su banco -> sube comprobante -> commerce valida.
- **Estado en Zonix:** YA IMPLEMENTADO como tipo `mobile_payment`.
- **Bancos habilitados:** TODOS los bancos del sistema financiero venezolano.
- **NFC (novedad 2025):** BDV, BNC, Bancaribe y Bancamiga permiten pago movil NFC (solo fisico, no aplica a app).
- **En divisas:** Algunos bancos ya permiten pago movil desde cuentas en dolares.

#### 2. Transferencia Bancaria

- **Permiso para Zonix:** NINGUNO.
- **Comision:** $0 entre mismo banco; variable interbancario.
- **Costo para Zonix:** $0.
- **Flujo:** Datos de cuenta del commerce -> buyer transfiere -> sube comprobante.
- **Estado en Zonix:** YA IMPLEMENTADO como tipo `bank_transfer`.
- **Monedas:** VES y USD (cuentas en divisas disponibles en BDV, Banesco, Mercantil, Provincial, Bancaribe, BNC, Bancamiga, BOD, Exterior, Banplus, BFC, Venezolano de Credito).

#### 3. Efectivo (Dolares, Euros, Bolivares)

- **Permiso para Zonix:** NINGUNO.
- **Comision:** $0.
- **Flujo:** Buyer selecciona efectivo -> delivery cobra al entregar -> confirma en app.
- **Estado en Zonix:** YA IMPLEMENTADO como tipo `cash`.
- **Monedas comunes:** USD (dominante), EUR, VES.

#### 4. Zelle

- **Que es:** Plataforma de pagos instantaneos en USD entre cuentas bancarias de EE.UU.
- **Permiso para Zonix:** NINGUNO (buyer paga directo al commerce).
- **Comision:** 0%.
- **Costo para Zonix:** $0.
- **Riesgo:** Zelle puede bloquear cuentas por uso comercial (viola T&C — esta diseñado para "amigos y familiares"). El riesgo lo asume el COMMERCE (es su cuenta Zelle), no Zonix.
- **Requisito del commerce:** Cuenta bancaria activa en EE.UU.
- **Flujo:** Commerce publica email/telefono Zelle -> buyer paga -> sube comprobante.
- **Estado en Zonix:** NO existe en el enum. AGREGAR como tipo `zelle`.
- **Referencia de mercado:** Yummy lo acepta, PedidosYa lo acepta. Es el metodo #1 para pagos en dolares en Venezuela.
- **Alternativa legal:** **Pipol Pay** (Facebank) — comision 1-3%, diseñado para comercios, conecta 10,000+ instituciones financieras US. App en Google Play e iOS.

### TIER 2 — Muy importantes (crecimiento rapido)

#### 5. Binance Pay / USDT

- **Que es:** Pago con cripto (USDT principalmente) via app de Binance.
- **Permiso para Zonix:** NINGUNO si el commerce tiene su cuenta Binance y recibe directo. Registro Sunacrip solo si Zonix procesa cripto como intermediario.
- **Comision directa:** 0% entre usuarios Binance.
- **Comision via gateway:** 0.5-1% (NOWPayments), variable (Crixto).
- **Alianza clave en VE:** Crixto (registrada ante Sunacrip) — convierte USDT a bolivares en tiempo real.
- **Estado en Zonix:** NO existe en el enum. AGREGAR como tipo `binance_pay`.

**Niveles de integracion (de menos a mas complejo):**

| Nivel | Descripcion | Costo | Licencia |
|-------|-------------|-------|----------|
| 0 - Manual | Commerce publica su QR/ID Binance. Buyer paga y sube comprobante. | $0 | Ninguna |
| 1 - Crixto | Crixto como intermediario licenciado. Commerce recibe en Bs. | Negociable | Crixto tiene licencia (Zonix no necesita) |
| 2 - NOWPayments | Gateway internacional. 350+ criptos. API REST + webhooks. | 0.5-1% por tx | Evaluar legalidad en VE |
| 3 - Binance Pay API | SDK Android/iOS. HMAC-SHA512. Webhooks. | Negociable | Cuenta merchant Binance aprobada |

**Endpoints Binance Pay API (referencia):**
- `POST /binancepay/openapi/v3/order` — Crear orden
- `POST /binancepay/openapi/payout/transfer` — Transferencias
- Webhooks para notificaciones de estado
- Autenticacion: HMAC-SHA512, certSn, merchantId, noncestr, timestamp

**Recomendacion:** Empezar con Nivel 0 (manual, $0, legal). Evolucionar a Nivel 1 o 2 cuando el volumen lo justifique.

#### 6. Pago Movil C2P (Comercio a Persona)

- **Que es:** Pago movil con OTP automatizado, diseñado para ecommerce.
- **Permiso para Zonix:** El COMMERCE necesita cuenta juridica en banco que soporte C2P.
- **Comision:** Variable — CrediCard: 1.45-10%, otros negociables.
- **Bancos receptores:** BNC, Mercantil, Banco del Tesoro, Venezolano de Credito, R4.
- **Flujo:** Buyer ingresa tel/cedula/banco -> recibe OTP -> ingresa OTP -> pago confirmado automaticamente.
- **Valor:** Es el UNICO metodo movil con confirmacion automatica (sin comprobante manual). Elimina fraude.
- **Estado en Zonix:** NO implementado. Considerar tipo `mobile_payment` con subtipo C2P o nuevo tipo `mobile_payment_c2p`.

**Pasarelas para integrar C2P:**

| Pasarela | Metodos | Comision | API | Notas |
|----------|---------|----------|-----|-------|
| Ramblay (VesVank) | C2P (VES) + Binance Pay (USDT) | No publica (contactar) | REST + webhooks + SDK (en desarrollo) | Panel unificado, payment links |
| Cujiware | C2P + Pago Movil BDV | No publica | Documentada, plugins WooCommerce | Docs en docs.cujiware.com |
| CrediCardPagos | TDC/TDD + Pago Movil | 1.45-10% | API v1.0.16 documentada | Liquidacion en 30 min |
| Boton BDV | TDC/TDD + transferencias | Variable | Requiere afiliacion BDV | Solo clientes BDV |
| API Mercantil | Visa/MC nacionales e internacionales | Variable | Soporte tecnico | Enterprise |

### TIER 3 — Complementarios

#### 7. Tarjetas de Credito (TDC)

- **Permiso:** Afiliacion del commerce a pasarela (CrediCard, Mercantil API, Boton BDV).
- **Comision:** 1.45-4% tipicamente.
- **Marcas en VE:** Visa, Mastercard.
- **Limites:** $150-400 USD equivalente (suficiente para comida rapida $5-30).
- **Bancos principales:** BDV, Banesco (Visa/MC Clasica a Black), Mercantil, Provincial, Bancaribe, BNC.
- **Realidad:** Pocos venezolanos tienen TDC activas.
- **Estado en Zonix:** Tipo `card` ya existe en enum.

#### 8. Tarjetas de Debito Mastercard (Online)

- **Novedad 2025-2026:** Mastercard esta migrando Maestro -> Debito Mastercard (permite compras online).
- **Bancos activos:** Bancaribe (MC Debit, 1.5% comision internacional), BNC (MC Debit + moneda extranjera), Bancamiga (MC Debit internacional), Mercantil (multimoneda).
- **En migracion:** BDV, Banesco, Provincial (esperado 2026-2027).
- **Estado en Zonix:** Cubierto por tipo `card`.

#### 9. Billeteras Digitales

| Plataforma | Tipo | Moneda | API/Integracion | Relevancia |
|------------|------|--------|-----------------|------------|
| Zinli | Billetera + Visa virtual | USD | Via red Visa (no API directa para merchants) | MEDIA — popular para ahorrar en $ |
| Pipol Pay | Alt. Zelle | USD | Facebank, 10K+ instituciones | MEDIA — emergente |
| Crixto/CrixtoPay | Cripto -> Bs | USDT/Bs | Binance Pay + POS | ALTA como gateway, no como wallet |
| Reserve | Billetera digital | USD | No documentada para merchants | BAJA — puede cerrar cuentas sin aviso |
| Belo | Neobanco | USD/cripto | Nuevo en VE (feb 2026) | BAJA — muy nuevo |

### TIER 4 — No recomendados / No disponibles

#### 10. PayPal

- **Estado en Venezuela:** NO opera oficialmente. No vincula bancos VE.
- **Riesgos:** Congelamiento 21 dias, suspension permanente, IPs venezolanas flaggeadas.
- **Comision de cambio:** 15-18% via AirTM.
- **Recomendacion:** Dejarlo como opcion `paypal` en el enum para nicho. NO invertir en integracion. NO priorizar.

#### 11. Stripe

- **Estado en Venezuela:** NO DISPONIBLE. No opera en el pais.
- **Workaround:** LLC en EE.UU. ($200-500 + mantenimiento anual). No tiene sentido para MVP en VE.
- **Recomendacion:** QUITAR del enum. No genera mas que confusion.

#### 12. MercadoPago

- **Estado en Venezuela:** NO OPERA en el pais.
- **Recomendacion:** QUITAR del enum.

---

## Competencia: Que Aceptan las Apps Similares en VE

| App | Metodos |
|-----|---------|
| **Yummy Delivery** | Zelle, Banesco Panama, bolivares (transferencia), PayPal, pesos, efectivo ($, EUR) |
| **PedidosYa** | Zelle, PedidosYa Pay (wallet propia — riesgo Sudeban), pago movil |

Con pago movil + Zelle + Binance Pay + efectivo + transferencia, Zonix Eats cubriria el 99% del mercado y superaria a Yummy en opciones digitales/cripto.

---

## Cambios Tecnicos Propuestos

### 1. Actualizar enum de `payment_methods`

**Quitar:** `stripe`, `mercadopago`
**Agregar:** `zelle`, `binance_pay`
**Mantener:** `card`, `mobile_payment`, `cash`, `paypal`, `digital_wallet`, `bank_transfer`, `other`

Nuevo enum: `'mobile_payment', 'bank_transfer', 'cash', 'zelle', 'binance_pay', 'card', 'digital_wallet', 'paypal', 'other'`

### 2. Campos nuevos en `payment_methods`

- `currency` ENUM('VES', 'USD', 'EUR', 'USDT') DEFAULT 'VES'
- Verificar que `owner_name` y `owner_id` esten en la migracion (estan en seeder pero puede faltar en schema)

### 3. Campos nuevos en `orders`

- `payment_currency` ENUM('VES', 'USD', 'EUR', 'USDT') — En que moneda pago el buyer
- `exchange_rate` DECIMAL(16,4) — Tasa usada al momento del pago (referencia)

### 4. Campo nuevo en `order_payments` (comprobantes)

- `reference_number` STRING UNIQUE NULLABLE — Numero de referencia del pago (antifraude: impide reutilizar comprobantes)

### 5. BanksSeeder: Limpiar

- Quitar duplicados: Banco Provincial (0108 x2), Venezolano de Credito (0104 x2)
- Agregar faltantes: Bancamiga (0172), Banplus (0174)
- Quitar bancos inexistentes/cerrados: Federal (0121), Confederado (0129)
- Verificar codigo BNC: el seeder dice 0191 pero el codigo oficial es 0191 (correcto)

### 6. Frontend

- Agregar tipos `zelle` y `binance_pay` al formulario de metodos de pago del commerce
- Campos por tipo: email/telefono para Zelle, QR/ID para Binance
- Iconos apropiados en el checkout del buyer
- Selector de moneda si el commerce acepta multiples monedas

---

## Cosas Fundamentales No Mencionadas Originalmente

### Multi-moneda

Venezuela opera en 3 monedas: VES, USD, USDT. La tabla `payment_methods` no tiene campo `currency`. El commerce debe poder indicar en que moneda acepta cada metodo.

### Cobro de Membresia de Zonix

Zonix solo cobra membresia fija (suscripcion mensual) a Commerce y Delivery Company. NO cobra comision % sobre ventas. Se necesita un modulo de suscripciones/facturacion para gestionar los cobros de membresia (aparte del flujo de ordenes).

### Fraude de Comprobantes

El flujo actual de "subir comprobante" es vulnerable a screenshots editados, comprobantes reutilizados y montos alterados. Agregar `reference_number` unico y validar que no exista duplicado en el sistema.

### Timeout por Metodo de Pago

5 minutos puede ser insuficiente para transferencia bancaria interbancaria (15-30 min). Considerar timeout configurable por tipo de metodo.

### Exchange Rate

Si el buyer paga en USDT/USD y el commerce tiene precios en Bs (o viceversa), quien fija la tasa? Propuesta: el commerce define su tasa o usa BCV oficial. Esto es configuracion del commerce, no de Zonix.

### Delivery Fee en Multi-moneda

Si buyer paga en USDT y delivery cobra en Bs, el commerce absorbe el spread cambiario (recibe el pago completo y paga al delivery en la moneda que este acepte).

---

## Fases de Implementacion

| Fase | Alcance | Estimacion | Costo externo |
|------|---------|------------|---------------|
| 0 - Limpieza | Enum, campos currency, BanksSeeder, reference_number | 1-2 dias | $0 |
| 1 - Zelle + Binance | Tipos nuevos en backend/frontend, flujo comprobante | 2-3 dias | $0 |
| 2 - Multi-moneda | payment_currency en orders, selector, tasa de cambio | 2-3 dias | $0 |
| 3 - Automatizacion | Evaluar Ramblay/CrediCard (C2P) y NOWPayments/Binance API | Variable | Comisiones de pasarela |

**Con Fases 0-1 se cubre el 99% del mercado venezolano con $0 en costos.**

---

## Contactos / URLs de Referencia

- **Ramblay:** ramblay.com — API C2P + Binance Pay
- **Cujiware:** docs.cujiware.com/c2p — API C2P documentada
- **CrediCardPagos:** Guia integracion API v1.0.16
- **Crixto:** crixto.com — Cripto/POS VE (registrada Sunacrip)
- **NOWPayments:** nowpayments.io — Gateway cripto 0.5-1%
- **Binance Pay API:** developers.binance.com/docs/binance-pay
- **Pipol Pay:** App Facebank (alternativa a Zelle)
- **Sunacrip:** sunacrip.gob.ve — Registro criptoactivos (RISEC)
- **Sudeban:** Resolucion Fintech 001.21 — Licencia ITFB

---

**Ultima actualizacion:** 27 Marzo 2026
