# Guía Rápida: Gestión de Insumos

## 🚀 Inicio Rápido

### 1. Cargar Unidades de Medida Iniciales

```bash
php artisan db:seed --class=UnidadesMedidaSeeder
```

Esto carga 11 unidades de medida comunes (ml, L, g, kg, unid, etc.)

### 2. Acceder al Sistema

**Unidades de Medida:**
- URL: `/unidades-medida`
- Gestiona las unidades de medida estandarizadas

**Insumos:**
- URL: `/insumos`
- Gestiona los insumos del laboratorio

## 📋 Flujos de Uso

### Crear una Unidad de Medida

1. Ve a `/unidades-medida`
2. Clic en "Nueva Unidad de Medida"
3. Completa:
   - **Nombre**: Ej: "Mililitros"
   - **Abreviatura**: Ej: "ml"
   - **Activo**: ✓ (marcado por defecto)
4. Guardar

### Crear un Insumo

1. Ve a `/insumos`
2. Clic en "Nuevo Insumo"
3. Completa:
   - **Nombre**: Ej: "Alcohol etílico"
   - **Unidad de medida**: Selecciona "Mililitros (ml)"
   - **Activo**: ✓ (marcado por defecto)
4. Define **Stock Mínimo** para cada sucursal:
   - Sucursal Central: 50.00
   - Sucursal Norte: 30.00
   - etc.
5. Guardar

**Resultado:**
- El insumo se crea con stock actual = 0
- Se registra el stock mínimo para cada sucursal

### Verificar Stock Bajo

1. Ve a `/insumos`
2. Selecciona una **sucursal** en el filtro
3. (Opcional) Marca "Solo stock bajo"
4. La tabla muestra:
   - 🟡 Filas amarillas = stock bajo
   - ⚠️ Icono de advertencia
   - 🏷️ Badge "Stock bajo"

### Desactivar un Insumo

1. Clic en el **switch** de estado del insumo
2. Confirma la acción
3. El insumo queda **inactivo**
4. No se puede usar en nuevos análisis

## 🎯 Reglas Importantes

### Stock Actual
- ⚠️ **NO se edita** desde este módulo
- Solo se modifica con:
  - Entradas de inventario (UC-A09)
  - Salidas de inventario (UC-A10)
  - Descuento automático al analizar (UC-S05)

### Stock Mínimo
- ✅ Se define al crear/editar insumo
- ✅ Específico por sucursal
- ⚠️ Cuando `stock_actual < stock_minimo` = alerta visual

### Estado de Insumos
- ✅ **Activo**: Se puede usar en análisis
- ❌ **Inactivo**: NO se puede usar en análisis
- 🚫 **No se pueden eliminar**, solo desactivar

### Unidades de Medida
- ✅ Solo unidades **activas** aparecen en formulario de insumos
- 🚫 **No se pueden eliminar**, solo desactivar

## 🔍 Búsqueda y Filtros

### Búsqueda de Insumos
- Busca por **nombre** del insumo
- Actualización en vivo (300ms debounce)

### Filtros Disponibles
- **Por sucursal**: Muestra columnas de stock
- **Solo stock bajo**: Filtra insumos con alerta

## 📊 Visualización de Datos

### Sin Filtro de Sucursal
| Nombre | Unidad | Estado | Acciones |
|--------|--------|--------|----------|
| Alcohol etílico | ml | ✓ | 🖊️ |

### Con Filtro de Sucursal
| Nombre | Unidad | Stock Actual | Stock Mínimo | Estado | Acciones |
|--------|--------|--------------|--------------|--------|----------|
| ⚠️ Alcohol etílico | ml | **20.00** | 50.00 | ✓ | 🏷️ Stock bajo 🖊️ |

## 🔧 Solución de Problemas

### Error: "La unidad de medida seleccionada no es válida"
**Solución:** Verifica que la unidad de medida esté activa.

### No aparecen sucursales en el formulario
**Solución:** 
1. Verifica que existan sucursales activas en el sistema
2. Ve a `/sucursales` y crea/activa sucursales

### Las alertas de stock no aparecen
**Solución:**
1. Debes seleccionar una **sucursal** en el filtro
2. Las alertas solo se muestran con filtro activo

## 💡 Consejos

### Organización de Insumos
- Usa nombres descriptivos
- Mantén las unidades consistentes
- Define stocks mínimos realistas

### Gestión de Stock Mínimo
- Considera el consumo promedio
- Ajusta según análisis frecuentes
- Revisa periódicamente las alertas

### Buenas Prácticas
- Desactiva en lugar de "eliminar"
- Mantén unidades de medida estandarizadas
- Revisa stock bajo semanalmente

## 🔗 Casos de Uso Relacionados

- **UC-A06**: Configurar tipo de análisis (asocia insumos requeridos)
- **UC-A09**: Entradas de inventario (aumenta stock)
- **UC-A10**: Salidas de inventario (disminuye stock)
- **UC-B05**: Registrar análisis (usa insumos)
- **UC-S05**: Descuento automático (resta stock al analizar)
- **UC-A17**: Dashboard de alertas (monitorea stock bajo)

## 📞 Soporte

Para más detalles técnicos, consulta `DOCUMENTACION_UC-A08.md`
