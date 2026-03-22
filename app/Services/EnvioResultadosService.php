<?php

namespace App\Services;

use App\Mail\ResultadosAnalisisMail;
use App\Models\Analisis;
use App\Models\Muestra;
use App\Models\TokenDescarga;
use Illuminate\Support\Facades\Mail;

class EnvioResultadosService
{
    private const DIAS_EXPIRACION_ENLACE = 14;

    protected AnalisisPdfService $pdfService;

    public function __construct(AnalisisPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Preparar envío de un análisis individual por WhatsApp.
     * Genera PDF, token de descarga y retorna la URL de WhatsApp.
     *
     * @return array{url: string, mensaje: string}
     *
     * @throws \Exception
     */
    public function prepararWhatsApp(int $analisisId, ?string $telefonoSeleccionado = null, string $formato = 'completo'): array
    {
        $analisis = Analisis::with([
            'tipoAnalisis',
            'muestra.veterinaria.telefonos',
            'muestra.sucursal',
            'muestra.especie',
            'pdfs',
        ])->findOrFail($analisisId);

        $this->validarEstadoEnvio($analisis);

        $telefono = $this->resolverTelefonoWhatsapp($analisis->muestra->veterinaria, $telefonoSeleccionado);

        if ($formato === 'limpio') {
            // Para formato limpio: reutilizar o generar PDF sin branding
            $pdfModel = $analisis->pdfs()
                ->where('ruta_archivo', 'like', '%_L.PDF')
                ->latest()
                ->first();

            if ($pdfModel && \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfModel->ruta_archivo)) {
                // Reutilizar PDF limpio existente
                $tokenDescarga = $pdfModel->tokenVigente();
                if (! $tokenDescarga) {
                    $tokenDescarga = TokenDescarga::crearParaPdf($pdfModel->id, self::DIAS_EXPIRACION_ENLACE);
                }
            } else {
                // Generar nuevo PDF limpio
                $nombreArchivo = $this->pdfService->generarNombreArchivo($analisis, '_L');
                $rutaRelativa = 'pdfs/'.date('Y/m').'/'.$nombreArchivo;

                $pdfModel = \App\Models\Pdf::create([
                    'analisis_id' => $analisis->id,
                    'ruta_archivo' => $rutaRelativa,
                    'generado_por' => auth()->id(),
                    'fecha_generacion' => now(),
                ]);

                $tokenDescarga = TokenDescarga::crearParaPdf($pdfModel->id, self::DIAS_EXPIRACION_ENLACE);
                $this->pdfService->renderizarPdf($analisis, $rutaRelativa, null, 'limpio');
            }

            $urlDescarga = $tokenDescarga->getUrlDescarga();
        } else {
            // Formato completo: reutilizar o generar PDF con branding
            $resultado = $this->pdfService->obtenerOGenerar($analisis);
            $pdf = $resultado['modelo'];

            $tokenDescarga = $pdf->tokenVigente();
            if (! $tokenDescarga) {
                $tokenDescarga = TokenDescarga::crearParaPdf($pdf->id, self::DIAS_EXPIRACION_ENLACE);
                $this->pdfService->renderizarPdf($analisis, $pdf->ruta_archivo, $tokenDescarga->getUrlDescarga());
            }
            $urlDescarga = $tokenDescarga->getUrlDescarga();
        }

        // Construir mensaje y URL
        $mensaje = $this->construirMensajeWhatsApp($analisis, $urlDescarga);
        $telefonoFormateado = $this->formatearTelefonoWhatsApp($telefono);
        $urlWhatsApp = 'https://wa.me/'.$telefonoFormateado.'?text='.rawurlencode($mensaje);

        // Marcar como enviado
        $analisis->update(['estado' => Analisis::ESTADO_ENVIADO]);

        return [
            'url' => $urlWhatsApp,
            'mensaje' => 'Enlace de WhatsApp generado. El análisis ha sido marcado como enviado.',
        ];
    }

