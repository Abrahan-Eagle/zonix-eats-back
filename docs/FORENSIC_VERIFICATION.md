# Verificación forense — Zonix Glasses

Comprobar que no queden restos de otros productos (Eats, Pharma, CorralX):

```bash
rg -i 'eats\.aiblockweb|com\.zonix\.eats|firebase-zonix-eats|admin@scaffold|package:zonix[^_]|DB_DATABASE=eats' \
  zonix-glasses-back zonix-glasses-front
```

Debe devolver **cero coincidencias** (salvo este doc o comentarios de auditoría).

## Anti-pivot smart-glasses / China (skills)

Comprobar que skills de dominio no conserven el modelo legacy:

```bash
rg -i 'China|sent_to_supplier|smart.?glass|/api/devices|proveedor único' \
  zonix-glasses-back/.agents/skills/zonix-glasses-* \
  zonix-glasses-back/skills/zonix-glasses-*
```

Debe devolver **cero** salvo comentarios de deprecación explícitos. Canon fulfillment: `docs/MODELO_NEGOCIO/CADENA_SUMINISTRO.md`.

## Excepciones conocidas (código congelado — fuera alcance docs-only)

| Archivo | Hallazgo | Acción |
|---------|----------|--------|
| `app/Helpers/SeoHelper.php` | Metadatos "Smart glasses companion" | Corregir al descongelar scaffold web; documentado en [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) |
| `zonix-glasses-front/linux/CMakeLists.txt` | `APPLICATION_ID "com.zonix.eats"` (scaffold Linux) | Corregir a `com.zonix.glasses` al retomar build desktop; fuera alcance docs-only v2 |

Identificadores canónicos actuales:

| Capa | Valor |
|------|-------|
| BD | `zonix_glasses` |
| Dart | `zonix_glasses` |
| Android/iOS | `com.zonix.glasses` |
| Seed admin | `admin@zonix-glasses.local` |
