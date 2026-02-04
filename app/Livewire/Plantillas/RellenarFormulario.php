<?php

namespace App\Livewire\Plantillas;

use Livewire\Component;
use Livewire\WithFileUploads;

class RellenarFormulario extends Component
{
    use WithFileUploads;

    public $plantillaId;
    public $plantilla = [];
    public $datos = [];
    public $imagenes = [];

    public function mount($plantillaId)
    {
        $this->plantillaId = $plantillaId;
        // La plantilla se cargará desde el frontend
    }

    public function guardarAnalisis()
    {
        // Aquí se guardará el análisis completo en la BD
        $this->validate([
            'datos' => 'required|array',
        ]);

        // Guardar en BD...
        
        session()->flash('success', 'Análisis guardado correctamente');
        return redirect()->route('analisis.index');
    }

    public function render()
    {
        return view('livewire.plantillas.rellenar-formulario');
    }
}
