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
            color: #14532d;
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
            border-top: 1.5px solid #14532d;
            margin: 8px 0;
        }

        /* === RESUMEN === */
        .resumen {
            margin-bottom: 10px;
            padding: 8px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 4px;
            background: #f0fdf4;
        }

        .resumen-titulo {
            font-size: 9px;
            font-weight: bold;
            color: #14532d;
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
        table.cobros {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 8px;
        }

        table.cobros thead th {
            padding: 4px 5px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #14532d;
        }

        table.cobros tbody td {
            padding: 3px 5px;
            border-bottom: 0.5px solid #e0e0e0;
            vertical-align: top;
            font-size: 7.5px;
        }

        table.cobros tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        .monto {
            text-align: right;
            white-space: nowrap;
        }

        /* === BADGE ESTADO === */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
        }

        .badge-Pagado { background: #dcfce7; color: #166534; }
        .badge-Parcial { background: #fef3c7; color: #92400e; }
        .badge-Pendiente { background: #fee2e2; color: #991b1b; }
        .badge-Exonerado { background: #dbeafe; color: #1e40af; }

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
        @if($filtroEstado || $filtroMetodo)
            <div class="periodo">
                @if($filtroEstado) Estado: <strong>{{ $filtroEstado }}</strong> @endif
                @if($filtroMetodo) &middot; Método: <strong>{{ $filtroMetodo }}</strong> @endif
            </div>
        @endif
    </div>

    <hr class="separador">

    {{-- Resumen --}}
    <div class="resumen">
        <div class="resumen-titulo">Resumen</div>
        <table class="resumen-grid">
            <tr>
                <td class="label">Cobros:</td>
                <td class="value">{{ $resumen['cantidad'] }}</td>
                <td class="label">Total:</td>
                <td class="value">Bs {{ number_format($resumen['total'], 2) }}</td>
                <td class="label">Pagado:</td>
                <td class="value">Bs {{ number_format($resumen['pagado'], 2) }}</td>
                <td class="label">Saldo pendiente:</td>
                <td class="value">Bs {{ number_format($resumen['saldo'], 2) }}</td>
            </tr>
            <tr>
                @foreach($porEstado as $estado => $cantidad)
                    <td class="label">{{ $estado }}:</td>
                    <td class="value">{{ $cantidad }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach($porMetodo as $metodo => $monto)
                    <td class="label">{{ $metodo }}:</td>
                    <td class="value">Bs {{ number_format($monto, 2) }}</td>
                @endforeach
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    {{-- Tabla --}}
    <table class="cobros">
        <thead>
            <tr>
                <th style="width:45px">Fecha</th>
                <th style="width:50px">Código</th>
                <th style="width:60px">Paciente</th>
                <th style="width:70px">Veterinaria</th>
                <th style="width:50px">Sucursal</th>
                <th>Examen</th>
                <th style="width:50px" class="monto">Total</th>
                <th style="width:50px" class="monto">Pagado</th>
                <th style="width:50px" class="monto">Saldo</th>
                <th style="width:40px">Método</th>
                <th style="width:45px">Estado</th>
                <th style="width:90px">Observación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cobros as $cobro)
                <tr>
                    <td style="white-space:nowrap">{{ $cobro->muestra->fecha_recepcion?->format('d/m/Y') }}</td>
                    <td style="font-weight:bold; white-space:nowrap">{{ $cobro->muestra->codigo_muestra }}</td>
                    <td>{{ $cobro->muestra->paciente_nombre }}</td>
                    <td>{{ $cobro->muestra->veterinaria->nombre ?? 'N/A' }}</td>
                    <td>{{ $cobro->muestra->sucursal->nombre ?? 'N/A' }}</td>
                    <td>{{ $cobro->examen ?: '-' }}</td>
                    <td class="monto">{{ number_format((float) $cobro->total, 2) }}</td>
                    <td class="monto">{{ number_format((float) $cobro->total_pagado, 2) }}</td>
                    <td class="monto">{{ $cobro->exonerado ? '-' : number_format($cobro->saldo, 2) }}</td>
                    <td>{{ $cobro->metodo ?? '-' }}</td>
                    <td><span class="badge badge-{{ $cobro->estado }}">{{ $cobro->estado }}</span></td>
                    <td>{{ $cobro->observacion ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center; padding:20px; color:#999;">
                        No se encontraron cobros con los filtros seleccionados.
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
