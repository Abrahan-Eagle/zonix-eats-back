# 📊 ANÁLISIS: TABLAS MVP VS POST-MVP

**Fecha:** 16 de Enero 2025  
**Objetivo:** Clasificar todas las tablas según si pertenecen al MVP o son Post-MVP

---

## ✅ TABLAS MVP (REQUERIDAS PARA MVP)

### 🔐 Autenticación y Usuarios
1. **users** ✅ MVP
   - Usuarios del sistema
   - Roles: users, commerce, delivery, admin

2. **profiles** ✅ MVP
   - Perfiles extendidos de usuarios
   - Datos personales, foto, teléfono

3. **password_reset_tokens** ✅ MVP
   - Tokens para recuperación de contraseña

4. **personal_access_tokens** ✅ MVP
   - Tokens de autenticación Sanctum

### 📍 Geolocalización y Direcciones
5. **countries** ✅ MVP
   - Países para direcciones

6. **states** ✅ MVP
   - Estados/Provincias para direcciones

7. **cities** ✅ MVP
   - Ciudades para direcciones

8. **addresses** ✅ MVP
   - Direcciones de usuarios (2 direcciones: predeterminada + entrega)

9. **user_locations** ✅ MVP
   - Historial de ubicaciones GPS para búsqueda de comercios

10. **operator_codes** ✅ MVP
    - Códigos de operadores telefónicos

11. **phones** ✅ MVP
    - Múltiples teléfonos por perfil

12. **documents** ✅ MVP
    - Documentos de usuarios (CI, pasaporte, RIF, etc.)

### 🏪 Comercios y Productos
13. **commerces** ✅ MVP
    - Comercios/Restaurantes
    - Campos: business_name, business_type, tax_id, open, schedule, membresía, comisión

14. **products** ✅ MVP
    - Productos de comercios
    - Campos: available, stock_quantity (ambas opciones según modelo)

15. **categories** ✅ MVP
    - Categorías de productos

### 🛒 Carrito y Órdenes
16. **carts** ✅ MVP
    - Carritos de compra (uni-commerce)

17. **cart_items** ✅ MVP
    - Items del carrito

18. **orders** ✅ MVP
    - Órdenes/Pedidos
    - Estados: pending_payment, paid, processing, shipped, delivered, cancelled

19. **order_items** ✅ MVP
    - Items de órdenes

20. **order_delivery** ✅ MVP
    - Información de entrega (delivery_fee, notes)

### 🚚 Delivery
21. **delivery_companies** ✅ MVP
    - Empresas de delivery
    - Campos: name, tax_id, phone, address, image, open, schedule

22. **delivery_agents** ✅ MVP
    - Agentes de entrega (independientes o de empresa)
    - Campos: vehicle_type, license_number, current_latitude, current_longitude, working

23. **delivery_payments** ✅ MVP
    - Pagos a delivery (100% del delivery_fee)
    - Estados: pending_payment_to_delivery, paid_to_delivery

24. **delivery_zones** ⚠️ MVP (pero puede ser legacy)
    - Zonas de delivery
    - **Nota:** Puede ser complementario al sistema de expansión automática

### ⭐ Reviews y Calificaciones
25. **reviews** ✅ MVP
    - Reseñas/Calificaciones
    - Obligatorias después de orden entregada
    - Campos: order_id, comment, rating

### 💰 Pagos y Facturación
26. **banks** ✅ MVP
    - Bancos para métodos de pago

27. **payment_methods** ✅ MVP
    - Métodos de pago unificados (comercio, usuario, delivery)

28. **commerce_invoices** ✅ MVP
    - Facturas mensuales (membresía + comisiones)
    - Según modelo de negocio: membresía mensual + comisión % sobre ventas

### 🎁 Promociones y Descuentos
29. **promotions** ✅ MVP
    - Promociones manuales (comercio y admin pueden crear)
    - Según modelo: "Promociones/Descuentos Manuales (Comercio y Admin pueden crear)"

30. **coupons** ✅ MVP
    - Códigos promocionales
    - Según modelo: "Código promocional: Cliente ingresa código (ej: 'DESCUENTO20') al checkout"

31. **coupon_usages** ✅ MVP
    - Uso de cupones en órdenes

### 💬 Comunicación
32. **notifications** ✅ MVP
    - Notificaciones del sistema
    - Firebase + Pusher para tiempo real

