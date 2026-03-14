<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Pdf;
use App\Models\PlantillaFormulario;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;

class AnalisisPdfService
{
    /**
     * Genera un PDF para un análisis aprobado
     */
    public function generar(Analisis $analisis): array
    {
        // Validar que el análisis esté aprobado o enviado
        $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        if (! in_array($analisis->estado, $estadosValidos)) {
            throw new \Exception('Solo se pueden generar PDFs de análisis aprobados o enviados.');
        }

        // Cargar relaciones necesarias
        $analisis->load([
            'muestra.especie',
            'muestra.veterinaria',
            'muestra.sucursal',
            'tipoAnalisis.plantillas',
            'bioquimico',
            'aprobador',
            'resultados',
        ]);

        // Primero intentar usar la plantilla específica asignada al análisis
        $plantilla = null;
        if ($analisis->plantilla_formulario_id) {
            $plantilla = PlantillaFormulario::find($analisis->plantilla_formulario_id);
        }

        // Si no hay plantilla asignada, buscar una plantilla activa del tipo de análisis (fallback)
        if (! $plantilla) {
            $plantilla = $analisis->tipoAnalisis
                ->plantillas()
                ->where('activo', true)
                ->first();
        }

        if (! $plantilla) {
            throw new \Exception('No se encontró una plantilla activa para este tipo de análisis.');
        }

        // Preparar datos para la vista
        $datos = $this->prepararDatos($analisis, $plantilla);

        // Generar el PDF
        $pdf = DomPDF::loadView('pdf-v2.analisis', $datos);

        // Configurar PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        // Generar nombre único
        $nombreArchivo = $this->generarNombreArchivo($analisis);

        // Guardar en storage
        $rutaRelativa = 'pdfs/'.date('Y/m').'/'.$nombreArchivo;
        Storage::disk('public')->put($rutaRelativa, $pdf->output());

        // Registrar en base de datos
        $pdfModel = Pdf::create([
            'analisis_id' => $analisis->id,
            'ruta_archivo' => $rutaRelativa,
            'generado_por' => auth()->id(),
            'fecha_generacion' => now(),
        ]);

        return [
            'pdf' => $pdf,
            'modelo' => $pdfModel,
            'ruta' => $rutaRelativa,
            'nombre' => $nombreArchivo,
        ];
    }

    /**
     * Prepara los datos para la vista del PDF
     */
    private function prepararDatos(Analisis $analisis, PlantillaFormulario $plantilla): array
    {
        // Indexar resultados por indice para acceso directo
        $resultadosPorIndice = $analisis->resultados->keyBy('indice');

        // Preparar datos de componentes con resultados
        $componentesConDatos = [];
        foreach ($plantilla->componentes as $index => $componente) {
            $tipo = $componente['tipo'];
            $resultado = $resultadosPorIndice->get($index);

            $valorResultado = $resultado?->valor ?? [];

            // Omitir componentes sin resultados
            if (! $this->componenteTieneResultados($tipo, $valorResultado)) {
                continue;
            }

            // Buscar si hay gráfica guardada para este componente (nueva estructura año/mes)
            $chartPattern = storage_path("app/public/charts/*/*/{$analisis->id}_{$index}.png");
            $chartFiles = glob($chartPattern);
            $chartPath = $chartFiles[0] ?? null;

            // Fallback: buscar en la estructura antigua (plana)
            if (! $chartPath) {
                $oldPath = storage_path("app/public/charts/{$analisis->id}_{$index}.png");
                if (file_exists($oldPath)) {
                    $chartPath = $oldPath;
                }
            }

            $chartBase64 = null;
            if ($chartPath && file_exists($chartPath)) {
                $chartBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($chartPath));
            }

            $componentesConDatos[$index] = [
                'componente' => $componente,
                'resultado' => $resultado?->valor ?? [],
                'tipo' => $tipo,
                'chartImage' => $chartBase64,
            ];
        }

