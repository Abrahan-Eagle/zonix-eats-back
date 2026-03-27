# Guia Completa: Todo lo que Necesitas para Lanzar Zonix Eats en Venezuela

> Documento de referencia personal — 27 Marzo 2026
> Escrito para el lider del proyecto, no para la IA.

---

## Que es Zonix Eats (en resumen)

Zonix Eats es una app de marketplace de comida rapida. Conecta a compradores con restaurantes/comercios de comida y con empresas de delivery. Zonix no vende comida, no hace entregas, no maneja dinero de nadie. Solo conecta a las partes y cobra una membresia mensual fija a los comercios y a las empresas de delivery por usar la plataforma.

**Roles en la app:**
- **Buyer** (comprador): Pide comida y paga directo al comercio
- **Commerce** (restaurante/negocio de comida): Prepara el pedido, recibe el pago, paga al delivery
- **Delivery Company** (empresa de delivery): Empresa independiente que tiene sus propios repartidores
- **Delivery Agent** (repartidor): Trabaja para una Delivery Company
- **Delivery** (repartidor independiente): Trabaja por su cuenta sin empresa
- **Admin**: Administrador de la plataforma

**Como gana dinero Zonix Eats:**
- Membresia mensual fija que pagan los commerces
- Membresia mensual fija que pagan las delivery companies
- NO cobra comision sobre las ventas
- NO toca el dinero de las transacciones de comida

---

# PARTE 1: METODOS DE PAGO EN VENEZUELA

## La leccion mas importante: el caso Yummy

Antes de hablar de cualquier metodo de pago, tienes que saber esto:

**Yummy (la app de delivery mas grande de Venezuela) fue sancionada por la Sudeban** (Superintendencia de Bancos) en septiembre de 2022. Le prohibieron abrir cuentas bancarias y recibir transferencias en bolivares. Quedaron casi paralizados.

**Que hizo mal Yummy:**
- Creo billeteras digitales dentro de la app donde los usuarios cargaban saldo en bolivares
- Recibia el dinero del comprador, lo guardaba, y luego le pagaba al comercio
- Basicamente estaba actuando como un banco sin tener licencia

**Que ley violaron:** La Resolucion Fintech 001.21 de la Sudeban. Dice que cualquier empresa que reciba, retenga o redistribuya dinero de terceros necesita una licencia especial llamada ITFB (Institucion de Tecnologia Financiera del Sector Bancario). Obtener esa licencia es caro, lento y complicado.

**Como evita Zonix Eats este problema:**
- Zonix NUNCA toca el dinero. El comprador le paga directamente al comercio.
- Zonix no tiene billeteras, no retiene fondos, no redistribuye nada.
- Zonix solo muestra los datos de pago del comercio (su telefono de pago movil, su cuenta bancaria, su QR de Binance) y el comprador paga por su cuenta.
- La membresia que cobra Zonix es un servicio B2B aparte, como pagar Netflix o un hosting. No tiene nada que ver con la transaccion de la comida.

**Esto hace que Zonix Eats NO necesite licencia de Sudeban.** Es legalmente seguro.

---

## Como fluye el dinero en Zonix Eats

```
COMPRADOR ----paga directo----> COMERCIO (por la comida)
COMERCIO ----paga directo----> REPARTIDOR (por el delivery)
COMERCIO ----paga aparte----> ZONIX EATS (membresia mensual)
EMPRESA DE DELIVERY ----paga aparte----> ZONIX EATS (membresia mensual)
```

Zonix nunca toca el dinero de la comida. Punto.

---

## Los metodos de pago que existen en Venezuela (detallados)

### 1. PAGO MOVIL — El metodo #1 del pais

**Que es:** Un sistema del Banco Central de Venezuela que permite enviar dinero instantaneamente entre cualquier banco del pais usando solo el telefono, la cedula y el banco del receptor.

**Como funciona en Zonix Eats:**
1. El comercio registra en la app sus datos de pago movil (telefono, cedula, banco)
2. Cuando el comprador hace un pedido y elige "pago movil", la app le muestra esos datos
3. El comprador abre su propia app bancaria, hace el pago movil al comercio
4. El comprador sube la captura del comprobante en la app
5. El comercio revisa y valida el comprobante

