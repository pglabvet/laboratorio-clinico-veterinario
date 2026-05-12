<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MuestraRechazada extends Model
{
    use Auditable, HasFactory;

    protected $table = 'muestras_rechazadas';

    protected $fillable = [
        'codigo_muestra',
        'paciente_nombre',
        'especie_id',
        'raza',
        'edad',
        'sexo',
        'propietario_nombre',
        'veterinaria_id',
        'sucursal_id',
        'tipo_muestra',
        'motivo_rechazo',
        'observaciones',
        'registrado_por',
        'fecha_rechazo',
    ];

    protected $casts = [
        'fecha_rechazo' => 'datetime',
    ];

    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    public function veterinaria(): BelongsTo
    {
        return $this->belongsTo(Veterinaria::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