        // ===== FONDO DE HOJA (fondo-pdf.png) =====
        $fondoPdfPath = public_path('images/fondo-pdf.png');
        $fondoHojaBase64 = null;
        if (file_exists($fondoPdfPath)) {
            $fondoHojaBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($fondoPdfPath));
        } else {
            // Fallback al fondo original
            $fondoHojaPath = public_path('images/FONDO-HOJA.png');
            if (file_exists($fondoHojaPath)) {
                $fondoHojaBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($fondoHojaPath));
            }
        }

        // ===== LOGO =====
        $logoPath = public_path('images/LOGO.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath));
        }

        // ===== FIRMA =====
        $firmaPath = public_path('images/firma-sin_fondo.png');
        $firmaBase64 = null;
        if (file_exists($firmaPath)) {
            $firmaBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($firmaPath));
        }

        // ===== CÓDIGO DE BARRAS (picqer) =====
        $codigoMuestra = $analisis->muestra->codigo_muestra ?? '';
        $barcodeBase64 = null;
        if ($codigoMuestra) {
            try {
                $generator = new BarcodeGeneratorPNG;
                $barcodeData = $generator->getBarcode($codigoMuestra, $generator::TYPE_CODE_128, 2, 50);
                $barcodeBase64 = 'data:image/png;base64,'.base64_encode($barcodeData);
            } catch (\Exception $e) {
                // Si falla la generación del barcode, continuar sin él
            }
        }

        // ===== CÓDIGO QR (chillerlan) =====
        $qrBase64 = null;
        if ($codigoMuestra) {
            try {
                $qrUrl = url('/resultados/'.$codigoMuestra);
                $options = new QROptions([
                    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                    'scale' => 5,
                    'imageBase64' => false,
                    'imageTransparent' => true,
                ]);
                $qrData = (new QRCode($options))->render($qrUrl);
                $qrBase64 = 'data:image/png;base64,'.base64_encode($qrData);
            } catch (\Exception $e) {
                // Si falla la generación del QR, continuar sin él
            }
        }

        return [
            'analisis' => $analisis,
            'muestra' => $analisis->muestra,
            'plantilla' => $plantilla,
            'componentesConDatos' => $componentesConDatos,
            'fondoHojaBase64' => $fondoHojaBase64,
            'logoBase64' => $logoBase64,
            'firmaBase64' => $firmaBase64,
            'barcodeBase64' => $barcodeBase64,
            'codigoMuestra' => $codigoMuestra,
            'qrBase64' => $qrBase64,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Genera nombre de archivo único para el PDF
     */
    private function generarNombreArchivo(Analisis $analisis): string
    {
        $paciente = preg_replace('/[^A-Za-z0-9]/', '_', $analisis->muestra->paciente_nombre ?? 'SinNombre');
        $tipoAnalisis = preg_replace('/[^A-Za-z0-9]/', '_', $analisis->tipoAnalisis->nombre ?? 'Analisis');
        $fecha = now()->format('Ymd_His');

        return strtoupper("{$paciente}_{$tipoAnalisis}_{$fecha}.pdf");
    }

    /**
     * Verifica si un componente tiene resultados ingresados
     */
    private function componenteTieneResultados(string $tipo, mixed $valor): bool
    {
        if (empty($valor)) {
            return false;
        }

        if (! is_array($valor)) {
            return true;
        }

        return match ($tipo) {
            'tabla-hematologica' => ! empty($valor['parametros'])
                || ! empty($valor['diferenciales'])
                || ! empty($valor['indices']),

            'campo-texto', 'texto-libre' => ! empty($valor['valor'])
                || ! empty($valor['contenido']),

            'campo-imagenes' => collect($valor)->contains(fn ($img) => ! empty($img)),

            default => count($valor) > 0,
        };
    }

    /**
     * Descarga directamente el PDF sin guardar
     */
    public function descargarDirecto(Analisis $analisis)
    {
        $resultado = $this->generar($analisis);

        return $resultado['pdf']->stream($resultado['nombre']);
    }
}