**Datos importantes:**
- Funciona con TODOS los bancos de Venezuela (mas de 25)
- Limite diario: entre 6,000 y 20,000 bolivares dependiendo del banco
- Comision: aproximadamente 0.30% (la cobra el banco, no Zonix)
- Ya hay pago movil en dolares en algunos bancos (cuentas en divisas)
- Novedad 2025: BDV, BNC, Bancaribe y Bancamiga ya tienen pago movil NFC (acercar el telefono), pero eso es solo para pago presencial

**Permiso que necesita Zonix:** Ninguno. Solo muestra datos.
**Costo para Zonix:** $0

**Modalidad avanzada — C2P (Comercio a Persona):**
Existe una version especial del pago movil llamada C2P que esta disenada para ecommerce. Funciona asi:
1. El comprador ingresa su telefono, cedula y banco
2. Le llega un codigo OTP por SMS
3. Ingresa el codigo y el pago se confirma automaticamente

La ventaja es que NO necesita comprobante, se valida solo. Pero requiere que el comercio tenga cuenta juridica en un banco que soporte C2P (BNC, Mercantil, Banco del Tesoro, Venezolano de Credito) y se integre con una pasarela como Ramblay, Cujiware o CrediCard. Las comisiones van de 1.45% a 10%. Esto es para el futuro, cuando haya volumen.

---

### 2. TRANSFERENCIA BANCARIA — El clasico

**Que es:** Transferencia normal de un banco a otro, tanto en bolivares como en dolares.

**Como funciona en Zonix Eats:** Igual que pago movil. El comercio registra su cuenta, el comprador transfiere y sube comprobante.

**Datos importantes:**
- Gratis entre cuentas del mismo banco, variable entre bancos diferentes
- Ya existen cuentas en dolares en: BDV, Banesco ("Cuenta Verde"), Mercantil, Provincial, Bancaribe, BNC, Bancamiga, BOD, Exterior, Banplus, BFC, Venezolano de Credito
- Las transferencias interbancarias pueden tardar 15-30 minutos (ojo con el timeout de 5 minutos que tiene la app actualmente)

**Permiso que necesita Zonix:** Ninguno.
**Costo para Zonix:** $0

---

### 3. EFECTIVO — Dolares y bolivares en mano

**Que es:** El comprador paga en efectivo al repartidor cuando le llega la comida.

**Monedas comunes:** Dolares americanos (el mas usado), euros, bolivares.

**Como funciona en Zonix Eats:** El comprador elige "efectivo", el repartidor cobra al entregar y confirma en la app.

**Permiso que necesita Zonix:** Ninguno.
**Costo para Zonix:** $0

---

### 4. ZELLE — El dolar digital de Venezuela

**Que es:** Una plataforma de pagos instantaneos en dolares entre cuentas bancarias de Estados Unidos. En Venezuela se usa masivamente para pagos en dolares aunque tecnicamente no esta pensada para eso.

**La realidad:** Zelle es el metodo #1 para pagos en dolares en Venezuela. Lo acepta Yummy, lo acepta PedidosYa, lo acepta practicamente todo el comercio. Es instantaneo y no cobra comision (0%).

**El riesgo:** Zelle esta disenado para pagos entre "amigos y familiares", NO para uso comercial. Si Zelle detecta que una cuenta se usa comercialmente, puede bloquearla. Esto le pasa al COMERCIO (es su cuenta Zelle), no a Zonix. Zonix solo muestra los datos.

**Requisito del comercio:** Tener una cuenta bancaria activa en Estados Unidos (Wells Fargo, Chase, Bank of America, etc).

**Como funciona en Zonix Eats:**
1. El comercio registra su email/telefono Zelle en la app
2. El comprador hace el Zelle y sube comprobante
3. El comercio valida

**Alternativa mas segura: Pipol Pay**
Es una app de Facebank que funciona igual que Zelle pero esta disenada para uso comercial. Cobra entre 1% y 3% de comision. Conecta mas de 10,000 instituciones financieras estadounidenses. Todavia no tiene la adopcion masiva de Zelle pero es legal.

**Permiso que necesita Zonix:** Ninguno.
**Costo para Zonix:** $0

---

### 5. BINANCE PAY / USDT — Criptomonedas

