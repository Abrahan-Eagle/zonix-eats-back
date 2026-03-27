# Requisitos para Operar un Marketplace de Comida Rapida en Venezuela

> **Fecha:** 27 Marzo 2026
> **Estado:** Investigacion completa, referencia permanente.
> **Uso:** Consultar antes de tomar decisiones legales, fiscales o de modelo de negocio.
> **Complemento:** Ver tambien `docs/PLAN_METODOS_PAGO_VENEZUELA.md` para metodos de pago.

---

## RESUMEN EJECUTIVO

Para operar Zonix Eats en Venezuela se necesita:

1. **Empresa legalmente constituida** (Registro Mercantil, RIF, licencia municipal)
2. **Facturacion digital obligatoria** (SENIAT, vigente desde 19 marzo 2026)
3. **Terminos y condiciones + politica de privacidad** (Ley de Proteccion al Consumidor + nueva Ley de Datos Personales 2025)
4. **NO se necesita licencia de Ipostel** (regulacion de delivery fue derogada en feb 2024)
5. **NO se necesita licencia Sudeban** (siempre que Zonix NO sea intermediario de pagos - ver PLAN_METODOS_PAGO_VENEZUELA.md)
6. **Los COMERCIOS necesitan sus propios permisos sanitarios** (SACS) - Zonix es marketplace, no restaurante
7. **Registro de marca** recomendado (SAPI, ~$410, 15 anos de vigencia)
8. **Publicacion en Google Play y App Store** es posible desde Venezuela

---

## 1. CONSTITUCION DE LA EMPRESA

### Tipo de empresa recomendada

**Sociedad Anonima (C.A.)** o **Sociedad de Responsabilidad Limitada (S.R.L.)**

| Paso | Detalle | Costo aprox. |
|------|---------|--------------|
| Reserva de nombre | SAREN (Registro Mercantil) | Variable |
| Documento constitutivo | Redactado por abogado colegiado | $200-500 |
| Registro mercantil | Protocolizacion ante el Registro | $300-800 |
| Legalizacion de libros | Diario, Mayor, Inventarios | $50-150 |
| Obtencion del RIF | SENIAT (gratuito) | $0 |
| Licencia de Actividades Economicas | Alcaldia del municipio (Valencia: via SIGAT) | Variable por municipio |

### Para Valencia, Carabobo

La Alcaldia de Valencia gestiona las licencias via el **SIGAT** (Sistema Integrado de Gestion y Administracion Tributaria). Proceso en linea.

### Beneficio para nuevos emprendimientos

El Decreto 4.719 (2022) permite a nuevos emprendimientos registrados en el RNE (Registro Nacional de Emprendedores) obtener **dispensa del pago de inscripcion** en IVSS, INCES, FAOV y MPP Trabajo, si se formalizan dentro de 90 dias.

---

## 2. OBLIGACIONES FISCALES Y TRIBUTARIAS (SENIAT)

### IVA

| Aspecto | Detalle |
|---------|---------|
| Alicuota general | 16% |
| Alicuota reducida | 8% |
| Alicuota adicional (lujo) | 31% |
| Declaracion | Mensual, antes del dia 15 del mes siguiente |
| Portal | Portal SENIAT en linea |
| Credito fiscal | El IVA pagado en compras se acredita contra el IVA a pagar |

### Factura Digital (OBLIGATORIA desde 19 marzo 2026)

**CRITICO:** La Providencia SNAT/2024/000102 hace obligatoria la factura digital para:
- E-commerce y ventas online
- Marketplaces y plataformas de transaccion en linea
- Apps moviles
- Negocios mixtos (fisico + online)

**Requisitos de la factura digital:**
- RIF y datos del emisor y receptor
- Numero de Control SENIAT
- Numeracion consecutiva
- Total a pagar, base imponible y monto IVA desglosado
- Alicuota IVA aplicada
- Descripcion detallada de productos/servicios
- Formato: PDF o imagen
- Entrega: email, WhatsApp o plataforma online
- Almacenamiento: 5 anos

