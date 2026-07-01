# Checklist — Zonix Glasses Backend

## Git remoto

- [ ] `origin` debe apuntar al repo **Zonix Glasses** (no `zonix-eats-back`)
- [ ] Si aún heredas remote Eats: `git remote rename origin eats-legacy` y añade el remote correcto cuando exista

## Entorno

- [ ] BD `zonix_glasses` creada
- [ ] `cp .env.example .env` + `php artisan key:generate`
- [ ] `php artisan migrate --seed`
- [ ] Usuarios seed: `admin@zonix-glasses.local` / `user@zonix-glasses.local` — password local: `password` (solo dev)

## Credenciales (rotar en producción)

- [ ] Firebase: proyecto **Zonix Glasses** (no reutilizar Eats)
- [ ] Google OAuth: client IDs para `com.zonix.glasses`
- [ ] Pusher: app dedicada
- [ ] `SANCTUM_STATEFUL_DOMAINS`, `CORS_ALLOWED_ORIGINS`

## Verificación

```bash
php artisan test
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/auth/user
```

### Identidad producto (anti-Eats / anti-smart-glasses)

Comandos en [FORENSIC_VERIFICATION.md](FORENSIC_VERIFICATION.md) — ejecutar tras clonar o antes de release.