**Que es:** Pago con USDT (una criptomoneda que vale igual que el dolar) a traves de la app de Binance. Venezuela es uno de los paises con mayor adopcion de criptomonedas del mundo.

**Como funciona hoy en Venezuela:**
- Desde agosto 2025, Binance Pay opera oficialmente en Venezuela a traves de Crixto (una fintech local registrada ante Sunacrip)
- El usuario escanea un QR con su app de Binance, paga en USDT, y el comercio puede recibir en bolivares (via Crixto) o en USDT directamente
- Entre usuarios de Binance la comision es 0%

**Como funcionaria en Zonix Eats (forma simple):**
1. El comercio sube su QR de Binance Pay o su ID de Binance a la app
2. El comprador escanea o paga desde su Binance
3. Sube screenshot como comprobante
4. El comercio valida

**Formas mas avanzadas (para el futuro):**
- **Via Crixto:** Crixto actua como intermediario. El comercio recibe en bolivares. Crixto tiene licencia de Sunacrip. Comision negociable.
- **Via NOWPayments:** Un gateway internacional que acepta 350+ criptomonedas. Comision: 0.5% a 1%. Tiene API con webhooks para confirmar pagos automaticamente.
- **Via Binance Pay API directamente:** SDK para Android e iOS. Crear orden, generar QR, recibir confirmacion automatica via webhook. Requiere cuenta merchant aprobada por Binance.

**Regulacion:** Si Zonix solo muestra el QR del comercio y no procesa cripto, NO necesita registro en Sunacrip. Si algun dia Zonix quiere integrar un gateway cripto como intermediario, ahi si necesitaria registrarse.

**Permiso que necesita Zonix (forma simple):** Ninguno.
**Costo para Zonix:** $0

---

### 6. TARJETAS DE CREDITO — Limitadas pero utiles

**Marcas disponibles en Venezuela:** Visa y Mastercard.

**Bancos principales:**
- Banesco: Visa y Mastercard (Clasica, Dorada, Platinum, Signature, Black)
- BDV: Visa y Mastercard (limites de $150 a $400)
- Mercantil, Provincial, Bancaribe, BNC: Visa y/o Mastercard

**La realidad:** Pocos venezolanos tienen tarjeta de credito activa y los limites son bajos ($150-$400 equivalente). Pero para pedidos de comida rapida ($5-$30), es mas que suficiente.

**Como se integraria:** A traves de una pasarela de pagos como CrediCard (comision 1.45%-10%), API de Mercantil, o Boton de pago BDV. El COMERCIO necesita afiliarse a la pasarela con su cuenta. Zonix integraria el formulario de pago en la app.

**Para el futuro, no para ahora.**

---

### 7. TARJETAS DE DEBITO MASTERCARD — Novedad importante

Mastercard esta migrando todas las tarjetas Maestro (las tipicas de debito) a **Debito Mastercard**, que SI permite compras por internet. Esto es nuevo y esta cambiando las reglas del juego.

**Bancos que ya la tienen:**
- Bancaribe: MC Debit (compras online, 1.5% comision en compras internacionales)
- BNC: MC Debit + moneda extranjera
- Bancamiga: MC Debit internacional
- Mercantil: Debito multimoneda

**En proceso de migracion:** BDV, Banesco, Provincial (esperado entre 2026 y 2027)

Cuando la migracion se complete, practicamente todo venezolano con cuenta bancaria podra pagar online. Esto abre la puerta a integrar pasarelas de tarjetas en el futuro.

---

### 8. BILLETERAS DIGITALES

| Plataforma | Que es | Moneda | Util para Zonix? |
|------------|--------|--------|-----------------|
| **Zinli** | Billetera + tarjeta Visa virtual (Panama) | USD | Media. Popular para ahorrar en $. Los usuarios pueden pagar donde acepten Visa |
| **Pipol Pay** | Alternativa a Zelle (Facebank) | USD | Media. Emergente, legal para comercios |
| **Reserve** | Billetera digital | USD | Baja. Puede cerrar cuentas sin aviso |
| **Belo** | Neobanco (nuevo en VE, feb 2026) | USD/cripto | Baja. Muy nuevo |
| **Crixto/CrixtoPay** | Puente cripto-bolivares | USDT/VES | Alta como gateway de cripto |

---

### 9. PAYPAL — No recomendado

