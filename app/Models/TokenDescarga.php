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
        'codigo_corto',
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
     * @param int $diasExpiracion Días hasta que expire el token (default: 365)
     * @return self
     */
    public static function crearParaPdf(int $pdfId, int $diasExpiracion = 365): self
    {
        return self::create([
            'pdf_id' => $pdfId,
            'token' => Str::random(64),
            'codigo_corto' => self::generarCodigoCorto(),
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
     * Obtener la URL de descarga (usa código corto)
     */
    public function getUrlDescarga(): string
    {
        return url("/r/{$this->codigo_corto}");
    }

    /**
     * Buscar token válido por string (token largo)
     */
    public static function buscarValido(string $token): ?self
    {
        return self::where('token', $token)
            ->where('fecha_expiracion', '>', now())
            ->where('usado', false)
            ->first();
    }

    /**
     * Buscar token válido por código corto
     */
    public static function buscarPorCodigoCorto(string $codigo): ?self
    {
        return self::where('codigo_corto', $codigo)
            ->where('fecha_expiracion', '>', now())
            ->where('usado', false)
            ->first();
    }

    /**
     * Generar un código corto único de 10 caracteres alfanuméricos
     */
    private static function generarCodigoCorto(): string
    {
        do {
            $codigo = Str::random(10);
        } while (self::where('codigo_corto', $codigo)->exists());

        return $codigo;
    }
}

