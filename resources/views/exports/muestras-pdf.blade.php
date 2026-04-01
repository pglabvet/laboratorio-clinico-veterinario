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
            color: #1e3a5f;
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
            border-top: 1.5px solid #1e3a5f;
            margin: 8px 0;
        }

        .separador-light {
            border: none;
            border-top: 0.5px solid #ccc;
            margin: 4px 0;
        }

        /* === RESUMEN GENERAL === */
        .resumen {
            margin-bottom: 10px;
            padding: 8px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 4px;
            background: #f8f9fa;
        }

        .resumen-titulo {
            font-size: 9px;
            font-weight: bold;
            color: #1e3a5f;
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

        /* === GRUPO VETERINARIA === */
        .vet-header {
            margin-top: 12px;
            padding: 6px 10px;
            background: #1e3a5f;
            color: #fff;
            border-radius: 3px 3px 0 0;
            page-break-inside: avoid;
        }

        .vet-header-nombre {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .vet-header-stats {
            font-size: 7.5px;
            color: #b8c9e0;
            margin-top: 2px;
        }

        /* === TABLA DE MUESTRAS === */
        table.muestras {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 8px;
        }

        table.muestras thead th {
            padding: 4px 5px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1.5px solid #1e3a5f;
            background: #eef2f7;
        }

        table.muestras tbody td {
            padding: 3px 5px;
            border-bottom: 0.5px solid #e0e0e0;
            vertical-align: top;
            font-size: 7.5px;
        }

        table.muestras tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        /* === BADGES DE ESTADO === */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-proceso {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-completado {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-enviado {
            background: #e9d5ff;
            color: #6b21a8;
        }

        /* === SUBTOTALES === */
        .vet-subtotal {
            padding: 4px 10px;
            background: #eef2f7;
            border: 1px solid #d0d5dd;
            border-top: none;
            border-radius: 0 0 3px 3px;
            margin-bottom: 10px;
        }

        .vet-subtotal-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .vet-subtotal-grid td {
            padding: 2px 5px;
            font-size: 7px;
            border: none;
        }

        .vet-subtotal-grid .label {
            color: #555;
        }

        .vet-subtotal-grid .value {
            font-weight: bold;
            color: #1a1a1a;
            text-align: center;
        }

        /* === ANÁLISIS TAG === */
        .analisis-tag {
            display: inline-block;
            padding: 1px 4px;
            margin: 1px 2px 1px 0;
            border-radius: 2px;
            font-size: 6.5px;
            background: #e8f0fe;
            color: #1e3a5f;
            border: 0.5px solid #b8c9e0;
        }

        /* === PIE DE PÁGINA === */
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
        @if($filtroEstado)
            <div class="periodo">Estado: <strong>{{ $filtroEstado }}</strong></div>
        @endif
    </div>

    <hr class="separador">

    {{-- Resumen General --}}
    <div class="resumen">
        <div class="resumen-titulo">Resumen General</div>
        <table class="resumen-grid">
            <tr>
                <td class="label">Total Veterinarias:</td>
                <td class="value">{{ $totalVeterinarias }}</td>
                <td class="label">Total Muestras:</td>
                <td class="value">{{ $totalMuestras }}</td>
                <td class="label">Total Análisis:</td>
                <td class="value">{{ $totalAnalisis }}</td>
            </tr>
        </table>
    </div>

    {{-- Grupos por Veterinaria --}}
    @foreach($agrupadoPorVeterinaria as $grupo)
        @php
            $vet = $grupo['veterinaria'];
        @endphp

        {{-- Cabecera de la veterinaria --}}
        <div class="vet-header">
            <div class="vet-header-nombre">{{ $vet->nombre ?? 'Sin veterinaria' }}</div>
            <div class="vet-header-stats">
                {{ $grupo['total_muestras'] }} {{ $grupo['total_muestras'] == 1 ? 'muestra' : 'muestras' }}
                &nbsp;·&nbsp;
                {{ $grupo['total_analisis'] }} {{ $grupo['total_analisis'] == 1 ? 'análisis' : 'análisis' }}
                @if($vet && $vet->responsable)
                    &nbsp;·&nbsp; Responsable: {{ $vet->responsable }}
                @endif
            </div>
        </div>

        {{-- Tabla de muestras --}}
        <table class="muestras">
            <thead>
                <tr>
                    <th style="width:65px">Código</th>
                    <th style="width:70px">Paciente</th>
                    <th style="width:70px">Propietario</th>
                    <th style="width:45px">Especie</th>
                    <th style="width:48px">Tipo Muestra</th>
                    <th style="width:50px">Estado</th>
                    <th style="width:50px">Fecha</th>
                    <th style="width:60px">Sucursal</th>
                    <th>Análisis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grupo['muestras'] as $muestra)
                    @php
                        $badgeClass = match ($muestra->estado) {
                            'Pendiente' => 'badge-pendiente',
                            'En proceso' => 'badge-proceso',
                            'Completado' => 'badge-completado',
                            'Enviado' => 'badge-enviado',
                            default => 'badge-pendiente',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:bold; white-space:nowrap">{{ $muestra->codigo_muestra }}</td>
                        <td>{{ $muestra->paciente_nombre }}</td>
                        <td>{{ $muestra->propietario_nombre }}</td>
                        <td>{{ $muestra->especie->nombre ?? 'N/A' }}</td>
                        <td>{{ $muestra->tipo_muestra }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $muestra->estado }}</span></td>
                        <td style="white-space:nowrap">{{ $muestra->fecha_recepcion->format('d/m/Y') }}</td>
                        <td>{{ $muestra->sucursal->nombre ?? 'N/A' }}</td>
                        <td>
                            @forelse($muestra->analisis as $analisis)
                                <span class="analisis-tag">{{ $analisis->tipoAnalisis->nombre ?? 'N/A' }}
                                    ({{ $analisis->estado }})</span>
                            @empty
                                <span style="color:#999; font-style:italic">Sin análisis</span>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Subtotales por veterinaria --}}
        <div class="vet-subtotal">
            <table class="vet-subtotal-grid">
                <tr>
                    <td class="label">Pendientes:</td>
                    <td class="value">{{ $grupo['estados']['Pendiente'] }}</td>
                    <td class="label">En proceso:</td>
                    <td class="value">{{ $grupo['estados']['En proceso'] }}</td>
                    <td class="label">Completados:</td>
                    <td class="value">{{ $grupo['estados']['Completado'] }}</td>
                    <td class="label">Enviados:</td>
                    <td class="value">{{ $grupo['estados']['Enviado'] }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    @if($agrupadoPorVeterinaria->isEmpty())
        <div style="text-align:center; padding:40px; color:#999;">
            No se encontraron muestras con los filtros seleccionados.
        </div>
    @endif

    <div class="footer">
        Laboratorio Clínico Veterinario &middot; {{ now()->format('d/m/Y H:i') }}
    </div>

</body>

</html>