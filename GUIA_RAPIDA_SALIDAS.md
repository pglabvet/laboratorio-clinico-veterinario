# 🚀 Guía Rápida: Salidas Manuales de Inventario

## ⚡ Acceso Rápido

```
Dashboard → Inventario → Salidas Manuales
URL: /inventario/salidas
```

---

## 📝 Pasos para Registrar una Salida

### 1️⃣ Seleccionar Ubicación
- Elegir **Sucursal** del dropdown
- Seleccionar **Insumo** (se filtra por sucursal)
- El **stock actual** aparece automáticamente

### 2️⃣ Ingresar Datos
- **Cantidad:** Monto a retirar (ej: 5.50)
- **Motivo:** Elegir entre:
  - 🟠 Merma (pérdida/deterioro)
  - 🔴 Vencimiento (producto caducado)
  - 🔵 Uso Extraordinario (no asociado a análisis)
  - ⚫ Otro
- **Observación:** Descripción detallada (min 10 caracteres)

### 3️⃣ Confirmar
- Click en **"Registrar Salida"**
- Revisar resumen en modal
- Click en **"Confirmar Salida"**

✅ **¡Listo!** El stock se actualizó y el movimiento quedó registrado.

---

## ⚠️ Alertas Comunes

| Alerta | Significado |
|--------|-------------|
| "Stock insuficiente" | No hay suficiente cantidad disponible |
| "Insumo inactivo" | El insumo está deshabilitado |
| "Stock quedó por debajo del mínimo" | Se activó alerta de reabastecimiento |

---

## 📊 Ver Historial Completo

```
Dashboard → Inventario → Historial
URL: /inventario/historial
```

**Filtros disponibles:**
- 🏢 Por sucursal
- 📦 Por insumo (búsqueda)
- 🔄 Por tipo de movimiento
- 📝 Por motivo
- 📅 Por rango de fechas

---

## 💡 Consejos

1. **Observación clara:** Escribe detalles útiles para auditorías
2. **Motivo correcto:** Selecciona el más específico
3. **Verificar stock:** Revisa el stock actual antes de registrar
4. **Historial:** Usa filtros para encontrar movimientos específicos

---

## 🔍 Ejemplos de Observaciones

❌ **Malo:**
- "se rompió"
- "ya no sirve"

✅ **Bueno:**
- "Frasco de reactivo cayó al suelo durante transporte, derrame total"
- "Lote #ABC123 caducó el 15/01/2026, disposición según protocolo"
- "Usado en capacitación de personal nuevo, práctica de técnica"

---

## 📞 ¿Problemas?

Si encuentras algún error o comportamiento inesperado, contacta al administrador del sistema.