**Quien emite factura en Zonix Eats:**
- **El COMMERCE** emite factura al buyer por la venta de comida (es quien vende)
- **Zonix Eats** emite factura al commerce y delivery company por la membresia/suscripcion (es un servicio B2B)
- Zonix Eats NO emite factura al buyer por la comida (no es el vendedor)

**Accion requerida:** Registrarse ante SENIAT como emisor digital. Seleccionar o desarrollar software de facturacion compatible.

### ISLR (Impuesto Sobre la Renta)

- Declaracion anual
- Anticipos trimestrales si aplica
- Tarifa progresiva para personas juridicas

### Impuestos municipales

- Impuesto sobre Actividades Economicas (IAE) - porcentaje sobre ingresos brutos
- Varia por municipio (Valencia tiene su propio regimen)

---

## 3. OBLIGACIONES LABORALES

### Si Zonix Eats tiene empleados directos (equipo tech, soporte, admin)

| Obligacion | Aporte patronal | Aporte empleado | Frecuencia |
|------------|-----------------|-----------------|------------|
| IVSS (Seguro Social) | 11% | 4% | Mensual (primeros 5 dias) |
| FAOV (Vivienda) | 2% | 1% | Mensual (primeros 5 dias) |
| INCES (Capacitacion) | 2% (si 5+ empleados) | 0.5% | Trimestral |
| FONACIT (Ciencia y Tech) | 0.50% de ingresos brutos | N/A | Mensual (si >150,000x TCR) |

### Repartidores (delivery agents) - RIESGO LABORAL

**ALERTA:** La LOTTT (Ley Organica del Trabajo) PRESUME relacion laboral cuando una persona presta servicios bajo dependencia a cambio de remuneracion, INCLUSO SIN CONTRATO ESCRITO.

**Riesgo para Zonix Eats:**
- Si Zonix controla horarios, zonas, uniformes o tarifas de los delivery, puede interpretarse como relacion laboral
- Si se interpreta como relacion laboral: IVSS, prestaciones, vacaciones, utilidades, etc.
- Los accidentes de motorizados durante el trabajo se consideran accidentes laborales (INPSASEL, declarar en 24h)

**Como lo manejan las apps en Venezuela:**
- **Yummy:** Modelo de "conductores independientes" (~12,000)
- **PedidosYa:** Similar, contratistas independientes

**Modelo recomendado para Zonix Eats:**
- Los delivery son **usuarios de la plataforma**, no empleados
- Zonix no fija horarios, no impone zonas, no da uniformes obligatorios
- El delivery acepta o rechaza pedidos libremente
- Usar terminos claros: "prestador de servicio independiente", no "empleado" ni "trabajador"
- Tener clausula explicita en los T&C de que NO existe relacion laboral

**PERO:** Este modelo no es 100% seguro ante la LOTTT. Un delivery podria demandar alegando relacion laboral. Es un riesgo inherente a todas las plataformas de delivery en Latam. Tener asesoria legal sobre esto.

---

## 4. REGULACION ESPECIFICA DE DELIVERY

### Ipostel - DEROGADA

La regulacion de Ipostel para delivery (Gaceta Oficial 42.813, feb 2024) fue **DEROGADA 22 dias despues** en Gaceta Oficial 42.827 (27 feb 2024). Actualmente **NO hay regulacion especifica vigente** para apps de delivery en Venezuela.

**Sin embargo:** Podria surgir nueva regulacion en cualquier momento. La derogacion fue por presion del sector, no por decision definitiva. Mantener monitoreo.

### Sudeban - NO aplica si no eres intermediario de pagos

