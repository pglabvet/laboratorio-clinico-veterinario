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
        'motivo',
        'observacion',
        'usuario_id',
        'fecha',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
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
}
