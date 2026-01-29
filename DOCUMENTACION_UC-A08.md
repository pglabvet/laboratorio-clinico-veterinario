# UC-A08 – Gestionar Inventario de Insumos

## Descripción General
Sistema completo para la gestión de insumos de laboratorio veterinario con control de inventario por sucursal y alertas de stock bajo.

## Estructura de Base de Datos

### Tablas Creadas

#### 1. **unidades_medida**
Gestiona las unidades de medida estandarizadas para los insumos.

```sql
- id: Identificador único
- nombre: Nombre completo (ej: "Mililitros")
- abreviatura: Abreviatura (ej: "ml")
- estado: Activo/Inactivo (boolean)
- timestamps: created_at, updated_at
```

#### 2. **insumos** (actualizada)
Define los insumos disponibles en el laboratorio.

```sql
- id: Identificador único
- nombre: Nombre del insumo
- categoria_id: FK opcional a categorias_insumo
- unidad_medida_id: FK requerida a unidades_medida
- estado: Activo/Inactivo (boolean)
- timestamps: created_at, updated_at
```

**Cambios realizados:**
- Eliminados campos: `unidad`, `stock_actual`, `stock_minimo`
- `categoria_id` ahora es nullable
- Agregada FK `unidad_medida_id`

#### 3. **inventario_sucursal** (nueva)
Gestiona el stock de insumos por sucursal.

```sql
- id: Identificador único
- insumo_id: FK a insumos
- sucursal_id: FK a sucursales
- stock_actual: Cantidad actual (decimal 10,2)
- stock_minimo: Cantidad mínima (decimal 10,2)
- timestamps: created_at, updated_at
- UNIQUE(insumo_id, sucursal_id): Evita duplicados
```

## Modelos Eloquent

### UnidadMedida
**Ubicación:** `app/Models/UnidadMedida.php`

**Relaciones:**
- `hasMany(Insumo)`: Un unidad de medida puede tener muchos insumos

**Scopes:**
- `activas()`: Filtra solo unidades activas

### Insumo (actualizado)
**Ubicación:** `app/Models/Insumo.php`

**Relaciones:**
- `belongsTo(UnidadMedida)`: Cada insumo tiene una unidad de medida
- `belongsTo(CategoriaInsumo)`: Relación opcional con categoría
- `hasMany(InventarioSucursal)`: Un insumo tiene inventarios en múltiples sucursales
- `hasMany(MovimientoInventario)`: Historial de movimientos
- `belongsToMany(TipoAnalisis)`: Insumos requeridos por tipo de análisis
- `belongsToMany(Analisis)`: Uso de insumos en análisis

**Scopes:**
- `activos()`: Filtra solo insumos activos

### InventarioSucursal (nuevo)
**Ubicación:** `app/Models/InventarioSucursal.php`

**Relaciones:**
- `belongsTo(Insumo)`: Cada inventario pertenece a un insumo
- `belongsTo(Sucursal)`: Cada inventario pertenece a una sucursal

**Scopes:**
- `stockBajo()`: Filtra inventarios donde stock_actual < stock_minimo

**Métodos:**
- `tieneStockBajo()`: Verifica si el stock está por debajo del mínimo

## Componentes Livewire

### 1. GestionarUnidadesMedida
**Ubicación:** `app/Livewire/UnidadesMedida/GestionarUnidadesMedida.php`

**Funcionalidades:**
- ✅ Listar unidades de medida con paginación
- ✅ Buscar por nombre o abreviatura
- ✅ Crear nueva unidad de medida
- ✅ Editar unidad existente
- ✅ Activar/desactivar unidades (sin eliminación)
- ✅ Ordenamiento por columnas
- ✅ Validaciones de formulario

**Reglas de negocio:**
- No se pueden eliminar unidades de medida
- Solo se pueden desactivar
- Unidades inactivas no aparecen en formulario de insumos

### 2. GestionarInsumos
**Ubicación:** `app/Livewire/Insumos/GestionarInsumos.php`