Ver `docs/PLAN_METODOS_PAGO_VENEZUELA.md`. Zonix Eats NO necesita licencia Sudeban siempre que:
- No cree billeteras/wallets
- No retenga dinero del comprador
- No procese pagos como intermediario
- El pago vaya directo del buyer al commerce

### Sundde / Nueva Ley de Derechos Socioeconomicos

La Sundde (precios justos) esta siendo reemplazada por una nueva Ley de Derechos Socioeconomicos que:
- Amplia alcance a TODOS los bienes y servicios, incluyendo ecommerce
- Introduce "precios acordados" (Estado + productores + comerciantes)
- Obliga a proveedores a garantizar calidad, emitir facturas, trato no discriminatorio
- Aplica independientemente del canal de comercializacion (fisico o digital)

**Impacto en Zonix Eats:** Los comercios deben cumplir con precios justos. Zonix como marketplace no fija precios de la comida (el commerce lo hace), pero la membresia de Zonix podria estar sujeta a regulacion de precios de servicios.

### Sunacrip - Solo si manejas cripto

No aplica si los commerces usan su propio QR de Binance. Aplica si Zonix integra un gateway cripto como intermediario (ver PLAN_METODOS_PAGO_VENEZUELA.md).

---

## 5. PERMISOS SANITARIOS (Aplica a los COMERCIOS, no a Zonix)

**Importante:** Zonix Eats es un MARKETPLACE. No prepara ni vende comida. Los permisos sanitarios los necesita cada COMMERCE registrado en la plataforma.

### Lo que Zonix DEBERIA exigir a los commerces

| Requisito | Organismo | Detalle |
|-----------|-----------|---------|
| Permiso sanitario | SACS (Servicio Autonomo de Contraloria Sanitaria) | Tipo IV para expendio de alimentos. Vigencia: 1 ano, renovable |
| Certificado de manipulacion de alimentos | SACS | Personal que manipula alimentos |
| Registro mercantil | SAREN | Empresa legalmente constituida |
| RIF | SENIAT | Registro de informacion fiscal |
| Licencia de actividades economicas | Alcaldia municipal | Permiso para operar en el municipio |

**Recomendacion:** En el onboarding del commerce en Zonix Eats, solicitar que suban foto/PDF de su permiso sanitario y RIF. Esto no es obligatorio tecnicamente para Zonix (es responsabilidad del commerce), pero protege la plataforma ante reclamos y genera confianza.

### Transporte de alimentos (SICA)

Si Zonix o los delivery transportan alimentos que requieren cadena de frio o son procesados, podrian necesitar permiso del SICA (Sunagro). Para comida rapida preparada (hamburguesas, pizzas, etc.) generalmente no aplica.

---

## 6. PROTECCION DE DATOS PERSONALES

### Nueva Ley de Proteccion de Datos Personales (Abril 2025)

**CRITICO:** Venezuela aprobo una nueva ley de proteccion de datos personales que:
- Regula manejo, almacenamiento y tratamiento de datos por instituciones publicas y PRIVADAS
- Establece sanciones contra quienes vulneren la privacidad
- Se aplica transversalmente en TODOS los sectores
- Crea la Superintendencia Nacional de Proteccion de Datos (implementacion progresiva desde 2do semestre 2025)

**Que debe hacer Zonix Eats:**

1. **Politica de privacidad** clara y accesible en la app
2. **Consentimiento explicito** del usuario para recoger datos
3. **Minimizacion de datos:** Solo recoger lo necesario
4. **Seguridad:** Encriptacion de datos sensibles (tokens en flutter_secure_storage - ya implementado)
5. **Derecho de acceso y eliminacion:** El usuario debe poder pedir sus datos o eliminar su cuenta (export ya implementado)
6. **Notificacion de brechas:** Si hay fuga de datos, notificar a usuarios y autoridades
7. **No compartir datos con terceros** sin consentimiento

