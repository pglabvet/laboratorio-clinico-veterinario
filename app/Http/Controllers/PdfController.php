<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Services\AnalisisPdfService;
use Illuminate\Http\Request;

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
}
