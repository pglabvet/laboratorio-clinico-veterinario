<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoAnalisis extends Model
{
    use HasFactory;

    protected $table = 'tipos_analisis';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con análisis
     */
    public function analisis(): HasMany
    {
        return $this->hasMany(Analisis::class);
    }

    /**
     * Relación muchos a muchos con insumos
     */
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'tipo_analisis_insumos')
            ->withPivot('cantidad_requerida')
            ->withTimestamps();
    }

    /**
     * Relación con plantillas de formulario
     */
    public function plantillas(): HasMany
    {
        return $this->hasMany(PlantillaFormulario::class);
    }
}
