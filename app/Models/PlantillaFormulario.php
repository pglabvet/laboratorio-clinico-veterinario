<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'componentes' => 'array',
        'activo' => 'boolean',
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
}
