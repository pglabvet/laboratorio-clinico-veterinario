{{-- PDF V2 - Vista principal del reporte de análisis --}}
{{-- Diseño moderno inspirado en PG LABVET --}}

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $analisis->tipoAnalisis->nombre }} - {{ $muestra->paciente_nombre }}</title>

  <style>
    /* ===========================
       ESTILOS PDF V2 - DISEÑO MODERNO
       =========================== */

    @page {
      margin: 115pt 55pt 130pt 55pt;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 10px;
      line-height: 1.5;
      color: #1a1a1a;
    }

    /* === FONDO FULL-BLEED === */
    .page-background {
      position: fixed;
      top: -115pt;
      left: -55pt;
      width: 612pt;
      height: 792pt;
      z-index: 0;
    }

    .page-background img {
      width: 612pt;
      height: 792pt;
    }

    /* === LOGO FIJO (todas las páginas) === */
    .header-logo {
      position: fixed;
      top: -85pt;
      left: 0pt;
      z-index: 3;
    }

    .header-logo img {
      height: 50pt;
      width: auto;
    }

    /* === BARCODE (solo primera página, via CSS) === */
    .header-barcode {
      position: fixed;
      top: -85pt;
      right: 0pt;
      text-align: right;
      z-index: 3;
    }

    .barcode-text {
      font-size: 12px;
      color: #1e3a5f;
      text-transform: uppercase;
      font-weight: bold;
      letter-spacing: 0.5px;
      margin-bottom: 2pt;
    }

    .barcode-img {
      margin-top: 2pt;
    }

    .barcode-img img {
      width: 100%;
      height: 25pt;
    }

    /* === FIRMA FIJA (todas las páginas) === */
    .fixed-signature {
      position: fixed;
      bottom: -55pt;
      left: 0;
      right: 0;
      text-align: center;
      z-index: 3;
    }

    .fixed-signature img {
      max-width: 160px;
      max-height: 80px;
    }

    /* === QR FIJO (todas las páginas) === */
    .fixed-qr {
      position: fixed;
      bottom: -75pt;
      left: -10pt;
      z-index: 3;
    }

    .fixed-qr img {
      width: 70pt;
      height: 70pt;
    }

    .qr-label {
      font-size: 6px;
      color: #1e3a5f;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 2pt;
      text-align: center;
    }

    .qr-sublabel {
      font-size: 5px;
      color: #718096;
      margin-top: 1pt;
      text-align: center;
    }

    /* === BARRA AZUL INFERIOR (todas las páginas) === */
    .footer-bar {
      position: fixed;
      bottom: -130pt;
      left: -55pt;
      width: 612pt;
      height: 42pt;
      background-color: #1e3a5f;
      z-index: 2;
    }

    .footer-bar-content {
      position: fixed;
      bottom: -125pt;
      left: 0;
      right: 0;
      z-index: 3;
      color: white;
      font-size: 8px;
      letter-spacing: 0.2px;
      line-height: 1.4;
    }

    .footer-bar-content table {
      width: 100%;
      border-collapse: collapse;
      margin: 0;
    }

    .footer-bar-content table td {
      padding: 2px 12px;
      border: none !important;
      border-bottom: none !important;
      color: white;
      font-size: 8px;
      vertical-align: middle;
    }

    .footer-bar-content table tr:last-child td {
      border-bottom: none !important;
    }

    /* === CONTENIDO PRINCIPAL === */
    .container {
      position: relative;
      z-index: 1;
      padding: 0;
    }

    /* === TÍTULO DEL ANÁLISIS === */
    .analysis-title {
      color: #1e3a5f;
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 18px;
      padding-bottom: 8px;
    }

    /* === CONTENEDOR DATOS DEL PACIENTE === */
    .patient-card {
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 10px 8px;
      margin-bottom: 18px;
      background-color: #fbfcfd;
    }

    /* === DATOS DEL PACIENTE === */
    .patient-grid {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 0;
    }

    .patient-grid td {
      padding: 4px 6px;
      border: none;
      vertical-align: top;
      width: 25%;
    }

    .patient-label {
      font-size: 7px;
      color: #718096;
      text-transform: uppercase;
      font-weight: bold;
      letter-spacing: 0.5px;
      display: block;
      margin-bottom: 1px;
    }

    .patient-value {
      font-size: 10px;
      color: #1a1a1a;
      font-weight: 600;
    }

    .patient-value-highlight {
      font-size: 10px;
      color: #1e3a5f;
      font-weight: bold;
    }

    /* === SEPARADOR === */
    .separator {
      border: none;
      border-top: 1px solid #cbd5e0;
      margin: 10px 0;
    }

    /* === COMPONENTES === */
    .component {
      margin-bottom: 24px;
      page-break-inside: auto;
    }

    /* Componentes pequeños que no deben cortarse entre páginas */
    .component-no-break {
      margin-bottom: 24px;
      page-break-inside: avoid;
    }

    .component-title {
      font-size: 11px;
      font-weight: bold;
      color: #1e3a5f;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 8px;
      padding-bottom: 4px;
    }

    /* === TABLAS SIN BORDES (estilo datos sueltos) === */
    table {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      background: transparent;
      margin-bottom: 8px;
    }

    table th {
      font-size: 9px;
      font-weight: bold;
      color: #1e3a5f;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 6px 8px;
      border-bottom: none;
      text-align: left;
      background: transparent;
    }

    table td {
      padding: 5px 8px;
      border: none;
      font-size: 9px;
      word-wrap: break-word;
      overflow-wrap: break-word;
      background: transparent;
    }

    table tr:last-child td {
      border-bottom: none;
    }

    /* Color classes para resultados */
    .resultado-normal { color: #1a1a1a; }
    .resultado-alerta { color: #2563eb; font-weight: bold; }
    .resultado-critico { color: #dc2626; font-weight: bold; }
    .ref-text { color: #718096; font-size: 9px; }

    /* === TEXTO CONTENIDO === */
    .text-content {
      padding: 8px 0;
      margin: 0;
      background-color: transparent;
      border: none;
      white-space: normal;
      font-size: 9px;
      line-height: 1.6;
    }

    .text-content p {
      margin: 0 0 4px 0;
    }
    .text-content p:last-child {
      margin-bottom: 0;
    }
    .text-content ul, .text-content ol {
      margin: 0 0 4px 0;
      padding-left: 20px;
    }
    .text-content li {
      margin-bottom: 2px;
    }

    .text-content strong, .text-content b { font-weight: bold; }
    .text-content em, .text-content i { font-style: italic; }
    .text-content u { text-decoration: underline; }
    .text-content s { text-decoration: line-through; }

    /* === IMÁGENES === */
    .images-container {
      display: table;
      width: 100%;
      margin: 8px 0;
    }

    .image-cell {
      display: table-cell;
      width: 50%;
      padding: 5px;
      text-align: center;
      vertical-align: top;
    }

    .image-cell img {
      max-width: 95%;
      height: auto;
      border: 1px solid #e2e8f0;
      border-radius: 3px;
    }

    /* === PAGINACIÓN === */
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }

    /* === COMENTARIO DEL PROFESIONAL === */
    .comment-section {
      margin-top: 12px;
      padding-top: 8px;
      border-top: 1px solid #cbd5e0;
    }

    .comment-label {
      font-size: 9px;
      font-weight: bold;
      color: #1a1a1a;
      margin-bottom: 4px;
    }
  </style>
</head>

<body>
  {{-- === FONDO FULL-BLEED (todas las páginas) === --}}
  @if(isset($fondoHojaBase64) && $fondoHojaBase64)
    <div class="page-background">
      <img src="{{ $fondoHojaBase64 }}" alt="">
    </div>
  @endif

  {{-- === LOGO FIJO (todas las páginas) === --}}
  @if(isset($logoBase64) && $logoBase64)
    <div class="header-logo">
      <img src="{{ $logoBase64 }}" alt="Logo">
    </div>
  @endif

  {{-- === BARCODE (solo primera página) === --}}
  @if(isset($barcodeBase64) && $barcodeBase64)
    <div class="header-barcode">
      <div class="barcode-text">MUESTRA ID: {{ $codigoMuestra }}</div>
      <div class="barcode-img">
        <img src="{{ $barcodeBase64 }}" alt="Barcode">
      </div>
    </div>
  @endif

  {{-- === FIRMA FIJA (todas las páginas) === --}}
  @if(isset($firmaBase64) && $firmaBase64)
    <div class="fixed-signature">
      <img src="{{ $firmaBase64 }}" alt="Firma">
    </div>
  @endif

  {{-- === QR FIJO (todas las páginas) === --}}
  @if(isset($qrBase64) && $qrBase64)
    <div class="fixed-qr">
      <img src="{{ $qrBase64 }}" alt="QR">
      <div class="qr-label">REPORTE DIGITAL</div>
      <div class="qr-sublabel">Escanee para verificar</div>
    </div>
  @endif

  {{-- === BARRA AZUL INFERIOR (todas las páginas, solo formato completo) === --}}
  @if(($formato ?? 'completo') !== 'limpio')
  <div class="footer-bar"></div>
  <div class="footer-bar-content">
    <table>
      <tr>
        <td style="width: 25%; text-align: left;">
          <strong>ZONA SUR</strong><br>
          Calacoto Calle 23 Av. Ballivian<br>
          "Torre Faith" Planta Baja Nº10
        </td>
        <td style="width: 50%; text-align: center;">
          <strong>pglaboratoriobiologicoclinico@gmail.com</strong><br>
          75091961 - 64176776
        </td>
        <td style="width: 25%; text-align: right;">
          <strong>ZONA CENTRO</strong><br>
          Calle Coroico Nº 1551 Zona Barbara<br>
          Edif. Los Laureles (Detras del Mercado Yungas)
        </td>
      </tr>
    </table>
  </div>
  @endif

  {{-- === CONTENIDO PRINCIPAL === --}}
  <div class="container">
    {{-- Título del análisis --}}
    <div class="analysis-title">
      {{ $analisis->tipoAnalisis->nombre }}
    </div>

    {{-- Datos del paciente (envueltos en tarjeta redondeada) --}}
    <div class="patient-card">
      <table class="patient-grid">
        <tr>
          <td>
            <span class="patient-label">Paciente</span>
            <span class="patient-value">{{ $muestra->paciente_nombre ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Edad</span>
            <span class="patient-value">{{ $muestra->eedad ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Especie / Raza</span>
            <span class="patient-value">{{ ($muestra->especie->nombre ?? '') }} / {{ $muestra->raza ?? 'SRD' }}</span>
          </td>
          <td>
            <span class="patient-label">Código</span>
            <span class="patient-value-highlight">{{ $muestra->codigo_muestra ?? 'N/A' }}</span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="patient-label">Propietario</span>
            <span class="patient-value">{{ $muestra->propietario_nombre ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Solicitado por</span>
            <span class="patient-value">{{ $muestra->veterinaria->nombre ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Color / Sexo</span>
            <span class="patient-value">{{ $muestra->color ?? '' }} / {{ $muestra->sexo ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Fecha</span>
            <span class="patient-value">{{ $muestra->created_at ? $muestra->created_at->format('d/m/Y') : 'N/A' }}</span>
          </td>
        </tr>
      </table>
    </div>

    <hr class="separator">

    {{-- Componentes dinámicos --}}
    @foreach($componentesConDatos as $index => $item)
      @php
        $componente = $item['componente'];
        $resultado = $item['resultado'];
        $tipo = $item['tipo'];
        $chartImage = $item['chartImage'] ?? null;

        // Componentes pequeños que NO deben cortarse entre páginas
        $componentesPequenos = ['subtitulo', 'campo-texto', 'texto-libre', 'campo-imagenes', 'citologia'];
        $cssClass = in_array($tipo, $componentesPequenos) ? 'component-no-break' : 'component';
      @endphp

      <div class="{{ $cssClass }}">
        @if(view()->exists('pdf-v2.componentes.' . $tipo))
          @include('pdf-v2.componentes.' . $tipo, [
            'componente' => $componente,
            'resultado' => $resultado,
            'chartImage' => $chartImage
          ])
        @endif
      </div>
    @endforeach

  </div>
</body>
</html>
