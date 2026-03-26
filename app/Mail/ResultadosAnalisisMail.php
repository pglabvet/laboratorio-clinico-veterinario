<?php

namespace App\Mail;

use App\Models\Analisis;
use App\Models\Muestra;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ResultadosAnalisisMail extends Mailable
{
    use SerializesModels;

    public Muestra $muestra;
    public array $analisisIds;
    public bool $adjuntarPdfs;
    public string $formato;

    /**
     * Create a new message instance.
     *
     * @param Muestra $muestra
     * @param array $analisisIds IDs de análisis a incluir
     * @param bool $adjuntarPdfs Si true, adjunta los PDFs al email
     * @param string $formato 'completo' o 'limpio'
     */
    public function __construct(Muestra $muestra, array $analisisIds, bool $adjuntarPdfs = true, string $formato = 'completo')
    {
        $this->muestra = $muestra;
        $this->analisisIds = $analisisIds;
        $this->adjuntarPdfs = $adjuntarPdfs;
        $this->formato = $formato;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $cantidadAnalisis = count($this->analisisIds);
        $sufijo = $cantidadAnalisis > 1 ? "({$cantidadAnalisis} análisis)" : '';

        return new Envelope(
            subject: "Resultados de Análisis - {$this->muestra->codigo_muestra} | {$this->muestra->paciente_nombre} {$sufijo}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $analisisList = Analisis::with(['tipoAnalisis', 'bioquimico', 'aprobador'])
            ->whereIn('id', $this->analisisIds)
            ->get();

        return new Content(
            view: 'emails.resultados-analisis',
            with: [
                'muestra' => $this->muestra,
                'analisisList' => $analisisList,
                'veterinaria' => $this->muestra->veterinaria,
                'sucursal' => $this->muestra->sucursal,
                'especie' => $this->muestra->especie,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->adjuntarPdfs) {
            return [];
        }

        $attachments = [];

        $analisisList = Analisis::with(['tipoAnalisis', 'pdfs'])
            ->whereIn('id', $this->analisisIds)
            ->get();

        foreach ($analisisList as $analisis) {
            $pdfQuery = $analisis->pdfs();

            if ($this->formato === 'limpio') {
                $pdfQuery->where('ruta_archivo', 'like', '%_L.PDF');
            } else {
                $pdfQuery->where('ruta_archivo', 'not like', '%_L.PDF');
            }

            $pdf = $pdfQuery->latest()->first();

            if ($pdf && Storage::disk('public')->exists($pdf->ruta_archivo)) {
                $nombreAnalisis = $analisis->tipoAnalisis->nombre ?? 'Analisis';
                // Reemplazar acentos y caracteres especiales por letras normales
                $nombreArchivo = $this->limpiarNombreArchivo($nombreAnalisis);

                $attachments[] = Attachment::fromStorageDisk('public', $pdf->ruta_archivo)
                    ->as("{$this->muestra->codigo_muestra}_{$nombreArchivo}.pdf")
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }

    /**
     * Limpia el nombre de archivo: reemplaza acentos por letras normales y elimina caracteres especiales.
     */
    private function limpiarNombreArchivo(string $nombre): string
    {
        $buscar =    ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü'];
        $reemplazar = ['a','e','i','o','u','A','E','I','O','U','n','N','u','U'];
        $nombre = str_replace($buscar, $reemplazar, $nombre);
        $nombre = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $nombre);
        $nombre = preg_replace('/\s+/', '_', $nombre);
        return trim($nombre, '_');
    }
}