    /**
     * Preparar envío de todos los análisis de una muestra por WhatsApp.
     *
     * @return array{url: string, mensaje: string}
     *
     * @throws \Exception
     */
    public function prepararWhatsAppMasivo(Muestra $muestra, ?string $telefonoSeleccionado = null, string $formato = 'completo'): array
    {
        $muestra->load([
            'veterinaria.telefonos',
            'especie',
            'sucursal',
            'analisis.tipoAnalisis',
            'analisis.pdfs',
        ]);

        $analisisCollection = $muestra->analisis;

        $this->validarColeccionParaEnvio($analisisCollection);

        $telefono = $this->resolverTelefonoWhatsapp($muestra->veterinaria, $telefonoSeleccionado);

        $linksDescarga = [];

        foreach ($analisisCollection as $analisis) {
            if ($formato === 'limpio') {
                $pdfModel = $analisis->pdfs()
                    ->where('ruta_archivo', 'like', '%_limpio.pdf')
                    ->latest()
                    ->first();

                if ($pdfModel && \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfModel->ruta_archivo)) {
                    $tokenDescarga = $pdfModel->tokenVigente();
                    if (! $tokenDescarga) {
                        $tokenDescarga = TokenDescarga::crearParaPdf($pdfModel->id, self::DIAS_EXPIRACION_ENLACE);
                    }
                } else {
                    $nombreArchivo = $this->pdfService->generarNombreArchivo($analisis, '_L');
                    $rutaRelativa = 'pdfs/'.date('Y/m').'/'.$nombreArchivo;

                    $pdfModel = \App\Models\Pdf::create([
                        'analisis_id' => $analisis->id,
                        'ruta_archivo' => $rutaRelativa,
                        'generado_por' => auth()->id(),
                        'fecha_generacion' => now(),
                    ]);

                    $tokenDescarga = TokenDescarga::crearParaPdf($pdfModel->id, self::DIAS_EXPIRACION_ENLACE);
                    $this->pdfService->renderizarPdf($analisis, $rutaRelativa, null, 'limpio');
                }

                $urlToken = $tokenDescarga->getUrlDescarga();
            } else {
                $resultado = $this->pdfService->obtenerOGenerar($analisis);
                $pdf = $resultado['modelo'];

                $tokenDescarga = $pdf->tokenVigente();
                if (! $tokenDescarga) {
                    $tokenDescarga = TokenDescarga::crearParaPdf($pdf->id, self::DIAS_EXPIRACION_ENLACE);
                    $this->pdfService->renderizarPdf($analisis, $pdf->ruta_archivo, $tokenDescarga->getUrlDescarga());
                }
                $urlToken = $tokenDescarga->getUrlDescarga();
            }

            $linksDescarga[] = [
                'nombre' => $analisis->tipoAnalisis->nombre ?? 'Análisis',
                'url' => $urlToken,
            ];

            $analisis->update(['estado' => Analisis::ESTADO_ENVIADO]);
        }

        $mensaje = $this->construirMensajeWhatsAppMultiple($muestra, $linksDescarga);
        $telefonoFormateado = $this->formatearTelefonoWhatsApp($telefono);
        $urlWhatsApp = 'https://wa.me/'.$telefonoFormateado.'?text='.rawurlencode($mensaje);