**Estado:** NO opera oficialmente en Venezuela. No permite vincular bancos venezolanos. Los retiros directos no son posibles.

**Riesgos:** Las cuentas PayPal usadas desde Venezuela pueden ser congeladas 21 dias o suspendidas permanentemente. Las IPs venezolanas estan flaggeadas.

**Para cambiar PayPal a bolivares:** Se usa AirTM o Binance P2P, con comisiones del 15-18%.

**Recomendacion:** No invertir en esto. Dejarlo como opcion manual para el nicho de gente que ya tiene PayPal verificado, pero no priorizarlo.

---

### 10. STRIPE — No disponible

**Stripe NO opera en Venezuela.** Punto. La unica forma de usarlo seria crear una LLC en Estados Unidos ($200-500 + mantenimiento anual), lo cual no tiene sentido ahora.

---

### 11. MERCADOPAGO — No disponible

**MercadoPago NO opera en Venezuela.** No hay nada que hacer aqui.

---

## Que aceptan las apps de la competencia

| App | Metodos de pago |
|-----|-----------------|
| **Yummy** | Zelle, Banesco Panama (transferencia), bolivares (transferencia), PayPal, pesos colombianos, efectivo ($, EUR) |
| **PedidosYa** | Zelle, PedidosYa Pay (billetera propia — riesgo Sudeban), pago movil |

**Zonix Eats con pago movil + Zelle + Binance Pay + efectivo + transferencia cubriria el 99% del mercado venezolano.** Y con Binance Pay, estaria ofreciendo algo que Yummy NO tiene.

---

## Resumen de prioridades de metodos de pago

**Implementar ya (cubren el 99% del mercado, $0 de costo):**
1. Pago Movil P2P — ya funciona
2. Transferencia bancaria — ya funciona
3. Efectivo — ya funciona
4. Zelle — agregar (mismo flujo de comprobante)
5. Binance Pay — agregar (mismo flujo de comprobante)

**Para cuando haya volumen:**
6. Pago Movil C2P (automatizado, sin comprobante)
7. Tarjetas de credito/debito (via pasarela)

**No priorizar:**
8. PayPal (riesgoso)
9. Stripe (no existe en VE)
10. MercadoPago (no existe en VE)

---

## Bancos de Venezuela (lista completa con codigos)

Esto es importante porque el pago movil y las transferencias usan codigos bancarios.

**Bancos publicos:**
| Banco | Codigo |
|-------|--------|
| Banco de Venezuela (BDV) | 0102 |
| Banco del Tesoro | 0163 |
| Banco Bicentenario | 0175 |
| Banco Agricola de Venezuela | 0168 |
| BANFANB (Fuerza Armada) | 0177 |
| Banco Industrial de Venezuela | 0106 |

**Bancos privados:**
| Banco | Codigo |
|-------|--------|
| Banesco | 0134 |
| Banco Mercantil | 0105 |
| BBVA Provincial | 0108 |
| Bancaribe | 0114 |
| BNC (Banco Nacional de Credito) | 0191 |
| BOD (Banco Occidental de Descuento) | 0116 |
| Banco Exterior | 0113 |
| Bancamiga | 0172 |
| Banplus | 0174 |
| Venezolano de Credito | 0104 |
| BFC (Fondo Comun) | 0151 |
| 100% Banco | 0156 |
| Banco del Sur (DelSur) | 0157 |
| Banco Activo | 0166 |
| Banco Caroni | 0128 |
| Banco Sofitasa | 0137 |
| Banco Plaza | 0138 |
| Bancrecer | 0164 |
| Mi Banco | 0169 |

**Nota:** La app ya tiene un seeder de bancos pero tiene duplicados y le faltan Bancamiga y Banplus. Tambien incluye bancos que ya no existen (Federal, Confederado). Hay que limpiarlo.

---

## Cosas importantes sobre pagos que no preguntaste pero debes saber

### Multi-moneda
Venezuela opera con 3 monedas de facto: bolivares (VES), dolares (USD) y USDT (cripto). Los comercios necesitan poder indicar en que moneda aceptan cada metodo de pago. Un restaurante podria aceptar pago movil en bolivares, Zelle en dolares y Binance en USDT, todo al mismo tiempo.