**Funcionalidades:**
- ✅ Listar insumos con paginación
- ✅ Buscar por nombre
- ✅ Filtrar por sucursal
- ✅ Mostrar solo insumos con stock bajo
- ✅ Crear nuevo insumo
- ✅ Editar insumo existente
- ✅ Activar/desactivar insumos (sin eliminación)
- ✅ Gestionar stock mínimo por sucursal
- ✅ Alertas visuales de stock bajo
- ✅ Ordenamiento por columnas
- ✅ Validaciones de formulario

**Reglas de negocio:**
- No se pueden eliminar insumos
- Solo se pueden desactivar
- Insumos inactivos no se pueden usar en análisis
- Stock actual SIEMPRE inicia en 0
- Stock mínimo se define por sucursal
- Stock solo se modifica mediante movimientos (UC-A09, UC-A10, UC-S05)

**Alertas de Stock Bajo:**
- 🟡 Fila con fondo amarillo
- ⚠️ Icono de advertencia
- 🏷️ Badge "Stock bajo"

## Vistas Blade

### 1. Unidades de Medida
**Ubicación:** `resources/views/livewire/unidades-medida/gestionar-unidades-medida.blade.php`

**Componentes:**
- Barra de búsqueda
- Botón "Nueva Unidad de Medida"
- Tabla con columnas: Nombre, Abreviatura, Estado, Acciones
- Modal de formulario (crear/editar)
- Modal de confirmación de cambio de estado

### 2. Insumos
**Ubicación:** `resources/views/livewire/insumos/gestionar-insumos.blade.php`

**Componentes:**
- Barra de búsqueda
- Filtro por sucursal
- Checkbox "Solo stock bajo"
- Botón "Nuevo Insumo"
- Tabla dinámica (columnas cambian según filtros)
- Alertas visuales de stock bajo
- Modal de formulario con sección de inventarios por sucursal
- Modal de confirmación de cambio de estado

## Rutas

```php
// Unidades de Medida
Route::view('/unidades-medida', 'unidades-medida.index')
    ->name('unidades-medida.index');

// Insumos
Route::view('/insumos', 'insumos.index')
    ->name('insumos.index');
```

## Migraciones Ejecutadas

1. `2026_01_09_000007_create_unidades_medida_table.php`
2. `2026_01_09_000008_create_insumos_table.php` (modificada)
3. `2026_01_09_000009_create_inventario_sucursal_table.php`

## Seeders Incluidos

### UnidadesMedidaSeeder
Carga unidades de medida comunes:

**Volumen:**
- Mililitros (ml)
- Litros (L)
- Microlitros (μl)

**Peso:**
- Gramos (g)
- Kilogramos (kg)
- Miligramos (mg)

**Cantidad:**
- Unidades (unid)
- Piezas (pza)
- Cajas (caja)

**Concentración:**
- Molar (M)
- Porcentaje (%)

## Flujo de Uso

### Crear un Insumo Nuevo

1. Ir a `/insumos`
2. Clic en "Nuevo Insumo"
3. Completar:
   - Nombre del insumo
   - Unidad de medida (select de unidades activas)
   - Estado (checkbox activo por defecto)
4. Definir stock mínimo para cada sucursal activa
5. Guardar

**Resultado:**
- Se crea el insumo con estado activo
- Se crean registros en `inventario_sucursal` para cada sucursal
- Stock actual = 0 para todas las sucursales
- Stock mínimo según lo definido

### Editar un Insumo

1. Clic en botón "Editar" (ícono lápiz)
2. Modificar datos necesarios
3. Ajustar stock mínimo por sucursal
4. Guardar

**Nota:** El stock actual NO se modifica desde aquí.

### Desactivar un Insumo

1. Clic en el switch de estado
2. Confirmar acción
3. El insumo queda inactivo
4. No se puede usar en nuevos análisis

### Verificar Stock Bajo

1. Seleccionar sucursal en filtro
2. Opcional: Activar checkbox "Solo stock bajo"
3. La tabla muestra:
   - Filas con fondo amarillo (stock bajo)
   - Icono de advertencia
   - Badge "Stock bajo"

