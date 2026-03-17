# Flujo de pago y orden (paso a paso)

Flujo acordado para comprador y comercio: desde el carrito hasta que el comercio comienza a elaborar el pedido.

---

## 1. Comprador: ir a pagar

- El comprador está en **su carrito**.
- Toca el botón **“Ir a pagar”** (o similar) para pasar al checkout.

---

## 2. Comprador: verificar y confirmar

- En **checkout** el comprador:
  - Verifica una última vez su compra.
  - Elige si **recoge** o **delivery**.
  - Aplica **cupón** si tiene uno.
  - **Confirma el pedido**.

---

## 3. Pedido creado → directo al detalle del recibo

- Se crea la orden en estado **Pendiente de pago**.
- En lugar de varias pantallas, se muestra un **modal** “¡Pedido creado!” y se pasa **directo a la vista de detalle del recibo** (una sola pantalla de detalle).
- El usuario no tiene que tocar “Seguir mi pedido” en una pantalla intermedia; llega al detalle del recibo de inmediato.

---

## 4. Detalle del recibo: esperar al comercio

- El comprador está en la **vista detalle del recibo**.
- **Espera a que el comercio lea la orden** (productos, cantidades, notas).
- En esta etapa **aún no** aparece el botón “Subir comprobante”.

---

## 5. Comercio: revisar y aceptar para que el cliente pague

- El **comercio** ve los **productos** que pidió el usuario.
- Si **falta algún ingrediente** o hay duda, el comercio se comunica por **chat** con el cliente.
- Si **todo está bien**, el comercio **acepta la solicitud** (botón “Aprobar para pago”).
- A partir de ese momento el **cliente puede transferir o pagar** al comercio y subir el comprobante en la app.

---

## 6. Comprador: subir comprobante (solo cuando el comercio aceptó)

- En la **vista detalle del recibo**, cuando el comercio **ya aceptó**, **aparece el botón “Subir comprobante”** (o “Pagar” / “Subir comprobante de pago”).
- El comprador:
  - Toca el botón para **pagar / subir comprobante**.
  - Elige el **método de pago** (transferencia, pago móvil, etc.).
  - Ingresa en el input la **referencia de pago**.
  - Sube la **factura o comprobante** del pago (**imagen o PDF**).

---

## 7. Comercio: conciliar el pago

- Al comercio **le llega el aviso** de que el usuario **ya subió el comprobante** (no que canceló; es la notificación de pago subido).
- El comercio entra al **detalle de la orden** y ve:
  - Datos del usuario.
  - **Número de referencia**.
  - **Comprobante** (imagen o PDF).
- Si el comercio confirma que **todo está ok**, toca **Validar** (validar el pago).
- Si algo no cuadra, puede **Rechazar** e indicar el motivo.

---

## 8. Comercio: comenzar la elaboración

- Una vez el comercio **validó el pago**, la orden pasa a estado **Pagado**.
- El comercio **comienza la elaboración** del producto (cambia estado a “En preparación” o el que use la app).
- A partir de ahí sigue el flujo normal: preparación → envío/recogida → entregado.

---

## Resumen del orden

| Paso | Quién    | Acción |
|------|----------|--------|
| 1    | Comprador | Carrito → “Ir a pagar” |
| 2    | Comprador | Checkout: verificar, recoger/delivery, cupón, confirmar |
| 3    | App      | Pedido creado → modal “¡Pedido creado!” → **directo a detalle del recibo** |
| 4    | Comprador | En detalle del recibo, esperar a que el comercio lea la orden |
| 5    | Comercio | Ver productos, chat si falta algo, **aceptar** para que el cliente pueda pagar |
| 6    | Comprador | Cuando aparece el botón: **subir comprobante** (método, referencia, imagen o PDF) |
| 7    | Comercio | Ver datos, referencia y comprobante → **Validar** o Rechazar |
| 8    | Comercio | **Comenzar la elaboración** del producto |

---

**Última actualización:** Marzo 2026
