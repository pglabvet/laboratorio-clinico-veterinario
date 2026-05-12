<?php

namespace App\Livewire\MuestrasRechazadas;

use App\Models\Especie;
use App\Models\MuestraRechazada;
use App\Models\Sucursal;
use App\Models\Veterinaria;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RegistrarMuestraRechazada extends Component
{
    // Paciente
    public $paciente_nombre = '';
    public $especie_id = '';
    public $raza = '';
    public $edadCantidad = '';
    public $edadUnidad = 'años';
    public $sexo = 'M';
    public $propietario_nombre = '';

    // Muestra
    public $tipo_muestra = '';
    public $veterinaria_id = '';
    public $sucursal_id = '';

    // Rechazo
    public $motivo_rechazo = '';
    public $motivo_personalizado = '';
    public $observaciones = '';

    // Edición
    public $muestraId = null;
    public $modoEdicion = false;

    public $motivosPredefinidos = [
        'Muestra hemolizada',
        'Muestra coagulada',
        'Volumen insuficiente',
        'Muestra mal etiquetada',
        'Muestra deteriorada por transporte',
        'Tubo incorrecto',
    ];

    public function mount($id = null)
    {
        $user = auth()->user();
        if ($user->sucursal_id) {
            $this->sucursal_id = $user->sucursal_id;
        }

        if ($id) {
            $muestra = MuestraRechazada::findOrFail($id);
            $this->muestraId = $muestra->id;
            $this->modoEdicion = true;
            $this->paciente_nombre = $muestra->paciente_nombre;
            $this->especie_id = $muestra->especie_id;
            $this->raza = $muestra->raza ?? '';
            $this->propietario_nombre = $muestra->propietario_nombre;
            $this->tipo_muestra = $muestra->tipo_muestra;
            $this->veterinaria_id = $muestra->veterinaria_id;
            $this->sucursal_id = $muestra->sucursal_id;
            $this->observaciones = $muestra->observaciones ?? '';
            $this->sexo = $muestra->sexo;

            // Parsear edad (formato "3 años")
            if ($muestra->edad && preg_match('/^(\d+)\s+(.+)$/', $muestra->edad, $matches)) {
                $this->edadCantidad = $matches[1];
                $this->edadUnidad = $matches[2];
            }

            // Verificar si el motivo es predefinido o personalizado
            if (in_array($muestra->motivo_rechazo, $this->motivosPredefinidos)) {
                $this->motivo_rechazo = $muestra->motivo_rechazo;
            } else {
                $this->motivo_rechazo = 'Otro';
                $this->motivo_personalizado = $muestra->motivo_rechazo;
            }
        }
    }

    public function guardar()
    {
        $this->validate([
            'paciente_nombre'    => 'required|min:2|max:255',
            'especie_id'         => 'required|exists:especies,id',
            'edadCantidad'       => 'required|numeric|min:0',
            'edadUnidad'         => 'required|in:días,semanas,meses,años',
            'sexo'               => 'required|in:M,H',
            'propietario_nombre' => 'required|min:2|max:255',
            'tipo_muestra'       => 'required|max:100',
            'veterinaria_id'     => 'required|exists:veterinarias,id',
            'sucursal_id'        => 'required|exists:sucursales,id',
            'motivo_rechazo'     => 'required',
        ], [
            'paciente_nombre.required'    => 'El nombre del paciente es obligatorio',
            'especie_id.required'         => 'Debe seleccionar una especie',
            'edadCantidad.required'       => 'La edad es obligatoria',
            'propietario_nombre.required' => 'El nombre del propietario es obligatorio',
            'tipo_muestra.required'       => 'El tipo de muestra es obligatorio',
            'veterinaria_id.required'     => 'Debe seleccionar una veterinaria',
            'sucursal_id.required'        => 'Debe seleccionar una sucursal',
            'motivo_rechazo.required'     => 'Debe indicar el motivo de rechazo',
        ]);

        if ($this->motivo_rechazo === 'Otro') {
            $this->validate([
                'motivo_personalizado' => 'required|min:5|max:500',
            ], [
                'motivo_personalizado.required' => 'Debe especificar el motivo de rechazo',
                'motivo_personalizado.min'      => 'El motivo debe tener al menos 5 caracteres',
            ]);
        }

        $motivoFinal = $this->motivo_rechazo === 'Otro'
            ? ($this->motivo_personalizado ?: 'Otro')
            : $this->motivo_rechazo;

        try {
            DB::beginTransaction();

            $edad = $this->edadCantidad . ' ' . $this->edadUnidad;
            $datos = [
                'paciente_nombre'    => $this->paciente_nombre,
                'especie_id'         => $this->especie_id,
                'raza'               => $this->raza,
                'edad'               => $edad,
                'sexo'               => $this->sexo,
                'propietario_nombre' => $this->propietario_nombre,
                'veterinaria_id'     => $this->veterinaria_id,
                'sucursal_id'        => $this->sucursal_id,
                'tipo_muestra'       => $this->tipo_muestra,
                'motivo_rechazo'     => $motivoFinal,
                'observaciones'      => $this->observaciones ?: null,
            ];

            if ($this->modoEdicion) {
                $muestra = MuestraRechazada::findOrFail($this->muestraId);
                $muestra->update($datos);
                $mensaje = 'Muestra rechazada actualizada exitosamente.';
            } else {
                $codigo = $this->generarCodigo();
                $datos['codigo_muestra'] = $codigo;
                $datos['registrado_por'] = auth()->id();
                $datos['fecha_rechazo'] = now();
                MuestraRechazada::create($datos);
                $mensaje = "Muestra rechazada registrada exitosamente. Código: {$codigo}";
            }

            DB::commit();

            session()->flash('success', $mensaje);
            return redirect()->route('muestras-rechazadas.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al ' . ($this->modoEdicion ? 'actualizar' : 'registrar') . ': ' . $e->getMessage());
        }
    }

    private function generarCodigo(): string
    {
        $ultimo = MuestraRechazada::orderBy('id', 'desc')->lockForUpdate()->first();

        if (!$ultimo) {
            return 'R-0001';
        }

        if (preg_match('/^R-(\d+)$/', $ultimo->codigo_muestra, $matches)) {
            $siguiente = (int)$matches[1] + 1;
            return 'R-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
        }

        return 'R-0001';
    }

    public function cancelar()
    {
        return redirect()->route('muestras-rechazadas.index');
    }

    #[Computed]
    public function especies()
    {
        return Especie::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function veterinarias()
    {
        return Veterinaria::where('estado', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('estado', true)->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.muestras-rechazadas.registrar-muestra-rechazada')
            ->layout('components.layouts.app');
    }
}
