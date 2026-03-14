<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeterinariaTelefono extends Model
{
    protected $table = 'veterinaria_telefonos';

    protected $fillable = [
        'veterinaria_id',
        'telefono',
        'nombre_contacto',
        'es_principal',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    public function veterinaria(): BelongsTo
    {
        return $this->belongsTo(Veterinaria::class);
    }
}
