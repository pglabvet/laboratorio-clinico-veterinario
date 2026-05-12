<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page {
            margin: 15mm 18mm 15mm 18mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #1a1a1a;
            padding: 0 5px;
        }

        /* === ENCABEZADO === */
        .titulo-principal {
            text-align: center;
            padding: 12px 0 5px 0;
        }

        .titulo-principal h1 {
            font-size: 13px;
            font-weight: bold;
            color: #8b0000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .titulo-principal .subtitulo {
            font-size: 9px;
            color: #333;
            margin-top: 3px;
        }

        .titulo-principal .periodo {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }

        .separador {
            border: none;
            border-top: 1.5px solid #8b0000;
            margin: 8px 0;
        }

        /* === RESUMEN === */
        .resumen {
            margin-bottom: 10px;
            padding: 8px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 4px;
            background: #fff5f5;
        }

        .resumen-titulo {
            font-size: 9px;
            font-weight: bold;
            color: #8b0000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .resumen-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .resumen-grid td {
            padding: 2px 8px;
            font-size: 8px;
            border: none;
        }

        .resumen-grid .label {
            color: #555;
            font-weight: normal;
        }

        .resumen-grid .value {
            font-weight: bold;
            color: #1a1a1a;
        }

        /* === TABLA === */
        table.rechazadas {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 8px;
        }

        table.rechazadas thead th {
            padding: 4px 5px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #8b0000;
        }

        table.rechazadas tbody td {
            padding: 3px 5px;
            border-bottom: 0.5px solid #e0e0e0;
            vertical-align: top;
            font-size: 7.5px;
        }

        table.rechazadas tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        /* === BADGE MOTIVO === */
        .badge-motivo {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            background: #fef3c7;
            color: #92400e;
        }

        /* === PIE === */
        .footer {
            margin-top: 12px;
            text-align: right;
            font-size: 7px;
            color: #999;
            border-top: 0.5px solid #CCC;
            padding-top: 4px;
        }
    </style>
</head>

<body>

    {{-- Encabezado --}}
    <div class="titulo-principal">
        <h1>{{ $titulo }}</h1>
        <div class="subtitulo">Laboratorio Clínico Veterinario</div>
        @if($fechaDesde || $fechaHasta)
            <div class="periodo">
                Período: <strong>{{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : 'Inicio' }}</strong>
                al
                <strong>{{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
            </div>
        @endif
        @if($filtroMotivo)
            <div class="periodo">Motivo: <strong>{{ $filtroMotivo }}</strong></div>
        @endif
    </div>

    <hr class="separador">

    {{-- Resumen --}}
    <div class="resumen">
        <div class="resumen-titulo">Resumen</div>
        <table class="resumen-grid">
            <tr>
                <td class="label">Total Muestras Rechazadas:</td>
                <td class="value">{{ $muestras->count() }}</td>
                <td class="label">Motivos distintos:</td>
                <td class="value">{{ $porMotivo->count() }}</td>
            </tr>
            @foreach($porMotivo->take(6) as $motivo => $cantidad)
                <tr>
                    <td class="label">{{ $motivo }}:</td>
                    <td class="value">{{ $cantidad }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- Tabla --}}
    <table class="rechazadas">
        <thead>
            <tr>
                <th style="width:55px">Código</th>
                <th style="width:65px">Paciente</th>
                <th style="width:65px">Propietario</th>
                <th style="width:40px">Especie</th>
                <th style="width:65px">Veterinaria</th>
                <th style="width:50px">Sucursal</th>
                <th style="width:45px">Tipo Muestra</th>
                <th style="width:80px">Motivo de Rechazo</th>
                <th>Observaciones</th>
                <th style="width:50px">Fecha</th>
                <th style="width:55px">Registrado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($muestras as $muestra)
                <tr>
                    <td style="font-weight:bold; white-space:nowrap">{{ $muestra->codigo_muestra }}</td>
                    <td>{{ $muestra->paciente_nombre }}</td>
                    <td>{{ $muestra->propietario_nombre }}</td>
                    <td>{{ $muestra->especie->nombre ?? 'N/A' }}</td>
                    <td>{{ $muestra->veterinaria->nombre ?? 'N/A' }}</td>
                    <td>{{ $muestra->sucursal->nombre ?? 'N/A' }}</td>
                    <td>{{ $muestra->tipo_muestra }}</td>
                    <td><span class="badge-motivo">{{ $muestra->motivo_rechazo }}</span></td>
                    <td>{{ $muestra->observaciones ?: '-' }}</td>
                    <td style="white-space:nowrap">{{ $muestra->fecha_rechazo->format('d/m/Y') }}</td>
                    <td>{{ $muestra->registradoPor->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center; padding:20px; color:#999;">
                        No se encontraron muestras rechazadas con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laboratorio Clínico Veterinario &middot; {{ now()->format('d/m/Y H:i') }}
    </div>

</body>

</html>