        return [
            'url' => $urlWhatsApp,
            'mensaje' => 'Enlace de WhatsApp generado. Todos los análisis han sido marcados como enviados.',
        ];
    }

    /**
     * Enviar resultado de un análisis individual por Email.
     *
     * @return string Mensaje de éxito
     *
     * @throws \Exception
     */
    public function enviarEmail(int $analisisId, string $formato = 'completo'): string
    {
        $analisis = Analisis::with([
            'tipoAnalisis',
            'muestra.veterinaria',
            'muestra.sucursal',
            'muestra.especie',
            'pdfs',
        ])->findOrFail($analisisId);

        $this->validarEstadoEnvio($analisis);

        $email = $analisis->muestra->veterinaria->email ?? null;
        if (! $email) {
            throw new \Exception('La veterinaria no tiene un correo electrónico registrado.');
        }

        if ($formato === 'limpio') {
            $nombreArchivo = $this->pdfService->generarNombreArchivo($analisis, '_L');
            $rutaRelativa = 'pdfs/'.date('Y/m').'/'.$nombreArchivo;
            $this->pdfService->renderizarPdf($analisis, $rutaRelativa, null, 'limpio');
        } else {
            $this->pdfService->obtenerOGenerar($analisis);
        }

        // Refrescar la relación de PDFs
        $analisis->load('pdfs');

        $muestra = $analisis->muestra->load(['veterinaria', 'sucursal', 'especie']);

        Mail::to($email)->sendNow(
            new ResultadosAnalisisMail($muestra, [$analisis->id], true)
        );

        $analisis->update(['estado' => Analisis::ESTADO_ENVIADO]);

        return 'Resultados enviados por correo electrónico a '.$email;
    }

    /**
     * Enviar todos los análisis de una muestra por Email.
     *
     * @return string Mensaje de éxito
     *
     * @throws \Exception
     */
    public function enviarEmailMasivo(Muestra $muestra, string $formato = 'completo'): string
    {
        $muestra->load([
            'veterinaria',
            'especie',
            'sucursal',
            'analisis.tipoAnalisis',
            'analisis.pdfs',
        ]);

        $analisisCollection = $muestra->analisis;

        $this->validarColeccionParaEnvio($analisisCollection);

        $email = $muestra->veterinaria->email ?? null;
        if (! $email) {
            throw new \Exception('La veterinaria no tiene un correo electrónico registrado.');
        }

        $analisisIds = [];

        foreach ($analisisCollection as $analisis) {
            if ($formato === 'limpio') {
                $nombreArchivo = $this->pdfService->generarNombreArchivo($analisis, '_L');
                $rutaRelativa = 'pdfs/'.date('Y/m').'/'.$nombreArchivo;
                $this->pdfService->renderizarPdf($analisis, $rutaRelativa, null, 'limpio');
            } else {
                $this->pdfService->obtenerOGenerar($analisis);
            }

            $analisisIds[] = $analisis->id;
            $analisis->update(['estado' => Analisis::ESTADO_ENVIADO]);
        }

        Mail::to($email)->sendNow(
            new ResultadosAnalisisMail($muestra, $analisisIds, true)
        );

        return 'Todos los resultados fueron enviados por correo electrónico a '.$email;
    }

    // ─── Métodos privados auxiliares ───────────────────────────────────

    /**
     * Validar que un análisis esté en estado válido para envío.
     */
    private function validarEstadoEnvio(Analisis $analisis): void
    {
        $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        if (! in_array($analisis->estado, $estadosValidos)) {
            throw new \Exception(
                'Solo se pueden enviar análisis aprobados o ya enviados. Estado actual: '.$analisis->estado
            );
        }
    }

    /**
     * Validar una colección de análisis para envío masivo.
     */
    private function validarColeccionParaEnvio($analisisCollection): void
    {
        if ($analisisCollection->isEmpty()) {
            throw new \Exception('No hay análisis para enviar.');
        }

        $estadosValidos = [Analisis::ESTADO_APROBADO, Analisis::ESTADO_ENVIADO];
        $noValidos = $analisisCollection->filter(function ($analisis) use ($estadosValidos) {
            return ! in_array($analisis->estado, $estadosValidos);
        });

        if ($noValidos->count() > 0) {
            $nombresNoValidos = $noValidos->map(
                fn ($a) => ($a->tipoAnalisis->nombre ?? 'Sin nombre').' ('.$a->estado.')'
            )->implode(', ');
            throw new \Exception(
                'Todos los análisis deben estar aprobados o enviados. Pendientes: '.$nombresNoValidos
            );
        }
    }

    /**
     * Formatear número de teléfono para WhatsApp.
     * Limpia caracteres no numéricos y añade código de país de Bolivia (591).
     */
    private function formatearTelefonoWhatsApp(string $telefono): string
    {
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

        return '591'.ltrim($telefonoLimpio, '0');
    }

    /**
     * Resuelve el telefono elegido para WhatsApp priorizando el seleccionado,
     * luego el principal y finalmente el primero disponible.
     */
    private function resolverTelefonoWhatsapp($veterinaria, ?string $telefonoSeleccionado = null): string
    {
        $telefonos = collect($veterinaria?->telefonos ?? []);

        if ($telefonoSeleccionado) {
            $telefonoExiste = $telefonos->firstWhere('telefono', $telefonoSeleccionado);

            if ($telefonoExiste) {
                return $telefonoExiste->telefono;
            }
        }

        $telefonoPrincipal = $telefonos->firstWhere('es_principal', true)?->telefono;

        if ($telefonoPrincipal) {
            return $telefonoPrincipal;
        }

        $telefonoAlternativo = $telefonos->first()?->telefono;

        if ($telefonoAlternativo) {
            return $telefonoAlternativo;
        }

        throw new \Exception('La veterinaria no tiene números de teléfono registrados.');
    }

    /**
     * Construir mensaje de WhatsApp para un análisis individual.
     */
    private function construirMensajeWhatsApp(Analisis $analisis, string $urlDescarga): string
    {
        $muestra = $analisis->muestra;
        $sucursal = $muestra->sucursal->nombre ?? 'N/A';

        return "*PG LABVET LABORATORIO CLINICO VETERINARIO*\n".
            "_{$sucursal}_\n".
            "------------------------------------\n\n".
            "*Paciente:* {$muestra->paciente_nombre}\n".
            "*Propietario:* {$muestra->propietario_nombre}\n".
            '*Analisis:* '.($analisis->tipoAnalisis->nombre ?? 'N/A')."\n".
            "*Codigo:* {$muestra->codigo_muestra}\n\n".
            "------------------------------------\n".
            "*Descarga tu resultado aqui:*\n".
            $urlDescarga."\n\n".
            "_Enlace valido por 14 dias_\n\n".
            '_Gracias por confiar en nosotros!_';
    }

    /**
     * Construir mensaje de WhatsApp para múltiples análisis.
     */
    private function construirMensajeWhatsAppMultiple(Muestra $muestra, array $linksDescarga): string
    {
        $sucursal = $muestra->sucursal->nombre ?? 'N/A';

        $mensaje = "*PGLABVET LABORATORIO CLINICO VETERINARIO*\n".
            "_{$sucursal}_\n".
            "------------------------------------\n\n".
            "*Paciente:* {$muestra->paciente_nombre}\n".
            "*Propietario:* {$muestra->propietario_nombre}\n".
            "*Codigo:* {$muestra->codigo_muestra}\n\n".
            "------------------------------------\n".
            "*Resultados disponibles:*\n\n";

        foreach ($linksDescarga as $index => $link) {
            $numero = $index + 1;
            $mensaje .= "{$numero}. *{$link['nombre']}*\n{$link['url']}\n\n";
        }

        $mensaje .= "_Enlaces validos por 14 dias_\n\n".
            '_Gracias por confiar en nosotros!_';

        return $mensaje;
    }
}
