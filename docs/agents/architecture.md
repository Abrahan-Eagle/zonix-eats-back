# Architecture — Zonix Eats Backend

## Estructura del Proyecto

```
zonix-eats-back/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # 54 controladores organizados por módulo
│   │   │   ├── Authenticator/   # Autenticación
│   │   │   ├── Buyer/           # Funcionalidades de comprador
│   │   │   ├── Commerce/        # Funcionalidades de comercio
│   │   │   ├── Delivery/        # Funcionalidades de delivery
│   │   │   ├── Admin/           # Funcionalidades de administrador
│   │   │   └── ...
│   │   ├── Middleware/          # Middleware personalizado (RoleMiddleware)
│   │   └── Requests/            # Form Requests para validación
│   ├── Models/                  # 35 modelos Eloquent
│   ├── Services/                # 9 servicios de negocio
│   │   ├── OrderService.php
│   │   ├── CartService.php
│   │   ├── ProductService.php
│   │   ├── DeliveryAssignmentService.php
│   │   └── ...
│   ├── Events/                  # Eventos para broadcasting
│   └── Providers/               # Service providers
├── database/
│   ├── migrations/              # 51 migraciones
│   ├── seeders/                 # Seeders de datos
│   └── factories/               # 27 factories
├── routes/
│   └── api.php                  # 233+ endpoints REST
├── tests/
│   └── Feature/                 # 206+ tests
└── config/                      # Configuración
```

## Patrón Arquitectónico

**MVC con separación de servicios:**

- **Controllers** → Manejan requests/responses HTTP (delgados)
- **Services** → Contienen lógica de negocio (gruesos)
- **Models** → Representan entidades de base de datos + relaciones
- **Events** → Broadcasting con Firebase + Pusher (NO WebSocket)
- **Form Requests** → Validación de datos de entrada

**Principios:** SRP, Dependency Injection, Separation of Concerns, DRY
