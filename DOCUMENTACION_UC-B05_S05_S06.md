# 📋 ETAPA 5 - INVENTARIO: UC-B05, UC-S05, UC-S06

## ✅ IMPLEMENTACIÓN COMPLETADA

**Fecha:** 30 de Enero, 2026  
**Desarrollador:** GitHub Copilot + Usuario

---

## 🎯 CASOS DE USO IMPLEMENTADOS

### **UC-B05: Validar Stock al Iniciar Análisis**
**Estado:** ✅ COMPLETADO

**Descripción:**  
Valida la disponibilidad de insumos antes de permitir la creación de un análisis.

**Reglas de Negocio:**
- **BLOQUEO:** Si `stock_actual = 0` o `stock_actual < cantidad_requerida`
  - No permite crear el análisis
  - Muestra mensaje de error con detalles
  - Sugiere registrar entrada de inventario
  
- **ADVERTENCIA:** Si `stock_actual <= stock_minimo` (pero > cantidad_requerida)
  - Permite crear el análisis
  - Muestra alerta amarilla recomendando reabastecer
  - No bloquea el flujo

**Archivo Modificado:**
- `app/Livewire/Muestras/GestionarMuestras.php`
  - Método `validarStockDisponible()`
  - Se ejecuta en `guardar()` antes de crear análisis

**Flujo:**
1. Usuario intenta crear muestra con análisis
2. Sistema valida stock de insumos asociados a la plantilla
3. Si hay stock insuficiente → ERROR y bloquea
4. Si hay stock bajo (pero suficiente) → ADVIERTE pero continúa
5. Si hay stock adecuado → Crea muestra y análisis normalmente

---

### **UC-S05: Descontar Stock Automáticamente**
**Estado:** ✅ COMPLETADO

**Descripción:**  
Descuenta automáticamente los insumos del inventario cuando el bioquímico completa el análisis y lo envía a revisión.

**Momento de Ejecución:**  
Cuando el bioquímico hace clic en **"Completar y Revisar"** (estado = `COMPLETADO`)

**Acciones Realizadas:**
1. Obtiene plantilla activa asociada al tipo de análisis
2. Obtiene insumos requeridos por la plantilla con sus cantidades
3. Para cada insumo:
   - Verifica stock disponible en la sucursal
   - Descuenta cantidad requerida de `inventario_sucursal.stock_actual`
   - Registra consumo en tabla `analisis_insumos`
   - Crea movimiento en `movimientos_inventario` con tipo `CONSUMO_ANALISIS`
4. Verifica stock mínimo post-descuento (UC-S06)

**Archivo Modificado:**
- `app/Livewire/Analisis/RegistrarResultados.php`
  - Método `descontarInsumos()`
  - Se ejecuta en `completarYRevisar()` dentro de transacción DB

**Seguridad:**
- Todo se ejecuta en transacción de base de datos
- Si falla cualquier paso, hace rollback completo
- Validación de stock antes de descontar

---

### **UC-S06: Validar Stock Mínimo Post-Descuento**
**Estado:** ✅ COMPLETADO

**Descripción:**  
Genera alertas automáticas cuando un insumo queda por debajo del stock mínimo después de descontar.

**Momento de Ejecución:**  
Inmediatamente después de UC-S05 (en el mismo flujo)

**Lógica:**
```php
if ($inventario->stock_actual < $inventario->stock_minimo) {
    $insumosConStockBajo[] = $insumo->nombre;
}
```

**Resultado:**
- Genera alerta amarilla visible en pantalla
- Formato: "⚠️ ALERTA: Los siguientes insumos quedaron por debajo del stock mínimo: [lista]"
- No bloquea el flujo (es informativo)

---

## 🔗 ASOCIACIÓN DE INSUMOS A PLANTILLAS

### **Decisión de Diseño:**
Los insumos se asocian a **PLANTILLAS**, NO a tipos de análisis.

**Justificación:**
- Una plantilla es más específica que un tipo de análisis
- Diferentes plantillas del mismo tipo pueden requerir distintos insumos
- Mayor flexibilidad y granularidad

### **Tabla Pivot Creada:**
```php
Schema::create('plantilla_insumos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plantilla_formulario_id')->constrained()->onDelete('cascade');
    $table->foreignId('insumo_id')->constrained()->onDelete('cascade');
    $table->decimal('cantidad_requerida', 10, 2);
    $table->timestamps();
    $table->unique(['plantilla_formulario_id', 'insumo_id']);
});
```

### **Interfaz de Usuario:**
Al crear/editar una plantilla, el administrador puede:
1. Agregar insumos haciendo clic en "+ Agregar Insumo"
2. Seleccionar insumo del dropdown (muestra: nombre + unidad)
3. Especificar cantidad requerida
4. Eliminar insumos no necesarios

**Archivo Modificado:**
- `app/Livewire/Plantillas/GestionarPlantillas.php`
  - Propiedades: `$insumos`
  - Métodos: `agregarInsumo()`, `eliminarInsumo()`, `sincronizarInsumos()`
  
