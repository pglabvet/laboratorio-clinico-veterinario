<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultado extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'analisis_id',
        'tipo',
        'valor',
        'fuera_rango',
    ];

    protected $casts = [
        'fuera_rango' => 'boolean',
        'valor' => 'array',
    ];

    /**
     * Relación con análisis
     */
    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class);
    }
}
