# Plan: Módulo de Tarifa de Delivery

**Estado:** Plan futuro — no implementado.  
**Objetivo:** Cuando se decida implementar, usar este documento como base y ir puliendo la idea paso a paso.

---

## 1. Situación actual

| Aspecto | Estado |
|--------|--------|
| **Tabla `delivery_zones`** | Existe: `name`, `center_latitude`, `center_longitude`, `radius`, `delivery_fee`, `delivery_time`, `is_active`, `description`. |
| **API zonas** | Solo lectura: `GET .../delivery-zones` (listar). No hay CRUD Admin. |
| **Tarifa al crear orden** | El front envía `delivery_fee` en el body; el backend lo valida y guarda. El backend **no** calcula la tarifa. |
| **Config global** | Un único valor: `ZONIX_DEFAULT_DELIVERY_FEE` en `.env` / `config/zonix.php`. |
| **README / negocio** | Define: costo híbrido **Base + $/km** (ej. $2.00 base + $0.50/km), configurable por admin. No implementado. |

---

## 2. Objetivos del módulo (a refinar cuando se implemente)

- Que **Admin** pueda configurar la tarifa de delivery sin tocar `.env`.
- Opción A: **Fórmula global** (base + precio por km).  
- Opción B: **Tarifa por zona** (CRUD de zonas con tarifa fija o por km).  
- Opción C: **Ambos** (config global + zonas que puedan sobrescribir o complementar).
- Que el **backend calcule** la tarifa al crear la orden (o en un endpoint “calcular tarifa”) usando dirección/comercio/distancia, y el front solo muestre/confirme ese valor.

---

## 3. Opciones de diseño (para elegir / combinar al implementar)

### 3.1 Configuración global (base + $/km)

- **BD:** Tabla `delivery_fee_config` o filas en `settings`: `delivery_base_cost`, `delivery_cost_per_km`, quizá `delivery_free_km` (primeros X km sin cargo).
- **API Admin:** `GET/PUT` (o `GET/PATCH`) para leer y actualizar estos valores.
- **Cálculo:** En backend, al calcular tarifa: `base + max(0, distancia_km - free_km) * cost_per_km` (redondeado a 2 decimales). La distancia ya se calcula en el flujo de búsqueda/orden (Haversine o OSRM).

### 3.2 Zonas de entrega (CRUD)

- **BD:** Ya existe `delivery_zones`. Solo falta API y pantallas.
- **API Admin:** `GET /api/admin/delivery-zones`, `POST`, `PUT /api/admin/delivery-zones/{id}`, `DELETE` (soft delete o borrado lógico si se prefiere).
- **Regla de tarifa:** Si la dirección del pedido cae en una zona, usar `delivery_fee` (y opcionalmente `delivery_time`) de esa zona; si no cae en ninguna, fallback a config global (base + km) o a un “default” configurable.

### 3.3 Cálculo en el flujo de orden

- **Endpoint nuevo (recomendado):** `POST /api/buyer/delivery-fee/calculate` (o dentro de un “preview” de orden): body con `commerce_id`, `address_id` o `latitude`, `longitude`. Respuesta: `{ "delivery_fee": 3.50, "currency": "...", "breakdown": "..." }`.
- **Crear orden:** El backend puede volver a calcular la tarifa y comparar con el `delivery_fee` enviado (tolerancia pequeña por redondeo) o directamente calcular y guardar sin que el front envíe el monto (más seguro).
- **Compatibilidad:** Mantener aceptar `delivery_fee` en el body de crear orden como opcional mientras se migra el front a usar el cálculo backend.

---

## 4. Plan de implementación (orden sugerido)

Cuando se diga *“vamos a crear este módulo”*, seguir este orden y ir puliendo cada paso.

1. **Definir alcance**  
   - Decidir: solo global (base + km), solo zonas CRUD, o ambos.  
   - Decidir si la tarifa por zona es fija o también “base + km” por zona.

2. **Backend – Config global (si aplica)**  
   - Migración: tabla o filas para `delivery_base_cost`, `delivery_cost_per_km` (y opcionales).  
   - Config en `config/zonix.php` que lea de BD o cache (o solo de .env como fallback hasta tener BD).  
   - Servicio `DeliveryFeeService::calculate(float $distanceKm, ?int $zoneId = null): float`.  
   - Endpoints Admin: leer/actualizar configuración.  
   - Tests unitarios del cálculo.

3. **Backend – Zonas (si aplica)**  
   - Endpoints Admin: CRUD de `delivery_zones` (crear, editar, listar, desactivar/eliminar).  
   - Form Request para validar nombre, centro, radio, `delivery_fee`, `delivery_time`.  
   - En `DeliveryFeeService`: si hay zona que contenga la dirección, usar tarifa (y tiempo) de la zona; si no, usar config global.

4. **Backend – Integración con órdenes**  
   - Endpoint `POST /api/buyer/delivery-fee/calculate` (o equivalente) que use dirección/comercio y devuelva `delivery_fee` (y opcionalmente tiempo estimado).  
   - En `OrderController::store`: calcular tarifa en backend; validar que el `delivery_fee` enviado (si se envía) esté dentro de una tolerancia del calculado, o ignorar el enviado y usar solo el calculado.  
   - Documentar en README/AGENTS el flujo.

5. **Frontend**  
   - Pantalla Admin: configuración de tarifa global (base, $/km) y/o CRUD de zonas (lista, alta, edición).  
   - En flujo de compra: llamar al endpoint de cálculo de tarifa antes de confirmar orden; mostrar tarifa y desglose (opcional).  
   - Dejar de enviar un `delivery_fee` “inventado” por el cliente cuando el backend ya lo calcule.

6. **Limpieza**  
   - Reducir dependencia de `ZONIX_DEFAULT_DELIVERY_FEE` en .env a solo fallback cuando no haya config en BD.  
   - Actualizar `docs/ENV_VARIABLES.md` y README con el nuevo comportamiento.

---

## 5. Notas para cuando se implemente

- **Distancia:** Reutilizar la lógica existente (Haversine entre comercio y dirección del cliente, o OSRM si ya se usa para rutas).  
- **Seguridad:** Solo Admin puede cambiar config global y zonas; solo buyer (o endpoints públicos necesarios) puede llamar al cálculo de tarifa.  
- **Idioma:** Mantener nombres en inglés en API/BD (`delivery_fee`, `delivery_base_cost`, etc.); textos de UI en español.  
- **Pulir:** Cuando digas *“vamos a crear este módulo”*, se puede bajar esto a historias de usuario, criterios de aceptación y tareas concretas por sprint.

---

**Última actualización:** 6 marzo 2026  
**Referencias:** README (Costo Delivery, Base + $/km), `config/zonix.php`, `LocationController::getDeliveryZones`, `OrderController::store`, modelo `DeliveryZone`.
