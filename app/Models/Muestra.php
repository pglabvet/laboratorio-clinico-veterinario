<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Picqer\Barcode\BarcodeGeneratorSVG;

class Muestra extends Model
{
    use HasFactory;

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
        $generator = new BarcodeGeneratorSVG();
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
        } elseif (str_contains($svg, 'preserveAspectRatio=')) {
            $svg = preg_replace('/preserveAspectRatio="[^"]*"/', 'preserveAspectRatio="xMidYMid meet"', $svg, 1);
            $svg = preg_replace('/<svg\b([^>]*)>/', '<svg$1 id="' . $uniqueId . '">', $svg, 1);
        } else {
            $svg = preg_replace('/<svg\b([^>]*)>/', '<svg$1 id="' . $uniqueId . '" preserveAspectRatio="xMidYMid meet">', $svg, 1);
        }

        return $svg;
    }
}