**Datos que Zonix Eats maneja:**
- Nombre, cedula, telefono, email, foto (perfil)
- Direcciones de casa y entrega (geolocalizacion)
- Historial de ordenes y pagos
- Documentos (CI, RIF)
- Datos de metodos de pago (telefono, banco, cuenta)

**Recomendacion:** Revisar y actualizar la politica de privacidad de la app. Asegurar que el flujo de "exportar datos personales" (ya implementado) cumpla con la nueva ley.

---

## 7. PROTECCION AL CONSUMIDOR

### Ley de Proteccion al Consumidor y al Usuario

Aplica a Zonix Eats como plataforma y a los commerces como vendedores:

| Obligacion | Quien cumple | Detalle |
|------------|-------------|---------|
| Informacion clara de productos | Commerce (Zonix muestra) | Precio, descripcion, foto, disponibilidad |
| Terminos y condiciones | Zonix Eats | Contrato de adhesion. Obligatorio, claro, en la app |
| Politica de devoluciones | Commerce + Zonix | Que pasa si la comida llega en mal estado, fria, incompleta |
| Atencion de reclamos | Zonix (sistema de disputas) | Ya existe modulo de disputas/tickets en la app |
| Factura | Commerce | Al buyer por la compra |
| Garantia de calidad | Commerce | Estandares minimos de calidad |

### Terminos y Condiciones (OBLIGATORIO)

Zonix Eats ya tiene T&C en la vista web (`resources/views/front/pages/legal/terms.blade.php`). Verificar que incluyan:

- Descripcion del servicio (marketplace, NO vendedor de comida)
- Responsabilidades de cada parte (Zonix, commerce, buyer, delivery)
- Politica de cancelaciones y reembolsos
- Politica de privacidad y datos personales
- Clausula de que Zonix NO es empleador de los delivery
- Clausula de que Zonix NO es responsable de la calidad de la comida (es el commerce)
- Metodos de pago aceptados y flujo
- Resolucion de disputas
- Jurisdiccion aplicable (Venezuela)
- Modificaciones a los terminos (como se notifican)

### Ley de Comercio Electronico (EN TRAMITE)

En febrero 2025, Maduro ordeno presentar un Proyecto de Ley de Comercio Electronico ante la Asamblea Nacional. Busca:
- Que productos en plataformas digitales cumplan legislacion nacional
- Evitar especulacion y estafa
- Proteger derechos de compradores y vendedores
- Precios justos y calidad

**Estado:** En primera discusion. No es ley todavia. Monitorear.

---

## 8. PROPIEDAD INTELECTUAL

### Registro de Marca "Zonix Eats" ante SAPI

| Aspecto | Detalle |
|---------|---------|
| Organismo | SAPI (Servicio Autonomo de la Propiedad Intelectual) |
| Costo total aprox. | ~$410 USD |
| Tiempo de proceso | 16 meses o mas |
| Vigencia | 15 anos (renovable) |
| Proteccion | Nombre, logo, isotipo |

**Pasos:**
1. Busqueda de antecedentes (~$100, 5 dias)
2. Presentacion de solicitud (Planilla FM-02 + timbres fiscales + RIF + registro mercantil)
3. Publicacion en Boletin de Propiedad Industrial (~4 meses)
4. Periodo de oposicion (30 dias habiles)
5. Emision del Certificado de Registro

**Recomendacion:** Iniciar el proceso lo antes posible. 16 meses es mucho tiempo. Mientras tanto, la marca se puede usar pero sin proteccion legal plena.

**Clases recomendadas (Clasificacion de Niza):**
- Clase 35: Publicidad, gestion de negocios comerciales, servicios de intermediacion comercial
- Clase 39: Transporte, reparto de mercancias, servicios de entrega
- Clase 42: Servicios de diseno y desarrollo de software, SaaS
- Clase 43: Servicios de alimentacion (si aplica)

---

## 9. SEGUROS

### Para repartidores

