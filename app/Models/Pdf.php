<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pdf extends Model
{
    use HasFactory;

    protected $fillable = [
        'analisis_id',
        'ruta_archivo',
        'generado_por',
        'fecha_generacion',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
    ];

    /**
     * Relación con análisis
     */
    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class);
    }

    /**
     * Relación con usuario que generó el PDF
     */
    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    /**
     * Relación con tokens de descarga
     */
    public function tokensDescarga(): HasMany
    {
        return $this->hasMany(TokenDescarga::class);
    }

    /**
     * Alias para tokensDescarga (uso simplificado)
     */
    public function tokens(): HasMany
    {
        return $this->tokensDescarga();
    }

    /**
     * Obtiene el token vigente (no expirado) más reciente para este PDF.
     */
    public function tokenVigente(): ?TokenDescarga
    {
        return $this->tokens()
            ->where('fecha_expiracion', '>', now())
            ->latest()
            ->first();
    }
}
