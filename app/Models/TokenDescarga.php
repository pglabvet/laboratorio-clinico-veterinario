<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TokenDescarga extends Model
{
    use HasFactory;

    protected $table = 'tokens_descarga';

    protected $fillable = [
        'pdf_id',
        'token',
        'fecha_expiracion',
        'usado',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'usado' => 'boolean',
    ];

    /**
     * Relación con PDF
     */
    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Pdf::class);
    }

    /**
     * Relación con logs de descarga
     */
    public function logsDescarga(): HasMany
    {
        return $this->hasMany(LogDescarga::class, 'token_id');
    }

    /**
     * Crear un token de descarga para un PDF
     * 
     * @param int $pdfId ID del PDF
    * @param int $diasExpiracion Días hasta que expire el token (default: 14)
     * @return self
     */
    public static function crearParaPdf(int $pdfId, int $diasExpiracion = 14): self
    {
        return self::create([
            'pdf_id' => $pdfId,
            'token' => Str::random(64),
            'fecha_expiracion' => now()->addDays($diasExpiracion),
            'usado' => false,
        ]);
    }

    /**
     * Verificar si el token es válido (no expirado y no usado)
     */
    public function esValido(): bool
    {
        return !$this->usado && $this->fecha_expiracion->isFuture();
    }

    /**
     * Obtener la URL de descarga completa
     */
    public function getUrlDescarga(): string
    {
        return url("/descargar/{$this->token}");
    }

    /**
     * Buscar token válido por string
     */
    public static function buscarValido(string $token): ?self
    {
        return self::where('token', $token)
            ->where('fecha_expiracion', '>', now())
            ->where('usado', false)
            ->first();
    }
}
