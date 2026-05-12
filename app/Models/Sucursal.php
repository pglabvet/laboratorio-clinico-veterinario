<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use Auditable, HasFactory;

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
        'telefono_2',
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

    /**
     * Obtener el prefijo para códigos de muestra
     * Extrae las primeras letras del nombre después de "Sucursal" para evitar conflictos
     * Ejemplos:
     *   "Sucursal Centro" -> "C"
     *   "Sucursal Sur" -> "S"
     *   "Sucursal Equipetrol" -> "EQ"
     */
    public function getPrefijo(): string
    {
        // Eliminar "Sucursal" del nombre y limpiar espacios
        $nombreLimpio = trim(str_replace('Sucursal', '', $this->nombre));

        // Mapeo especial para sucursales conocidas
        $prefijosMapeados = [
            'Centro' => 'C',
            'Norte' => 'N',
            'Sur' => 'S',
            'Este' => 'E',
            'Oeste' => 'O',
            'Equipetrol' => 'EQ',
        ];

        // Si existe un mapeo, usarlo
        if (isset($prefijosMapeados[$nombreLimpio])) {
            return $prefijosMapeados[$nombreLimpio];
        }

        // Por defecto, usar las primeras 2 letras en mayúsculas
        return strtoupper(substr($nombreLimpio, 0, 2));
    }
}
