# Pending Improvements — Zonix Eats Backend

## 🟡 ALTO

- Paginación en 30+ endpoints que usan `->get()` sin paginación
- Índices BD: `orders.status`, `orders.created_at`, `products.commerce_id`
- Refactorizar God Classes: `AnalyticsController` (1,130 líneas), `PaymentController` (815 líneas)

## 🟢 MEDIO

- Swagger/OpenAPI docs
- Redis caching
- Queues para emails/procesamiento pesado
- Permisos granulares (mejora de roles)

---

**Documentación completa de lógica de negocio:** Ver `README.md` (raíz)
