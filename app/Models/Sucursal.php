<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con muestras
     */
    public function muestras(): HasMany
    {
        return $this->hasMany(Muestra::class);
    }
   


    /**
     * Get the users for the sucursal.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope a query to only include active sucursales.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', true);
    }

    /**
     * Scope a query to only include inactive sucursales.
     */
    public function scopeInactive($query)
    {
        return $query->where('estado', false);
    }
}
