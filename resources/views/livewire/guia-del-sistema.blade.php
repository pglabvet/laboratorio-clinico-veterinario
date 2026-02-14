<div class="space-y-8">
    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl">Guía del Sistema</flux:heading>
        <flux:subheading>Información de referencia sobre los estados, flujos y procesos del sistema LABVET</flux:subheading>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 1: ESTADOS DE MUESTRA --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                <flux:icon.beaker class="size-5 text-blue-600 dark:text-blue-400" />
            </div>
            <flux:heading size="lg">Estados de Muestra</flux:heading>
        </div>

        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
            Cada muestra pasa por los siguientes estados desde su recepción hasta la entrega de resultados:
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Pendiente --}}
            <div class="rounded-lg border border-amber-200 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="amber" size="sm">Pendiente</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Muestra recibida en el laboratorio. Aún no se ha iniciado ningún análisis.
                </p>
            </div>

            {{-- En proceso --}}
            <div class="rounded-lg border border-blue-200 dark:border-blue-800/50 bg-blue-50/50 dark:bg-blue-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="blue" size="sm">En proceso</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Al menos uno de sus análisis se está realizando, está en revisión o aprobado.
                </p>
            </div>

            {{-- Completado --}}
            <div class="rounded-lg border border-green-200 dark:border-green-800/50 bg-green-50/50 dark:bg-green-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="green" size="sm">Completado</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Todos los análisis asociados han sido aprobados por un administrador.
                </p>
            </div>

            {{-- Enviado --}}
            <div class="rounded-lg border border-purple-200 dark:border-purple-800/50 bg-purple-50/50 dark:bg-purple-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="purple" size="sm">Enviado</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Los resultados han sido enviados a la veterinaria o propietario del paciente.
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 2: ESTADOS DE ANÁLISIS --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                <flux:icon.clipboard-document-check class="size-5 text-indigo-600 dark:text-indigo-400" />
            </div>
            <flux:heading size="lg">Estados de Análisis</flux:heading>
        </div>

        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
            Cada análisis individual dentro de una muestra sigue este ciclo de vida:
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Pendiente --}}
            <div class="rounded-lg border border-amber-200 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="amber" size="sm">Pendiente</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Análisis asignado al bioquímico pero aún sin resultados capturados.
                </p>
            </div>

            {{-- En revisión --}}
            <div class="rounded-lg border border-blue-200 dark:border-blue-800/50 bg-blue-50/50 dark:bg-blue-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="blue" size="sm">En revisión</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Resultados ingresados por el bioquímico. Esperando revisión y aprobación del administrador.
                </p>
            </div>

            {{-- Aprobado --}}
            <div class="rounded-lg border border-green-200 dark:border-green-800/50 bg-green-50/50 dark:bg-green-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="green" size="sm">Aprobado</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Resultados validados por un administrador. Listo para generar PDF y enviar.
                </p>
            </div>

            {{-- Enviado --}}
            <div class="rounded-lg border border-purple-200 dark:border-purple-800/50 bg-purple-50/50 dark:bg-purple-950/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:badge color="purple" size="sm">Enviado</flux:badge>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    PDF generado y proporcionado al cliente mediante enlace de descarga.
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 3: FLUJO DEL SISTEMA --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                <flux:icon.arrow-path class="size-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <flux:heading size="lg">Flujo del Sistema</flux:heading>
        </div>

        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">
            El estado de la muestra se actualiza <strong>automáticamente</strong> según el estado de sus análisis:
        </p>

        {{-- Flujo de Análisis --}}
        <div class="mb-8">
            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Flujo de cada Análisis:</h4>
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge color="amber" size="sm">Pendiente</flux:badge>
                <flux:icon.arrow-right class="size-4 text-neutral-400" />
                <flux:badge color="blue" size="sm">En revisión</flux:badge>
                <flux:icon.arrow-right class="size-4 text-neutral-400" />
                <flux:badge color="green" size="sm">Aprobado</flux:badge>
                <flux:icon.arrow-right class="size-4 text-neutral-400" />
                <flux:badge color="purple" size="sm">Enviado</flux:badge>
            </div>
        </div>

        {{-- Flujo de Muestra --}}
        <div class="mb-8">
            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Flujo de cada Muestra:</h4>
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge color="amber" size="sm">Pendiente</flux:badge>
                <flux:icon.arrow-right class="size-4 text-neutral-400" />
                <flux:badge color="blue" size="sm">En proceso</flux:badge>
                <flux:icon.arrow-right class="size-4 text-neutral-400" />
                <flux:badge color="green" size="sm">Completado</flux:badge>
                <flux:icon.arrow-right class="size-4 text-neutral-400" />
                <flux:badge color="purple" size="sm">Enviado</flux:badge>
            </div>
        </div>

        {{-- Reglas de sincronización --}}
        <div>
            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Reglas de sincronización automática:</h4>
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:icon.information-circle class="size-5 text-blue-500 mt-0.5 shrink-0" />
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Si <strong>todos</strong> los análisis están <flux:badge color="amber" size="sm">Pendientes</flux:badge>, la muestra permanece como <flux:badge color="amber" size="sm">Pendiente</flux:badge>.
                    </p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:icon.information-circle class="size-5 text-blue-500 mt-0.5 shrink-0" />
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Si <strong>al menos uno</strong> de los análisis avanza (en revisión, aprobado o enviado parcial), la muestra cambia a <flux:badge color="blue" size="sm">En proceso</flux:badge>.
                    </p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:icon.information-circle class="size-5 text-blue-500 mt-0.5 shrink-0" />
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Si <strong>todos</strong> los análisis están <flux:badge color="green" size="sm">Aprobados</flux:badge> (o enviados), la muestra cambia a <flux:badge color="green" size="sm">Completado</flux:badge>.
                    </p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:icon.information-circle class="size-5 text-blue-500 mt-0.5 shrink-0" />
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Si <strong>todos</strong> los análisis están <flux:badge color="purple" size="sm">Enviados</flux:badge>, la muestra cambia a <flux:badge color="purple" size="sm">Enviado</flux:badge>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SECCIÓN 4: MOVIMIENTOS DE INVENTARIO --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30">
                <flux:icon.cube class="size-5 text-orange-600 dark:text-orange-400" />
            </div>
            <flux:heading size="lg">Movimientos de Inventario</flux:heading>
        </div>

        {{-- Tipos de Movimiento --}}
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Tipos de Movimiento:</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 rounded-lg bg-green-50/50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/50">
                    <flux:badge color="green" size="sm">ENTRADA</flux:badge>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Ingreso de insumos al inventario (compra, devolución, etc.).</p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-red-50/50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/50">
                    <flux:badge color="red" size="sm">SALIDA MANUAL</flux:badge>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Salida registrada manualmente (merma, vencimiento, uso extraordinario).</p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800/50">
                    <flux:badge color="blue" size="sm">CONSUMO ANÁLISIS</flux:badge>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Descuento automático cuando se usan insumos en un análisis.</p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/50">
                    <flux:badge color="amber" size="sm">AJUSTE</flux:badge>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Corrección de inventario por discrepancias o conteo físico.</p>
                </div>
            </div>
        </div>

        {{-- Motivos --}}
        <div>
            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Motivos de Movimiento:</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="green" size="sm">Compra</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Adquisición de insumos</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="blue" size="sm">Devolución</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Retorno de insumos</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="red" size="sm">Merma</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pérdida o deterioro</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="red" size="sm">Vencimiento</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Insumo caducado</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="amber" size="sm">Uso Extraordinario</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Uso fuera de análisis</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="blue" size="sm">Consumo Análisis</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Usado en un análisis</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="amber" size="sm">Ajuste Inventario</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Corrección manual</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/50">
                    <flux:badge color="zinc" size="sm">Otro</flux:badge>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Motivo no clasificado</p>
                </div>
            </div>
        </div>
    </div>
</div>
