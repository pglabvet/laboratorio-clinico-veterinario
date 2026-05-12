<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteInventario extends Model
{
    use Auditable, HasFactory;

    protected $table = 'lotes_inventario';

    protected $fillable = [
        'insumo_id',
        'sucursal_id',
        'movimiento_entrada_id',
        'cantidad_inicial',
        'cantidad_restante',
        'costo_unitario',
        'fecha_entrada',
        'codigo_lote',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'cantidad_inicial' => 'decimal:6',
        'cantidad_restante' => 'decimal:6',
        'costo_unitario' => 'decimal:6',
        'fecha_entrada' => 'datetime',
        'fecha_vencimiento' => 'date',
    ];

    /**
     * Relación con insumo
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    /**
     * Relación con sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Relación con el movimiento de entrada que creó este lote
     */
    public function movimientoEntrada(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class , 'movimiento_entrada_id');
    }

    /**
     * Scope: solo lotes con stock disponible
     */
    public function scopeConStock($query)
    {
        return $query->where('cantidad_restante', '>', 0);
    }

    /**
     * Scope: ordenar PEPS (más antiguo primero)
     */
    public function scopePeps($query)
    {
        return $query->orderBy('fecha_entrada', 'asc');
    }

    /**
     * Costo total restante del lote
     */
    public function getCostoTotalLoteAttribute(): float
    {
        return round($this->cantidad_restante * $this->costo_unitario, 6);
    }
}
