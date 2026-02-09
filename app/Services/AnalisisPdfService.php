<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Pdf;
use App\Models\PlantillaFormulario;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Illuminate\Support\Facades\Storage;

class AnalisisPdfService
{
    /**
     * Genera un PDF para un análisis aprobado
     */
    public function generar(Analisis $analisis): array
    {
        // Validar que el análisis esté aprobado
        if ($analisis->estado !== Analisis::ESTADO_APROBADO) {
            throw new \Exception('Solo se pueden generar PDFs de análisis aprobados.');
        }

        // Cargar relaciones necesarias
        $analisis->load([
            'muestra.especie',
            'muestra.veterinaria',
            'muestra.sucursal',
            'tipoAnalisis.plantillas',
            'bioquimico',
            'aprobador',
            'resultados'
        ]);

        // Primero intentar usar la plantilla específica asignada al análisis
        $plantilla = null;
        if ($analisis->plantilla_formulario_id) {
            $plantilla = PlantillaFormulario::find($analisis->plantilla_formulario_id);
        }
        
        // Si no hay plantilla asignada, buscar una plantilla activa del tipo de análisis (fallback)
        if (!$plantilla) {
            $plantilla = $analisis->tipoAnalisis
                ->plantillas()
                ->where('activo', true)
                ->first();
        }

        if (!$plantilla) {
            throw new \Exception('No se encontró una plantilla activa para este tipo de análisis.');
        }

        // Preparar datos para la vista
        $datos = $this->prepararDatos($analisis, $plantilla);

        // Generar el PDF
        $pdf = DomPDF::loadView('pdf.analisis', $datos);
        
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
        $rutaRelativa = 'pdfs/' . date('Y/m') . '/' . $nombreArchivo;
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
        // Agrupar resultados por tipo para fácil acceso
        $resultadosPorTipo = $analisis->resultados->groupBy('tipo');

        // Preparar datos de componentes con resultados
        $componentesConDatos = [];
        foreach ($plantilla->componentes as $index => $componente) {
            $tipo = $componente['tipo'];
            $resultado = $resultadosPorTipo->get($tipo)?->first();
            
            // Buscar si hay gráfica guardada para este componente
            $chartPath = storage_path("app/public/charts/{$analisis->id}_{$index}.png");
            $chartBase64 = null;
            
            if (file_exists($chartPath)) {
                $chartBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($chartPath));
            }

            $componentesConDatos[$index] = [
                'componente' => $componente,
                'resultado' => $resultado?->valor ?? [],
                'tipo' => $tipo,
                'chartImage' => $chartBase64,
            ];
        }

        // Ruta del fondo de hoja
        $fondoHojaPath = public_path('images/FONDO-HOJA.png');
        $fondoHojaBase64 = null;
        if (file_exists($fondoHojaPath)) {
            $fondoHojaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fondoHojaPath));
        }

        // Ruta de la firma (sin fondo para transparencia)
        $firmaPath = public_path('images/firma-sin_fondo.png');
        $firmaBase64 = null;
        if (file_exists($firmaPath)) {
            $firmaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($firmaPath));
        }

        return [
            'analisis' => $analisis,
            'muestra' => $analisis->muestra,
            'plantilla' => $plantilla,
            'componentesConDatos' => $componentesConDatos,
            'fondoHojaBase64' => $fondoHojaBase64,
            'firmaBase64' => $firmaBase64,
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
     * Descarga directamente el PDF sin guardar
     */
    public function descargarDirecto(Analisis $analisis)
    {
        $resultado = $this->generar($analisis);
        return $resultado['pdf']->download($resultado['nombre']);
    }
}
