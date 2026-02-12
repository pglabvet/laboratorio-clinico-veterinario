<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Análisis</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <!-- Wrapper -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f6f9;">
        <tr>
            <td align="center" style="padding: 30px 15px;">
                <!-- Container -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    
                    <!-- Header con gradiente -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5f8a 50%, #3b82b8 100%); padding: 35px 40px; text-align: center;">
                            {{-- Logo placeholder - reemplazar cuando tengan el logo --}}
                            <div style="margin-bottom: 15px;">
                                <table role="presentation" cellspacing="0" cellpadding="0" align="center">
                                    <tr>
                                        <td style="background-color: rgba(255,255,255,0.15); border-radius: 50%; padding: 12px; display: inline-block;">
                                            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOS43NSA5Ljc1bC40MS0uNDFhMi4xIDIuMSAwIDAxMi45NyAwbC44MyA4My44MyAwIDAxMi45NyAwbC40MS0uNDFNOS43NSA5Ljc1YzAgLjU0LS4yIDEuMDYtLjU5IDEuNDRMNy41IDEyLjg1bS41LjVMMjEgMjFsLTUuMzUtNS4zNG0wIDBhMi4xIDIuMSAwIDAwMi45NyAwbDUuMDUgNS4wNU0xMiAyYTEwIDEwIDAgMTAwIDIwIDEwIDEwIDAgMDAwLTIweiIvPjwvc3ZnPg==" alt="Lab" width="40" height="40" style="display: block;" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 700; margin: 0 0 5px 0; letter-spacing: 0.5px;">
                                LABORATORIO CLÍNICO VETERINARIO
                            </h1>
                            <p style="color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; letter-spacing: 1px;">
                                RESULTADOS DE ANÁLISIS
                            </p>
                        </td>
                    </tr>

                    <!-- Saludo -->
                    <tr>
                        <td style="padding: 30px 40px 15px 40px;">
                            <p style="color: #374151; font-size: 15px; line-height: 1.6; margin: 0;">
                                Estimado/a <strong>{{ $veterinaria->responsable ?? $veterinaria->nombre ?? 'Doctor/a' }}</strong>,
                            </p>
                            <p style="color: #6b7280; font-size: 14px; line-height: 1.6; margin: 10px 0 0 0;">
                                Le informamos que los resultados de los análisis solicitados están listos. Adjunto encontrará los documentos correspondientes en formato PDF.
                            </p>
                        </td>
                    </tr>

                    <!-- Tarjeta de información del paciente -->
                    <tr>
                        <td style="padding: 10px 40px 20px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding: 20px 25px;">
                                        <!-- Título de la tarjeta -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
                                                    <h2 style="color: #1e3a5f; font-size: 14px; font-weight: 600; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                                        📋 Información de la Muestra
                                                    </h2>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Datos en 2 columnas -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 15px;">
                                            <tr>
                                                <td width="50%" valign="top" style="padding-right: 10px;">
                                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="padding-bottom: 12px;">
                                                                <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Código</span>
                                                                <span style="color: #1e3a5f; font-size: 15px; font-weight: 700; font-family: 'Courier New', monospace;">{{ $muestra->codigo_muestra }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding-bottom: 12px;">
                                                                <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Paciente</span>
                                                                <span style="color: #374151; font-size: 14px; font-weight: 600;">{{ $muestra->paciente_nombre }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding-bottom: 12px;">
                                                                <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Especie / Raza</span>
                                                                <span style="color: #374151; font-size: 14px;">{{ $especie->nombre ?? 'N/A' }} {{ $muestra->raza ? '/ ' . $muestra->raza : '' }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="50%" valign="top" style="padding-left: 10px;">
                                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="padding-bottom: 12px;">
                                                                <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Propietario</span>
                                                                <span style="color: #374151; font-size: 14px; font-weight: 600;">{{ $muestra->propietario_nombre }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding-bottom: 12px;">
                                                                <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Veterinaria</span>
                                                                <span style="color: #374151; font-size: 14px;">{{ $veterinaria->nombre ?? 'N/A' }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding-bottom: 12px;">
                                                                <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Sucursal</span>
                                                                <span style="color: #374151; font-size: 14px;">{{ $sucursal->nombre ?? 'N/A' }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Tabla de análisis -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <h2 style="color: #1e3a5f; font-size: 14px; font-weight: 600; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                🔬 Análisis Realizados
                            </h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                <thead>
                                    <tr>
                                        <th style="background-color: #1e3a5f; color: #ffffff; padding: 10px 15px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Análisis
                                        </th>
                                        <th style="background-color: #1e3a5f; color: #ffffff; padding: 10px 15px; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Estado
                                        </th>
                                        <th style="background-color: #1e3a5f; color: #ffffff; padding: 10px 15px; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            PDF Adjunto
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analisisList as $index => $analisis)
                                        <tr style="background-color: {{ $index % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                            <td style="padding: 12px 15px; font-size: 14px; color: #374151; font-weight: 500; border-bottom: 1px solid #f1f5f9;">
                                                {{ $analisis->tipoAnalisis->nombre ?? 'N/A' }}
                                            </td>
                                            <td style="padding: 12px 15px; text-align: center; border-bottom: 1px solid #f1f5f9;">
                                                <span style="display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.3px;
                                                    {{ $analisis->estado === 'Aprobado' || $analisis->estado === 'Enviado' 
                                                        ? 'background-color: #d1fae5; color: #065f46;' 
                                                        : 'background-color: #fef3c7; color: #92400e;' }}">
                                                    ✓ {{ $analisis->estado }}
                                                </span>
                                            </td>
                                            <td style="padding: 12px 15px; text-align: center; font-size: 13px; color: #6b7280; border-bottom: 1px solid #f1f5f9;">
                                                @if($analisis->pdfs()->exists())
                                                    📎 Adjunto
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <!-- Nota informativa -->
                    <tr>
                        <td style="padding: 0 40px 25px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="color: #1e40af; font-size: 13px; line-height: 1.5; margin: 0;">
                                            <strong>ℹ️ Nota:</strong> Los archivos PDF adjuntos contienen los resultados detallados de cada análisis. 
                                            Si tiene alguna consulta sobre los resultados, no dude en comunicarse con nosotros.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Separador -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 25px 40px 30px 40px; text-align: center;">
                            <p style="color: #9ca3af; font-size: 12px; line-height: 1.5; margin: 0 0 8px 0;">
                                Este correo ha sido enviado automáticamente por el sistema del<br>
                                <strong style="color: #6b7280;">Laboratorio Clínico Veterinario</strong>
                            </p>
                            <p style="color: #d1d5db; font-size: 11px; margin: 0;">
                                {{ now()->format('d/m/Y H:i') }} — {{ $sucursal->nombre ?? 'Sucursal Principal' }}
                            </p>
                        </td>
                    </tr>

                    <!-- Barra inferior decorativa -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5f8a 50%, #3b82b8 100%); height: 6px; font-size: 0; line-height: 0;">
                            &nbsp;
                        </td>
                    </tr>

                </table>

                <!-- Texto legal -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding: 20px 40px; text-align: center;">
                            <p style="color: #b0b8c4; font-size: 11px; line-height: 1.4; margin: 0;">
                                Este mensaje y sus adjuntos son confidenciales y están dirigidos exclusivamente a su destinatario.
                                Si ha recibido este correo por error, por favor notifíquenos y elimínelo.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
