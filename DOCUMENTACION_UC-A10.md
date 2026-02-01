# 📦 UC-A10: Registrar Salida Manual de Insumos

## 📋 Información General

**Actor:** Administrador  
**Objetivo:** Disminuir stock de un insumo sin pasar por un análisis, dejando trazabilidad completa  
**Estado:** ✅ Implementado

---

## 🎯 Casos de Uso

### ¿Cuándo usar este módulo?

Cuando un insumo sale del inventario, pero **NO** porque fue consumido en un análisis.

**Ejemplos reales:**
- ✅ Reactivo vencido
- ✅ Insumo dañado / derramado  
- ✅ Pruebas internas / capacitación
- ✅ Uso extraordinario no asociado a muestra
- ✅ Corrección por error previo

> ⚠️ **Importante:** Esto NO debe mezclarse con consumo por análisis.

---

## 🔄 Flujo Completo

### 1. Acceso al Módulo
El administrador accede a:
```
Inventario → Salidas Manuales
Ruta: /inventario/salidas
```

### 2. Selección de Contexto

El sistema solicita:
- **Sucursal** (obligatorio)
- **Insumo** (obligatorio, filtra por sucursal)
- **Stock actual** (solo lectura, actualización automática)
- **Unidad de medida** (informativa)

> 📌 **Regla:** El insumo debe existir en el inventario de esa sucursal

### 3. Registro de la Salida

El administrador ingresa:

**Cantidad a retirar**
- Debe ser > 0
- No puede exceder el stock disponible

**Motivo de salida** (ENUM):
- `MERMA` - Pérdida por deterioro/manipulación
- `VENCIMIENTO` - Producto caducado
- `USO_EXTRAORDINARIO` - Uso no asociado a análisis
- `OTRO` - Otros motivos

**Observación** (obligatoria):
- Mínimo 10 caracteres
- Máximo 1000 caracteres
- Describe detalladamente el motivo

### 4. Validaciones del Sistema

Antes de guardar, el sistema valida:

| Validación | Error |
|------------|-------|
| `cantidad ≤ 0` | "La cantidad debe ser mayor a 0" |
| `cantidad > stock disponible` | "La cantidad no puede ser mayor al stock disponible" |
| `insumo inactivo` | "El insumo seleccionado está inactivo" |
| `sucursal inválida` | "Debe seleccionar una sucursal válida" |
| `observación < 10 caracteres` | "La observación debe tener al menos 10 caracteres" |

> 🚫 **Nunca se permite stock negativo**

### 5. Confirmación Explícita

El sistema muestra un modal con resumen:

```
📋 Confirmar Salida Manual

Sucursal: Central
Insumo: Reactivo X
Cantidad a retirar: -5.00 mL
Stock actual: 20.00 mL
Stock resultante: 15.00 mL
Motivo: VENCIMIENTO

¿Confirmar?
[Confirmar Salida] [Cancelar]
```

> 💡 Esto evita errores humanos

### 6. Ejecución de la Salida

Si el usuario confirma:

#### 6.1 Actualizar Inventario por Sucursal
```sql
UPDATE inventario_sucursal
SET stock_actual = stock_actual - cantidad
WHERE insumo_id = ? AND sucursal_id = ?
```

#### 6.2 Registrar Movimiento de Inventario
```php
MovimientoInventario::create([
    'tipo_movimiento' => 'SALIDA_MANUAL',
    'motivo' => 'VENCIMIENTO',  // ENUM
    'cantidad' => -5,            // Negativo para salidas
    'usuario_id' => auth()->id(),
    'sucursal_id' => $sucursal_id,
    'insumo_id' => $insumo_id,
    'fecha' => now(),
    'observacion' => $observacion,
]);
```

### 7. Resultado Final

✅ **Stock actualizado**  
✅ **Movimiento registrado**  
✅ **Auditoría completa**  
⚠️ **Alertas si stock < mínimo**

---

## 🚨 Alertas Posteriores (Automáticas)

Si después de la salida:
```php
stock_actual < stock_minimo
```

El sistema:
1. Marca el insumo como **STOCK BAJO**
2. Muestra mensaje de advertencia al admin
3. Aparece en:
   - Dashboard admin
   - Reporte por sucursal

---

## 🗄️ Estructura de Datos

### Tabla: `movimientos_inventario`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único |
| `insumo_id` | bigint | FK a insumos |
| `sucursal_id` | bigint | FK a sucursales |
| `tipo_movimiento` | ENUM | 'ENTRADA', 'SALIDA_MANUAL', 'CONSUMO_ANALISIS', 'AJUSTE' |
| `cantidad` | decimal(10,2) | Cantidad (negativo para salidas) |
| `motivo` | ENUM | Ver lista de motivos |
| `observacion` | text | Descripción detallada |
| `usuario_id` | bigint | FK a users (quién registró) |
| `fecha` | timestamp | Fecha/hora del movimiento |

### Tipos ENUM

**tipo_movimiento_enum:**
```sql
'ENTRADA', 'SALIDA_MANUAL', 'CONSUMO_ANALISIS', 'AJUSTE'
```

