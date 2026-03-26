<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use Auditable, HasFactory;

    protected $table = 'movimientos_inventario';

    /**
     * Tipos de movimiento permitidos
     */
    const TIPOS_MOVIMIENTO = [
        'ENTRADA',
        'SALIDA_MANUAL',
        'CONSUMO_ANALISIS',
        'AJUSTE',
    ];

    /**
     * Motivos de movimiento permitidos
     */
    const MOTIVOS = [
        'MERMA',
        'VENCIMIENTO',
        'USO_EXTRAORDINARIO',
        'CONSUMO_ANALISIS',
        'AJUSTE_INVENTARIO',
        'COMPRA',
        'DEVOLUCION',
        'OTRO',
    ];

    protected $fillable = [
        'insumo_id',
        'sucursal_id',
        'tipo_movimiento',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'motivo',
        'observacion',
        'usuario_id',
        'fecha',
    ];

    protected $casts = [
        'cantidad' => 'decimal:6',
        'costo_unitario' => 'decimal:6',
        'costo_total' => 'decimal:6',
        'fecha' => 'datetime',
    ];

    /**
     * Relación con insumo
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    /**
     * Relación con usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Relación con lote de inventario (para movimientos de entrada)
     */
    public function lote(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LoteInventario::class , 'movimiento_entrada_id');
    }
}
