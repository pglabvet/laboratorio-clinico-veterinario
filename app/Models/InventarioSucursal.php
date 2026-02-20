<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioSucursal extends Model
{
    use Auditable, HasFactory;

    protected $table = 'inventario_sucursal';

    protected $fillable = [
        'insumo_id',
        'sucursal_id',
        'stock_actual',
        'stock_minimo',
        'costo_total',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'costo_total' => 'decimal:4',
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
     * Scope para obtener inventarios con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<', 'stock_minimo');
    }

    /**
     * Verifica si el stock está bajo
     */
    public function tieneStockBajo(): bool
    {
        return $this->stock_actual < $this->stock_minimo;
    }

    /**
     * Relación con lotes de inventario
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class , 'sucursal_id', 'sucursal_id')
            ->where('insumo_id', $this->insumo_id);
    }

    /**
     * Recalcular el costo total basado en los lotes con stock disponible
     */
    public function recalcularCostoTotal(): void
    {
        $this->costo_total = LoteInventario::where('insumo_id', $this->insumo_id)
            ->where('sucursal_id', $this->sucursal_id)
            ->where('cantidad_restante', '>', 0)
            ->selectRaw('SUM(cantidad_restante * costo_unitario) as total')
            ->value('total') ?? 0;
        $this->save();
    }
}
