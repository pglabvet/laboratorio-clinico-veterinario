<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analisis extends Model
{
    use Auditable, HasFactory;

    protected $table = 'analisis';

    // Estados disponibles
    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_EN_REVISION = 'En revision';

    public const ESTADO_APROBADO = 'Aprobado';

    public const ESTADO_ENVIADO = 'Enviado';

    protected $fillable = [
        'muestra_id',
        'tipo_analisis_id',
        'plantilla_formulario_id',
        'bioquimico_id',
        'aprobador_id',
        'estado',
        'observaciones_bioquimico',
        'observaciones_aprobador',
        'fecha_inicio',
        'fecha_finalizacion',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_finalizacion' => 'datetime',
        'fecha_aprobacion' => 'datetime',
    ];

    /**
     * Boot del modelo - registrar eventos
     */
    protected static function boot()
    {
        parent::boot();

        // Cuando se actualiza un análisis, verificar si debe cambiar el estado de la muestra
        static::updated(function (Analisis $analisis) {
            // Solo actuar si cambió el estado
            if ($analisis->wasChanged('estado')) {
                $analisis->muestra?->actualizarEstadoSegunAnalisis();
            }
        });
    }

    /**
     * Relación con muestra
     */
    public function muestra(): BelongsTo
    {
        return $this->belongsTo(Muestra::class);
    }

    /**
     * Relación con tipo de análisis
     */
    public function tipoAnalisis(): BelongsTo
    {
        return $this->belongsTo(TipoAnalisis::class);
    }

    /**
     * Relación con plantilla de formulario
     */
    public function plantillaFormulario(): BelongsTo
    {
        return $this->belongsTo(PlantillaFormulario::class);
    }

    /**
     * Relación con bioquímico (usuario)
     */
    public function bioquimico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bioquimico_id');
    }

    /**
     * Relación con aprobador (usuario)
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }

    /**
     * Relación con resultados
     */
    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class);
    }

    /**
     * Relación con imágenes
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenAnalisis::class);
    }

    /**
     * Relación con revisiones
     */
    public function revisiones(): HasMany
    {
        return $this->hasMany(Revision::class);
    }

    /**
     * Relación con PDFs
     */
    public function pdfs(): HasMany
    {
        return $this->hasMany(Pdf::class);
    }

    /**
     * Relación muchos a muchos con insumos
     */
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'analisis_insumos')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
    }

    /**
     * Obtener los estados disponibles
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EN_REVISION => 'En revision',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_ENVIADO => 'Enviado',
        ];
    }

    /**
     * Obtener el color del badge según el estado
     */
    public function getColorEstado(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'amber',
            self::ESTADO_EN_REVISION => 'blue',
            self::ESTADO_APROBADO => 'green',
            self::ESTADO_ENVIADO => 'purple',
            default => 'zinc',
        };
    }

    /**
     * Verificar si el análisis puede ser enviado (está aprobado o ya enviado)
     */
    public function puedeSerEnviado(): bool
    {
        return in_array($this->estado, [self::ESTADO_APROBADO, self::ESTADO_ENVIADO]);
    }

    /**
     * Verificar si el análisis ya fue enviado
     */
    public function estaEnviado(): bool
    {
        return $this->estado === self::ESTADO_ENVIADO;
    }

    /**
     * Verificar si el análisis está aprobado
     */
    public function estaAprobado(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }
}