### Fraude de comprobantes
El flujo actual de "subir captura de pantalla como comprobante" es vulnerable. La gente puede editar screenshots, reutilizar el mismo comprobante para dos pedidos diferentes, o alterar montos. Solucion: agregar un campo de "numero de referencia" unico y validar que no se repita.

### El timeout de 5 minutos puede ser muy corto
Actualmente la app da 5 minutos para subir comprobante. Para pago movil esta bien, pero una transferencia interbancaria puede tardar 15-30 minutos. Habria que hacer el timeout configurable segun el metodo de pago.

### Tasa de cambio
Si un comercio tiene precios en dolares pero el comprador paga en bolivares, alguien tiene que fijar la tasa. La propuesta es que cada comercio defina su propia tasa (BCV oficial, paralelo, la que ellos quieran). Eso no es responsabilidad de Zonix.

---

# PARTE 2: REQUISITOS LEGALES Y REGULATORIOS

## 1. Constituir la empresa

Para operar formalmente necesitas una empresa registrada. Lo recomendado es una **C.A. (Compania Anonima)** o **S.R.L. (Sociedad de Responsabilidad Limitada)**.

**Pasos y costos aproximados:**

| Paso | Que es | Costo |
|------|--------|-------|
| Reserva de nombre | Ir al Registro Mercantil (SAREN) y reservar "Zonix Eats" como nombre comercial | Variable |
| Documento constitutivo | Un abogado redacta el acta de constitucion de la empresa | $200-500 |
| Registro mercantil | Inscribir la empresa formalmente | $300-800 |
| Legalizar libros contables | Diario, Mayor, Inventarios | $50-150 |
| Obtener RIF | Numero fiscal ante SENIAT | Gratis |
| Licencia de Actividades Economicas | Permiso de la alcaldia para operar en tu municipio. En Valencia se tramita online via SIGAT | Variable |

**Dato util:** El Decreto 4.719 (2022) permite a nuevos emprendimientos registrados en el RNE (Registro Nacional de Emprendedores) no pagar la inscripcion en IVSS, INCES, FAOV y Ministerio del Trabajo si se formalizan dentro de 90 dias. Esto ahorra plata al inicio.

---

## 2. Impuestos y facturacion

### IVA (Impuesto al Valor Agregado)
- Tasa general: 16%
- Se declara mensualmente ante SENIAT (antes del dia 15 del mes siguiente)
- El IVA que pagas en tus compras/gastos se descuenta del IVA que cobras (credito fiscal)

### Factura digital — OBLIGATORIA DESDE EL 19 DE MARZO DE 2026

**Esto ya esta vigente.** La Providencia SNAT/2024/000102 hace obligatoria la factura digital para todo e-commerce, marketplace, app movil y venta online.

**Que tiene que incluir la factura:**
- RIF y datos del que emite y del que recibe
- Numero de Control del SENIAT
- Numeracion consecutiva
- Total, base imponible, monto de IVA desglosado
- Descripcion de lo que se vendio/presto
- Se puede enviar por email, WhatsApp o la plataforma

**Quien emite que factura:**
- El **comercio** le emite factura al comprador por la comida (ellos son los que venden)
- **Zonix Eats** le emite factura al comercio y a las delivery companies por la membresia (nosotros prestamos un servicio B2B)
- Zonix NO le emite factura al comprador por la comida (nosotros no vendemos comida)

**Que hacer:** Registrarse ante SENIAT como emisor de factura digital y usar un software compatible.

### ISLR (Impuesto Sobre la Renta)
- Declaracion anual
- Tarifa progresiva para empresas

### Impuestos municipales
- Impuesto sobre Actividades Economicas: porcentaje sobre ingresos brutos, varia por municipio

### Necesitas un contador
Alguien que maneje: declaracion mensual de IVA, declaracion anual de ISLR, contribuciones parafiscales, impuesto municipal, libros de compras y ventas, retenciones.

---

## 3. Obligaciones laborales (si tienes empleados)

Si Zonix Eats tiene equipo propio (programadores, soporte, admin, etc.), hay que inscribirlos y pagar:

| Concepto | Lo que paga la empresa | Lo que paga el empleado |
|----------|----------------------|------------------------|
| IVSS (Seguro Social) | 11% del sueldo | 4% del sueldo |
| FAOV (Vivienda) | 2% del sueldo | 1% del sueldo |
| INCES (Capacitacion, si hay 5+ empleados) | 2% de la nomina | 0.5% de la nomina |
| FONACIT (Ciencia y Tech) | 0.50% de ingresos brutos | No aplica |

