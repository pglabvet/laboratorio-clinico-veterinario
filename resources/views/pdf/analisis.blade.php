<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $analisis->tipoAnalisis->nombre }} - {{ $muestra->paciente_nombre }}</title>

  <style>
    /* =========================
       1) MÁRGENES PARA EL CONTENIDO
       ========================= */
    @page {
      /* top  right bottom left  (ajústalos a tu diseño) */
      margin: 70pt 70pt 120pt 25pt;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      font-size: 10px;
      line-height: 1.4;
      color: #333;
    }

    /* =========================
       2) FONDO FULL-BLEED (toda la hoja)
       Letter: 8.5x11in => 612pt x 792pt
       IMPORTANT: lo compensamos con top/left negativos (márgenes)
       ========================= */
    .page-background{
      position: fixed;

      /* Compensación exacta de los márgenes de @page */
      top: -70pt;
      left: -25pt;

      width: 612pt;
      height: 792pt;

      z-index: 0;
    }

    .page-background img{
      width: 612pt;
      height: 792pt;
    }

    /* =========================
       3) CONTENIDO (respeta márgenes por @page)
       ========================= */
    .container{
      position: relative;
      z-index: 1;
      padding: 0; /* NO padding aquí, ya lo hace @page en cada página */
    }

    /* ✅ evita que "PDF generado..." se vaya a una hoja extra */
    .generation-info{
      position: fixed;
      bottom: 16pt;
      right: 0;
      font-size: 7px;
      color: #718096;
      text-align: right;
      z-index: 2;
    }

    /* Información del paciente */
    .patient-info {
      background-color: transparent;
      border: 1px solid #1e3a5f;
      border-radius: 6px;
      padding: 12px;
      margin-bottom: 15px;
    }

    .patient-info-title {
      font-size: 11px;
      font-weight: bold;
      color: #1e3a5f;
      margin-bottom: 10px;
      text-transform: uppercase;
      border-bottom: 2px solid #1e3a5f;
      padding-bottom: 5px;
    }

    /* ✅ Tablas estables en dompdf */
    table{
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      background: transparent;
      margin-bottom: 10px;
    }

    /* Si alguna tabla roza bordes por el 1px de cálculo de dompdf */
    .patient-table,
    .footer-table{
      width: 99%;
    }

    .patient-table td {
      padding: 4px 8px;
      border: none;
      width: 25%;
      background: transparent;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    .patient-label {
      font-size: 8px;
      color: #4a5568;
      text-transform: uppercase;
      font-weight: bold;
      display: block;
    }

    .patient-value {
      font-size: 10px;
      color: #1a202c;
      font-weight: 500;
    }

    /* Título del análisis */
    .analysis-title {
      color: #1e3a5f;
      padding: 10px 15px;
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
      text-transform: uppercase;
    }

    /* Componentes */
    .component {
      margin-bottom: 15px;
      page-break-inside: avoid;
      background-color: transparent;
      padding: 10px;
      border-radius: 4px;
    }

    .component-title {
      font-size: 11px;
      font-weight: bold;
      color: #1e3a5f;
      margin-bottom: 8px;
      text-transform: uppercase;
      border-left: 4px solid #1e3a5f;
      padding-left: 8px;
    }

    table th,
    table td {
      border: 1px solid #cbd5e0;
      padding: 6px 10px;
      text-align: left;
      font-size: 9px;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    table th {
      background-color: #1e3a5f;
      color: white;
      font-weight: bold;
    }

    /* Footer con firmas */
    .footer {
      margin-top: 25px;
      border-top: 2px solid #1e3a5f;
      padding-top: 15px;
      background-color: transparent;
      border-radius: 4px;
      padding: 15px;
    }

    .footer-table td {
      width: 50%;
      vertical-align: top;
      padding: 5px;
      border: none;
      background: transparent;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    .signature-box {
      border: 1px solid #1e3a5f;
      padding: 12px;
      min-height: 70px;
      background-color: transparent;
      border-radius: 4px;
    }

    .signature-label {
      font-size: 8px;
      color: #4a5568;
      text-transform: uppercase;
      font-weight: bold;
    }

    .signature-name {
      font-size: 11px;
      font-weight: bold;
      color: #1e3a5f;
      margin-top: 5px;
    }

    .signature-date {
      font-size: 8px;
      color: #718096;
      margin-top: 3px;
    }

    .signature-image-container {
      text-align: center;
      margin-top: 20px;
      padding: 15px;
      background-color: transparent;
      border-radius: 4px;
    }

    .signature-image {
      max-width: 200px;
      max-height: 120px;
    }

    .text-content {
      padding: 10px;
      background-color: #f7fafc;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      white-space: pre-wrap;
    }

    /* Imágenes lado a lado */
    .images-container {
      display: table;
      width: 100%;
      margin: 10px 0;
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
      border: 1px solid #cbd5e0;
      border-radius: 4px;
    }

    /* Paginación */
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
  </style>
</head>

<body>
  {{-- Fondo full hoja --}}
  @if(isset($fondoHojaBase64) && $fondoHojaBase64)
    <div class="page-background">
      <img src="{{ $fondoHojaBase64 }}" alt="">
    </div>
  @endif

  {{-- Info generación fija (no crea hoja extra) --}}
  <div class="generation-info">
    PDF generado el {{ $fechaGeneracion }} | Análisis #{{ $analisis->id }}
  </div>

  <div class="container">
        {{-- Título del análisis --}}
    <div class="analysis-title">
      {{ $analisis->tipoAnalisis->nombre }}
    </div>
    {{-- Información del paciente --}}
    <div class="patient-info">
      <div class="patient-info-title">Datos del Paciente</div>
      <table class="patient-table">
        <tr>
          <td>
            <span class="patient-label">Paciente</span>
            <span class="patient-value">{{ $muestra->paciente_nombre ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Especie</span>
            <span class="patient-value">{{ $muestra->especie->nombre ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Raza</span>
            <span class="patient-value">{{ $muestra->raza ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Edad</span>
            <span class="patient-value">{{ $muestra->edad ?? 'N/A' }}</span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="patient-label">Propietario</span>
            <span class="patient-value">{{ $muestra->propietario_nombre ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Sexo</span>
            <span class="patient-value">{{ $muestra->sexo ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="patient-label">Código Muestra</span>
            <span class="patient-value" style="color: #2c5282; font-weight: bold;">
              {{ $muestra->codigo_muestra }}
            </span>
          </td>
          <td>
            <span class="patient-label">Veterinaria</span>
            <span class="patient-value">{{ $muestra->veterinaria->nombre ?? 'N/A' }}</span>
          </td>
        </tr>
      </table>
    </div>



    {{-- Componentes dinámicos --}}
    @foreach($componentesConDatos as $index => $item)
      @php
        $componente = $item['componente'];
        $resultado = $item['resultado'];
        $tipo = $item['tipo'];
      @endphp

      <div class="component">
        @if(view()->exists('pdf.componentes.' . $tipo))
          @include('pdf.componentes.' . $tipo, [
            'componente' => $componente,
            'resultado' => $resultado
          ])
        @else
          @if(isset($componente['propiedades']['titulo']))
            <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
          @endif

          @if(!empty($resultado))
            <div class="text-content">
              @if(is_array($resultado))
                {{ json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
              @else
                {{ $resultado }}
              @endif
            </div>
          @endif
        @endif
      </div>
    @endforeach

    {{-- Footer con firmas --}}
    <div class="footer">
      <table class="footer-table">
        <tr>
          <td>
            <div class="signature-box">
              <span class="signature-label">Bioquímico Responsable</span>
              <div class="signature-name">{{ $analisis->bioquimico->name ?? 'N/A' }}</div>
              @if($analisis->fecha_finalizacion)
                <div class="signature-date">Fecha: {{ $analisis->fecha_finalizacion->format('d/m/Y H:i') }}</div>
              @endif
            </div>
          </td>
          <td>
            <div class="signature-box">
              <span class="signature-label">Aprobado por</span>
              <div class="signature-name">{{ $analisis->aprobador->name ?? 'N/A' }}</div>
              @if($analisis->fecha_aprobacion)
                <div class="signature-date">Fecha: {{ $analisis->fecha_aprobacion->format('d/m/Y H:i') }}</div>
              @endif
            </div>
          </td>
        </tr>
      </table>
    </div>

    {{-- Firma --}}
    @if(isset($firmaBase64) && $firmaBase64)
      <div class="signature-image-container">
        <img src="{{ $firmaBase64 }}" alt="Firma" class="signature-image">
      </div>
    @endif
  </div>
</body>
</html>