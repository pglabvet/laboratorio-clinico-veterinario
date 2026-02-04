<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resultado extends Model
{
    use HasFactory;

    protected $fillable = [
        'analisis_id',
        'parametro_id',
        'valor',
        'fuera_rango',
    ];

    protected $casts = [
        'fuera_rango' => 'boolean',
    ];

    /**
     * Relación con análisis
     */
    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class);
    }

    /**
     * Relación con parámetro
     */
    public function parametro(): BelongsTo
    {
        return $this->belongsTo(ParametroAnalisis::class, 'parametro_id');
    }

    /**
     * Relación con historial de resultados
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialResultado::class);
    }
}
