<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Insumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria_id',
        'unidad',
        'stock_actual',
        'stock_minimo',
        'estado',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'estado' => 'boolean',
    ];

    /**
     * Relación con categoría de insumo
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_id');
    }

    /**
     * Relación con movimientos de inventario
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    /**
     * Relación muchos a muchos con tipos de análisis
     */
    public function tiposAnalisis(): BelongsToMany
    {
        return $this->belongsToMany(TipoAnalisis::class, 'tipo_analisis_insumos')
            ->withPivot('cantidad_requerida')
            ->withTimestamps();
    }

    /**
     * Relación muchos a muchos con análisis
     */
    public function analisis(): BelongsToMany
    {
        return $this->belongsToMany(Analisis::class, 'analisis_insumos')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
    }
}