| Seguro | Obligatorio | Costo | Quien paga |
|--------|-------------|-------|------------|
| RCV (Responsabilidad Civil Vehiculos) | SI (Ley de Transito) | 15 EUR/ano para motos | El delivery (es su moto) |
| Seguro de accidentes | Recomendado | Variable | Opcional - Zonix podria ofrecerlo como beneficio |

**Nota:** Si Zonix no es empleador de los delivery, no tiene obligacion legal de pagar su seguro. Pero es un diferenciador competitivo frente a Yummy.

### Para la empresa

| Seguro | Recomendado | Detalle |
|--------|-------------|---------|
| Responsabilidad civil general | SI | Cubre reclamos de terceros por danos |
| Seguro de cyberriesgos | Recomendado | Proteccion ante hackeos o fugas de datos |
| Poliza de directores y oficiales (D&O) | Opcional (si hay inversionistas) | Protege a fundadores ante demandas |

---

## 10. PUBLICACION EN TIENDAS DE APPS

### Google Play Store

| Aspecto | Detalle |
|---------|---------|
| Developer registration | Venezuela esta SOPORTADO |
| Merchant registration | Venezuela esta SOPORTADO |
| Costo cuenta developer | $25 USD (unico pago) |
| Pago de ingresos | Wire transfer, minimo $100 USD |
| Requisitos tecnicos | Target API level actualizado, formato AAB, politica de privacidad |

### Apple App Store

| Aspecto | Detalle |
|---------|---------|
| Developer account | Disponible (individual o organizacion) |
| Costo anual | $99 USD/ano |
| Requisitos | Apple ID, DUNS number (para organizaciones) |
| Pago de ingresos | Verificar disponibilidad para Venezuela |

**Nota:** Apple es menos claro sobre restricciones para Venezuela. Se recomienda verificar directamente. Una alternativa es registrar la cuenta de developer bajo una entidad en otro pais (si se tiene LLC en USA, por ejemplo).

---

## 11. INFRAESTRUCTURA TECNOLOGICA

### Hosting/Servidores

**Recomendacion:** NO usar hosting local venezolano para produccion. Razones:
- Inestabilidad electrica
- Internet intermitente (CANTV)
- Latencia a usuarios fuera de VE
- Limitaciones de escalabilidad

**Mejor opcion:** Hosting en la nube (DigitalOcean, AWS, Hetzner) con CDN. El backend de Zonix ya parece estar en servidor externo (verificar).

**Opciones locales** (solo si hay requisito legal de soberania de datos):
- CANTV servicios TI (hospedaje en Venezuela)
- ATAL Networks (servidores dedicados en Caracas, nivel III)
- NetcroHosting (cloud VMware, DCs en Miami/Amsterdam)

### Conectividad

Venezuela tiene problemas recurrentes de internet. Considerar:
- Modo offline/cache en la app (pedidos frecuentes, menu, etc.)
- Compresion de imagenes agresiva
- Timeouts generosos para conexiones lentas
- Notificaciones push como canal principal (ya implementado con FCM + Pusher)

---

## 12. COSAS QUE NO PREGUNTASTE PERO SON FUNDAMENTALES

### A. Modelo de relacion con Commerces

Zonix Eats necesita un **contrato de afiliacion** con cada commerce (y con cada delivery company) que defina:
- Membresia mensual (plan/precio de la suscripcion)
- Responsabilidades del commerce (calidad, permisos, precios, disponibilidad)
- Responsabilidades de Zonix (plataforma, visibilidad, soporte)
- Politica de cancelaciones y disputas
- Exclusividad o no (el commerce puede estar en Yummy y Zonix a la vez?)
- Terminacion del contrato
- Propiedad de datos (de quien son los datos de los clientes?)

### B. Facturacion B2B (Zonix -> Commerce / Delivery Company)

Zonix necesita facturar a commerces y delivery companies por la membresia mensual (suscripcion). NO hay comision % sobre ventas.

