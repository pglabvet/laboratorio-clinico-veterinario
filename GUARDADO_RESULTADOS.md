# Documentación: Sistema de Guardado de Resultados con JSON

## 📋 Resumen
Implementación completa del guardado de resultados dinámicos usando almacenamiento JSON en PostgreSQL.

## 🗃️ Estructura de Base de Datos

### Tabla `resultados`
```sql
- id (bigint)
- analisis_id (bigint) → FK a analisis
- parametro_id (bigint, nullable) → FK a parametros_analisis
- tipo (string) → Tipos: 'parametro', 'antibiograma', 'lista_items', etc.
- valor (json) → Almacena estructuras complejas
- fuera_rango (boolean)
- timestamps
```

## 📊 Ejemplos de Datos Guardados

### Antibiograma
```json
{
    "tipo": "antibiograma",
    "valor": [
        {
            "sensible": "LEVOFLOXACINA",
            "intermedio": "AZITROMICINA", 
            "resistente": "PENICILINA"
        },
        {
            "sensible": "CIPROFLOXACINO",
            "intermedio": "",
            "resistente": "AMOXICILINA"
        }
    ]
}
```

### Lista de Items
```json
{
    "tipo": "lista_items",
    "valor": [
        "Leucocitos elevados",
        "Hemoglobina normal",
        "Plaquetas normales"
    ]
}
```

## 🔄 Flujo de Funcionamiento

### 1. **Captura de Datos (Frontend)**
- Alpine.js gestiona los datos en el navegador
- `x-init="$watch('filas', () => enviarDatos())"` detecta cambios automáticamente
- Sincroniza datos con Livewire usando `$wire.set()`

### 2. **Sincronización Automática**
```javascript
// En antibiograma.blade.php
enviarDatos() {
    $wire.set('componentesData.{{ $index }}.data', this.filas);
}

// En lista-items.blade.php
enviarDatos() {
    const itemsFiltrados = this.items
        .filter(item => item.texto.trim() !== '')
        .map(item => item.texto);
    $wire.set('componentesData.{{ $index }}.data', itemsFiltrados);
}
```

### 3. **Guardado en Base de Datos**
```php
// En CapturarResultados.php - finalizarYEnviar()
foreach ($this->componentesData as $index => $componenteData) {
    if (!empty($componenteData['data'])) {
        Resultado::create([
            'analisis_id' => $this->analisis->id,
            'parametro_id' => null,
            'tipo' => $componenteData['tipo'],
            'valor' => $componenteData['data'], // Se convierte automáticamente a JSON
            'fuera_rango' => false,
        ]);
    }
}
```

## ✅ Ventajas del Sistema

1. **Flexibilidad**: Soporta cualquier estructura de datos compleja
2. **Escalabilidad**: Fácil agregar nuevos tipos de componentes
3. **Sincronización Automática**: Los datos se actualizan en tiempo real
4. **Filtrado Inteligente**: Solo guarda datos con contenido válido
5. **PostgreSQL JSON**: Soporte nativo con índices y consultas eficientes

## 🔍 Consultas PostgreSQL

```sql
-- Buscar antibiogramas con antibiótico específico
SELECT * FROM resultados 
WHERE tipo = 'antibiograma' 
AND valor::jsonb @> '[{"sensible": "LEVOFLOXACINA"}]'::jsonb;

-- Contar items en listas
SELECT 
    id,
    jsonb_array_length(valor::jsonb) as cantidad_items
FROM resultados 
WHERE tipo = 'lista_items';

-- Extraer datos específicos
SELECT 
    id,
    valor->0->>'sensible' as primer_sensible
FROM resultados 
WHERE tipo = 'antibiograma';
```

## 🚀 Próximas Mejoras

- [ ] Implementar validación de datos antes de guardar
- [ ] Agregar guardado de borradores con `guardarBorrador()`
- [ ] Crear vistas para visualizar resultados guardados
- [ ] Implementar historial de cambios con `historial_resultados`
- [ ] Agregar exportación a PDF de resultados

## 📝 Notas Importantes

- Los datos se guardan solo cuando se hace clic en "Finalizar y Enviar"
- El sistema filtra automáticamente campos vacíos en lista_items
- El campo `parametro_id` es nullable para componentes dinámicos
- Se usa transacción DB para garantizar integridad de datos
- El estado del análisis cambia a 'finalizado' automáticamente
