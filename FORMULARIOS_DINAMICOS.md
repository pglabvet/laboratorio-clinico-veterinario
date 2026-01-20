# 🧪 Sistema de Formularios Dinámicos - LabVet

Sistema completo para crear y rellenar formularios de análisis veterinarios de manera dinámica, sin necesidad de modificar código.

---

## 📋 Flujo Completo del Sistema

### 1️⃣ **ADMINISTRADOR** - Crear Plantillas
**Ruta:** `/formularios/constructor`

El administrador diseña la estructura de un tipo de análisis:
- Agrega componentes (tablas, textos, imágenes, antibiogramas, etc.)
- Configura propiedades de cada componente
- Define columnas, títulos, campos requeridos
- Guarda la plantilla con un nombre descriptivo

**Ejemplo:** Crear plantilla "Raspado Cutáneo" con:
- Tabla de información del paciente (incluida automáticamente)
- Microscopia directa (texto libre)
- Cultivo (subtítulo)
- Antibiograma (tabla)

---

### 2️⃣ **BIOQUÍMICO** - Ver Plantillas Disponibles
**Ruta:** `/formularios/plantillas`

El bioquímico ve todas las plantillas creadas:
- Lista con tarjetas de cada tipo de análisis
- Búsqueda por nombre
- Previsualización de componentes incluidos
- Botón "Usar esta Plantilla" para comenzar análisis

---

### 3️⃣ **BIOQUÍMICO** - Rellenar Análisis
**Ruta:** `/analisis/nuevo/{plantillaId}`

El bioquímico completa el formulario con datos reales:
- Campos editables según la plantilla
- Agregar/eliminar filas en tablas dinámicas
- Subir imágenes si está habilitado
- Barra de progreso
- Guardado automático como borrador
- Botón "Guardar Análisis" para finalizar

---

### 4️⃣ **VER ANÁLISIS COMPLETADOS**
**Ruta:** `/analisis`

Lista de todos los análisis realizados:
- Tabla con código, tipo, fecha, estado
- Filtros por plantilla y búsqueda
- Acciones: Ver, Descargar PDF, Eliminar
- Ordenar por fecha

---

## 🎨 Componentes Disponibles

### ⚠️ **Tabla de Información del Paciente** (Incluida automáticamente)
Esta tabla viene por defecto en todos los análisis con campos preestablecidos:
- PACIENTE, EDAD, ESPECIE, PROPIETARIO, SEXO, RAZA, SOLICITADO POR, COLOR, FECHA
- **No necesitas agregarla manualmente**

### 1. **Tabla de Resultados**
Para resultados de análisis con columnas personalizables
- Agregar/eliminar filas dinámicamente
- Columnas configurables (nombre, resultado, rangos)

### 2. **Antibiograma**
Tabla especializada para sensibilidad antibiótica
- 3 columnas: Sensible, Intermedio, Resistente
- Agregar antibióticos dinámicamente

### 3. **Texto Libre**
Para observaciones, descripciones
- Formato párrafo o lista
- Área de texto multilínea

### 4. **Lista de Items**
Para listas con viñetas
- Items predefinidos o ingresados

### 5. **Subtítulo**
Separadores de secciones
- Tamaño: grande, mediano, pequeño
- Alineación: izquierda, centro, derecha

### 6. **Campo de Imagen**
Para frotis sanguíneos, microscopías
- Subida de imagen opcional
- Tamaño configurable

### 7. **Campo de Texto Simple**
Para campos individuales
- Tipos: texto, número, fecha
- Con label y placeholder

---

## 🚀 Rutas del Sistema

