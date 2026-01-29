<?php

namespace App\Livewire\Analisis;

use App\Models\Analisis;
use App\Models\Resultado;
use App\Models\ImagenAnalisis;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class RegistrarResultados extends Component
{
    use WithFileUploads;

    public Analisis $analisis;
    public $resultados = [];
    public $observaciones = '';
    public $imagenes = [];
    public $imagenesTemporales = [];

    public function mount($analisisId)
    {
        $this->analisis = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
            'muestra.sucursal',
            'tipoAnalisis.parametros.rangosReferencia',
            'resultados.parametro',
            'imagenes'
        ])->findOrFail($analisisId);

        // Cargar resultados existentes o inicializar
        foreach ($this->analisis->tipoAnalisis->parametros as $parametro) {
            $resultadoExistente = $this->analisis->resultados->firstWhere('parametro_id', $parametro->id);
            
            $this->resultados[$parametro->id] = [
                'valor' => $resultadoExistente?->valor ?? '',
                'fuera_rango' => $resultadoExistente?->fuera_rango ?? false,
            ];
        }

        $this->observaciones = $this->analisis->observaciones_bioquimico ?? '';
    }

    /**
     * Obtener rango de referencia para un parámetro
     */
    public function getRangoReferencia($parametro)
    {
        $especieId = $this->analisis->muestra->especie_id;
        
        $rango = $parametro->rangosReferencia
            ->where('especie_id', $especieId)
            ->first();

        if ($rango) {
            return "{$rango->valor_minimo} - {$rango->valor_maximo}";
        }

        return 'N/A';
    }

    /**
     * Validar si un valor está fuera de rango
     */
    public function validarRango($parametroId)
    {
        $parametro = $this->analisis->tipoAnalisis->parametros->find($parametroId);
        $valor = $this->resultados[$parametroId]['valor'] ?? null;

        if (!$valor || !is_numeric($valor)) {
            return;
        }

        $especieId = $this->analisis->muestra->especie_id;
        $rango = $parametro->rangosReferencia->where('especie_id', $especieId)->first();

        if ($rango) {
            $fueraRango = $valor < $rango->valor_minimo || $valor > $rango->valor_maximo;
            $this->resultados[$parametroId]['fuera_rango'] = $fueraRango;
        }
    }

    /**
     * Guardar como borrador
     */
    public function guardarBorrador()
    {
        $this->guardarResultados('EN_PROCESO');
        
        session()->flash('mensaje', 'Borrador guardado exitosamente.');
        return redirect()->route('muestras.escanear');
    }

    /**
     * Completar y revisar
     */
    public function completarYRevisar()
    {
        $this->guardarResultados('COMPLETADO');
        
        session()->flash('mensaje', 'Resultados registrados exitosamente.');
        return redirect()->route('muestras.escanear');
    }

    /**
     * Guardar resultados
     */
    private function guardarResultados($estado)
    {
        // Guardar resultados
        foreach ($this->resultados as $parametroId => $data) {
            if (!empty($data['valor'])) {
                Resultado::updateOrCreate(
                    [
                        'analisis_id' => $this->analisis->id,
                        'parametro_id' => $parametroId,
                    ],
                    [
                        'valor' => $data['valor'],
                        'fuera_rango' => $data['fuera_rango'] ?? false,
                    ]
                );
            }
        }

        // Guardar imágenes
        foreach ($this->imagenesTemporales as $imagen) {
            $path = $imagen->store('analisis-imagenes', 'public');
            
            ImagenAnalisis::create([
                'analisis_id' => $this->analisis->id,
                'ruta' => $path,
                'descripcion' => 'Imagen de análisis',
            ]);
        }

        // Actualizar análisis
        $this->analisis->update([
            'estado' => $estado,
            'observaciones_bioquimico' => $this->observaciones,
            'fecha_finalizacion' => $estado === 'COMPLETADO' ? now() : null,
        ]);
    }

    /**
     * Eliminar imagen
     */
    public function eliminarImagen($imagenId)
    {
        $imagen = ImagenAnalisis::find($imagenId);
        
        if ($imagen && $imagen->analisis_id === $this->analisis->id) {
            Storage::disk('public')->delete($imagen->ruta);
            $imagen->delete();
            
            $this->analisis->refresh();
        }
    }

    public function render()
    {
        return view('livewire.analisis.registrar-resultados');
    }
}
