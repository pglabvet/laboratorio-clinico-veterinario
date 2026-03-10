{{-- PDF V2 - Vista principal del reporte de análisis --}}
{{-- Este archivo reemplaza a pdf/analisis.blade.php con un diseño completamente nuevo --}}

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $analisis->tipoAnalisis->nombre }} - {{ $muestra->paciente_nombre }}</title>

  <style>
    /* ===========================
       ESTILOS PDF V2
       Escribe aquí tu nuevo diseño
       =========================== */

    @page {
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
  </style>
</head>

<body>

  {{-- === FONDO DE HOJA === --}}
  {{-- Descomenta si quieres usar la imagen de fondo --}}
  {{--
  @if(isset($fondoHojaBase64) && $fondoHojaBase64)
    <div style="position: fixed; top: -70pt; left: -25pt; width: 612pt; height: 792pt; z-index: 0;">
      <img src="{{ $fondoHojaBase64 }}" style="width: 612pt; height: 792pt;">
    </div>
  @endif
  --}}

  {{-- === FIRMA FIJA === --}}
  {{-- Descomenta si quieres la firma en cada página --}}
  {{--
  @if(isset($firmaBase64) && $firmaBase64)
    <div style="position: fixed; bottom: -20pt; left: 0; right: 0; text-align: center; z-index: 2;">
      <img src="{{ $firmaBase64 }}" alt="Firma" style="max-width: 180px; max-height: 100px;">
    </div>
  @endif
  --}}

  <div>
    {{-- === TÍTULO DEL ANÁLISIS === --}}
    <h1>{{ $analisis->tipoAnalisis->nombre }}</h1>

    {{-- === DATOS DEL PACIENTE === --}}
    {{-- Variables disponibles: $muestra->paciente_nombre, $muestra->especie->nombre,
         $muestra->raza, $muestra->edad, $muestra->propietario_nombre,
         $muestra->sexo, $muestra->codigo_muestra, $muestra->veterinaria->nombre --}}

    {{-- === COMPONENTES DINÁMICOS === --}}
    @foreach($componentesConDatos as $index => $item)
      @php
        $componente = $item['componente'];
        $resultado = $item['resultado'];
        $tipo = $item['tipo'];
        $chartImage = $item['chartImage'] ?? null;
      @endphp

      <div>
        @if(view()->exists('pdf-v2.componentes.' . $tipo))
          @include('pdf-v2.componentes.' . $tipo, [
            'componente' => $componente,
            'resultado' => $resultado,
            'chartImage' => $chartImage
          ])
        @else
          {{-- Fallback: si el componente v2 no existe, usa el original --}}
          @if(view()->exists('pdf.componentes.' . $tipo))
            @include('pdf.componentes.' . $tipo, [
              'componente' => $componente,
              'resultado' => $resultado,
              'chartImage' => $chartImage
            ])
          @endif
        @endif
      </div>
    @endforeach

    {{-- === FOOTER / FIRMAS === --}}

  </div>
</body>
</html>
