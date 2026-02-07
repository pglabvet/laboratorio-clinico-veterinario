<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';

    // Estados disponibles (deben coincidir con CHECK constraint en DB)
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_PROCESO = 'en_proceso';
    public const ESTADO_FINALIZADO = 'finalizado';
    public const ESTADO_APROBADO = 'aprobado';
    public const ESTADO_RECHAZADO = 'rechazado';

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
            self::ESTADO_EN_PROCESO => 'En Proceso',
            self::ESTADO_FINALIZADO => 'Finalizado',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_RECHAZADO => 'Rechazado',
        ];
    }

    /**
     * Obtener el color del badge según el estado
     */
    public function getColorEstado(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'amber',
            self::ESTADO_EN_PROCESO => 'blue',
            self::ESTADO_FINALIZADO => 'cyan',
            self::ESTADO_APROBADO => 'green',
            self::ESTADO_RECHAZADO => 'red',
            default => 'zinc',
        };
    }
    
    /**
     * Sincronizar estado de la muestra basándose en los estados de sus análisis
     */
    protected static function booted()
    {
        static::saved(function ($analisis) {
            $analisis->sincronizarEstadoMuestra();
        });
        
        static::deleted(function ($analisis) {
            $analisis->sincronizarEstadoMuestra();
        });
    }
    
    /**
     * Sincronizar el estado de la muestra según los estados de todos sus análisis
     */
    public function sincronizarEstadoMuestra(): void
    {
        $muestra = $this->muestra;
        if (!$muestra) {
            return;
        }
        
        $analisis = $muestra->analisis;
        
        // Si no tiene análisis, la muestra queda pendiente
        if ($analisis->isEmpty()) {
            $muestra->estado = Muestra::ESTADO_PENDIENTE;
            $muestra->saveQuietly();
            return;
        }
        
        $totalAnalisis = $analisis->count();
        $pendientes = $analisis->where('estado', self::ESTADO_PENDIENTE)->count();
        $enProceso = $analisis->where('estado', self::ESTADO_EN_PROCESO)->count();
        $finalizados = $analisis->where('estado', self::ESTADO_FINALIZADO)->count();
        $aprobados = $analisis->where('estado', self::ESTADO_APROBADO)->count();
        
        // Lógica de sincronización:
        // - Si TODOS están aprobados → Muestra "Enviado"
        // - Si TODOS están finalizados o aprobados → Muestra "Completado"
        // - Si al menos uno está en proceso, finalizado o aprobado → Muestra "En proceso"
        // - Si TODOS están pendientes → Muestra "Pendiente"
        
        if ($aprobados === $totalAnalisis) {
            $nuevoEstado = Muestra::ESTADO_ENVIADO;
        } elseif (($finalizados + $aprobados) === $totalAnalisis) {
            $nuevoEstado = Muestra::ESTADO_COMPLETADO;
        } elseif ($enProceso > 0 || $finalizados > 0 || $aprobados > 0) {
            $nuevoEstado = Muestra::ESTADO_EN_PROCESO;
        } else {
            $nuevoEstado = Muestra::ESTADO_PENDIENTE;
        }
        
        if ($muestra->estado !== $nuevoEstado) {
            $muestra->estado = $nuevoEstado;
            $muestra->saveQuietly();
        }
    }
}