- `resources/views/livewire/plantillas/gestionar-plantillas.blade.php`
  - Sección "Insumos Requeridos"

---

## 📊 FLUJO COMPLETO INTEGRADO

```
1. CREAR MUESTRA (UC-B05)
   └─> Validar stock disponible
       ├─> Stock = 0 o insuficiente → ❌ BLOQUEA
       ├─> Stock bajo pero suficiente → ⚠️ ADVIERTE
       └─> Stock OK → ✅ Crea muestra y análisis

2. REGISTRAR RESULTADOS (Bioquímico)
   └─> Llena formulario con datos
   └─> Sube imágenes

3. COMPLETAR Y REVISAR (UC-S05)
   └─> Descuenta stock automáticamente
       ├─> Actualiza `inventario_sucursal`
       ├─> Registra en `analisis_insumos`
       └─> Crea `movimiento_inventario`
   
4. VALIDAR STOCK MÍNIMO (UC-S06)
   └─> Si stock < mínimo → ⚠️ Genera alerta
```

---

## 🗂️ ARCHIVOS MODIFICADOS

### Migraciones:
- ✅ `2026_01_30_200000_create_plantilla_insumos_table.php`

### Modelos:
- ✅ `app/Models/PlantillaFormulario.php` → Relación `insumos()`

### Componentes Livewire:
- ✅ `app/Livewire/Plantillas/GestionarPlantillas.php`
- ✅ `app/Livewire/Muestras/GestionarMuestras.php`
- ✅ `app/Livewire/Analisis/RegistrarResultados.php`

### Vistas:
- ✅ `resources/views/livewire/plantillas/gestionar-plantillas.blade.php`
- ✅ `resources/views/livewire/muestras/gestionar-muestras.blade.php`
- ✅ `resources/views/livewire/analisis/registrar-resultados.blade.php`

---

## 🧪 PRUEBAS SUGERIDAS

### Test UC-B05:
1. Crear insumo con stock = 0 en sucursal
2. Asociar insumo a plantilla (cantidad = 1)
3. Intentar crear muestra con análisis de esa plantilla
4. **Resultado esperado:** ERROR, no permite crear

### Test UC-S05:
1. Crear insumo con stock = 10
2. Asociar a plantilla (cantidad = 3)
3. Crear muestra y análisis
4. Registrar resultados y hacer "Completar y Revisar"
5. **Resultado esperado:** Stock queda en 7, movimiento creado

### Test UC-S06:
1. Insumo con stock = 5, stock_minimo = 10
2. Consumir 2 unidades
3. **Resultado esperado:** Alerta amarilla "Stock bajo"

---

## 📝 NOTAS IMPORTANTES

### Advertencias (Warnings):
El sistema ya tenía soporte para warnings en el componente `toast`:
```php
<x-toast type="warning" :message="session('warning')" />
```

### Transacciones:
Ambos flujos (crear muestra y completar análisis) usan transacciones de BD:
```php
DB::beginTransaction();
try {
    // Operaciones
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

### Relación Plantilla-Insumos:
```php
// En PlantillaFormulario
public function insumos(): BelongsToMany
{
    return $this->belongsToMany(Insumo::class, 'plantilla_insumos')
        ->withPivot('cantidad_requerida')
        ->withTimestamps();
}
```

---

## ✨ CARACTERÍSTICAS ADICIONALES

1. **Mensajes Descriptivos:**
   - Errores claros con nombres de insumos y cantidades
   - Alertas diferenciadas (bloqueantes vs informativas)

2. **Trazabilidad Completa:**
   - Todo movimiento queda registrado en `movimientos_inventario`
   - Incluye observación con detalles del análisis

3. **Validación en Múltiples Niveles:**
   - Al crear muestra (preventivo)
   - Al descontar (defensivo)
   - Post-descuento (informativo)

---

## 🎉 ESTADO FINAL

| Caso de Uso | Estado | Implementación |
|-------------|--------|----------------|
| UC-A08 | ✅ Completado | CRUD Insumos |
| UC-A09 | ✅ Completado | Entradas |
| UC-A10 | ✅ Completado | Salidas |
| **UC-B05** | ✅ **COMPLETADO** | **Validación Stock** |
| **UC-S05** | ✅ **COMPLETADO** | **Descuento Automático** |
| **UC-S06** | ✅ **COMPLETADO** | **Alerta Stock Mínimo** |

---

## 📚 REFERENCIAS

- [DOCUMENTACION_UC-A08.md](DOCUMENTACION_UC-A08.md) - Gestión de Insumos
- [DOCUMENTACION_UC-A10.md](DOCUMENTACION_UC-A10.md) - Salidas de Inventario
- [GUIA_RAPIDA_INSUMOS.md](GUIA_RAPIDA_INSUMOS.md) - Guía para usuarios

---

**¡ETAPA 5 - INVENTARIO COMPLETADA AL 100%!** 🎊
