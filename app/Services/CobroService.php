<?php

namespace App\Services;

use App\Models\Cobro;
use App\Models\Muestra;
use App\Models\PagoCobro;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CobroService
{
    public const METODO_MIXTO = 'Mixto';

    public const SIN_PAGO = 'Sin pago';

    /**
     * Consulta de cobros con los filtros del módulo, limitada a lo que el usuario puede ver
     * (su sucursal, salvo vista general). La usan la tabla y las exportaciones.
     */
    public function consulta(array $filtros, User $usuario): Builder
    {
        $filtros = array_filter($filtros, fn ($valor) => $valor !== null && $valor !== '');
        $metodosDistintos = '(select count(distinct metodo) from pagos_cobro where pagos_cobro.cobro_id = cobros.id and pagos_cobro.anulado_at is null)';

        return Cobro::query()
            ->when(! $usuario->can('vista-general-sistema'), function ($query) use ($usuario) {
                $query->whereHas('muestra', fn ($q) => $q->where('sucursal_id', $usuario->sucursal_id));
            })
            ->when($filtros['buscar'] ?? null, function ($query, $buscar) {
                $buscar = "%{$buscar}%";
                $query->whereHas('muestra', function ($q) use ($buscar) {
                    $q->whereRaw('unaccent(codigo_muestra) ilike unaccent(?)', [$buscar])
                        ->orWhereRaw('unaccent(paciente_nombre) ilike unaccent(?)', [$buscar])
                        ->orWhereRaw('unaccent(propietario_nombre) ilike unaccent(?)', [$buscar]);
                });
            })
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($filtros['metodo'] ?? null, function ($query, $metodo) use ($metodosDistintos) {
                match ($metodo) {
                    self::METODO_MIXTO => $query->whereRaw("{$metodosDistintos} > 1"),
                    self::SIN_PAGO => $query->whereRaw("{$metodosDistintos} = 0"),
                    // Un solo método: tiene pagos con ese método y ninguno con otro
                    default => $query->whereRaw("{$metodosDistintos} = 1")
                        ->whereHas('pagosVigentes', fn ($q) => $q->where('metodo', $metodo)),
                };
            })
            ->when($filtros['veterinaria_id'] ?? null, function ($query, $veterinariaId) {
                $query->whereHas('muestra', fn ($q) => $q->where('veterinaria_id', $veterinariaId));
            })
            ->when(($filtros['sucursal_id'] ?? null) && $usuario->can('filtro-de-sucursal-cobro'), function ($query) use ($filtros) {
                $query->whereHas('muestra', fn ($q) => $q->where('sucursal_id', $filtros['sucursal_id']));
            })
            ->when($filtros['fecha_desde'] ?? null, function ($query, $desde) {
                $query->whereHas('muestra', fn ($q) => $q->whereDate('fecha_recepcion', '>=', $desde));
            })
            ->when($filtros['fecha_hasta'] ?? null, function ($query, $hasta) {
                $query->whereHas('muestra', fn ($q) => $q->whereDate('fecha_recepcion', '<=', $hasta));
            })
            ->orderByDesc(
                Muestra::select('fecha_recepcion')->whereColumn('muestras.id', 'cobros.muestra_id')
            );
    }

    /**
     * Crear el cobro de una muestra recién registrada (estado Pendiente, sin monto).
     * El examen se autocompleta con los análisis de la muestra.
     */
    public function crearParaMuestra(Muestra $muestra, ?int $usuarioId = null): Cobro
    {
        return Cobro::firstOrCreate(
            ['muestra_id' => $muestra->id],
            [
                'examen' => $this->examenDesdeAnalisis($muestra),
                'total' => 0,
                'total_pagado' => 0,
                'estado' => Cobro::ESTADO_PENDIENTE,
                'registrado_por' => $usuarioId,
            ]
        );
    }

    /**
     * Nombres de los análisis de la muestra separados por coma.
     */
    public function examenDesdeAnalisis(Muestra $muestra): string
    {
        return $muestra->analisis()
            ->with('tipoAnalisis')
            ->get()
            ->pluck('tipoAnalisis.nombre')
            ->filter()
            ->unique()
            ->implode(', ');
    }

    /**
     * Registrar un pago. No permite pagar más que el saldo.
     */
    public function registrarPago(Cobro $cobro, float $monto, string $metodo, ?int $usuarioId = null): PagoCobro
    {
        return DB::transaction(function () use ($cobro, $monto, $metodo, $usuarioId) {
            $cobro = Cobro::lockForUpdate()->findOrFail($cobro->id);

            if ($cobro->exonerado) {
                throw new \DomainException('No se pueden registrar pagos en un cobro exonerado.');
            }

            if (! in_array($metodo, PagoCobro::METODOS, true)) {
                throw new \DomainException('Método de pago no válido.');
            }

            if ($monto <= 0) {
                throw new \DomainException('El monto debe ser mayor a 0.');
            }

            if (round($monto, 2) > $cobro->saldo) {
                throw new \DomainException('El monto supera el saldo pendiente (Bs '.number_format($cobro->saldo, 2).').');
            }

            $pago = $cobro->pagos()->create([
                'monto' => round($monto, 2),
                'metodo' => $metodo,
                'fecha_pago' => now(),
                'registrado_por' => $usuarioId,
            ]);

            $this->recalcular($cobro);

            return $pago;
        });
    }

    /**
     * Anular un pago (no se borra, queda en el historial).
     */
    public function anularPago(PagoCobro $pago, string $motivo, ?int $usuarioId = null): void
    {
        DB::transaction(function () use ($pago, $motivo, $usuarioId) {
            if ($pago->estaAnulado()) {
                throw new \DomainException('El pago ya fue anulado.');
            }

            $pago->update([
                'anulado_at' => now(),
                'anulado_por' => $usuarioId,
                'motivo_anulacion' => $motivo,
            ]);

            $this->recalcular($pago->cobro);
        });
    }

    /**
     * Actualizar total, examen y observación. El total no puede ser menor a lo ya pagado.
     */
    public function actualizar(Cobro $cobro, float $total, ?string $examen, ?string $observacion): void
    {
        DB::transaction(function () use ($cobro, $total, $examen, $observacion) {
            $cobro = Cobro::lockForUpdate()->findOrFail($cobro->id);

            if (round($total, 2) < (float) $cobro->total_pagado) {
                throw new \DomainException('El total no puede ser menor a lo ya pagado (Bs '.number_format((float) $cobro->total_pagado, 2).').');
            }

            $cobro->fill([
                'total' => round($total, 2),
                'examen' => $examen,
                'observacion' => $observacion,
            ]);

            $this->recalcular($cobro);
        });
    }

    /**
     * Marcar como exonerado (requiere motivo, que se guarda en la observación).
     */
    public function exonerar(Cobro $cobro, string $motivo): void
    {
        $cobro->exonerado = true;
        $cobro->observacion = $motivo;
        $this->recalcular($cobro);
    }

    /**
     * Quitar la exoneración: el estado vuelve a calcularse por los pagos.
     */
    public function quitarExoneracion(Cobro $cobro): void
    {
        $cobro->exonerado = false;
        $this->recalcular($cobro);
    }

    /**
     * Recalcular total pagado y estado a partir de los pagos vigentes.
     */
    public function recalcular(Cobro $cobro): Cobro
    {
        $pagado = round((float) $cobro->pagosVigentes()->sum('monto'), 2);
        $total = round((float) $cobro->total, 2);

        $cobro->total_pagado = $pagado;
        $cobro->estado = match (true) {
            $cobro->exonerado => Cobro::ESTADO_EXONERADO,
            $total > 0 && $pagado >= $total => Cobro::ESTADO_PAGADO,
            $pagado > 0 => Cobro::ESTADO_PARCIAL,
            default => Cobro::ESTADO_PENDIENTE,
        };
        $cobro->save();
        $cobro->unsetRelation('pagosVigentes');

        return $cobro;
    }
}
