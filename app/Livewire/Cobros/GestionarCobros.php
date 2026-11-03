<?php

namespace App\Livewire\Cobros;

use App\Models\Cobro;
use App\Models\PagoCobro;
use App\Models\Sucursal;
use App\Models\Veterinaria;
use App\Services\CobroService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarCobros extends Component
{
    use WithPagination;

    // Filtros
    public $buscar = '';

    public $filtroEstado = '';

    public $filtroMetodo = '';

    public $filtroVeterinaria = '';

    public $filtroSucursal = '';

    public $filtroDesde = '';

    public $filtroHasta = '';

    // Modal ver detalle
    public $modalVer = false;

    public $cobroAVer = null;

    // Modal registrar pago
    public $modalPago = false;

    public $cobroPagoId = null;

    public $montoPago = '';

    public $metodoPago = PagoCobro::METODO_EFECTIVO;

    // Modal editar
    public $modalEditar = false;

    public $cobroEditarId = null;

    public $total = '';

    public $examen = '';

    public $observacion = '';

    // Modal exonerar
    public $modalExonerar = false;

    public $cobroExonerarId = null;

    public $motivoExoneracion = '';

    // Modal anular pago
    public $modalAnularPago = false;

    public $pagoAnularId = null;

    public $motivoAnulacion = '';

    // Modal eliminar
    public $modalEliminar = false;

    public $cobroEliminarId = null;

    protected $queryString = [
        'buscar' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
    ];

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroMetodo()
    {
        $this->resetPage();
    }

    public function updatingFiltroVeterinaria()
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal()
    {
        $this->resetPage();
    }

    public function updatingFiltroDesde()
    {
        $this->resetPage();
    }

    public function updatingFiltroHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['buscar', 'filtroEstado', 'filtroMetodo', 'filtroVeterinaria', 'filtroSucursal', 'filtroDesde', 'filtroHasta']);
        $this->resetPage();
    }

    /**
     * Cobros visibles para el usuario: los de su sucursal, salvo vista general.
     */
    private function consultaVisible(): Builder
    {
        return app(CobroService::class)->consulta([], auth()->user());
    }

    private function buscarCobro($id): Cobro
    {
        return $this->consultaVisible()->findOrFail($id);
    }

    // ─── Acciones: Ver detalle ───
    public function ver($id)
    {
        abort_unless(auth()->user()->can('mostrar-detalle-cobro'), 403);

        $this->cobroAVer = $this->consultaVisible()
            ->with(['muestra.veterinaria', 'muestra.sucursal', 'pagos.registradoPor', 'pagos.anuladoPor', 'pagosVigentes', 'registradoPor'])
            ->findOrFail($id);
        $this->modalVer = true;
    }

    public function cerrarModalVer()
    {
        $this->modalVer = false;
        $this->cobroAVer = null;
    }

    // ─── Acciones: Registrar pago ───
    public function abrirPago($id)
    {
        abort_unless(auth()->user()->can('registrar-pago-cobro'), 403);

        $cobro = $this->buscarCobro($id);
        $this->resetValidation();
        $this->cobroPagoId = $cobro->id;
        $this->montoPago = $cobro->saldo > 0 ? number_format($cobro->saldo, 2, '.', '') : '';
        $this->metodoPago = PagoCobro::METODO_EFECTIVO;
        $this->modalPago = true;
    }

    public function guardarPago(CobroService $cobroService)
    {
        abort_unless(auth()->user()->can('registrar-pago-cobro'), 403);

        $this->validate([
            'montoPago' => 'required|numeric|min:0.01',
            'metodoPago' => 'required|in:'.implode(',', PagoCobro::METODOS),
        ], [
            'montoPago.required' => 'Ingrese el monto.',
            'montoPago.min' => 'El monto debe ser mayor a 0.',
        ]);

        try {
            $cobroService->registrarPago($this->buscarCobro($this->cobroPagoId), (float) $this->montoPago, $this->metodoPago, auth()->id());
        } catch (\DomainException $e) {
            $this->addError('montoPago', $e->getMessage());

            return;
        }

        $this->modalPago = false;
        $this->cobroPagoId = null;
        session()->flash('success', 'Pago registrado exitosamente.');
    }

    // ─── Acciones: Anular pago (desde el detalle) ───
    public function confirmarAnularPago($pagoId)
    {
        abort_unless(auth()->user()->can('anular-pago-cobro'), 403);

        $this->resetValidation();
        $this->pagoAnularId = $pagoId;
        $this->motivoAnulacion = '';
        $this->modalAnularPago = true;
    }

    public function anularPago(CobroService $cobroService)
    {
        abort_unless(auth()->user()->can('anular-pago-cobro'), 403);

        $this->validate(['motivoAnulacion' => 'required|string|max:255'], [
            'motivoAnulacion.required' => 'Indique el motivo de la anulación.',
        ]);

        $pago = PagoCobro::findOrFail($this->pagoAnularId);
        $this->buscarCobro($pago->cobro_id); // Verifica que el cobro sea visible para el usuario

        try {
            $cobroService->anularPago($pago, $this->motivoAnulacion, auth()->id());
        } catch (\DomainException $e) {
            $this->addError('motivoAnulacion', $e->getMessage());

            return;
        }

        $this->modalAnularPago = false;
        $this->pagoAnularId = null;

        if ($this->cobroAVer) {
            $this->ver($this->cobroAVer->id);
        }

        session()->flash('success', 'Pago anulado exitosamente.');
    }

    // ─── Acciones: Editar ───
    public function editar($id)
    {
        abort_unless(auth()->user()->can('editar-cobros'), 403);

        $cobro = $this->buscarCobro($id);
        $this->resetValidation();
        $this->cobroEditarId = $cobro->id;
        $this->total = number_format((float) $cobro->total, 2, '.', '');
        $this->examen = $cobro->examen;
        $this->observacion = $cobro->observacion;
        $this->modalEditar = true;
    }

    public function guardarEdicion(CobroService $cobroService)
    {
        abort_unless(auth()->user()->can('editar-cobros'), 403);

        $this->validate([
            'total' => 'required|numeric|min:0',
            'examen' => 'nullable|string|max:500',
            'observacion' => 'nullable|string|max:1000',
        ], [
            'total.required' => 'Ingrese el total.',
        ]);

        try {
            $cobroService->actualizar($this->buscarCobro($this->cobroEditarId), (float) $this->total, $this->examen, $this->observacion);
        } catch (\DomainException $e) {
            $this->addError('total', $e->getMessage());

            return;
        }

        $this->modalEditar = false;
        $this->cobroEditarId = null;
        session()->flash('success', 'Cobro actualizado exitosamente.');
    }

    // ─── Acciones: Exonerar ───
    public function confirmarExonerar($id)
    {
        abort_unless(auth()->user()->can('exonerar-cobro'), 403);

        $cobro = $this->buscarCobro($id);
        $this->resetValidation();
        $this->cobroExonerarId = $cobro->id;
        $this->motivoExoneracion = '';
        $this->modalExonerar = true;
    }

    public function exonerar(CobroService $cobroService)
    {
        abort_unless(auth()->user()->can('exonerar-cobro'), 403);

        $this->validate(['motivoExoneracion' => 'required|string|max:1000'], [
            'motivoExoneracion.required' => 'Indique el motivo de la exoneración.',
        ]);

        $cobroService->exonerar($this->buscarCobro($this->cobroExonerarId), $this->motivoExoneracion);

        $this->modalExonerar = false;
        $this->cobroExonerarId = null;
        session()->flash('success', 'Cobro marcado como exonerado.');
    }

    public function quitarExoneracion($id, CobroService $cobroService)
    {
        abort_unless(auth()->user()->can('exonerar-cobro'), 403);

        $cobroService->quitarExoneracion($this->buscarCobro($id));
        session()->flash('success', 'Se quitó la exoneración del cobro.');
    }

    // ─── Acciones: Eliminar ───
    public function confirmarEliminar($id)
    {
        abort_unless(auth()->user()->can('eliminar-cobros'), 403);

        $this->cobroEliminarId = $this->buscarCobro($id)->id;
        $this->modalEliminar = true;
    }

    public function cancelarEliminar()
    {
        $this->modalEliminar = false;
        $this->cobroEliminarId = null;
    }

    public function eliminar()
    {
        abort_unless(auth()->user()->can('eliminar-cobros'), 403);

        $this->buscarCobro($this->cobroEliminarId)->delete();
        $this->modalEliminar = false;
        $this->cobroEliminarId = null;
        session()->flash('success', 'Cobro eliminado exitosamente.');
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

    #[Computed]
    public function metodos()
    {
        return PagoCobro::METODOS;
    }

    #[Computed]
    public function metodosFiltro()
    {
        return [...PagoCobro::METODOS, CobroService::METODO_MIXTO, CobroService::SIN_PAGO];
    }

    #[Computed]
    public function estados()
    {
        return Cobro::ESTADOS;
    }

    /**
     * Filtros actuales (los mismos que usa la exportación)
     */
    public function filtros(): array
    {
        return [
            'buscar' => $this->buscar,
            'estado' => $this->filtroEstado,
            'metodo' => $this->filtroMetodo,
            'veterinaria_id' => $this->filtroVeterinaria,
            'sucursal_id' => $this->filtroSucursal,
            'fecha_desde' => $this->filtroDesde,
            'fecha_hasta' => $this->filtroHasta,
        ];
    }

    public function render(CobroService $cobroService)
    {
        $query = $cobroService->consulta($this->filtros(), auth()->user())
            ->with(['muestra.veterinaria', 'muestra.sucursal', 'pagosVigentes']);

        return view('livewire.cobros.gestionar-cobros', [
            'cobros' => $query->paginate(15),
        ])->layout('components.layouts.app');
    }
}
