<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <style>
        @page {
            margin: 15mm 22mm 15mm 22mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #1a1a1a;
            padding: 0 10px;
        }

        .titulo-principal {
            text-align: center;
            padding: 15px 0 5px 0;
        }
        .titulo-principal h1 {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .titulo-principal .subtitulo {
            font-size: 10px;
            color: #333;
            margin-top: 3px;
        }
        .titulo-principal .periodo {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }
        .titulo-principal .expresado {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
            font-style: italic;
        }

        .separador {
            border: none;
            border-top: 1.5px solid #000;
            margin: 8px 0;
        }

        table { width: 100%; border-collapse: collapse; font-size: 7.5px; margin-top: 5px; }

        /* Encabezado de grupo (Físico / Valorado) */
        thead tr.grupo th {
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #999;
            background: #E8E8E8;
            color: #000;
        }

        /* Sub-encabezados */
        thead tr.sub th {
            padding: 3px 4px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            color: #000;
            border: 1px solid #999;
            background: #F5F5F5;
        }

        /* Encabezados de info (Fecha, Detalle, Insumo) */
        thead tr.grupo th.info {
            background: #E8E8E8;
            color: #000;
            border: 1px solid #999;
        }

        tbody tr td {
            padding: 2px 4px;
            border: 1px solid #CCC;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td { background: #FAFAFA; }

        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        td.saldo { font-weight: bold; }

        tfoot tr td {
            padding: 4px 4px;
            background: #E8E8E8;
            font-weight: bold;
            border: 1px solid #999;
            font-size: 8px;
        }

        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 7px;
            color: #999;
            border-top: 0.5px solid #CCC;
            padding-top: 4px;
        }
    </style>
</head>
<body>

<div class="titulo-principal">
    <div class="subtitulo">{{ $titulo }}</div>
    <div class="subtitulo">Sucursal: {{ $sucursalNombre }}</div>
    @if($fechaDesde || $fechaHasta)
        <div class="periodo">
            Del <strong>{{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d-m-Y') : 'Inicio' }}</strong>
            Al <strong>{{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d-m-Y') : now()->format('d-m-Y') }}</strong>
        </div>
    @endif
    <div class="expresado">(Expresado en Bolivianos)</div>
</div>

<hr class="separador">

<table>
    <thead>
        <tr class="grupo">
            <th class="info" rowspan="2" style="width:65px">FECHA</th>
            <th class="info" rowspan="2" style="width:180px">DETALLE</th>
            @if($mostrarColumnaInsumo)
            <th class="info" rowspan="2" style="width:100px">INSUMO</th>
            @endif
            <th colspan="4">CANTIDADES</th>
            <th colspan="4">COSTOS (BS)</th>
        </tr>
        <tr class="sub">
            <th>Inicio</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Saldo</th>
            <th>Inicio</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($registros as $registro)
        <tr>
            <td style="white-space:nowrap">{{ $registro['fecha'] }}</td>
            <td>{{ $registro['detalle'] }}</td>
            @if($mostrarColumnaInsumo)
            <td>{{ $registro['insumo_nombre'] ?? '' }}</td>
            @endif
            <td class="num">{{ number_format($registro['inicio_cantidad'], 2) }}</td>
            @if($registro['entrada_cantidad'] !== null)
                <td class="num">{{ number_format($registro['entrada_cantidad'], 2) }}</td>
            @else
                <td class="num"></td>
            @endif
            @if($registro['salida_cantidad'] !== null)
                <td class="num">{{ number_format($registro['salida_cantidad'], 2) }}</td>
            @else
                <td class="num"></td>
            @endif
            <td class="num saldo">{{ number_format($registro['saldo_cantidad'], 2) }}</td>
            <td class="num">{{ number_format($registro['inicio_costo'], 2) }}</td>
            @if($registro['entrada_costo'] !== null)
                <td class="num">{{ number_format($registro['entrada_costo'], 2) }}</td>
            @else
                <td class="num"></td>
            @endif
            @if($registro['salida_costo'] !== null)
                <td class="num">{{ number_format($registro['salida_costo'], 2) }}</td>
            @else
                <td class="num"></td>
            @endif
            <td class="num saldo">{{ number_format($registro['saldo_costo'], 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ $mostrarColumnaInsumo ? 11 : 10 }}" style="text-align:center;padding:12px;color:#999;">
                Sin movimientos registrados
            </td>
        </tr>
        @endforelse
    </tbody>
    @if(count($registros) > 0)
    <tfoot>
        <tr>
            <td colspan="{{ $mostrarColumnaInsumo ? 3 : 2 }}" style="text-align:left;font-weight:bold;">Totales Finales</td>
            <td colspan="3"></td>
            <td class="num saldo">{{ number_format($saldoFinalCantidad, 2) }}</td>
            <td colspan="3"></td>
            <td class="num saldo">{{ number_format($saldoFinalCosto, 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">
    Laboratorio Clínico Veterinario &middot; {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
