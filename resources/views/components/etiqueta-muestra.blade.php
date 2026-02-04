{{--
| Componente Blade: etiqueta-muestra
| Recibe: $muestra (App\Models\Muestra)
| Objetivo: Renderizar una etiqueta imprimible de 30mm x 20mm.
--}}
@props(['muestra'])

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Etiqueta - {{ $muestra->codigo_muestra }}</title>
    <style>
        /* RESET BÁSICO */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        /* TAMAÑO DE DOCUMENTO 30mm x 20mm */
        html, body {
            width: 30mm;
            height: 20mm;
            font-family: Arial, sans-serif;
            background-color: white; /* SIEMPRE BLANCO PARA TÉRMICA */
        }
        
        /* CONTENEDOR PRINCIPAL */
        .etiqueta {
            width: 30mm;
            height: 20mm;
            display: flex;
            flex-direction: column; /* Cambiado a columna para orientación horizontal del barcode */
            background: white;
            padding: 1mm; /* Borde de 1mm alrededor de toda la etiqueta */
            box-sizing: border-box; /* Asegura que el padding esté incluido en las dimensiones */
        }
        
        /* SECCIÓN DEL CÓDIGO DE BARRAS (SUPERIOR) */
        .barcode-section {
            width: 100%;
            height: 11mm; /* Espacio para el código horizontal */
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        /* ENVOLTORIO DEL CÓDIGO DE BARRAS */
        .barcode-wrap {
            width: 26mm; /* Ancho limitado para respetar márgenes (28mm - 2mm de margen interno) */
            height: 9mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }
        
        /* ESTILOS PARA EL SVG DEL CÓDIGO DE BARRAS */
        .barcode-wrap svg {
            width: 100% !important;
            height: 100% !important;
            max-width: 26mm;
            max-height: 9mm;
        }
        
        /* SECCIÓN DE INFORMACIÓN (INFERIOR) */
        .info-section {
            width: 100%;
            height: 5mm; /* Espacio restante */
            display: flex;
            flex-direction: row; /* Texto en horizontal */
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 3mm; /* Espacio entre LABVET y el código */
        }
        
        .titulo {
            font-size: 8pt;
            font-weight: 900;
            color: black;
            text-transform: uppercase;
            line-height: 1;
        }
        
        .codigo {
            font-size: 7pt;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: black;
            line-height: 1;
        }
        
        /* REGLAS DE IMPRESIÓN */
        @media print {
            @page {
                size: 30mm 20mm;
                margin: 0;
            }
            html, body {
                width: 30mm;
                height: 20mm;
            }
        }
        
        /* VISTA EN PANTALLA (DEBUG) */
        @media screen {
            html {
                background: #333; /* Fondo oscuro para contrastar la etiqueta blanca */
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            body {
                box-shadow: 0 0 10px rgba(0,0,0,0.5); /* Sombra para ver los bordes */
            }
        }
    </style>
</head>
<body>
    <script>
        // Forzar recarga si la página fue cacheada
        (function() {
            // Verificar si la página fue cargada desde caché
            if (performance.navigation.type === performance.navigation.TYPE_BACK_FORWARD) {
                // Forzar recarga
                window.location.reload(true);
            }
            
            // Prevenir cualquier caché adicional
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload(true);
                }
            });
        })();
    </script>
    <div class="etiqueta">
        
        {{-- SECCIÓN BARCODE (IZQUIERDA) --}}
        <div class="barcode-section">
            <div class="barcode-wrap">
                {{-- Asegúrate que tu generador no inyecte estilos inline con colores --}}
                {!! $muestra->generarCodigoBarras() !!}
            </div>
        </div>
        
        {{-- SECCIÓN TEXTO (DERECHA) --}}
        <div class="info-section">
            <div class="titulo">LABVET</div>
            <div class="codigo">{{ $muestra->codigo_muestra }}</div>
        </div>

    </div>
</body>
</html>