### Los repartidores — El tema mas delicado

**ALERTA LEGAL:** La Ley del Trabajo en Venezuela (LOTTT) dice que si una persona presta servicios a cambio de pago y bajo dependencia, hay relacion laboral, AUNQUE NO HAYA CONTRATO ESCRITO. La ley PRESUME que existe relacion laboral.

**El riesgo:** Si Zonix controla horarios, zonas, uniformes o tarifas de los repartidores, un juez podria decir que son empleados de Zonix. Y entonces Zonix tendria que pagar seguro social, prestaciones, vacaciones, utilidades, etc. por CADA repartidor.

**Como lo manejan Yummy y PedidosYa:** Usan el modelo de "conductores/prestadores de servicio independientes". No son empleados. Yummy tiene ~12,000 de estos.

**Como debe manejarlo Zonix Eats:**
- Los repartidores son USUARIOS de la plataforma, no empleados
- Zonix no fija horarios: el repartidor entra y sale cuando quiere
- Zonix no impone zonas: el repartidor acepta o rechaza pedidos libremente
- Zonix no da uniformes obligatorios
- En los Terminos y Condiciones debe haber una clausula explicita de que NO existe relacion laboral

**Pero:** Este modelo no es 100% blindado. Un repartidor siempre podria demandar alegando relacion laboral. Es un riesgo que tienen TODAS las plataformas de delivery en Latinoamerica. Se recomienda tener asesoria legal especializada en esto.

**Ademas:** En Zonix Eats, los repartidores pueden pertenecer a una Delivery Company (empresa independiente). En ese caso, la relacion laboral es entre el repartidor y su empresa, no con Zonix. Zonix solo conecta.

---

## 4. Regulacion especifica de delivery

### Ipostel — Ya no aplica

En febrero 2024, Ipostel saco una regulacion que pedia $300 de licencia + pago anual de $240 + impuesto del 1% sobre envios. **Pero fue derogada 22 dias despues** (Gaceta Oficial 42.827). La presion del sector fue tal que el gobierno la echo para atras.

**Actualmente NO hay regulacion especifica para apps de delivery en Venezuela.** Pero podria surgir una nueva en cualquier momento.

### Sudeban — No aplica si no tocas el dinero

Ya explicado arriba. Zonix no necesita licencia porque no es intermediario de pagos.

### Sundde / Precios Justos

La Sundde esta siendo reemplazada por una nueva "Ley de Derechos Socioeconomicos" que amplia su alcance al e-commerce. Usa un modelo de "precios acordados" entre Estado, productores y comerciantes. Los comercios deben cumplir con precios justos, pero Zonix no fija precios (los pone el comercio). La membresia de Zonix podria estar sujeta a regulacion de precios de servicios.

### Sunacrip — Solo si tocas cripto directamente

Si Zonix solo muestra el QR de Binance del comercio, no necesita registrarse. Si algun dia integra un gateway cripto como intermediario, ahi si.

---

## 5. Permisos sanitarios — Los necesita el COMERCIO, no Zonix

Zonix es un marketplace. No cocina ni vende comida. Los permisos sanitarios son responsabilidad de cada restaurante/comercio que se registre en la plataforma.

**Lo que cada comercio deberia tener:**
- Permiso sanitario Tipo IV (SACS) — para expendio de alimentos, se renueva cada ano
- Certificado de manipulacion de alimentos
- Registro mercantil y RIF
- Licencia de actividades economicas municipal

**Recomendacion para Zonix:** En el registro del comercio (onboarding), pedir que suban foto/PDF de su permiso sanitario y RIF. Tecnicamente no es obligatorio para Zonix (es responsabilidad del comercio), pero protege la plataforma ante reclamos y genera confianza con los compradores.

---

## 6. Proteccion de datos personales — LEY NUEVA

En abril de 2025 Venezuela aprobo una **Ley de Proteccion de Datos Personales**. Esto es nuevo y aplica a TODAS las empresas privadas.

