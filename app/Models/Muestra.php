<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Picqer\Barcode\BarcodeGeneratorSVG;

class Muestra extends Model
{
    use Auditable, HasFactory;

    // Estados disponibles
    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_EN_PROCESO = 'En proceso';

    public const ESTADO_COMPLETADO = 'Completado';

    public const ESTADO_ENVIADO = 'Enviado';

    protected $fillable = [
        'codigo_muestra',
        'paciente_nombre',
        'especie_id',
        'raza',
        'edad',
        'sexo',
        'color',
        'propietario_nombre',
        'veterinaria_id',
        'sucursal_id',
        'tipo_muestra',
        'fecha_recepcion',
        'estado',
        'observaciones',
        'diagnostico',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
    ];

    /**
     * Relación con especie
     */
    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    /**
     * Relación con veterinaria
     */
    public function veterinaria(): BelongsTo
    {
        return $this->belongsTo(Veterinaria::class);
    }

    /**
     * Relación con sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Relación con análisis
     */
    public function analisis(): HasMany
    {
        return $this->hasMany(Analisis::class);
    }

    /**
     * Generar código de barras como SVG
     */
    public function generarCodigoBarras(): string
    {
        $generator = new BarcodeGeneratorSVG;
        // Parámetros optimizados para impresora térmica 203 DPI
        $svg = $generator->getBarcode(
            $this->codigo_muestra,
            $generator::TYPE_CODE_128,
            2.0, //  ancho de barra optimizado para 203 DPI
            65 //  altura aumentada para mejor lectura del escáner
        );

        // Agregar viewBox para que el SVG escale correctamente por CSS
        preg_match('/width="([^"]+)"/', $svg, $widthMatch);
        preg_match('/height="([^"]+)"/', $svg, $heightMatch);
        $width = floatval($widthMatch[1] ?? 0);
        $height = floatval($heightMatch[1] ?? 0);

        // Generar ID único basado en el código de muestra para evitar problemas de caché
        $uniqueId = 'barcode-' . $this->codigo_muestra . '-' . uniqid();

        if ($width > 0 && $height > 0 && !str_contains($svg, 'viewBox=')) {
            $svg = preg_replace(
                '/<svg\b([^>]*)>/',
                '<svg$1 id="' . $uniqueId . '" viewBox="0 0 ' . $width . ' ' . $height . '" preserveAspectRatio="xMidYMid meet">',
                $svg,
                1
            );
        }
        elseif (str_contains($svg, 'preserveAspectRatio=')) {
            $svg = preg_replace('/preserveAspectRatio="[^"]*"/', 'preserveAspectRatio="xMidYMid meet"', $svg, 1);
            $svg = preg_replace('/<svg\b([^>]*)>/', '<svg$1 id="' . $uniqueId . '">', $svg, 1);
        }
        else {
            $svg = preg_replace('/<svg\b([^>]*)>/', '<svg$1 id="' . $uniqueId . '" preserveAspectRatio="xMidYMid meet">', $svg, 1);
        }

        return $svg;
    }

    /**
     * Obtener los estados disponibles
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EN_PROCESO => 'En proceso',
            self::ESTADO_COMPLETADO => 'Completado',
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
                self::ESTADO_EN_PROCESO => 'blue',
                self::ESTADO_COMPLETADO => 'green',
                self::ESTADO_ENVIADO => 'purple',
                default => 'zinc',
            };
    }

    /**
     * Actualizar el estado de la muestra según los estados de sus análisis
     */
    public function actualizarEstadoSegunAnalisis(): void
    {
        // Refrescar la muestra para asegurar datos frescos
        $this->refresh();

        // Obtener conteos directamente de la BD con una sola query
        $conteos = $this->analisis()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $totalAnalisis = $conteos->sum();

        // Si no hay análisis, mantener el estado actual
        if ($totalAnalisis === 0) {
            return;
        }

        $enviados = $conteos->get(Analisis::ESTADO_ENVIADO, 0);
        $aprobados = $conteos->get(Analisis::ESTADO_APROBADO, 0);
        $enRevision = $conteos->get(Analisis::ESTADO_EN_REVISION, 0);
        $pendientes = $conteos->get(Analisis::ESTADO_PENDIENTE, 0);

        // Determinar el nuevo estado de la muestra
        // IMPORTANTE: El orden de las condiciones importa
        $nuevoEstado = match (true) {
                // Todos los análisis enviados -> Muestra enviada
                $enviados === $totalAnalisis => self::ESTADO_ENVIADO,
                // TODOS aprobados o enviados (sin ninguno en revisión ni pendiente) -> Muestra completada
                ($aprobados + $enviados) === $totalAnalisis && $enRevision === 0 && $pendientes === 0 => self::ESTADO_COMPLETADO,
                // Al menos uno en proceso (en revisión, aprobado o enviado parcial) -> En proceso
                ($enRevision > 0 || $aprobados > 0 || $enviados > 0) => self::ESTADO_EN_PROCESO,
                // Todos pendientes -> Pendiente
                $pendientes === $totalAnalisis => self::ESTADO_PENDIENTE,
                // Por defecto mantener en proceso
                default => self::ESTADO_EN_PROCESO,
            };

        // Solo actualizar si el estado cambió
        if ($this->estado !== $nuevoEstado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }

    /**
     * Verificar si todos los análisis de la muestra pueden ser enviados
     * (todos deben estar aprobados o ya enviados)
     */
    public function puedeEnviarTodosAnalisis(): bool
    {
        $analisis = $this->analisis;

        if ($analisis->isEmpty()) {
            return false;
        }

        return $analisis->every(fn($a) => $a->puedeSerEnviado());
    }
}
