# Política canal aliado — Zonix Glasses

> §14 [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) v3. Contrato B2B `[PENDIENTE abogado VE]`.

## Principio

**Proteger al aliado:** Zonix no captura pacientes del aliado para venderles en canal directo sin reglas de atribución.

## Modelo comercial aliado (v3 — mayorista, no comisión %)

Ver [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §4 y §14. Resumen: mayor **por SKU** · PVP libre al paciente · **100% mayor** a Zonix antes de fabricar · stock VE vs MTO = mismo mayor, distinto plazo.

## Atribución paciente

| Regla | Detalle |
|-------|---------|
| Registro | Paciente bajo `optical_partner_id` del aliado que lo registró o atiende en panel |
| Panel aliado (confirmado founder §16–§17) | Óptica logueada; paciente en tenant; captura facial → marketplace monturas → detalle (try-on + fotos catálogo + specs) → carrito |
| Checkout aliado | Carrito en panel → fórmula si `lens_and_frame` → T&C → pago mayor 100% a Zonix (u ops valida) → notificación ops Zonix → aprobación antes de fab |
| Inventario propio aliado | Monturas en almacén de la óptica = activo del aliado; fuera de `SupplierOrder` / hub Zonix — ver [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §12 |
| Canal Zonix directo | Pacientes sin aliado o reasignados por §6 portabilidad |

## Flujo panel aliado (resumen)

1. Óptica inicia sesión en panel partner.
2. Paciente (usuario del aliado) completa **captura facial** (varias fotos reales) con consentimiento.
3. IA analiza rostro → paciente navega **marketplace** (fotos catálogo) → **toca montura** → detalle: try-on en su rostro + galería producto + ficha técnica abajo.
4. Añade montura al carrito; si `lens_and_frame`, carga fórmula (flujo a+b+c).
5. Acepta T&C (protección óptica + Zonix).
6. Elige método de pago y paga tramo correspondiente (100% mayor a Zonix antes de fabricar).
7. Sistema notifica **ops Zonix** → revisión → si OK: pago a fabricante(s) y producción; si no: cancelación.

Detalle pagos: [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) · flujos: [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md) Flujo 1 y 5 (aliado).

## Cese de óptica aliada

Reglas founder: [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §6 — pedidos en curso **culminan**; pacientes nuevos → reasignación Zonix; datos clínicos portables (no borrado).

## Tensiones documentadas

| Tema | Resolución |
|------|------------|
| §6 cese → continuidad | No es "captura"; es cumplir pedidos abiertos + reasignación |
| Exclusividad de datos | Tensiona portabilidad — `[PENDIENTE abogado]` |
| Cobro paciente vs mayor Zonix | Riesgo de cobranza **de la óptica**, no de Zonix |

## Línea roja

No usar datos de pacientes del aliado para prospección directa Zonix sin reglas contractuales.

## Referencias

- [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)
- [ROLES_Y_ACTORES.md](ROLES_Y_ACTORES.md)
- [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md)
- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md) (pago aliado)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [CHECKLIST_FOUNDER_S11.md](CHECKLIST_FOUNDER_S11.md)

**Última actualización:** 2026-06-27 (v3 + §16–§17 try-on multi-foto)
