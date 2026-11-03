<?php

namespace App\Livewire\Cobros;

use App\Models\Cobro;
use App\Models\PagoCobro;
use App\Services\CobroService;
use Livewire\Component;

/**
 * Formulario flotante que aparece al registrar una muestra.
 * Fecha, veterinaria, código, paciente y sucursal se muestran desde la muestra;
 * el usuario completa total, pago inicial y observación. Si lo cierra sin
 * guardar, el cobro queda en estado Pendiente.
 */
class RegistrarCobroMuestra extends Component
{
    public $muestraId = null;

    public $cobroId = null;

    public $modalAbierto = false;

    public $examen = '';

    public $total = '';

    public $montoPago = '';

    public $metodoPago = PagoCobro::METODO_EFECTIVO;

    public $observacion = '';

    public function mount($muestraId = null)
    {
        if (! $muestraId || ! auth()->user()->can('registrar-cobro-muestra')) {
            return;
        }

        $cobro = Cobro::where('muestra_id', $muestraId)->first();
        if (! $cobro) {
            return;
        }

        $this->muestraId = $muestraId;
        $this->cobroId = $cobro->id;
        $this->examen = $cobro->examen;
        $this->modalAbierto = true;
    }

    public function guardar(CobroService $cobroService)
    {
        abort_unless(auth()->user()->can('registrar-cobro-muestra'), 403);

        $this->validate([
            'total' => 'required|numeric|min:0',
            'montoPago' => 'nullable|numeric|min:0|lte:total',
            'metodoPago' => 'required_with:montoPago|in:'.implode(',', PagoCobro::METODOS),
            'examen' => 'nullable|string|max:500',
            'observacion' => 'nullable|string|max:1000',
        ], [
            'total.required' => 'Ingrese el total.',
            'montoPago.lte' => 'El pago no puede ser mayor al total.',
        ]);

        $cobro = Cobro::findOrFail($this->cobroId);

        try {
            $cobroService->actualizar($cobro, (float) $this->total, $this->examen, $this->observacion);

            if ((float) $this->montoPago > 0) {
                $cobroService->registrarPago($cobro, (float) $this->montoPago, $this->metodoPago, auth()->id());
            }
        } catch (\DomainException $e) {
            $this->addError('montoPago', $e->getMessage());

            return;
        }

        session()->flash('mensaje_cobro', 'Cobro registrado exitosamente.');
        $this->cerrar();
    }

    /**
     * Omitir: el cobro queda en Pendiente para completarlo desde el módulo Cobros.
     */
    public function cerrar()
    {
        $this->modalAbierto = false;
        $this->dispatch('cobro-muestra-cerrado', muestraId: $this->muestraId);
        $this->muestraId = null;
    }

    /**
     * Cierre con Esc o clic fuera del modal.
     */
    public function updatedModalAbierto($value)
    {
        if (! $value && $this->muestraId) {
            $this->cerrar();
        }
    }

    public function render()
    {
        $cobro = $this->cobroId
            ? Cobro::with(['muestra.veterinaria', 'muestra.sucursal'])->find($this->cobroId)
            : null;

        return view('livewire.cobros.registrar-cobro-muestra', [
            'cobro' => $cobro,
            'metodos' => PagoCobro::METODOS,
        ]);
    }
}