**Que obliga:**
- Tener una politica de privacidad clara y accesible
- Pedir consentimiento explicito para recoger datos
- Solo recoger los datos que realmente necesitas
- Encriptar datos sensibles
- Permitir que el usuario pida sus datos o elimine su cuenta
- Notificar si hay una fuga de datos
- No compartir datos con terceros sin permiso

**Datos que Zonix maneja:** Nombre, cedula, telefono, email, foto, direcciones, historial de pedidos, documentos (CI, RIF), datos de metodos de pago. Todo esto esta protegido por la ley.

**Lo bueno:** Zonix ya tiene implementado el "exportar datos personales" y usa encriptacion para tokens. Pero hay que revisar y actualizar la politica de privacidad.

---

## 7. Proteccion al consumidor

La Ley de Proteccion al Consumidor exige:
- Informacion clara de productos (precio, descripcion, foto)
- Terminos y condiciones claros (contrato de adhesion)
- Politica de devoluciones (que pasa si la comida llega mal)
- Canales de atencion de reclamos (Zonix ya tiene chat y sistema de disputas)
- Facturas

**Terminos y Condiciones — lo que deben incluir:**
- Que Zonix es un marketplace, NO vende comida
- Responsabilidades de cada parte
- Politica de cancelaciones y reembolsos
- Politica de privacidad
- Que Zonix NO es empleador de los repartidores
- Que Zonix NO es responsable de la calidad de la comida (es el comercio)
- Metodos de pago y como funciona el flujo
- Como se resuelven disputas
- Jurisdiccion: Venezuela

**Ley de Comercio Electronico — EN TRAMITE:**
En febrero 2025 se presento un proyecto de ley ante la Asamblea Nacional. Busca regular plataformas digitales, evitar especulacion, proteger compradores y vendedores. Todavia esta en primera discusion. No es ley pero hay que monitorear.

---

## 8. Registro de marca

### Registrar "Zonix Eats" ante el SAPI

| Dato | Detalle |
|------|---------|
| Organismo | SAPI (Servicio Autonomo de la Propiedad Intelectual) |
| Costo total | ~$410 |
| Cuanto tarda | 16 meses o mas |
| Cuanto dura | 15 anos (renovable) |
| Que protege | Nombre, logo, isotipo |

**Pasos:**
1. Busqueda de antecedentes (~$100, 5 dias) — ver si alguien mas ya registro algo parecido
2. Llenar la planilla FM-02, pegar timbres fiscales, adjuntar RIF y registro mercantil
3. Se publica en el Boletin de Propiedad Industrial (~4 meses de espera)
4. 30 dias para que alguien objete
5. Si nadie objeta, te dan el certificado

**Recomendacion:** Iniciar lo antes posible. 16 meses es mucho tiempo. Mientras tanto se puede usar la marca sin proteccion legal plena.

**Clases de Niza recomendadas:**
- Clase 35: Servicios de intermediacion comercial
- Clase 39: Servicios de entrega
- Clase 42: Software y SaaS
- Clase 43: Servicios de alimentacion

---

## 9. Seguros

### Para los repartidores
- **RCV (Responsabilidad Civil Vehiculos):** Obligatorio por ley para circular. Cuesta 15 EUR/ano para motos. Lo paga el repartidor (es su moto). Solo cubre danos a terceros, no al repartidor.
- **Seguro de accidentes:** Recomendado pero no obligatorio. Zonix podria ofrecerlo como beneficio y diferenciador frente a Yummy.

### Para la empresa
- **Responsabilidad civil general:** Recomendado. Cubre reclamos de terceros.
- **Seguro de cyberriesgos:** Recomendado. Proteccion ante hackeos o fugas de datos.
- **Poliza D&O (directores y oficiales):** Opcional. Solo si hay inversionistas.

---

## 10. Publicacion en tiendas de apps

### Google Play Store
- Venezuela SI esta soportado para registro de developer y merchant
- Costo: $25 (unico pago)
- Se pueden recibir ingresos via wire transfer (minimo $100)

### Apple App Store
- Costo: $99/ano
- Disponible para Venezuela pero Apple es menos claro sobre restricciones
- Alternativa: registrar la cuenta bajo una entidad en otro pais si es necesario

---

## 11. Infraestructura tecnologica

**No usar hosting venezolano para produccion.** La electricidad es inestable, el internet de CANTV es intermitente. Usar hosting en la nube (DigitalOcean, AWS, Hetzner).

