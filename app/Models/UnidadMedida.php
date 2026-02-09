<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadMedida extends Model
{
    use Auditable, HasFactory;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con insumos
     */
    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class, 'unidad_medida_id');
    }

    /**
     * Scope para obtener solo unidades activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }
}
