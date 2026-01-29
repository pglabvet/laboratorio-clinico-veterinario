<?php

namespace App\Livewire\Muestras;

use App\Models\Muestra;
use Livewire\Component;

class EscanearMuestra extends Component
{
    public $codigo_muestra = '';
    public $muestra = null;
    public $mensaje_error = '';

    protected $rules = [
        'codigo_muestra' => 'required|string',
    ];

    protected $messages = [
        'codigo_muestra.required' => 'Debe ingresar un código de muestra.',
    ];

    /**
     * Inicializar componente
     */
    public function mount()
    {
        // Si viene un código desde el escaneo rápido
        if (session()->has('codigo_escaneado')) {
            $this->codigo_muestra = session('codigo_escaneado');
            session()->forget('codigo_escaneado');
            
            // Escanear automáticamente
            $this->escanear();
        }
    }

    /**
     * Escanear o buscar muestra por código
     */
    public function escanear()
    {
        $this->validate();
        $this->mensaje_error = '';
        $this->muestra = null;

        // Buscar la muestra por código
        $this->muestra = Muestra::with([
            'especie',
            'veterinaria',
            'sucursal',
            'analisis.tipoAnalisis',
            'analisis.bioquimico'
        ])
        ->where('codigo_muestra', $this->codigo_muestra)
        ->first();

        if (!$this->muestra) {
            $this->mensaje_error = 'No se encontró ninguna muestra con el código: ' . $this->codigo_muestra;
        }
    }

    /**
     * Limpiar búsqueda
     */
    public function limpiar()
    {
        $this->reset(['codigo_muestra', 'muestra', 'mensaje_error']);
    }

    /**
     * Listener para cuando se escanea un código de barras
     */
    public function updatedCodigoMuestra()
    {
        // Si el código tiene el formato completo, escanear automáticamente
        // Formato: AA0000 (2 letras + 4 dígitos)
        if (strlen($this->codigo_muestra) >= 6 && preg_match('/^[A-Z]{2}\d{4}$/', $this->codigo_muestra)) {
            $this->escanear();
        }
    }

    public function render()
    {
        return view('livewire.muestras.escanear-muestra');
    }
}
