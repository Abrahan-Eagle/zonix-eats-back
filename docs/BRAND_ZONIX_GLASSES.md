# BRAND — Zonix Glasses

> **CANON founder** — aprobado 2026-06-27 ([DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) §16 · [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md)). Flutter aún no alineado (ver § implementación).

> Identidad visual óptica tech (ecosistema Zonix). Tokens objetivo Flutter: `lib/features/utils/app_colors.dart` (front) — **pendiente migración**.

## Naming

- **Producto:** Zonix Glasses
- **Vertical:** Óptica online B2B2C (lentes a medida)
- **Marketplace monturas:** **web** (navegador) — PDP estilo e-commerce; try-on en galería. Flutter/app móvil no es canal canon de este flujo.
- **No usar:** “smart glasses”, wearable HUD, copy Eats/Pharma

## Paleta (propuesta MVP)

| Token | Hex | Uso |
|-------|-----|-----|
| `brandNavy` | `#1A2B4A` | AppBar, headers, confianza |
| `brandTeal` | `#0D9488` | CTA primario, links |
| `brandMint` | `#99F6E4` | Acentos suaves, badges |
| `brandSurface` | `#F8FAFC` | Fondo claro |
| `brandInk` | `#0F172A` | Texto principal |
| `brandMuted` | `#64748B` | Texto secundario |

Modo oscuro: navy más profundo `#0B1220`, surface `#1E293B`.

## Tipografía

- **Primaria:** Plus Jakarta Sans (Flutter + web Blade).
- Pesos: 400 cuerpo, 600 subtítulos, 700 títulos/CTA.

## Tono de voz

- Claro, profesional, cercano (salud visual sin alarmismo).
- Explicar materiales de lente en lenguaje simple.
- Transparencia en tiempos de entrega y precios.

## Do / Don't

| Do | Don't |
|----|-------|
| Mostrar precio desglosado (lente + montura) | Ocultar recargos al checkout |
| Badge “Requiere fórmula válida” | Vender lentes graduados sin fórmula confirmada |
| Marketplace: fotos producto + detalle con try-on en tu rostro | Solo filtro IA sin fotos reales de montura |
| Try-on como ayuda visual (varias fotos → preview en detalle) | Prometer ajuste clínico exacto solo con IA |
| Marca Zonix coherente con ecosistema | Mezclar assets Zonix Pharma/Eats |

## Iconografía

Material Icons / SVG 24px grid. Evitar emojis como iconos UI.

## Checklist contraste

- CTA teal sobre blanco ≥ WCAG AA.
- Texto ink sobre surface ≥ 4.5:1.

**Última actualización:** Junio 2026
