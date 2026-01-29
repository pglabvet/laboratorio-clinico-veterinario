<?php

namespace App\Livewire\Analisis;

use Livewire\Component;
use App\Models\Analisis;

class VerAnalisis extends Component
{
    public $analisis;
    public $resultadosAgrupados = [];

    public function mount($analisisId)
    {
        $this->analisis = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
            'muestra.sucursal',
            'tipoAnalisis.plantillas',
            'bioquimico',
            'aprobador',
            'resultados'
        ])->findOrFail($analisisId);

        // Agrupar resultados por tipo
        $this->resultadosAgrupados = $this->analisis->resultados->groupBy('tipo');
    }

    public function aprobar()
    {
        $this->analisis->update([
            'estado' => 'aprobado',
            'aprobador_id' => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);

        session()->flash('success', 'Análisis aprobado correctamente.');
        return redirect()->route('analisis.revisar');
    }

    public function rechazar()
    {
        $this->analisis->update([
            'estado' => 'rechazado',
            'aprobador_id' => auth()->id(),
        ]);

        session()->flash('warning', 'Análisis rechazado. El bioquímico debe realizar correcciones.');
        return redirect()->route('analisis.revisar');
    }

    public function render()
    {
        return view('livewire.analisis.ver-analisis')
            ->layout('components.layouts.app');
    }
}
