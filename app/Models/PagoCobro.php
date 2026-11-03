<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoCobro extends Model
{
    use Auditable;

    public const METODO_QR = 'QR';

    public const METODO_EFECTIVO = 'Efectivo';

    public const METODO_TARJETA = 'Tarjeta';

    public const METODOS = [
        self::METODO_QR,
        self::METODO_EFECTIVO,
        self::METODO_TARJETA,
    ];

    protected $table = 'pagos_cobro';

    protected string $nombreAuditable = 'Pago de Cobro';

    protected $fillable = [
        'cobro_id',
        'monto',
        'metodo',
        'fecha_pago',
        'registrado_por',
        'anulado_at',
        'anulado_por',
        'motivo_anulacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'anulado_at' => 'datetime',
    ];

    public function cobro(): BelongsTo
    {
        return $this->belongsTo(Cobro::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function estaAnulado(): bool
    {
        return $this->anulado_at !== null;
    }
}