```
📍 Rutas de Administrador:
GET /formularios/constructor          - Crear nueva plantilla
GET /formularios/constructor?plantilla={id} - Editar plantilla existente

📍 Rutas de Bioquímico:
GET /formularios/plantillas           - Ver plantillas disponibles
GET /analisis/nuevo/{plantillaId}     - Rellenar nuevo análisis
GET /analisis                         - Ver análisis completados

📍 Rutas futuras (cuando conectes BD):
GET /analisis/ver/{id}                - Ver detalle de análisis
GET /analisis/{id}/pdf                - Descargar PDF
```

---

## 💾 Almacenamiento Actual (Frontend)

**LocalStorage Keys:**
- `plantilla-{id}` - Plantillas creadas por el admin
- `analisis-{id}` - Análisis completados
- `borrador-{plantillaId}` - Borradores temporales

**Estructura de Plantilla:**
```json
{
  "nombre": "Raspado Cutáneo",
  "componentes": [
    {
      "id": "comp-123",
      "tipo": "tabla-info",
      "propiedades": {
        "titulo": "INFORMACIÓN DEL PACIENTE",
        "columnas": 3,
        "filas": [...]
      }
    }
  ],
  "createdAt": "2026-01-18T12:00:00.000Z"
}
```

**Estructura de Análisis:**
```json
{
  "plantillaId": "123456",
  "fecha": "2026-01-18T12:00:00.000Z",
  "datos": {
    "comp-123": {
      "nombre": "Firulais",
      "propietario": "Juan Pérez"
    }
  }
}
```

---

## 🔄 Próximos Pasos (Backend)

Cuando decidas conectar a base de datos:

1. **Crear Migraciones:**
   - `plantillas_formularios` (id, nombre, estructura_json, created_at)
   - `analisis` (id, plantilla_id, datos_json, muestra_id, created_at)

2. **Modificar Componentes Livewire:**
   - Cambiar `localStorage` por queries a BD
   - Agregar validaciones
   - Relacionar con muestras existentes

3. **Generar PDFs:**
   - Usar librería como DomPDF
   - Renderizar según estructura de la plantilla

4. **Permisos:**
   - Admin: crear/editar plantillas
   - Bioquímico: usar plantillas, crear análisis
   - Veterinario: solo ver análisis

---

## 🎯 Ventajas del Sistema

✅ **Flexibilidad Total:** El admin crea cualquier tipo de análisis sin tocar código
✅ **Reutilizable:** Cada plantilla se usa múltiples veces
✅ **Escalable:** Agregar nuevos tipos de componentes es fácil
✅ **Frontend Completo:** Todo funciona sin backend (por ahora)
✅ **UX Moderna:** Drag & drop, preview en tiempo real, autoguardado
✅ **Tabla de Info Automática:** Todos los análisis incluyen datos del paciente por defecto

---

## 📝 Ejemplo de Uso Completo

1. **Admin crea "Química Sanguínea":**
   - (Tabla de info del paciente ya incluida automáticamente)
   - Tabla de resultados (Glicemia, Creatinina, etc.)
   - Campo de texto para observaciones

2. **Bioquímico realiza análisis:**
   - Selecciona plantilla "Química Sanguínea"
   - Rellena: Paciente "Luna", Glicemia: 95, Creatinina: 1.2
   - Agrega observación: "Valores normales"
   - Guarda análisis

3. **Sistema almacena:**
   - Análisis guardado con timestamp
   - Disponible en listado
   - Listo para generar PDF

---

## 🛠️ Tecnologías Utilizadas

- **Laravel 11** - Framework PHP
- **Livewire 3** - Componentes reactivos
- **Alpine.js** - Interactividad JavaScript
- **Tailwind CSS** - Estilos
- **Font Awesome** - Iconos
- **LocalStorage** - Almacenamiento temporal

---

## 📞 Soporte

Si necesitas agregar más tipos de componentes o modificar funcionalidades, los archivos principales son:

- `app/Livewire/FormularioConstructor.php` - Lógica del constructor
- `resources/views/livewire/constructor/` - Vistas de componentes
- `app/Livewire/FormularioRellenar.php` - Lógica para rellenar
