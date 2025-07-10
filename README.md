# Zonix Eats Backend (Laravel)

API REST y lógica de negocio de Zonix Eats, desarrollada en Laravel. Gestiona usuarios, comercios, productos, órdenes, entregas y notificaciones en tiempo real.

---

## 📦 Estructura del proyecto

```
app/
  Http/
    Controllers/
      Auth/
      Buyer/
      Commerce/
      Delivery/
      Admin/
    Middleware/
    Requests/
  Models/
  Services/
  Providers/
database/
  factories/
  migrations/
  seeders/
routes/
  api.php
  web.php
tests/
  Feature/
  Unit/
```

---

## 🚀 Cómo correr el backend

1. Instala dependencias:
   ```bash
   composer install
   ```
2. Copia el archivo de entorno y configura:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Migra y llena la base de datos:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. Corre el servidor:
   ```bash
   php artisan serve
   ```

---

## 🧪 Cómo correr los tests

```bash
php artisan test
```
Todos los tests relevantes deben pasar (Feature y Unit).

---

## 📝 Convenciones y buenas prácticas
- Agrupa controladores y servicios por dominio.
- Usa nombres claros y descriptivos para archivos y carpetas.
- Mantén los tests junto a la lógica que prueban.
- Documenta cualquier convención especial aquí.

---

## 📄 Contacto y soporte
Para dudas o soporte, contacta a tu equipo de desarrollo o abre un issue en el repositorio.
