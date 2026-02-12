<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'nombre',
        'categoria_id',
        'unidad_medida_id',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con unidad de medida
     */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    /**
     * Relación con categoría de insumo
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_id');
    }

    /**
     * Relación con inventarios por sucursal
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(InventarioSucursal::class);
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

    /**
     * Scope para obtener solo insumos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }
}
