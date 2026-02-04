<?php

namespace App\Livewire\Resultados;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Analisis;
use App\Models\PlantillaFormulario;
use App\Models\Resultado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CapturarResultados extends Component
{
    use WithFileUploads;
    
    public $analisis;
    public $plantilla;
    public $resultados = [];
    public $modoEdicion = false;
    public $modoRevision = false; // Para mostrar botones de aprobar/rechazar
    
    // Propiedades para capturar datos dinámicos de componentes
    public $componentesData = [];
    
    // Propiedades para rechazo
    public $mostrarModalRechazo = false;
    public $observacionesRechazo = '';
    
    // Propiedades para manejo de imágenes
    public $imagenes = [];
    
    public function mount($analisisId)
    {
        // Cargar el análisis con todas sus relaciones
        $this->analisis = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
            'muestra.sucursal',
            'tipoAnalisis.plantillas',
            'bioquimico',
            'resultados'
        ])->findOrFail($analisisId);
        
        // Obtener la plantilla activa del tipo de análisis
        $this->plantilla = $this->analisis->tipoAnalisis
            ->plantillas()
            ->where('activo', true)
            ->first();
        
        // Verificar que existe una plantilla
        if (!$this->plantilla) {
            session()->flash('error', 'Este tipo de análisis no tiene una plantilla activa asignada.');
            return redirect()->route('muestras.index');
        }
        
        // Inicializar resultados vacíos para cada componente
        $this->inicializarResultados();
        
        // Si el análisis ya tiene resultados, cargarlos (modo edición)
        if ($this->analisis->resultados->isNotEmpty()) {
            $this->modoEdicion = true;
            $this->cargarResultadosExistentes();
        }
        
        // Detectar si es modo revisión (análisis en revisión o aprobado)
        if (in_array($this->analisis->estado, ['En revision', 'Aprobado'])) {
            $this->modoRevision = true;
        }
    }
    
    private function inicializarResultados()
    {
        // Inicializar array para almacenar datos de cada componente
        foreach ($this->plantilla->componentes as $index => $componente) {
            $this->componentesData[$index] = [
                'tipo' => $componente['tipo'],
                'data' => []
            ];
            
            // Inicializar array de imágenes si el componente es campo-imagenes
            if ($componente['tipo'] === 'campo-imagenes') {
                $this->imagenes[$index] = [
                    'imagen1' => null,
                    'imagen2' => null,
                    'preview1' => null,
                    'preview2' => null
                ];
            }
        }
    }
    
    /**
     * Cargar resultados existentes para modo edición
     */
    private function cargarResultadosExistentes()
    {
        $resultadosAgrupados = $this->analisis->resultados->groupBy('tipo');
        
        foreach ($this->plantilla->componentes as $index => $componente) {
            $tipo = $componente['tipo'];
            
            if (isset($resultadosAgrupados[$tipo])) {
                $resultado = $resultadosAgrupados[$tipo]->first();
                $this->componentesData[$index]['data'] = $resultado->valor;
                
                // Si es campo-imagenes, marcar que tiene imágenes guardadas
                if ($tipo === 'campo-imagenes') {
                    $this->imagenes[$index]['preview1'] = $resultado->valor['imagen1'] ?? null;
                    $this->imagenes[$index]['preview2'] = $resultado->valor['imagen2'] ?? null;
                }
            }
        }
    }
    
    /**
     * Actualizar imagen cuando se sube
     */
    public function updatedImagenes($value, $key)
    {
        // $key viene en formato: "0.imagen1" o "0.imagen2"
        $parts = explode('.', $key);
        $componentIndex = $parts[0];
        $imagenKey = $parts[1];
        
        // Validar imagen
        $this->validate([
            "imagenes.{$componentIndex}.{$imagenKey}" => 'image|max:10240', // 10MB máx
        ], [
            "imagenes.{$componentIndex}.{$imagenKey}.image" => 'El archivo debe ser una imagen',
            "imagenes.{$componentIndex}.{$imagenKey}.max" => 'La imagen no debe superar 10MB'
        ]);
    }
    
    /**
     * Eliminar una imagen guardada previamente
     */
    public function eliminarImagenGuardada($index, $previewKey)
    {
        if (isset($this->imagenes[$index][$previewKey])) {
            // Limpiar el preview para que la imagen no se muestre más
            $this->imagenes[$index][$previewKey] = null;
            
            session()->flash('success', 'Imagen eliminada. Los cambios se aplicarán al guardar.');
        }
    }
    
    /**
     * Actualizar estado de la muestra a 'En proceso' cuando se empieza a capturar datos
     */
    private function actualizarMuestraEnProceso()
    {
        // Solo actualizar si la muestra está en estado 'Pendiente'
        if ($this->analisis->muestra->estado === 'Pendiente') {
            $this->analisis->muestra->update([
                'estado' => 'En proceso'
            ]);
        }
    }
    
    /**
     * Verificar si todos los análisis de la muestra están completados
     */
    private function verificarMuestraCompletada()
    {
        $muestra = $this->analisis->muestra->fresh();
        $analisisRestantes = $muestra->analisis()->whereNotIn('estado', ['En revision', 'Aprobado', 'Enviado'])->count();
        
        // Si no quedan análisis pendientes o en proceso, marcar muestra como completada
        if ($analisisRestantes === 0) {
            $muestra->update([
                'estado' => 'Completado'
            ]);
        }
    }

    public function guardarBorrador()
    {
        try {
            DB::beginTransaction();
            
            // Obtener las imágenes que se van a mantener
            $imagenesAMantener = [];
            foreach ($this->imagenes as $index => $imagenData) {
                if (!empty($imagenData['preview1'])) {
                    $imagenesAMantener[] = $imagenData['preview1'];
                }
                if (!empty($imagenData['preview2'])) {
                    $imagenesAMantener[] = $imagenData['preview2'];
                }
            }
            
            // Eliminar solo las imágenes que NO se van a mantener
            $imagenesAnteriores = $this->analisis->resultados()->where('tipo', 'campo-imagenes')->get();
            foreach ($imagenesAnteriores as $resultado) {
                if (isset($resultado->valor['imagen1']) && !in_array($resultado->valor['imagen1'], $imagenesAMantener)) {
                    Storage::disk('public')->delete($resultado->valor['imagen1']);
                }
                if (isset($resultado->valor['imagen2']) && !in_array($resultado->valor['imagen2'], $imagenesAMantener)) {
                    Storage::disk('public')->delete($resultado->valor['imagen2']);
                }
            }
            
            // Eliminar todos los resultados anteriores de la BD
            $this->analisis->resultados()->delete();
            
            $resultadosGuardados = 0;
            
            // Guardar todos los resultados de componentes dinámicos
            foreach ($this->componentesData as $index => $componenteData) {
                $componente = $this->plantilla->componentes[$index];
                
                // Manejar componente de imágenes
                if ($componenteData['tipo'] === 'campo-imagenes' && isset($this->imagenes[$index])) {
                    $imagenesGuardadas = [];
                    
                    // Guardar imagen 1
                    if (!empty($this->imagenes[$index]['imagen1'])) {
                        // Nueva imagen subida
                        $path1 = $this->imagenes[$index]['imagen1']->store('analisis/imagenes', 'public');
                        $imagenesGuardadas['imagen1'] = $path1;
                    } elseif (!empty($this->imagenes[$index]['preview1'])) {
                        // Mantener imagen anterior (no fue eliminada ni reemplazada)
                        $imagenesGuardadas['imagen1'] = $this->imagenes[$index]['preview1'];
                    }
                    
                    // Guardar imagen 2
                    if (!empty($this->imagenes[$index]['imagen2'])) {
                        // Nueva imagen subida
                        $path2 = $this->imagenes[$index]['imagen2']->store('analisis/imagenes', 'public');
                        $imagenesGuardadas['imagen2'] = $path2;
                    } elseif (!empty($this->imagenes[$index]['preview2'])) {
                        // Mantener imagen anterior (no fue eliminada ni reemplazada)
                        $imagenesGuardadas['imagen2'] = $this->imagenes[$index]['preview2'];
                    }
                    
                    // Guardar en BD si hay imágenes
                    if (!empty($imagenesGuardadas)) {
                        Resultado::create([
                            'analisis_id' => $this->analisis->id,
                            'parametro_id' => null,
                            'tipo' => 'campo-imagenes',
                            'valor' => $imagenesGuardadas,
                            'fuera_rango' => false,
                        ]);
                        
                        $resultadosGuardados++;
                    }
                    
                    continue;
                }
                
                // Solo guardar si hay datos y no están vacíos
                if (!empty($componenteData['data'])) {
                    // Filtrar datos vacíos dependiendo del tipo
                    $datosParaGuardar = $this->filtrarDatosVacios($componenteData['data'], $componenteData['tipo']);
                    
                    if (!empty($datosParaGuardar)) {
                        Resultado::create([
                            'analisis_id' => $this->analisis->id,
                            'parametro_id' => null,
                            'tipo' => $componenteData['tipo'],
                            'valor' => $datosParaGuardar,
                            'fuera_rango' => false,
                        ]);
                        
                        $resultadosGuardados++;
                    }
                }
            }
            
            // NO actualizar estado ni fecha_finalizacion - permanece en captura
            
            DB::commit();
            
            // Guardar el código de muestra en sesión para que se cargue automáticamente
            session()->put('codigo_escaneado', $this->analisis->muestra->codigo_muestra);
            
            session()->flash('success', "Borrador guardado correctamente. Se guardaron {$resultadosGuardados} componente(s).");
            return redirect()->route('muestras.escanear');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al guardar borrador:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al guardar borrador: ' . $e->getMessage());
        }
    }
    
    public function finalizarYEnviar()
    {
        try {
            DB::beginTransaction();
            
            // Si es modo edición, eliminar resultados anteriores
            if ($this->modoEdicion) {
                // Obtener las imágenes que se van a mantener
                $imagenesAMantener = [];
                foreach ($this->imagenes as $index => $imagenData) {
                    if (!empty($imagenData['preview1'])) {
                        $imagenesAMantener[] = $imagenData['preview1'];
                    }
                    if (!empty($imagenData['preview2'])) {
                        $imagenesAMantener[] = $imagenData['preview2'];
                    }
                }
                
                // Eliminar solo las imágenes que NO se van a mantener
                $imagenesAnteriores = $this->analisis->resultados()->where('tipo', 'campo-imagenes')->get();
                foreach ($imagenesAnteriores as $resultado) {
                    if (isset($resultado->valor['imagen1']) && !in_array($resultado->valor['imagen1'], $imagenesAMantener)) {
                        Storage::disk('public')->delete($resultado->valor['imagen1']);
                    }
                    if (isset($resultado->valor['imagen2']) && !in_array($resultado->valor['imagen2'], $imagenesAMantener)) {
                        Storage::disk('public')->delete($resultado->valor['imagen2']);
                    }
                }
                
                // Eliminar todos los resultados anteriores de la BD
                $this->analisis->resultados()->delete();
            }
            
            $resultadosGuardados = 0;
            
            // Guardar todos los resultados de componentes dinámicos
            foreach ($this->componentesData as $index => $componenteData) {
                $componente = $this->plantilla->componentes[$index];
                
                // Manejar componente de imágenes
                if ($componenteData['tipo'] === 'campo-imagenes' && isset($this->imagenes[$index])) {
                    $imagenesGuardadas = [];
                    
                    // Guardar imagen 1
                    if (!empty($this->imagenes[$index]['imagen1'])) {
                        // Nueva imagen subida
                        $path1 = $this->imagenes[$index]['imagen1']->store('analisis/imagenes', 'public');
                        $imagenesGuardadas['imagen1'] = $path1;
                    } elseif (!empty($this->imagenes[$index]['preview1'])) {
                        // Mantener imagen anterior (no fue eliminada ni reemplazada)
                        $imagenesGuardadas['imagen1'] = $this->imagenes[$index]['preview1'];
                    }
                    
                    // Guardar imagen 2
                    if (!empty($this->imagenes[$index]['imagen2'])) {
                        // Nueva imagen subida
                        $path2 = $this->imagenes[$index]['imagen2']->store('analisis/imagenes', 'public');
                        $imagenesGuardadas['imagen2'] = $path2;
                    } elseif (!empty($this->imagenes[$index]['preview2'])) {
                        // Mantener imagen anterior (no fue eliminada ni reemplazada)
                        $imagenesGuardadas['imagen2'] = $this->imagenes[$index]['preview2'];
                    }
                    
                    // Guardar en BD si hay imágenes
                    if (!empty($imagenesGuardadas)) {
                        Resultado::create([
                            'analisis_id' => $this->analisis->id,
                            'parametro_id' => null,
                            'tipo' => 'campo-imagenes',
                            'valor' => $imagenesGuardadas,
                            'fuera_rango' => false,
                        ]);
                        
                        $resultadosGuardados++;
                    }
                    
                    continue;
                }
                
                // Solo guardar si hay datos y no están vacíos
                if (!empty($componenteData['data'])) {
                    // Filtrar datos vacíos dependiendo del tipo
                    $datosParaGuardar = $this->filtrarDatosVacios($componenteData['data'], $componenteData['tipo']);
                    
                    if (!empty($datosParaGuardar)) {
                        Resultado::create([
                            'analisis_id' => $this->analisis->id,
                            'parametro_id' => null,
                            'tipo' => $componenteData['tipo'],
                            'valor' => $datosParaGuardar,
                            'fuera_rango' => false,
                        ]);
                        
                        $resultadosGuardados++;
                    }
                }
            }
            
            // Actualizar estado de la muestra a 'En proceso' si estaba pendiente
            $this->actualizarMuestraEnProceso();
            
            // Actualizar estado del análisis a 'En revision'
            $this->analisis->update([
                'estado' => Analisis::ESTADO_EN_REVISION,
                'fecha_finalizacion' => now(),
            ]);
            
            // Verificar si todos los análisis de la muestra están completados
            $this->verificarMuestraCompletada();
            
            DB::commit();
            
            // Guardar el código de muestra en sesión para que se cargue automáticamente
            session()->put('codigo_escaneado', $this->analisis->muestra->codigo_muestra);
            
            session()->flash('success', "Resultados enviados correctamente. Se guardaron {$resultadosGuardados} componente(s).");
            return redirect()->route('muestras.escanear');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al guardar resultados:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al guardar los resultados: ' . $e->getMessage());
        }
    }
    
    /**
     * Filtrar datos vacíos según el tipo de componente
     */
    private function filtrarDatosVacios($data, $tipo)
    {
        if (empty($data)) {
            return null;
        }
        
        switch ($tipo) {
            case 'antibiograma':
                // Filtrar filas donde todas las columnas estén vacías
                return array_filter($data, function($fila) {
                    return !empty($fila['sensible']) || !empty($fila['intermedio']) || !empty($fila['resistente']);
                });
                
            case 'lista_items':
                // Ya viene filtrado desde el frontend
                return array_filter($data);
                
            case 'tabla-resultados':
                // Filtrar filas que tengan al menos un campo con valor (excluyendo 'nombre')
                return array_filter($data, function($item) {
                    if (is_array($item)) {
                        // Verificar todos los campos excepto 'nombre'
                        foreach ($item as $key => $value) {
                            if ($key !== 'nombre' && !empty($value)) {
                                return true;
                            }
                        }
                        return false;
                    }
                    return !empty($item);
                });
                
            case 'tabla-dos-columnas':
            case 'campos-etiquetados':
                // Filtrar campos con valor vacío
                return array_filter($data, function($item) {
                    if (is_array($item)) {
                        return !empty($item['valor']) || !empty($item['resultado']);
                    }
                    return !empty($item);
                });
                
            case 'tabla-temporal':
                // Filtrar filas que tengan resultado ingresado
                return array_filter($data, function($fila) {
                    return isset($fila['resultado']) && $fila['resultado'] !== '' && $fila['resultado'] !== null;
                });
                
            case 'tabla-hematologica':
                // Filtrar valores vacíos en cada sección
                if (isset($data['parametros'])) {
                    $data['parametros'] = array_filter($data['parametros'], function($p) {
                        return !empty($p['resultado']);
                    });
                }
                if (isset($data['diferenciales'])) {
                    $data['diferenciales'] = array_filter($data['diferenciales'], function($d) {
                        return !empty($d['valor_rel']) || !empty($d['valor_abs']);
                    });
                }
                if (isset($data['indices'])) {
                    $data['indices'] = array_filter($data['indices'], function($i) {
                        return !empty($i['resultado']);
                    });
                }
                return $data;
                
            case 'campo-texto':
            case 'texto-libre':
                // Solo guardar si el contenido/valor no está vacío
                if (is_array($data)) {
                    return (!empty($data['valor']) || !empty($data['contenido'])) ? $data : null;
                }
                return !empty($data) ? $data : null;
                
            default:
                return $data;
        }
    }
    
    public function aprobarAnalisis()
    {
        try {
            DB::beginTransaction();
            
            // Primero guardar todos los cambios de resultados
            $this->guardarResultadosInterno();
            
            // Luego aprobar el análisis
            $this->analisis->update([
                'estado' => Analisis::ESTADO_APROBADO,
                'aprobador_id' => auth()->id(),
                'fecha_aprobacion' => now(),
            ]);
            
            // Verificar si todos los análisis de la muestra están completados
            $this->verificarMuestraCompletada();
            
            DB::commit();
            
            session()->flash('success', 'Análisis aprobado exitosamente');
            return redirect()->route('analisis.revisar');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al aprobar el análisis: ' . $e->getMessage());
        }
    }
    
    /**
     * Método interno para guardar resultados sin cambiar estado
     */
    private function guardarResultadosInterno()
    {
        // Obtener las imágenes que se van a mantener
        $imagenesAMantener = [];
        foreach ($this->imagenes as $index => $imagenData) {
            if (!empty($imagenData['preview1'])) {
                $imagenesAMantener[] = $imagenData['preview1'];
            }
            if (!empty($imagenData['preview2'])) {
                $imagenesAMantener[] = $imagenData['preview2'];
            }
        }
        
        // Eliminar solo las imágenes que NO se van a mantener
        $imagenesAnteriores = $this->analisis->resultados()->where('tipo', 'campo-imagenes')->get();
        foreach ($imagenesAnteriores as $resultado) {
            if (isset($resultado->valor['imagen1']) && !in_array($resultado->valor['imagen1'], $imagenesAMantener)) {
                Storage::disk('public')->delete($resultado->valor['imagen1']);
            }
            if (isset($resultado->valor['imagen2']) && !in_array($resultado->valor['imagen2'], $imagenesAMantener)) {
                Storage::disk('public')->delete($resultado->valor['imagen2']);
            }
        }
        
        // Eliminar todos los resultados anteriores de la BD
        $this->analisis->resultados()->delete();
        
        // Guardar todos los resultados de componentes dinámicos
        foreach ($this->componentesData as $index => $componenteData) {
            $componente = $this->plantilla->componentes[$index];
            
            // Manejar componente de imágenes
            if ($componenteData['tipo'] === 'campo-imagenes' && isset($this->imagenes[$index])) {
                $imagenesGuardadas = [];
                
                // Guardar imagen 1
                if (!empty($this->imagenes[$index]['imagen1'])) {
                    $path1 = $this->imagenes[$index]['imagen1']->store('analisis/imagenes', 'public');
                    $imagenesGuardadas['imagen1'] = $path1;
                } elseif (!empty($this->imagenes[$index]['preview1'])) {
                    $imagenesGuardadas['imagen1'] = $this->imagenes[$index]['preview1'];
                }
                
                // Guardar imagen 2
                if (!empty($this->imagenes[$index]['imagen2'])) {
                    $path2 = $this->imagenes[$index]['imagen2']->store('analisis/imagenes', 'public');
                    $imagenesGuardadas['imagen2'] = $path2;
                } elseif (!empty($this->imagenes[$index]['preview2'])) {
                    $imagenesGuardadas['imagen2'] = $this->imagenes[$index]['preview2'];
                }
                
                if (!empty($imagenesGuardadas)) {
                    Resultado::create([
                        'analisis_id' => $this->analisis->id,
                        'parametro_id' => null,
                        'tipo' => 'campo-imagenes',
                        'valor' => $imagenesGuardadas,
                        'fuera_rango' => false,
                    ]);
                }
                
                continue;
            }
            
            // Solo guardar si hay datos y no están vacíos
            if (!empty($componenteData['data'])) {
                $datosParaGuardar = $this->filtrarDatosVacios($componenteData['data'], $componenteData['tipo']);
                
                if (!empty($datosParaGuardar)) {
                    Resultado::create([
                        'analisis_id' => $this->analisis->id,
                        'parametro_id' => null,
                        'tipo' => $componenteData['tipo'],
                        'valor' => $datosParaGuardar,
                        'fuera_rango' => false,
                    ]);
                }
            }
        }
    }
    
    public function abrirModalRechazo()
    {
        $this->mostrarModalRechazo = true;
        $this->observacionesRechazo = '';
    }
    
    public function rechazarAnalisis()
    {
        $this->validate([
            'observacionesRechazo' => 'required|min:10',
        ], [
            'observacionesRechazo.required' => 'Debe indicar el motivo del rechazo',
            'observacionesRechazo.min' => 'El motivo debe tener al menos 10 caracteres',
        ]);
        
        try {
            $this->analisis->update([
                'estado' => Analisis::ESTADO_PENDIENTE, // Volver a pendiente para que lo corrijan
                'aprobador_id' => auth()->id(),
                'observaciones_aprobador' => $this->observacionesRechazo,
                'fecha_aprobacion' => now(),
            ]);
            
            $this->mostrarModalRechazo = false;
            session()->flash('success', 'Análisis rechazado. El bioquímico deberá corregirlo.');
            return redirect()->route('analisis.revisar');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al rechazar el análisis: ' . $e->getMessage());
        }
    }
    
    public function cancelar()
    {
        if ($this->modoRevision) {
            return redirect()->route('analisis.revisar');
        }
        return redirect()->route('muestras.index');
    }

    public function descargarPdf()
    {
        return redirect()->route('analisis.pdf', $this->analisis->id);
    }

    public function render()
    {
        return view('livewire.resultados.capturar-resultados')
            ->layout('components.layouts.app');
    }
}
