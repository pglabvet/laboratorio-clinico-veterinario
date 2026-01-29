<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioSucursal extends Model
{
    use HasFactory;

    protected $table = 'inventario_sucursal';

    protected $fillable = [
        'insumo_id',
        'sucursal_id',
        'stock_actual',
        'stock_minimo',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
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
}