**motivo_enum:**
```sql
'MERMA', 'VENCIMIENTO', 'USO_EXTRAORDINARIO', 
'CONSUMO_ANALISIS', 'AJUSTE_INVENTARIO', 
'COMPRA', 'DONACION', 'OTRO'
```

---

## 📂 Archivos Creados

### Backend
```
app/Livewire/Inventario/
├── RegistrarSalida.php          # Componente principal UC-A10
└── HistorialMovimientos.php     # Vista de historial completo
```

### Vistas
```
resources/views/
├── inventario/
│   ├── salidas.blade.php        # Layout principal
│   └── historial.blade.php      # Layout historial
└── livewire/inventario/
    ├── registrar-salida.blade.php          # Formulario de salida
    └── historial-movimientos.blade.php     # Tabla de historial
```

### Migraciones
```
database/migrations/
└── 2026_01_30_000001_modify_movimientos_inventario_add_enums.php
```

### Rutas
```php
Route::get('/inventario/salidas', \App\Livewire\Inventario\RegistrarSalida::class)
    ->name('inventario.salidas');

Route::get('/inventario/historial', \App\Livewire\Inventario\HistorialMovimientos::class)
    ->name('inventario.historial');
```

---

## 🎨 Interfaz de Usuario

### Pantalla: Registrar Salida

**Secciones:**
1. **Formulario de Registro**
   - Sucursal (select)
   - Insumo (select, dependiente de sucursal)
   - Stock actual (readonly, actualización automática)
   - Cantidad a retirar (input numérico)
   - Motivo (select con opciones)
   - Observación (textarea)

2. **Historial Reciente**
   - Últimas 10 salidas
   - Filtro por sucursal
   - Badges de color según motivo

3. **Modal de Confirmación**
   - Resumen de datos
   - Advertencia si stock quedará bajo

### Pantalla: Historial de Movimientos

**Funcionalidades:**
- 📊 Estadísticas rápidas (total, entradas mes, salidas mes)
- 🔍 Filtros avanzados:
  - Por sucursal
  - Por insumo (búsqueda)
  - Por tipo de movimiento
  - Por motivo
  - Rango de fechas
- 📋 Tabla paginada con todos los movimientos
- 🎨 Badges de colores según tipo

---

## ✅ Validaciones Implementadas

### Validaciones Frontend (Livewire)
- ✅ Campos obligatorios
- ✅ Stock suficiente
- ✅ Cantidad > 0
- ✅ Insumo activo
- ✅ Insumo existe en sucursal

### Validaciones Backend (DB Transaction)
- ✅ Lock pesimista (`lockForUpdate()`)
- ✅ Doble verificación de stock
- ✅ Transacción atómica
- ✅ Rollback en caso de error

---

## 🔒 Seguridad

1. **Middleware de autenticación:** Solo usuarios autenticados
2. **Control de acceso:** Solo rol Administrador
3. **Transacciones DB:** Previene race conditions
4. **Lock pesimista:** Evita concurrencia
5. **Auditoría completa:** Todo movimiento registra usuario y fecha

---

## 📊 Estados Involucrados

Este CU **NO cambia** estados de:
- ❌ Análisis
- ❌ Muestras

Solo afecta:
- ✅ Inventario (tabla `inventario_sucursal`)
- ✅ Movimientos (tabla `movimientos_inventario`)

---

## 🧪 Casos de Prueba

### CP-01: Salida exitosa
**Precondiciones:** Stock disponible ≥ cantidad  
**Pasos:**
1. Seleccionar sucursal "Central"
2. Seleccionar insumo "Reactivo X" (stock: 20)
3. Ingresar cantidad: 5
4. Seleccionar motivo: "VENCIMIENTO"
5. Observación: "Lote vencido, fecha caducidad 01/2026"
6. Click "Registrar Salida"
7. Confirmar en modal

**Resultado esperado:**
- Stock actualizado: 15
- Movimiento registrado con cantidad: -5
- Mensaje de éxito

### CP-02: Cantidad mayor a stock disponible
**Precondiciones:** Stock disponible < cantidad  
**Pasos:**
1. Insumo con stock: 5
2. Intentar retirar: 10

**Resultado esperado:**
- ❌ Error: "La cantidad no puede ser mayor al stock disponible (5.00 mL)"
- No se registra el movimiento

### CP-03: Alerta de stock bajo
**Precondiciones:** stock_minimo = 10  
**Pasos:**
1. Stock actual: 12
2. Retirar: 5

**Resultado esperado:**
- Stock final: 7 (< 10)
- ⚠️ Mensaje: "Salida registrada. ALERTA: El stock quedó por debajo del mínimo"

---

## 🚀 Próximas Mejoras (Opcional)

- [ ] Exportar historial a PDF/Excel
- [ ] Notificaciones por email cuando stock < mínimo
- [ ] Dashboard de tendencias de consumo
- [ ] Predicción de reabastecimiento
- [ ] Integración con proveedores

---

## 📞 Soporte

Para dudas sobre este módulo, consultar con el equipo de desarrollo.
