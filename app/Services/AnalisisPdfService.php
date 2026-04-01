<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Pdf;
use App\Models\PlantillaFormulario;
use App\Models\TokenDescarga;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;

class AnalisisPdfService
{
    /**
     * Genera un PDF completo: renderiza, guarda en storage, crea registro en BD y token de descarga.
     * Usar este método solo cuando se necesita un PDF NUEVO (primera vez o regeneración explícita).
     *
     * @return array{pdf: \Barryvdh\DomPDF\PDF, modelo: Pdf, ruta: string, nombre: string, token: TokenDescarga}
     */
    public function generar(Analisis $analisis, ?string $qrUrl = null, string $formato = 'completo'): array
    {
        // Validar que el análisis esté aprobado o enviado
        $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        if (! in_array($analisis->estado, $estadosValidos)) {
            throw new \Exception('Solo se pueden generar PDFs de análisis aprobados o enviados.');
        }

        $esLimpio = $formato === 'limpio';

        // Generar nombre único y ruta
        $nombreArchivo = $this->generarNombreArchivo($analisis, $esLimpio ? '_L' : '');
        $rutaRelativa = 'pdfs/'.date('Y/m').'/'.$nombreArchivo;

        // Crear registro del PDF en la BD
        $pdfModel = Pdf::create([
            'analisis_id' => $analisis->id,
            'ruta_archivo' => $rutaRelativa,
            'generado_por' => auth()->id(),
            'fecha_generacion' => now(),
        ]);

        // Crear token de descarga (necesario para compartir por WhatsApp/Email)
        $tokenDescarga = TokenDescarga::crearParaPdf($pdfModel->id);

        // Solo usar QR en el PDF para formato completo
        if (! $esLimpio) {
            if (! $qrUrl) {
                $qrUrl = $tokenDescarga->getUrlDescarga();
            }
        }

        // Renderizar y guardar el PDF (limpio no incluye QR)
        $pdf = $this->renderizarPdf($analisis, $rutaRelativa, $esLimpio ? null : $qrUrl, $formato);

        return [
            'pdf' => $pdf,
            'modelo' => $pdfModel,
            'ruta' => $rutaRelativa,
            'nombre' => $nombreArchivo,
            'token' => $tokenDescarga,
        ];
    }

    /**
     * Renderiza el PDF y lo guarda en storage. NO crea registros en BD ni tokens.
     * Usar este método para regenerar el archivo de un PDF que ya existe en la BD.
     *
     * @return \Barryvdh\DomPDF\PDF El objeto PDF renderizado
     */
    public function renderizarPdf(Analisis $analisis, string $rutaRelativa, ?string $qrUrl = null, string $formato = 'completo'): \Barryvdh\DomPDF\PDF
    {
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

        // Resolver plantilla
        $plantilla = null;
        if ($analisis->plantilla_formulario_id) {
            $plantilla = PlantillaFormulario::find($analisis->plantilla_formulario_id);
        }
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
        $datos = $this->prepararDatos($analisis, $plantilla, $qrUrl, $formato);

        // Generar el PDF
        $pdf = DomPDF::loadView('pdf-v2.analisis', $datos);
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        // Guardar en storage
        Storage::disk('public')->put($rutaRelativa, $pdf->output());

        return $pdf;
    }

    /**
     * Obtiene un PDF existente o genera uno nuevo si no existe.
     * Uso principal: cuando el admin da click en "Ver PDF" o "Descargar PDF".
     *
     * @return array{modelo: Pdf, ruta: string, nombre: string, fullPath: string}
     */
    public function obtenerOGenerar(Analisis $analisis, string $formato = 'completo'): array
    {
        $esLimpio = $formato === 'limpio';

        // Buscar PDF existente según formato
        $pdfModel = $analisis->pdfs()
            ->where('ruta_archivo', $esLimpio ? 'like' : 'not like', '%_L.PDF')
            ->latest()
            ->first();

        if ($pdfModel && Storage::disk('public')->exists($pdfModel->ruta_archivo)) {
            // PDF existe en BD y en disco: reutilizar sin generar nada nuevo
            return [
                'ruta' => $pdfModel->ruta_archivo,
                'modelo' => $pdfModel,
                'nombre' => basename($pdfModel->ruta_archivo),
                'fullPath' => Storage::disk('public')->path($pdfModel->ruta_archivo),
            ];
        }

        if ($pdfModel && ! Storage::disk('public')->exists($pdfModel->ruta_archivo)) {
            // Registro existe pero archivo no: regenerar solo el archivo
            $token = $pdfModel->tokenVigente();
            if (! $token) {
                $token = TokenDescarga::crearParaPdf($pdfModel->id);
            }

            // Solo pasar QR URL para formato completo
            $qrUrl = $esLimpio ? null : $token->getUrlDescarga();

            $this->renderizarPdf($analisis, $pdfModel->ruta_archivo, $qrUrl, $formato);

            return [
                'ruta' => $pdfModel->ruta_archivo,
                'modelo' => $pdfModel,
                'nombre' => basename($pdfModel->ruta_archivo),
                'fullPath' => Storage::disk('public')->path($pdfModel->ruta_archivo),
            ];
        }

        // No existe PDF: generar uno nuevo
        $resultado = $this->generar($analisis, null, $formato);

        return [
            'ruta' => $resultado['ruta'],
            'modelo' => $resultado['modelo'],
            'nombre' => $resultado['nombre'],
            'fullPath' => Storage::disk('public')->path($resultado['ruta']),
        ];
    }

    /**
     * Prepara los datos para la vista del PDF
     */
    private function prepararDatos(Analisis $analisis, PlantillaFormulario $plantilla, ?string $qrUrl = null, string $formato = 'completo'): array
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

        $esLimpio = $formato === 'limpio';

        // ===== FONDO DE HOJA (fondo-pdf.png) =====
        $fondoHojaBase64 = null;
        if (! $esLimpio) {
            $fondoPdfPath = public_path('images/fondo-pdf.png');
            if (file_exists($fondoPdfPath)) {
                $fondoHojaBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($fondoPdfPath));
            } else {
                $fondoHojaPath = public_path('images/FONDO-HOJA.png');
                if (file_exists($fondoHojaPath)) {
                    $fondoHojaBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($fondoHojaPath));
                }
            }
        }

        // ===== LOGO =====
        $logoBase64 = null;
        if (! $esLimpio) {
            $logoPath = public_path('images/LOGO.png');
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath));
            }
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
        if (! $esLimpio && $codigoMuestra) {
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
        if (! $esLimpio && $qrUrl) {
            try {
                $options = new QROptions([
                    'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                    'scale' => 5,
                    'outputBase64' => true,
                    'imageTransparent' => true,
                ]);
                $qrBase64 = (new QRCode($options))->render($qrUrl);
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
            'formato' => $formato,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Genera nombre de archivo único para el PDF
     */
    public function generarNombreArchivo(Analisis $analisis, string $sufijo = ''): string
    {
        $paciente = preg_replace('/[^A-Za-z0-9]/', '_', $analisis->muestra->paciente_nombre ?? 'SinNombre');
        $tipoAnalisis = preg_replace('/[^A-Za-z0-9]/', '_', $analisis->tipoAnalisis->nombre ?? 'Analisis');
        $fecha = now()->format('Ymd_His');

        return strtoupper("{$paciente}_{$tipoAnalisis}_{$fecha}{$sufijo}.pdf");
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

            'citologia' => ! empty($valor['tumor'])
                || ! empty($valor['secciones']),

            'campo-imagenes' => collect($valor)->contains(fn ($img) => ! empty($img)),

            default => count($valor) > 0,
        };
    }
}