**Considerar para la app:**
- Modo offline/cache para cuando el internet del usuario falla
- Compresion agresiva de imagenes (la conexion en VE es lenta)
- Timeouts generosos para conexiones lentas
- Notificaciones push como canal principal de comunicacion (ya esta con Firebase y Pusher)

---

## 12. Geopolitica y sanciones

Venezuela esta bajo sanciones de EE.UU. (OFAC). Esto afecta:
- Stripe no opera, PayPal es limitado
- Algunos servicios cloud podrian bloquear VE (la mayoria NO lo hacen)
- Dificultad para transferencias internacionales

**NO afecta (al momento de esta investigacion):** Google Play, Apple App Store, Firebase, Pusher, DigitalOcean. Pero hay que estar pendiente.

---

## 13. La competencia

| App | Presencia | Fortaleza | Debilidad |
|-----|-----------|-----------|-----------|
| **Yummy** | 11 estados, +2.5M usuarios, $69M en inversion | Base masiva | Sancion Sudeban, servicio irregular |
| **PedidosYa** | Nacional | Marca regional | Menos adaptada a VE |
| **Zonix Eats** | En desarrollo | Nicho comida rapida, Binance Pay, modelo limpio | Nuevo, sin base de usuarios |

**Diferenciadores de Zonix Eats:**
- Binance Pay / USDT (Yummy no lo tiene)
- Pago directo sin billeteras (sin riesgo Sudeban)
- Solo comida rapida (no intenta ser super-app)
- Membresia fija y transparente (sin comision oculta sobre ventas)

---

# PARTE 3: CHECKLIST DE LANZAMIENTO

## Obligatorio (antes de operar)

- [ ] Empresa constituida (Registro Mercantil + RIF)
- [ ] Licencia de Actividades Economicas (Alcaldia municipal)
- [ ] Registro ante SENIAT como emisor de factura digital
- [ ] Terminos y condiciones en la app
- [ ] Politica de privacidad actualizada (Ley de Datos Personales 2025)
- [ ] Contrato de afiliacion con commerces y delivery companies
- [ ] Sistema de facturacion para cobrar membresias (Zonix -> Commerce / Delivery Company)
- [ ] Cuenta de developer Google Play ($25) y/o Apple ($99/ano)
- [ ] Inscripcion patronal (IVSS, INCES, FAOV) si hay empleados

## Muy recomendado

- [ ] Registro de marca "Zonix Eats" ante SAPI (~$410, tarda 16 meses)
- [ ] Verificar permiso sanitario de cada comercio al registrarse
- [ ] Seguro de responsabilidad civil para la empresa
- [ ] Asesoria legal sobre la relacion con repartidores
- [ ] Contador para declaraciones fiscales

## A monitorear permanentemente

- [ ] Proyecto de Ley de Comercio Electronico (en tramite en la Asamblea Nacional)
- [ ] Nueva Ley de Derechos Socioeconomicos (reemplaza a Sundde)
- [ ] Posible nueva regulacion de delivery (la de Ipostel fue derogada pero puede volver)
- [ ] Sanciones internacionales OFAC y su impacto en servicios tecnologicos
- [ ] Implementacion de la Superintendencia de Datos Personales

---

## Contactos y URLs de referencia

| Recurso | URL / Referencia |
|---------|-----------------|
| Ramblay (pasarela C2P + Binance) | ramblay.com |
| Cujiware (pasarela C2P) | docs.cujiware.com/c2p |
| CrediCardPagos (TDC + pago movil) | Guia API v1.0.16 |
| Crixto (cripto en VE) | crixto.com |
| NOWPayments (gateway cripto) | nowpayments.io |
| Binance Pay API | developers.binance.com/docs/binance-pay |
| Pipol Pay (alternativa a Zelle) | App en Play Store / App Store |
| SAPI (registro de marca) | sapi.gob.ve |
| SENIAT (impuestos, factura digital) | seniat.gob.ve |
| SACS (permisos sanitarios) | sacs.gob.ve |
| Sudeban | sudeban.gob.ve |
| Sunacrip | sunacrip.gob.ve |
| Alcaldia Valencia (SIGAT) | alcaldiadevalencia.gob.ve |

---

**Ultima actualizacion:** 27 Marzo 2026
