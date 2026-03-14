<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Models\LogDescarga;
use App\Models\TokenDescarga;
use App\Services\AnalisisPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    protected AnalisisPdfService $pdfService;

    public function __construct(AnalisisPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Genera y descarga el PDF de un análisis (uso admin)
     * Reutiliza el PDF existente si ya fue generado.
     */
    public function descargar(int $analisisId)
    {
        $analisis = Analisis::findOrFail($analisisId);

        // Verificar que esté aprobado o enviado
        $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        if (! in_array($analisis->estado, $estadosValidos)) {
            abort(403, 'Solo se pueden generar PDFs de análisis aprobados o enviados. Estado actual: '.$analisis->estado);
        }

        try {
            $resultado = $this->pdfService->obtenerOGenerar($analisis);

            return response()->file($resultado['fullPath'], [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$resultado['nombre'].'"',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generando PDF:', [
                'analisis_id' => $analisisId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error al generar el PDF: '.$e->getMessage());
        }
    }

    /**
     * Ver el PDF en el navegador (uso admin)
     * Reutiliza el PDF existente si ya fue generado.
     */
    public function ver(int $analisisId)
    {
        $analisis = Analisis::findOrFail($analisisId);

        $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        if (! in_array($analisis->estado, $estadosValidos)) {
            return back()->with('error', 'Solo se pueden ver PDFs de análisis aprobados o enviados.');
        }

        try {
            $resultado = $this->pdfService->obtenerOGenerar($analisis);

            return response()->file($resultado['fullPath'], [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$resultado['nombre'].'"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el PDF: '.$e->getMessage());
        }
    }

    /**
     * Guarda la imagen de la gráfica para un análisis
     */
    public function guardarGrafica(Request $request, int $analisisId)
    {
        $request->validate([
            'image' => 'required|string',
            'component_index' => 'required|integer',
        ]);

        $analisis = Analisis::findOrFail($analisisId);

        // Decodificar imagen base64
        $image = $request->input('image');
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        // Guardar archivo con estructura año/mes
        $path = 'charts/'.date('Y/m')."/{$analisisId}_{$request->input('component_index')}.png";
        \Storage::disk('public')->put($path, $imageData);

        return response()->json(['success' => true, 'path' => $path]);
    }

    /**
     * Descarga un PDF usando un código corto (ruta pública, URL corta)
     */
    public function descargarPorCodigoCorto(string $codigo)
    {
        // Buscar token válido por código corto
        $tokenDescarga = TokenDescarga::buscarPorCodigoCorto($codigo);

        if (! $tokenDescarga) {
            abort(404, 'El enlace de descarga ha expirado o no es válido.');
        }

        // Cargar relaciones
        $tokenDescarga->load('pdf.analisis.muestra');

        $pdf = $tokenDescarga->pdf;

        if (! $pdf) {
            abort(404, 'El PDF no fue encontrado.');
        }

        // Verificar que el archivo existe, si no, regenerarlo (solo el archivo, sin crear registros nuevos)
        if (! Storage::disk('public')->exists($pdf->ruta_archivo)) {
            try {
                $analisis = $pdf->analisis;
                if ($analisis) {
                    $qrUrl = $tokenDescarga->getUrlDescarga();
                    $this->pdfService->renderizarPdf($analisis, $pdf->ruta_archivo, $qrUrl);
                } else {
                    abort(404, 'El archivo PDF no existe y no se puede regenerar.');
                }
            } catch (\Exception $e) {
                abort(404, 'El archivo PDF no existe: '.$e->getMessage());
            }
        }

        // Verificar límite de descargas (máximo 10)
        $totalDescargas = $tokenDescarga->logsDescarga()->count();
        if ($totalDescargas >= 10) {
            abort(403, 'Se ha excedido el límite de descargas permitidas para este enlace.');
        }

        // Registrar log de descarga
        LogDescarga::create([
            'token_id' => $tokenDescarga->id,
            'ip' => request()->ip(),
            'fecha' => now(),
        ]);

        // Generar nombre de archivo
        $analisis = $pdf->analisis;
        $nombreArchivo = 'Resultado_'.($analisis->muestra->codigo_muestra ?? 'PDF').'_'.$analisis->id.'.pdf';

        // Mostrar el PDF en el navegador (inline)
        $fullPath = Storage::disk('public')->path($pdf->ruta_archivo);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nombreArchivo.'"',
        ]);
    }
}

