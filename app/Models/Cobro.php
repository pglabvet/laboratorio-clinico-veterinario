<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cobro extends Model
{
    use Auditable, SoftDeletes;

    // Estados disponibles (calculados por CobroService, salvo Exonerado)
    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_PARCIAL = 'Parcial';

    public const ESTADO_PAGADO = 'Pagado';

    public const ESTADO_EXONERADO = 'Exonerado';

    public const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_PARCIAL,
        self::ESTADO_PAGADO,
        self::ESTADO_EXONERADO,
    ];

    protected string $nombreAuditable = 'Cobro';

    protected $fillable = [
        'muestra_id',
        'examen',
        'total',
        'total_pagado',
        'estado',
        'exonerado',
        'observacion',
        'registrado_por',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'total_pagado' => 'decimal:2',
        'exonerado' => 'boolean',
    ];

    public function muestra(): BelongsTo
    {
        return $this->belongsTo(Muestra::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Todos los pagos (incluye anulados, para el historial)
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCobro::class)->orderBy('fecha_pago');
    }

    /**
     * Solo pagos vigentes (no anulados)
     */
    public function pagosVigentes(): HasMany
    {
        return $this->hasMany(PagoCobro::class)->whereNull('anulado_at');
    }

    /**
     * Saldo pendiente
     */
    public function getSaldoAttribute(): float
    {
        return max(0, round((float) $this->total - (float) $this->total_pagado, 2));
    }

    /**
     * Método de pago a mostrar: el único usado, "Mixto" si hay varios, o null si no hay pagos.
     */
    public function getMetodoAttribute(): ?string
    {
        $metodos = $this->pagosVigentes->pluck('metodo')->unique();

        return match ($metodos->count()) {
            0 => null,
            1 => $metodos->first(),
            default => 'Mixto',
        };
    }
}
