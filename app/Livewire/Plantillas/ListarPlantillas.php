<?php

namespace App\Livewire\Plantillas;

use Livewire\Component;
use App\Models\PlantillaFormulario;
use Livewire\WithPagination;

class ListarPlantillas extends Component
{
    use WithPagination;

    public $busqueda = '';
    
    // Propiedades de control del modal
    public $modalEliminar = false;
    public $plantillaAEliminar = null;

    public function render()
    {
        $plantillas = PlantillaFormulario::query()
            ->when($this->busqueda, function ($query) {
                $query->where('nombre', 'like', '%' . $this->busqueda . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->busqueda . '%');
            })
            ->with('creador')
            ->latest()
            ->paginate(10);

        return view('livewire.plantillas.listar-plantillas', [
            'plantillas' => $plantillas,
        ]);
    }

    public function toggleActivo($plantillaId)
    {
        $plantilla = PlantillaFormulario::findOrFail($plantillaId);
        $plantilla->update(['activo' => !$plantilla->activo]);
        
        session()->flash('success', 'Estado actualizado correctamente');
    }

    /**
     * Abrir modal de confirmación para eliminar
     */
    public function confirmarEliminar($plantillaId)
    {
        $this->plantillaAEliminar = $plantillaId;
        $this->modalEliminar = true;
    }

    /**
     * Cancelar eliminación
     */
    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->plantillaAEliminar = null;
    }

    /**
     * Eliminar plantilla
     */
    public function eliminar()
    {
        try {
            if (!$this->plantillaAEliminar) {
                return;
            }

            PlantillaFormulario::findOrFail($this->plantillaAEliminar)->delete();
            session()->flash('success', 'Plantilla eliminada correctamente');
            
            $this->modalEliminar = false;
            $this->plantillaAEliminar = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la plantilla: ' . $e->getMessage());
            $this->modalEliminar = false;
            $this->plantillaAEliminar = null;
        }
    }

    public function duplicar($plantillaId)
    {
        $plantilla = PlantillaFormulario::findOrFail($plantillaId);
        
        session()->flash('success', 'Plantilla lista para editar. Puedes modificar el nombre y los componentes.');
        
        return redirect()->route('plantillas.crear', ['duplicar' => $plantillaId]);
    }
}
