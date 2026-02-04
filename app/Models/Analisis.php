<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';

    protected $fillable = [
        'muestra_id',
        'tipo_analisis_id',
        'bioquimico_id',
        'estado',
        'observaciones_bioquimico',
        'fecha_inicio',
        'fecha_finalizacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_finalizacion' => 'datetime',
    ];

    /**
     * Relación con muestra
     */
    public function muestra(): BelongsTo
    {
        return $this->belongsTo(Muestra::class);
    }

    /**
     * Relación con tipo de análisis
     */
    public function tipoAnalisis(): BelongsTo
    {
        return $this->belongsTo(TipoAnalisis::class);
    }

    /**
     * Relación con bioquímico (usuario)
     */
    public function bioquimico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bioquimico_id');
    }

    /**
     * Relación con resultados
     */
    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class);
    }

    /**
     * Relación con imágenes
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenAnalisis::class);
    }

    /**
     * Relación con revisiones
     */
    public function revisiones(): HasMany
    {
        return $this->hasMany(Revision::class);
    }

    /**
     * Relación con PDFs
     */
    public function pdfs(): HasMany
    {
        return $this->hasMany(Pdf::class);
    }

    /**
     * Relación muchos a muchos con insumos
     */
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'analisis_insumos')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
    }
}
