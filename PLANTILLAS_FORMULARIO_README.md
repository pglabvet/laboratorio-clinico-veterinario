# Sistema de Plantillas de Formularios - Guía de Uso

## ✅ Implementación Completada

Se ha implementado exitosamente el **Sistema de Plantillas de Formularios con Base de Datos**.

---

## 📋 ¿Qué se creó?

### 1. **Base de Datos**
- ✅ Tabla `plantillas_formulario` creada
- ✅ Modelo `PlantillaFormulario` con relaciones

### 2. **Funcionalidades**
- ✅ Guardar plantillas en base de datos
- ✅ Cargar plantillas existentes
- ✅ Listar todas las plantillas
- ✅ Editar plantillas guardadas
- ✅ Duplicar plantillas
- ✅ Activar/Desactivar plantillas
- ✅ Eliminar plantillas
- ✅ Búsqueda de plantillas

---

## 🚀 Cómo Usar

### **Para Administradores:**

#### 1. **Crear Nueva Plantilla**
```
Ruta: /plantillas/crear
O: /plantillas (clic en "Nueva Plantilla")
```
- Ingresa el nombre del formulario (requerido)
- Opcionalmente agrega una descripción
- Arrastra componentes desde el panel izquierdo
- Configura las propiedades en el panel derecho
- Clic en "Guardar en Base de Datos"

#### 2. **Ver Todas las Plantillas**
```
Ruta: /plantillas
```
Verás una lista con:
- Nombre de la plantilla
- Descripción
- Cantidad de componentes
- Creador
- Fecha de creación
- Estado (Activa/Inactiva)

#### 3. **Editar Plantilla Existente**
```
Desde la lista de plantillas, clic en el ícono de editar (lápiz)
```
- Se cargará la plantilla con todos sus componentes
- Realiza los cambios necesarios
- Clic en "Guardar en Base de Datos" para actualizar

#### 4. **Duplicar Plantilla**
```
Desde la lista, clic en el ícono de copiar
```
- Se creará una copia con el nombre "(Copia)"
- Te redirigirá automáticamente a editar la nueva plantilla

#### 5. **Activar/Desactivar**
```
Clic en el ícono de ojo/ojo tachado
```
- Las inactivas no aparecerán para los bioquímicos

#### 6. **Eliminar Plantilla**
```
Clic en el ícono de basura (confirmación requerida)
```

---

### **Para Bioquímicos:**

Las plantillas guardadas ahora estarán disponibles en:
```
Ruta: /formularios/plantillas
```
- Ver solo plantillas activas
- Seleccionar una plantilla
- Rellenar el formulario con los datos del análisis
- Guardar resultados

---

## 🗄️ Estructura de Base de Datos

```sql
plantillas_formulario:
  - id
  - tipo_analisis_id (nullable, FK)
  - nombre (requerido)
  - descripcion (opcional)
  - componentes (JSON con toda la estructura)
  - activo (booleano, default true)
  - creado_por (FK a users)
  - created_at
  - updated_at
```

---

## 🔗 Rutas Disponibles

| Ruta | Propósito | Usuarios |
|------|-----------|----------|
| `/plantillas` | Listar plantillas | Admin |
| `/plantillas/crear` | Crear nueva plantilla | Admin |
| `/plantillas/{id}/editar` | Editar plantilla | Admin |
| `/formularios/plantillas` | Ver plantillas activas | Bioquímico |
| `/analisis/nuevo/{plantillaId}` | Rellenar análisis | Bioquímico |

---

## 💡 Ventajas de esta Implementación

1. **Persistencia Real**: Los formularios se guardan en la base de datos, no en LocalStorage
2. **Multi-usuario**: Varios administradores pueden crear y editar plantillas
3. **Auditoría**: Se registra quién creó cada plantilla y cuándo
4. **Versionamiento**: Puedes duplicar y modificar sin perder el original
5. **Control de Acceso**: Activa/desactiva plantillas según necesidad
6. **Búsqueda Eficiente**: Busca por nombre o descripción
7. **Escalable**: Fácil de extender con más funcionalidades

---

## 📝 Próximos Pasos Sugeridos

1. **Permisos**: Implementar control de permisos (solo admin puede crear/editar)
2. **Historial**: Guardar versiones anteriores de plantillas
3. **Compartir**: Permitir que una plantilla sea usada por múltiples sucursales
4. **Estadísticas**: Ver qué plantillas son más usadas
5. **Validaciones**: Agregar reglas de validación personalizadas

---

## ⚙️ Configuración Adicional (Opcional)

### Agregar al menú de navegación:
```blade
<a href="{{ route('plantillas.index') }}">
    <i class="fas fa-file-alt"></i> Plantillas
</a>
```

### Middleware de permisos (si usas Spatie):
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/plantillas', ...)->name('plantillas.index');
    Route::get('/plantillas/crear', ...)->name('plantillas.crear');
    Route::get('/plantillas/{plantilla}/editar', ...)->name('plantillas.editar');
});
```

---

## 🐛 Troubleshooting

**Problema**: No aparece el botón "Guardar en Base de Datos"
- **Solución**: Refresca la página con Ctrl+F5

**Problema**: Error al guardar
- **Solución**: Verifica que la migración se ejecutó correctamente
- **Comando**: `php artisan migrate:status`

**Problema**: No aparecen las plantillas en la lista
- **Solución**: Asegúrate de estar autenticado y tener permisos

---

## 📞 Soporte

Si encuentras algún problema o necesitas ayuda adicional, revisa:
1. Los logs de Laravel: `storage/logs/laravel.log`
2. La consola del navegador (F12)
3. Verifica que todas las migraciones estén ejecutadas

---

¡Listo! El sistema está completamente funcional y listo para usar. 🎉