33. **chat_messages** ✅ MVP
    - Mensajes de chat en órdenes
    - Chat básico con vendedor (por orden)

### ⚖️ Disputas y Quejas
34. **disputes** ✅ MVP
    - Sistema de tickets/quejas
    - Según modelo: "Sistema de Tickets/Chat con Soporte Admin"

### 🔧 Sistema
35. **roles** ✅ MVP
    - Roles del sistema

36. **cache** ✅ MVP
    - Cache de Laravel

37. **jobs** ✅ MVP
    - Jobs/Queues de Laravel

---

## ⚠️ TABLAS POST-MVP (NO REQUERIDAS PARA MVP)

### 📱 Posts Sociales
38. **posts** ⚠️ POST-MVP
    - Posts sociales de comercios
    - **Según README línea 1809:** "Posts sociales (evaluar si mantener)"
    - **Según README línea 1648:** No está en "Modelos Críticos (Mantener)"
    - **Nota:** Se usa en código, pero no es crítico para MVP

39. **post_likes** ⚠️ POST-MVP
    - Likes en posts
    - Depende de `posts`, por lo tanto también Post-MVP

### 🎮 Gamificación y Fidelización
40. **Tablas relacionadas con GamificationController y LoyaltyController** ⚠️ POST-MVP
    - **Según README línea 1100:** "❌ DECISIÓN: Por ahora NO hay programa de fidelización"
    - **Según README línea 1103:** "Se puede implementar en el futuro (Post-MVP)"
    - **Según README línea 1506:** "❌ Gamificación avanzada" está excluido del MVP
    - **Nota:** Hay controladores `GamificationController` y `LoyaltyController`, pero no hay tablas específicas creadas aún (pueden usar tablas existentes o no estar completamente implementadas)

---

## 📊 RESUMEN POR CATEGORÍA

### ✅ MVP: 37 tablas
- **Autenticación:** 4 tablas
- **Geolocalización:** 8 tablas
- **Comercios/Productos:** 3 tablas
- **Carrito/Órdenes:** 5 tablas
- **Delivery:** 4 tablas
- **Reviews:** 1 tabla
- **Pagos:** 3 tablas
- **Promociones:** 3 tablas
- **Comunicación:** 2 tablas
- **Disputas:** 1 tabla
- **Sistema:** 3 tablas

### ⚠️ POST-MVP: 2 tablas
- **Posts Sociales:** 2 tablas (`posts`, `post_likes`)

### 📝 TOTAL: 39 tablas
- **MVP:** 37 tablas (94.9%)
- **Post-MVP:** 2 tablas (5.1%)

---

## 🔍 ANÁLISIS DETALLADO

### Tablas que están en MVP pero pueden ser opcionales:

1. **delivery_zones** ⚠️
   - **Estado:** MVP (pero puede ser legacy)
   - **Razón:** El modelo de negocio usa "expansión automática de área" en lugar de zonas fijas
   - **Recomendación:** Evaluar si se usa o es legacy

2. **user_locations** ⚠️
   - **Estado:** MVP
   - **Razón:** Necesario para búsqueda de comercios por geolocalización
   - **Nota:** Puede ser opcional si se usa solo la dirección predeterminada

### Tablas Post-MVP que están implementadas:

1. **posts** y **post_likes** ⚠️
   - **Estado:** Post-MVP
   - **Razón:** No está en "Modelos Críticos (Mantener)" según README
   - **Nota:** Se usa en código, pero no es crítico para MVP
   - **Recomendación:** Mantener si ya está implementado, pero no es requerido para MVP

---

## ✅ CONCLUSIÓN

**Estado General:** ✅ **EXCELENTE**

- **94.9% de las tablas son MVP** (37 de 39)
- **Solo 2 tablas son Post-MVP** (`posts`, `post_likes`)
- **Todas las tablas críticas están presentes**

**Recomendaciones:**

1. ✅ **Mantener todas las tablas MVP** - Todas son necesarias
2. ⚠️ **Evaluar `posts` y `post_likes`** - Si ya están implementadas y funcionando, mantenerlas. Si no, pueden moverse a Post-MVP
3. ⚠️ **Evaluar `delivery_zones`** - Verificar si se usa o es legacy del sistema de expansión automática
4. ✅ **No hay tablas innecesarias** - Todas tienen propósito

**El esquema de base de datos está bien alineado con el MVP del modelo de negocio.**
