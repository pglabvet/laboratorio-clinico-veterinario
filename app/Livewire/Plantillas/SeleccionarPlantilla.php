<?php

namespace App\Livewire\Plantillas;

use Livewire\Component;

class SeleccionarPlantilla extends Component
{
    public $plantillas = [];
    public $busqueda = '';

    public function mount()
    {
        // Por ahora, las plantillas se manejan desde el frontend
        // Cuando implementes backend, aquí cargarás desde BD
    }

    public function eliminarPlantilla($id)
    {
        $this->dispatch('eliminarPlantillaLocalStorage', id: $id);
    }

    public function render()
    {
        return view('livewire.plantillas.seleccionar-plantilla');
    }
}