## Integraciones con Otros Casos de Uso

### UC-A06 – Configurar Tipo de Análisis
Los insumos activos están disponibles para asociar a tipos de análisis.

### UC-A09 – Entradas de Inventario (futuro)
Aumenta `stock_actual` en `inventario_sucursal`.

### UC-A10 – Salidas de Inventario (futuro)
Disminuye `stock_actual` en `inventario_sucursal`.

### UC-S05 – Descuento Automático (futuro)
Al registrar resultados de análisis, se descuenta automáticamente del stock.

### UC-A17 – Dashboard de Alertas (futuro)
Muestra alertas de insumos con stock bajo.

## Validaciones Implementadas

### Unidades de Medida
- ✅ Nombre requerido (max 100 caracteres)
- ✅ Abreviatura requerida (max 10 caracteres)
- ✅ Estado boolean

### Insumos
- ✅ Nombre requerido (max 255 caracteres)
- ✅ Unidad de medida requerida (debe existir)
- ✅ Stock mínimo >= 0 para cada sucursal
- ✅ Estado boolean

## Características de UX

### Mensajes Toast
- ✅ Mensaje de éxito al crear/editar/cambiar estado
- ✅ Mensaje de error en caso de fallo
- ✅ Sin recarga de página (Livewire)

### Confirmaciones
- ✅ Confirmación al desactivar unidad de medida
- ✅ Confirmación al desactivar insumo
- ✅ Advertencia sobre consecuencias

### Búsqueda y Filtros
- ✅ Búsqueda en vivo con debounce 300ms
- ✅ Filtros reactivos sin recarga
- ✅ Paginación integrada

### Ordenamiento
- ✅ Clic en encabezados de columna
- ✅ Indicador visual de dirección
- ✅ Toggle asc/desc

## Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeder de unidades de medida
php artisan db:seed --class=UnidadesMedidaSeeder

# Limpiar cache de vistas
php artisan view:clear

# Limpiar cache de Livewire
php artisan livewire:clear
```

## Archivos Creados/Modificados

### Migraciones
- ✅ `database/migrations/2026_01_09_000007_create_unidades_medida_table.php`
- ✅ `database/migrations/2026_01_09_000008_create_insumos_table.php` (modificada)
- ✅ `database/migrations/2026_01_09_000009_create_inventario_sucursal_table.php`

### Modelos
- ✅ `app/Models/UnidadMedida.php`
- ✅ `app/Models/Insumo.php` (actualizado)
- ✅ `app/Models/InventarioSucursal.php`

### Componentes Livewire
- ✅ `app/Livewire/UnidadesMedida/GestionarUnidadesMedida.php`
- ✅ `app/Livewire/Insumos/GestionarInsumos.php`

### Vistas
- ✅ `resources/views/unidades-medida/index.blade.php`
- ✅ `resources/views/insumos/index.blade.php`
- ✅ `resources/views/livewire/unidades-medida/gestionar-unidades-medida.blade.php`
- ✅ `resources/views/livewire/insumos/gestionar-insumos.blade.php`

### Seeders
- ✅ `database/seeders/UnidadesMedidaSeeder.php`

### Rutas
- ✅ `routes/web.php` (actualizado)

## Estado del Proyecto

✅ **COMPLETADO** - Todos los requerimientos del UC-A08 han sido implementados exitosamente.

### Funcionalidades Implementadas:
1. ✅ Registrar insumos
2. ✅ Definir unidad de medida
3. ✅ Definir stock mínimo por sucursal
4. ✅ Activar/desactivar insumos
5. ✅ CRUD completo de unidades de medida
6. ✅ Sistema de inventario por sucursal
7. ✅ Alertas visuales de stock bajo
8. ✅ Búsqueda y filtros avanzados

### Próximos Pasos (Casos de Uso Relacionados):
- UC-A09: Entradas de inventario
- UC-A10: Salidas de inventario
- UC-B05: Uso de insumos en análisis
- UC-S05: Descuento automático de insumos
- UC-A17: Dashboard de alertas de stock
