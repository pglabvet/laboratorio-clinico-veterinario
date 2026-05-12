<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veterinaria extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'nombre',
        'responsable',
        'email',
        'direccion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con teléfonos adicionales
     */
    public function telefonos(): HasMany
    {
        return $this->hasMany(VeterinariaTelefono::class);
    }

    /**
     * Relación con muestras
     */
    public function muestras(): HasMany
    {
        return $this->hasMany(Muestra::class);
    }
}
