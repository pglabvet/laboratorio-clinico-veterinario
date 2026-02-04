<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Models\TokenDescarga;
use App\Models\LogDescarga;
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
     * Genera y descarga el PDF de un análisis
     */
    public function descargar(int $analisisId)
    {
        $analisis = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
            'tipoAnalisis.plantillas',
            'bioquimico',
            'aprobador',
            'resultados'
        ])->findOrFail($analisisId);

        // Verificar que esté aprobado
        if ($analisis->estado !== Analisis::ESTADO_APROBADO) {
            abort(403, 'Solo se pueden generar PDFs de análisis aprobados. Estado actual: ' . $analisis->estado);
        }

        try {
            return $this->pdfService->descargarDirecto($analisis);
        } catch (\Exception $e) {
            \Log::error('Error generando PDF:', [
                'analisis_id' => $analisisId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Ver el PDF en el navegador (sin descargar)
     */
    public function ver(int $analisisId)
    {
        $analisis = Analisis::with([
            'muestra.especie',
            'muestra.veterinaria',
            'tipoAnalisis',
            'bioquimico',
            'aprobador',
            'resultados'
        ])->findOrFail($analisisId);

        if ($analisis->estado !== Analisis::ESTADO_APROBADO) {
            return back()->with('error', 'Solo se pueden ver PDFs de análisis aprobados.');
        }

        try {
            $resultado = $this->pdfService->generar($analisis);
            return $resultado['pdf']->stream($resultado['nombre']);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
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

        // Guardar archivo
        $path = "charts/{$analisisId}_{$request->input('component_index')}.png";
        \Storage::disk('public')->put($path, $imageData);

        return response()->json(['success' => true, 'path' => $path]);
    }

    /**
     * Descarga un PDF usando un token de descarga (ruta pública)
     */
    public function descargarPorToken(string $token)
    {
        // Buscar token válido
        $tokenDescarga = TokenDescarga::buscarValido($token);

        if (!$tokenDescarga) {
            abort(404, 'El enlace de descarga ha expirado o no es válido.');
        }

        // Cargar relaciones
        $tokenDescarga->load('pdf.analisis.muestra');
        
        $pdf = $tokenDescarga->pdf;
        
        if (!$pdf) {
            abort(404, 'El PDF no fue encontrado.');
        }

        // Verificar que el archivo existe, si no, regenerarlo
        if (!Storage::disk('public')->exists($pdf->ruta_archivo)) {
            // Intentar regenerar el PDF
            try {
                $analisis = $pdf->analisis;
                if ($analisis) {
                    $pdfService = app(AnalisisPdfService::class);
                    $resultado = $pdfService->generar($analisis);
                    // Actualizar la ruta del PDF existente
                    $pdf->update(['ruta_archivo' => $resultado['ruta']]);
                } else {
                    abort(404, 'El archivo PDF no existe y no se puede regenerar.');
                }
            } catch (\Exception $e) {
                abort(404, 'El archivo PDF no existe: ' . $e->getMessage());
            }
        }

        // Registrar log de descarga
        LogDescarga::create([
            'token_id' => $tokenDescarga->id,
            'ip' => request()->ip(),
            'fecha' => now(),
        ]);

        // Generar nombre de archivo
        $analisis = $pdf->analisis;
        $nombreArchivo = 'Resultado_' . ($analisis->muestra->codigo_muestra ?? 'PDF') . '_' . $analisis->id . '.pdf';

        // Descargar el archivo
        return Storage::disk('public')->download($pdf->ruta_archivo, $nombreArchivo);
    }
}