Esto requiere:
- Software de facturacion compatible con SENIAT
- Numeros de control y consecutivos
- Formato de factura digital
- Mecanismo de cobro (transferencia, pago movil, cargo automatico?)

### C. Contabilidad y Declaraciones

Necesitas un contador que maneje:
- Declaracion mensual de IVA
- Declaracion anual de ISLR
- Contribuciones parafiscales (IVSS, INCES, FAOV)
- Impuesto municipal de actividades economicas
- Retencion de IVA e ISLR (si aplica como agente de retencion)
- Libros de compras y ventas

### D. Soporte al usuario

La Ley de Proteccion al Consumidor exige canales de contacto accesibles. Zonix ya tiene:
- Chat en vivo (implementado)
- Sistema de disputas/tickets
- FAQ por rol

Asegurar que sean funcionales y que los tiempos de respuesta sean razonables.

### E. Geopolitica y sanciones internacionales

Venezuela esta bajo sanciones de EE.UU. (OFAC). Esto puede afectar:
- Pasarelas de pago internacionales (Stripe no opera, PayPal limitado)
- Servicios cloud (la mayoria funciona, pero algunas empresas bloquean VE)
- Bancos corresponsales (dificultad para transferencias internacionales)

**No afecta:** Google Play, Apple App Store, Firebase, Pusher, DigitalOcean (al momento de esta investigacion). Pero monitorear cambios.

### F. Competencia y diferenciacion

| App | Presencia en VE | Fortaleza | Debilidad |
|-----|-----------------|-----------|-----------|
| Yummy | 11 estados, +2.5M usuarios | Base masiva, $69M funding | Sancion Sudeban, servicio irregular |
| PedidosYa | Nacional | Marca regional, experiencia | Menos adaptada a realidad VE |
| Zonix Eats | En desarrollo | Nicho comida rapida, multi-metodo pago, cripto | Nuevo, sin base de usuarios |

**Diferenciadores potenciales de Zonix Eats:**
- Binance Pay / USDT (Yummy no lo tiene)
- Flujo de pago directo (sin wallet, sin riesgo Sudeban)
- Enfoque exclusivo en comida rapida (no super-app)
- Modelo transparente: solo membresia fija, sin comision sobre ventas

---

## CHECKLIST DE LANZAMIENTO

### Obligatorio (antes de operar)

- [ ] Empresa constituida (Registro Mercantil, RIF)
- [ ] Licencia de Actividades Economicas (Alcaldia municipal)
- [ ] Registro ante SENIAT como emisor de factura digital
- [ ] Terminos y condiciones en la app (contrato de adhesion)
- [ ] Politica de privacidad (Ley de Datos Personales 2025)
- [ ] Contrato de afiliacion con commerces y delivery companies
- [ ] Mecanismo de facturacion B2B para membresias (Zonix -> Commerce / Delivery Company)
- [ ] Cuenta de developer Google Play ($25) y/o Apple ($99/ano)
- [ ] Inscripcion patronal (IVSS, INCES, FAOV) si hay empleados

### Altamente recomendado

- [ ] Registro de marca "Zonix Eats" ante SAPI (~$410)
- [ ] Verificar permiso sanitario de cada commerce al onboardear
- [ ] Seguro de responsabilidad civil general para la empresa
- [ ] Asesoria legal sobre relacion con delivery (modelo no-laboral)
- [ ] Contrato con contador para declaraciones fiscales

### A monitorear

- [ ] Proyecto de Ley de Comercio Electronico (en tramite AN)
- [ ] Nueva Ley de Derechos Socioeconomicos (reemplaza Sundde)
- [ ] Posible nueva regulacion de delivery (post-derogacion Ipostel)
- [ ] Sanciones internacionales OFAC y su impacto en servicios tech
- [ ] Implementacion de la Superintendencia de Datos Personales

---

**Ultima actualizacion:** 27 Marzo 2026
