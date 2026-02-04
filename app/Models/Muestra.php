<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Muestra extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_muestra',
        'paciente_nombre',
        'especie_id',
        'raza',
        'edad',
        'sexo',
        'color',
        'propietario_nombre',
        'veterinaria_id',
        'sucursal_id',
        'tipo_muestra',
        'fecha_recepcion',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
    ];

    /**
     * Relación con especie
     */
    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    /**
     * Relación con veterinaria
     */
    public function veterinaria(): BelongsTo
    {
        return $this->belongsTo(Veterinaria::class);
    }

    /**
     * Relación con sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Relación con análisis
     */
    public function analisis(): HasMany
    {
        return $this->hasMany(Analisis::class);
    }
}
