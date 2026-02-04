<?php

namespace App\Livewire\Muestras;

use App\Models\Muestra;
use Livewire\Component;

class EscaneoRapido extends Component
{
    public $codigo = '';
    public $escaneando = false;

    /**
     * Cuando se actualiza el código (escáner envía Enter)
     */
    public function updatedCodigo()
    {
        if (empty($this->codigo)) {
            return;
        }

        $this->escaneando = true;

        // Buscar la muestra
        $muestra = Muestra::where('codigo_muestra', $this->codigo)->first();

        if ($muestra) {
            // Redirigir a la página de escanear con el código
            return redirect()->route('muestras.escanear')->with('codigo_escaneado', $this->codigo);
        }

        // Si no se encuentra, también redirigir para mostrar el error
        return redirect()->route('muestras.escanear')->with('codigo_escaneado', $this->codigo);
    }

    public function render()
    {
        return view('livewire.muestras.escaneo-rapido');
    }
}
