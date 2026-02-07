<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaFormulario extends Model
{
    use HasFactory;

    protected $table = 'plantillas_formulario';

    protected $fillable = [
        'tipo_analisis_id',
        'nombre',
        'descripcion',
        'componentes',
        'activo',
        'creado_por',
        'version',
        'plantilla_base_id',
    ];

    protected $casts = [
        'componentes' => 'array',
        'activo' => 'boolean',
        'version' => 'integer',
    ];

    /**
     * Relación con tipo de análisis
     */
    public function tipoAnalisis(): BelongsTo
    {
        return $this->belongsTo(TipoAnalisis::class);
    }

    /**
     * Relación con usuario creador
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación muchos a muchos con insumos
     */
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'plantilla_insumos', 'plantilla_formulario_id', 'insumo_id')
            ->withPivot('cantidad_requerida')
            ->withTimestamps();
    }

    /**
     * Relación con análisis que usan esta plantilla
     */
    public function analisis(): HasMany
    {
        return $this->hasMany(Analisis::class, 'plantilla_formulario_id');
    }

    /**
     * Relación con la plantilla base (para versiones)
     */
    public function plantillaBase(): BelongsTo
    {
        return $this->belongsTo(PlantillaFormulario::class, 'plantilla_base_id');
    }

    /**
     * Relación con versiones derivadas de esta plantilla
     */
    public function versiones(): HasMany
    {
        return $this->hasMany(PlantillaFormulario::class, 'plantilla_base_id');
    }

    /**
     * Verificar si la plantilla está en uso (tiene análisis asociados)
     */
    public function estaEnUso(): bool
    {
        return $this->analisis()->exists();
    }

    /**
     * Contar análisis que usan esta plantilla
     */
    public function contarAnalisis(): int
    {
        return $this->analisis()->count();
    }

    /**
     * Obtener parámetros del tipo de análisis asociado
     */
    public function parametros()
    {
        return $this->tipoAnalisis->parametros() ?? collect([]);
    }
}

