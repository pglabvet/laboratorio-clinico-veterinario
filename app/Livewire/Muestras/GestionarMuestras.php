<?php

namespace App\Livewire\Muestras;

use App\Models\Muestra;
use App\Models\Especie;
use App\Models\Veterinaria;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\Analisis;
use App\Models\InventarioSucursal;
use App\Models\TokenDescarga;
use App\Models\Pdf;
use App\Services\AnalisisPdfService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class GestionarMuestras extends Component
{
    use WithPagination;

    // Propiedades del formulario - Muestra
    public $muestra_id;
    public $codigo_muestra;
    public $tipo_muestra;
    public $fecha_recepcion;
    public $estado = 'Pendiente';
    public $observaciones;
    public $sucursal_id;

    // Propiedades del formulario - Paciente
    public $paciente_nombre;
    public $especie_id;
    public $raza;
    public $edad;
    public $sexo = 'M';
    public $color;
    public $propietario_nombre;

    // Propiedades del formulario - Veterinaria y Análisis
    public $veterinaria_id;
    public $tipos_analisis_seleccionados = [];

    // Propiedades de control
    public $modalAbierto = false;
    public $modalEliminar = false;
    public $modalVer = false;
    public $modalCodigoBarras = false;
    public $modalAnalisis = false;
    public $muestraAEliminar = null;
    public $muestraAVer = null;
    public $muestraCodigoBarras = null;
    public $muestraAnalisis = null;
    public $buscar = '';
    public $modoEdicion = false;

    // Propiedades de filtros
    public $filtroEstado = '';
    public $filtroEspecie = '';
    public $filtroVeterinaria = '';
    public $filtroFechaDesde = '';
    public $filtroFechaHasta = '';

    // Propiedades de ordenamiento
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Reglas de validación
    protected function rules()
    {
        return [
            'paciente_nombre' => 'required|string|max:255',
            'especie_id' => 'required|exists:especies,id',
            'raza' => 'nullable|string|max:100',
            'edad' => 'required|string|max:50',
            'sexo' => 'required|in:M,H',
            'color' => 'nullable|string|max:100',
            'propietario_nombre' => 'required|string|max:255',
            'veterinaria_id' => 'required|exists:veterinarias,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'tipo_muestra' => 'required|string|max:100',
            'fecha_recepcion' => 'required|date',
            'observaciones' => 'nullable|string',
            'tipos_analisis_seleccionados' => 'required|array|min:1',
            'tipos_analisis_seleccionados.*' => 'exists:tipos_analisis,id',
        ];
    }

    // Mensajes de validación personalizados
    protected $messages = [
        'paciente_nombre.required' => 'El nombre del paciente es obligatorio.',
        'especie_id.required' => 'Debe seleccionar una especie.',
        'edad.required' => 'La edad del paciente es obligatoria.',
        'sexo.required' => 'El sexo del paciente es obligatorio.',
        'propietario_nombre.required' => 'El nombre del propietario es obligatorio.',
        'veterinaria_id.required' => 'Debe seleccionar una veterinaria.',
        'sucursal_id.required' => 'Debe seleccionar una sucursal.',
        'tipo_muestra.required' => 'El tipo de muestra es obligatorio.',
        'fecha_recepcion.required' => 'La fecha de recepción es obligatoria.',
        'tipos_analisis_seleccionados.required' => 'Debe seleccionar al menos un tipo de análisis.',
        'tipos_analisis_seleccionados.min' => 'Debe seleccionar al menos un tipo de análisis.',
    ];

    /**
     * Inicializar componente
     */
    public function mount()
    {
        $this->fecha_recepcion = now()->format('Y-m-d');
        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;

        // Si hay una muestra recién creada, abrir su modal automáticamente
        if (session()->has('muestra_recien_creada_id')) {
            $muestraId = session()->pull('muestra_recien_creada_id');
            $this->verCodigoBarras($muestraId);
        }
    }

    /**
     * Abrir modal para crear nueva muestra
     */
    public function crear()
    {
        return redirect()->route('muestras.crear');
    }

    /**
     * Abrir modal para ver detalles de muestra
     */
    public function ver($id)
    {
        $this->muestraAVer = Muestra::with([
            'especie',
            'veterinaria',
            'sucursal',
            'analisis.tipoAnalisis',
            'analisis.plantillaFormulario'
        ])->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Cerrar modal de ver
     */
    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->muestraAVer = null;
    }

    /**
     * Abrir modal de código de barras
     */
    public function verCodigoBarras($id)
    {
        $this->muestraCodigoBarras = Muestra::with([
            'especie',
            'veterinaria',
            'sucursal'
        ])->findOrFail($id);
        $this->modalCodigoBarras = true;
    }

    /**
     * Cerrar modal de código de barras
     */
    public function cerrarModalCodigoBarras()
    {
        $this->modalCodigoBarras = false;
        $this->muestraCodigoBarras = null;
    }

    /**
     * Abrir modal de análisis de la muestra
     */
    public function verAnalisis($id)
    {
        $this->muestraAnalisis = Muestra::with([
            'especie',
            'veterinaria',
            'analisis.tipoAnalisis',
            'analisis.resultados'
        ])->findOrFail($id);
        $this->modalAnalisis = true;
    }

    /**
     * Cerrar modal de análisis
     */
    public function cerrarModalAnalisis()
    {
        $this->modalAnalisis = false;
        $this->muestraAnalisis = null;
    }

    /**
     * Enviar resultado de análisis por WhatsApp
     */
    public function enviarWhatsApp($analisisId)
    {
        try {
            $analisis = Analisis::with([
                'tipoAnalisis',
                'muestra.veterinaria',
                'muestra.sucursal',
                'muestra.especie',
                'pdfs'
            ])->find($analisisId);

            if (!$analisis) {
                session()->flash('error', 'Análisis no encontrado.');
                return;
            }

            // Validar que esté aprobado o enviado
            $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
            if (!in_array($analisis->estado, $estadosValidos)) {
                session()->flash('error', 'Solo se pueden enviar análisis aprobados o ya enviados. Estado actual: ' . $analisis->estado);
                return;
            }

            // Verificar teléfono de la veterinaria
            $telefono = $analisis->muestra->veterinaria->telefono ?? null;
            if (!$telefono) {
                session()->flash('error', 'La veterinaria no tiene un número de teléfono registrado.');
                return;
            }

            // Obtener o generar el PDF
            $pdf = $analisis->pdfs()->latest()->first();
            
            if (!$pdf) {
                // Generar PDF automáticamente
                $pdfService = app(AnalisisPdfService::class);
                $resultado = $pdfService->generar($analisis);
                $pdf = $resultado['modelo'];
            }

            // Crear token de descarga (3 días)
            $tokenDescarga = TokenDescarga::crearParaPdf($pdf->id, 3);
            $urlDescarga = $tokenDescarga->getUrlDescarga();

            // Construir mensaje de WhatsApp
            $mensaje = $this->construirMensajeWhatsApp($analisis, $urlDescarga);

            // Construir URL de WhatsApp
            $telefonoFormateado = $this->formatearTelefonoWhatsApp($telefono);
            $urlWhatsApp = 'https://wa.me/' . $telefonoFormateado . '?text=' . rawurlencode($mensaje);

            // Emitir evento para abrir URL en nueva pestaña
            $this->dispatch('abrir-whatsapp', url: $urlWhatsApp);

            // Cambiar estado a Enviado SOLO si todo salió bien
            $analisis->update(['estado' => Analisis::ESTADO_ENVIADO]);

            // Actualizar el modal si está abierto
            if ($this->muestraAnalisis) {
                $this->muestraAnalisis->load('analisis.tipoAnalisis');
            }

            session()->flash('mensaje', 'Enlace de WhatsApp generado. El análisis ha sido marcado como enviado.');

        } catch (\Exception $e) {
            // Registrar el error para poder investigarlo
            \Log::error('Error al generar enlace de WhatsApp para análisis '.$analisisId.': '.$e->getMessage(), [
                'exception' => $e,
            ]);

            // Informar claramente al usuario que el análisis no fue marcado como enviado
            session()->flash(
                'error',
                'No se pudo generar el enlace de WhatsApp. El análisis no ha sido marcado como enviado. Detalle técnico: '.$e->getMessage()
            );
        }
    }

    /**
     * Enviar todos los análisis de la muestra por WhatsApp
     */
    public function enviarTodoWhatsApp()
    {
        if (!$this->muestraAnalisis) {
            session()->flash('error', 'No hay muestra seleccionada.');
            return;
        }

        try {
            $this->muestraAnalisis->load([
                'veterinaria',
                'especie',
                'analisis.tipoAnalisis',
                'analisis.pdfs'
            ]);

            $analisisCollection = $this->muestraAnalisis->analisis;

            // Validar que todos estén aprobados o enviados
            $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
            $noValidos = $analisisCollection->filter(function ($analisis) use ($estadosValidos) {
                return !in_array($analisis->estado, $estadosValidos);
            });

            if ($noValidos->count() > 0) {
                $nombresNoValidos = $noValidos->map(fn($a) => ($a->tipoAnalisis->nombre ?? 'Sin nombre') . ' (' . $a->estado . ')')->implode(', ');
                session()->flash('error', 'Todos los análisis deben estar aprobados o enviados. Pendientes: ' . $nombresNoValidos);
                return;
            }

            if ($analisisCollection->isEmpty()) {
                session()->flash('error', 'No hay análisis para enviar.');
                return;
            }

            // Verificar teléfono de la veterinaria
            $telefono = $this->muestraAnalisis->veterinaria->telefono ?? null;
            if (!$telefono) {
                session()->flash('error', 'La veterinaria no tiene un número de teléfono registrado.');
                return;
            }

            $pdfService = app(AnalisisPdfService::class);
            $linksDescarga = [];

            foreach ($analisisCollection as $analisis) {
                // Obtener o generar el PDF
                $pdf = $analisis->pdfs()->latest()->first();
                
                if (!$pdf) {
                    $resultado = $pdfService->generar($analisis);
                    $pdf = $resultado['modelo'];
                }

                // Crear token de descarga (3 días)
                $tokenDescarga = TokenDescarga::crearParaPdf($pdf->id, 3);
                
                $linksDescarga[] = [
                    'nombre' => $analisis->tipoAnalisis->nombre ?? 'Análisis',
                    'url' => $tokenDescarga->getUrlDescarga(),
                ];

                // Cambiar estado a Enviado
                $analisis->update(['estado' => Analisis::ESTADO_ENVIADO]);
            }

            // Construir mensaje consolidado
            $mensaje = $this->construirMensajeWhatsAppMultiple($this->muestraAnalisis, $linksDescarga);

            // Construir URL de WhatsApp
            $telefonoFormateado = $this->formatearTelefonoWhatsApp($telefono);
            $urlWhatsApp = 'https://wa.me/' . $telefonoFormateado . '?text=' . rawurlencode($mensaje);

            // Actualizar el modal
            $this->muestraAnalisis->load('analisis.tipoAnalisis');

            // Emitir evento para abrir URL en nueva pestaña
            $this->dispatch('abrir-whatsapp', url: $urlWhatsApp);

            session()->flash('mensaje', 'Enlace de WhatsApp generado. Todos los análisis han sido marcados como enviados.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar enlaces: ' . $e->getMessage());
        }
    }

    /**
     * Formatear número de teléfono para WhatsApp
     * Limpia caracteres no numéricos y añade código de país de Bolivia (591)
     */
    private function formatearTelefonoWhatsApp(string $telefono): string
    {
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        return '591' . ltrim($telefonoLimpio, '0');
    }

    /**
     * Construir mensaje de WhatsApp para un análisis individual
     */
    private function construirMensajeWhatsApp(Analisis $analisis, string $urlDescarga): string
    {
        $muestra = $analisis->muestra;
        $sucursal = $muestra->sucursal->nombre ?? 'N/A';
        
        return "*LABORATORIO CLINICO VETERINARIO*\n" .
               "*Sucursal:* {$sucursal}\n\n" .
               "*Codigo:* {$muestra->codigo_muestra}\n" .
               "*Paciente:* {$muestra->paciente_nombre}\n" .
               "*Propietario:* {$muestra->propietario_nombre}\n" .
               "*Analisis:* " . ($analisis->tipoAnalisis->nombre ?? 'N/A') . "\n\n" .
               "Descarga tu resultado aqui (valido por 3 dias):\n" .
               "{$urlDescarga}\n\n" .
               "_Gracias por confiar en nosotros._";
    }

    /**
     * Construir mensaje de WhatsApp para múltiples análisis
     */
    private function construirMensajeWhatsAppMultiple(Muestra $muestra, array $linksDescarga): string
    {
        $sucursal = $muestra->sucursal->nombre ?? 'N/A';
        
        $mensaje = "*LABORATORIO CLINICO VETERINARIO*\n" .
                   "*Sucursal:* {$sucursal}\n\n" .
                   "*Codigo:* {$muestra->codigo_muestra}\n" .
                   "*Paciente:* {$muestra->paciente_nombre}\n" .
                   "*Propietario:* {$muestra->propietario_nombre}\n\n" .
                   "*Resultados disponibles (validos por 3 dias):*\n\n";

        foreach ($linksDescarga as $index => $link) {
            $numero = $index + 1;
            $mensaje .= "{$numero}. *{$link['nombre']}*\n{$link['url']}\n\n";
        }

        $mensaje .= "_Gracias por confiar en nosotros._";

        return $mensaje;
    }

    /**
     * Guardar muestra (crear o actualizar)
     */
    public function guardar()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            
            // UC-B05: Validar stock antes de crear análisis
            if (!$this->modoEdicion) {
                $this->validarStockDisponible();
            }
            
            if ($this->modoEdicion) {
                $muestra = Muestra::findOrFail($this->muestra_id);
                $muestra->update([
                    'paciente_nombre' => $this->paciente_nombre,
                    'especie_id' => $this->especie_id,
                    'raza' => $this->raza,
                    'edad' => $this->edad,
                    'sexo' => $this->sexo,
                    'color' => $this->color,
                    'propietario_nombre' => $this->propietario_nombre,
                    'veterinaria_id' => $this->veterinaria_id,
                    'sucursal_id' => $this->sucursal_id,
                    'tipo_muestra' => $this->tipo_muestra,
                    'fecha_recepcion' => $this->fecha_recepcion,
                    'observaciones' => $this->observaciones,
                ]);

                session()->flash('mensaje', 'Muestra actualizada exitosamente.');
            } else {
                // Generar código único de muestra
                $this->codigo_muestra = $this->generarCodigoMuestra();
                
                $muestra = Muestra::create([
                    'codigo_muestra' => $this->codigo_muestra,
                    'paciente_nombre' => $this->paciente_nombre,
                    'especie_id' => $this->especie_id,
                    'raza' => $this->raza,
                    'edad' => $this->edad,
                    'sexo' => $this->sexo,
                    'color' => $this->color,
                    'propietario_nombre' => $this->propietario_nombre,
                    'veterinaria_id' => $this->veterinaria_id,
                    'sucursal_id' => $this->sucursal_id,
                    'tipo_muestra' => $this->tipo_muestra,
                    'fecha_recepcion' => $this->fecha_recepcion,
                    'estado' => 'Pendiente',
                    'observaciones' => $this->observaciones,
                ]);

                // Crear registros de análisis para cada tipo seleccionado
                foreach ($this->tipos_analisis_seleccionados as $tipo_analisis_id) {
                    Analisis::create([
                        'muestra_id' => $muestra->id,
                        'tipo_analisis_id' => $tipo_analisis_id,
                        'bioquimico_id' => auth()->id(),
                        'estado' => 'Pendiente',
                        'fecha_inicio' => now(),
                    ]);
                }

                // Mostrar modal de código de barras
                $this->muestraCodigoBarras = $muestra->load(['especie', 'veterinaria', 'sucursal']);
                $this->modalCodigoBarras = true;

                session()->flash('mensaje', 'Muestra registrada exitosamente.');
            }

            DB::commit();
            $this->cerrarModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar la muestra: ' . $e->getMessage());
        }
    }

    /**
     * UC-B05: Validar stock disponible antes de crear análisis
     * - BLOQUEA si stock = 0
     * - ADVIERTE si stock <= stock_mínimo (pero > 0)
     */
    private function validarStockDisponible()
    {
        $tiposAnalisis = TipoAnalisis::with('plantillas.insumos')
            ->whereIn('id', $this->tipos_analisis_seleccionados)
            ->get();

        $insumosConStockCero = [];
        $insumosConStockBajo = [];

        foreach ($tiposAnalisis as $tipoAnalisis) {
            $plantilla = $tipoAnalisis->plantillas()->where('activo', true)->first();
            
            if (!$plantilla || $plantilla->insumos->isEmpty()) {
                continue; // Sin insumos configurados, no validar
            }

            foreach ($plantilla->insumos as $insumo) {
                $cantidadRequerida = $insumo->pivot->cantidad_requerida;

                $inventario = InventarioSucursal::where('insumo_id', $insumo->id)
                    ->where('sucursal_id', $this->sucursal_id)
                    ->first();

                if (!$inventario || $inventario->stock_actual <= 0) {
                    // BLOQUEO: Stock en cero
                    $insumosConStockCero[] = $insumo->nombre;
                } elseif ($inventario->stock_actual < $cantidadRequerida) {
                    // BLOQUEO: Stock insuficiente para realizar el análisis
                    $insumosConStockCero[] = "{$insumo->nombre} (Disponible: {$inventario->stock_actual}, Requerido: {$cantidadRequerida})";
                } elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                    // ADVERTENCIA: Stock bajo pero suficiente
                    $insumosConStockBajo[] = $insumo->nombre;
                }
            }
        }

        // Bloquear si hay stock en cero o insuficiente
        if (!empty($insumosConStockCero)) {
            throw new \Exception(
                "❌ No se puede crear el análisis. Los siguientes insumos tienen stock insuficiente: " . 
                implode(', ', $insumosConStockCero) . 
                ". Por favor, registre una entrada de inventario antes de continuar."
            );
        }

        // Advertir si hay stock bajo (pero permitir continuar)
        if (!empty($insumosConStockBajo)) {
            session()->flash('warning', 
                '⚠️ ADVERTENCIA: Los siguientes insumos tienen stock bajo: ' . 
                implode(', ', $insumosConStockBajo) . 
                '. Se recomienda reabastecer pronto.'
            );
        }
    }

    /**
     * Generar código único para la muestra por sucursal
     * Formato: {PREFIJO}-AA0000 (Prefijo de sucursal + 2 letras + 4 dígitos)
     * Ejemplo: S-AA0001 (Sucursal Sur), N-AA0002 (Sucursal Norte)
     * Rango por sucursal: AA0000 - ZZ9999 (676 * 10,000 = 6,760,000 combinaciones)
     */
    private function generarCodigoMuestra()
    {
        // Obtener prefijo de la sucursal
        $sucursal = Sucursal::find($this->sucursal_id);
        if (!$sucursal) {
            throw new \Exception('Sucursal no encontrada');
        }
        $prefijo = $sucursal->getPrefijo();
        
        // Obtener el último código de muestra de esta sucursal
        $ultimaMuestra = Muestra::where('sucursal_id', $this->sucursal_id)
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$ultimaMuestra) {
            // Primera muestra de esta sucursal
            return $prefijo . '-AA0001';
        }
        
        // Extraer las partes del último código
        $ultimoCodigo = $ultimaMuestra->codigo_muestra;
        
        // Si no sigue el formato PREFIJO-AA0000, empezar desde AA0001
        if (!preg_match('/^[A-Z]{1,2}-([A-Z]{2})(\d{4})$/', $ultimoCodigo, $matches)) {
            return $prefijo . '-AA0001';
        }
        
        $letras = $matches[1];
        $numero = (int)$matches[2];
        
        // Incrementar el número
        $numero++;
        
        // Si el número excede 9999, incrementar las letras
        if ($numero > 9999) {
            $numero = 1;
            $letras = $this->incrementarLetras($letras);
        }
        
        return $prefijo . '-' . $letras . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Incrementar las letras del código (AA -> AB -> AC ... -> AZ -> BA -> BB ... -> ZZ)
     */
    private function incrementarLetras($letras)
    {
        $letra1 = $letras[0];
        $letra2 = $letras[1];
        
        // Incrementar segunda letra
        if ($letra2 === 'Z') {
            $letra2 = 'A';
            // Incrementar primera letra
            if ($letra1 === 'Z') {
                // Se acabaron las combinaciones, volver a AA
                return 'AA';
            } else {
                $letra1 = chr(ord($letra1) + 1);
            }
        } else {
            $letra2 = chr(ord($letra2) + 1);
        }
        
        return $letra1 . $letra2;
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($id)
    {
        $this->muestraAEliminar = $id;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->muestraAEliminar = null;
    }

    /**
     * Eliminar muestra
     */
    public function eliminar()
    {
        try {
            if (!$this->muestraAEliminar) {
                return;
            }

            $muestra = Muestra::findOrFail($this->muestraAEliminar);
            
            // Verificar si tiene análisis en proceso o completados
            if ($muestra->analisis()->whereIn('estado', ['En revision', 'Aprobado', 'Enviado'])->count() > 0) {
                session()->flash('error', 'No se puede eliminar la muestra porque tiene análisis en revisión, aprobados o enviados.');
                $this->modalEliminar = false;
                $this->muestraAEliminar = null;
                return;
            }

            // Eliminar análisis pendientes
            $muestra->analisis()->where('estado', 'Pendiente')->delete();
            
            $muestra->delete();
            session()->flash('mensaje', 'Muestra eliminada exitosamente.');
            
            $this->modalEliminar = false;
            $this->muestraAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la muestra: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->muestraAEliminar = null;
        }
    }

    /**
     * Cerrar modal
     */
    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
        $this->resetValidation();
    }

    /**
     * Resetear formulario
     */
    private function resetearFormulario()
    {
        $this->muestra_id = null;
        $this->codigo_muestra = '';
        $this->paciente_nombre = '';
        $this->especie_id = null;
        $this->raza = '';
        $this->edad = '';
        $this->sexo = 'M';
        $this->color = '';
        $this->propietario_nombre = '';
        $this->veterinaria_id = null;
        $this->tipo_muestra = '';
        $this->observaciones = '';
        $this->tipos_analisis_seleccionados = [];
        $this->fecha_recepcion = now()->format('Y-m-d');
        $this->sucursal_id = auth()->user()->sucursal_id ?? Sucursal::first()?->id;
        $this->estado = 'Pendiente';
    }

    /**
     * Resetear búsqueda
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Cambiar ordenamiento
     */
    public function ordenarPor($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Limpiar todos los filtros
     */
    public function limpiarFiltros()
    {
        $this->buscar = '';
        $this->filtroEstado = '';
        $this->filtroEspecie = '';
        $this->filtroVeterinaria = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    /**
     * Filtrar por hoy
     */
    public function filtrarHoy()
    {
        $this->filtroFechaDesde = now()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
    }

    /**
     * Filtrar por ayer
     */
    public function filtrarAyer()
    {
        $this->filtroFechaDesde = now()->subDay()->format('Y-m-d');
        $this->filtroFechaHasta = now()->subDay()->format('Y-m-d');
    }

    /**
     * Filtrar últimos 7 días
     */
    public function filtrarUltimos7Dias()
    {
        $this->filtroFechaDesde = now()->subDays(6)->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
    }

    /**
     * Filtrar esta semana
     */
    public function filtrarEstaSemana()
    {
        $this->filtroFechaDesde = now()->startOfWeek()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfWeek()->format('Y-m-d');
    }

    /**
     * Filtrar este mes
     */
    public function filtrarEsteMes()
    {
        $this->filtroFechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->filtroFechaHasta = now()->endOfMonth()->format('Y-m-d');
    }

    /**
     * Filtrar año actual
     */
    public function filtrarAnioActual()
    {
        $this->filtroFechaDesde = now()->startOfYear()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        $muestras = Muestra::query()
            ->with(['especie', 'veterinaria', 'sucursal'])
            ->withCount('analisis')
            ->when($this->buscar, function ($query) {
                $searchTerm = '%' . $this->buscar . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('muestras.codigo_muestra', 'like', $searchTerm)
                        ->orWhere('muestras.paciente_nombre', 'like', $searchTerm)
                        ->orWhere('muestras.propietario_nombre', 'like', $searchTerm)
                        ->orWhereHas('veterinaria', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('nombre', 'like', $searchTerm);
                        });
                });
            })
            ->when($this->filtroEstado, function ($query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->when($this->filtroEspecie, function ($query) {
                $query->where('especie_id', $this->filtroEspecie);
            })
            ->when($this->filtroVeterinaria, function ($query) {
                $query->where('veterinaria_id', $this->filtroVeterinaria);
            })
            ->when($this->filtroFechaDesde, function ($query) {
                $query->whereDate('fecha_recepcion', '>=', $this->filtroFechaDesde);
            })
            ->when($this->filtroFechaHasta, function ($query) {
                $query->whereDate('fecha_recepcion', '<=', $this->filtroFechaHasta);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        $especies = Especie::where('estado', true)->orderBy('nombre')->get();
        $veterinarias = Veterinaria::where('estado', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::where('estado', true)->orderBy('nombre')->get();
        $tiposAnalisis = TipoAnalisis::where('estado', true)->orderBy('nombre')->get();

        return view('livewire.muestras.gestionar-muestras', [
            'muestras' => $muestras,
            'especies' => $especies,
            'veterinarias' => $veterinarias,
            'sucursales' => $sucursales,
            'tiposAnalisis' => $tiposAnalisis,
        ]);
    }
}
