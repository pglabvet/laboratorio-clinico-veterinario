<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />
    <x-toast type="warning" :message="session('warning')" />

    {{-- Header de la página --}}
    <div class="mb-4 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Gestión de Muestras</flux:heading>
            <flux:subheading>Registra y administra las muestras del laboratorio</flux:subheading>
        </div>
        {{-- Botón crear --}}
        <flux:button 
            wire:click="crear"
            icon="plus"
            variant="primary"
        >
            Registrar Muestra
        </flux:button>
    </div>

    {{-- Bloque de filtros --}}
    <div class="mb-4 rounded-lg border border-neutral-200 bg-neutral-50 p-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        {{-- Contenido de filtros --}}
        <div class="space-y-3">
            {{-- Fila 1: Búsqueda y fechas --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Búsqueda --}}
                <div class="w-full sm:flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="buscar"
                        icon="magnifying-glass"
                        placeholder="Buscar por código, paciente, propietario..."
                        class="w-full"
                    />
                </div>

                {{-- Fecha desde --}}
                <div class="w-full sm:w-auto">
                    <flux:input 
                        type="date"
                        wire:model.live="filtroFechaDesde"
                        label="Desde"
                    />
                </div>

                {{-- Fecha hasta --}}
                <div class="w-full sm:w-auto">
                    <flux:input 
                        type="date"
                        wire:model.live="filtroFechaHasta"
                        label="Hasta"
                    />
                </div>

                {{-- Dropdown de filtros rápidos de fecha --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="calendar-days" icon-trailing="chevron-down">
                            {{ $filtroPeriodo ?: 'Período' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="filtrarHoy" icon="sun">
                                Hoy
                            </flux:menu.item>
                            <flux:menu.item wire:click="filtrarAyer" icon="arrow-uturn-left">
                                Ayer
                            </flux:menu.item>
                            <flux:menu.item wire:click="filtrarUltimos7Dias" icon="calendar">
                                Últimos 7 días
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item wire:click="filtrarEstaSemana" icon="calendar-days">
                                Esta semana
                            </flux:menu.item>
                            <flux:menu.item wire:click="filtrarEsteMes" icon="calendar-days">
                                Este mes
                            </flux:menu.item>
                            <flux:menu.item wire:click="filtrarAnioActual" icon="calendar-days">
                                Año actual
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>

            {{-- Fila 2: Filtros por categorías --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Filtro por estado --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="funnel" icon-trailing="chevron-down">
                            {{ $filtroEstado ?: 'Estado' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroEstado', '')" icon="bars-3">
                                Todos los estados
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item wire:click="$set('filtroEstado', 'Pendiente')" icon="clock">
                                Pendiente
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroEstado', 'En proceso')" icon="arrow-path">
                                En proceso
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroEstado', 'Completado')" icon="check-circle">
                                Completado
                            </flux:menu.item>
                            <flux:menu.item wire:click="$set('filtroEstado', 'Enviado')" icon="paper-airplane">
                                Enviado
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Filtro por especie --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="heart" icon-trailing="chevron-down">
                            {{ $filtroEspecie ? $especies->firstWhere('id', $filtroEspecie)?->nombre : 'Especie' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroEspecie', '')" icon="bars-3">
                                Todas las especies
                            </flux:menu.item>
                            <flux:menu.separator />
                            @foreach($especies as $especie)
                                <flux:menu.item wire:click="$set('filtroEspecie', '{{ $especie->id }}')" icon="heart">
                                    {{ $especie->nombre }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Filtro por veterinaria --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="building-office" icon-trailing="chevron-down">
                            {{ $filtroVeterinaria ? Str::limit($veterinarias->firstWhere('id', $filtroVeterinaria)?->nombre, 15) : 'Veterinaria' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroVeterinaria', '')" icon="bars-3">
                                Todas las veterinarias
                            </flux:menu.item>
                            <flux:menu.separator />
                            @foreach($veterinarias as $veterinaria)
                                <flux:menu.item wire:click="$set('filtroVeterinaria', '{{ $veterinaria->id }}')" icon="building-office">
                                    {{ $veterinaria->nombre }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Filtro por sucursal --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="building-storefront" icon-trailing="chevron-down">
                            {{ $filtroSucursal ? ($sucursales->firstWhere('id', $filtroSucursal)?->nombre ?? 'Sucursales') : 'Sucursales' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroSucursal', '')" icon="bars-3">
                                Todas las sucursales
                            </flux:menu.item>
                            <flux:menu.separator />
                            @foreach($sucursales as $sucursal)
                                <flux:menu.item wire:click="$set('filtroSucursal', '{{ $sucursal->id }}')" icon="building-storefront">
                                    Sucursal {{ $sucursal->nombre }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Botón limpiar filtros --}}
                <div class="w-full sm:w-auto">
                    <flux:button 
                        wire:click="limpiarFiltros" 
                        variant="outline" 
                        icon="x-mark"
                    >
                        Limpiar
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de muestras --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('codigo_muestra')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>CODIGO</span>
                                @if($sortBy === 'codigo_muestra')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Paciente
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Especie
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Veterinaria
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Análisis
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Fecha Recepción
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($muestras as $muestra)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="muestra-{{ $muestra->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $muestra->codigo_muestra }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $muestra->paciente_nombre }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $muestra->propietario_nombre }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $muestra->especie->nombre ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $muestra->veterinaria->nombre ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $muestra->analisis_count }} {{ $muestra->analisis_count == 1 ? 'análisis' : 'análisis' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge size="sm" color="{{ $muestra->getColorEstado() }}" inset="top bottom">
                                    {{ $muestra->estado }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                <div class="flex flex-col">
                                    <span>{{ $muestra->fecha_recepcion->format('d/m/Y') }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $muestra->created_at->format('H:i:s') }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón ver análisis --}}
                                    <flux:button
                                        wire:click="verAnalisis({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="envelope-open"
                                        title="Enviar resultados"
                                    />

                                    {{-- Botón código de barras --}}
                                    <flux:button
                                        wire:click="verCodigoBarras({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="qr-code"
                                        title="Ver código de barras"
                                    />

                                    {{-- Botón ver --}}
                                    <flux:button
                                        wire:click="ver({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        title="Ver detalles"
                                    />

                                    {{-- Botón eliminar --}}
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        title="Eliminar"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay muestras</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron muestras con el término "{{ $buscar }}"
                                        @else
                                            Comienza registrando tu primera muestra
                                        @endif
                                    </flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($muestras->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $muestras->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para registrar muestra --}}
    <flux:modal wire:model="modalAbierto" class="w-full max-w-5xl">
        <form wire:submit.prevent="guardar">
            <flux:heading size="lg" class="mb-2">
                {{ $modoEdicion ? 'Editar Muestra' : 'Registrar Nueva Muestra' }}
            </flux:heading>
            <flux:subheading class="mb-6">
                Complete todos los datos de la muestra y del paciente
            </flux:subheading>

            <div class="space-y-6">
                {{-- Datos del Paciente --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Datos del Paciente</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input 
                            wire:model="paciente_nombre"
                            label="Nombre del Paciente"
                            placeholder="Ej: Max, Luna, Rocky"
                            required
                            :error="$errors->first('paciente_nombre')"
                        />

                        <flux:select 
                            wire:model="especie_id"
                            label="Especie"
                            required
                            :error="$errors->first('especie_id')"
                        >
                            <option value="">Seleccione una especie</option>
                            @foreach($especies as $especie)
                                <option value="{{ $especie->id }}">{{ $especie->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input 
                            wire:model="raza"
                            label="Raza"
                            placeholder="Ej: Labrador, Persa, Mestizo"
                        />

                        <flux:input 
                            wire:model="edad"
                            label="Edad"
                            placeholder="Ej: 2 años, 6 meses"
                            required
                            :error="$errors->first('edad')"
                        />

                        <flux:select 
                            wire:model="sexo"
                            label="Sexo"
                            required
                            :error="$errors->first('sexo')"
                        >
                            <option value="M">Macho</option>
                            <option value="H">Hembra</option>
                        </flux:select>

                        <flux:input 
                            wire:model="color"
                            label="Color"
                            placeholder="Ej: Negro, Blanco, Atigrado"
                        />

                        <div class="md:col-span-2">
                            <flux:input 
                                wire:model="propietario_nombre"
                                label="Nombre del Propietario"
                                placeholder="Nombre completo del dueño"
                                required
                                :error="$errors->first('propietario_nombre')"
                            />
                        </div>
                    </div>
                </div>

                {{-- Datos de la Muestra --}}
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Datos de la Muestra</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:select 
                            wire:model="veterinaria_id"
                            label="Veterinaria Remitente"
                            required
                            :error="$errors->first('veterinaria_id')"
                        >
                            <option value="">Seleccione una veterinaria</option>
                            @foreach($veterinarias as $veterinaria)
                                <option value="{{ $veterinaria->id }}">{{ $veterinaria->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select 
                            wire:model="sucursal_id"
                            label="Sucursal"
                            required
                            :error="$errors->first('sucursal_id')"
                        >
                            <option value="">Seleccione una sucursal</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input 
                            wire:model="tipo_muestra"
                            label="Tipo de Muestra"
                            placeholder="Ej: Sangre, Orina, Heces"
                            required
                            :error="$errors->first('tipo_muestra')"
                        />

                        <flux:input 
                            wire:model="fecha_recepcion"
                            label="Fecha de Recepción"
                            type="date"
                            required
                            :error="$errors->first('fecha_recepcion')"
                        />

                        <div class="md:col-span-2">
                            <flux:textarea 
                                wire:model="observaciones"
                                label="Observaciones"
                                placeholder="Información adicional sobre la muestra..."
                                rows="3"
                            />
                        </div>
                    </div>
                </div>

                {{-- Tipos de Análisis --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                        Tipos de Análisis a Realizar <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-4 bg-neutral-50 dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        @foreach($tiposAnalisis as $tipoAnalisis)
                            <flux:checkbox 
                                wire:model="tipos_analisis_seleccionados"
                                :value="$tipoAnalisis->id"
                                :label="$tipoAnalisis->nombre"
                            />
                        @endforeach
                    </div>
                    @error('tipos_analisis_seleccionados')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Botones del modal --}}
            <div class="mt-8 flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cerrarModal"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="primary"
                >
                    {{ $modoEdicion ? 'Actualizar' : 'Registrar Muestra' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal para ver detalles de la muestra --}}
    <flux:modal wire:model="modalVer" class="w-full max-w-2xl">
        @if($muestraAVer)
            @php
                $estadoBadge = [
                    'Pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                    'En proceso' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                    'Completado' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                    'Enviado' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
                ];
            @endphp
            <div class="space-y-5">
                {{-- Encabezado: Nombre paciente + Badge estado --}}
                <div class="pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->paciente_nombre }}</h2>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoBadge[$muestraAVer->estado] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/20 dark:text-neutral-400' }}">
                                {{ $muestraAVer->estado }}
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Código: <span class="font-mono font-medium">{{ $muestraAVer->codigo_muestra }}</span></p>
                </div>

                {{-- Datos del Paciente --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Propietario</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->propietario_nombre }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Especie / Raza</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->especie->nombre ?? 'N/A' }} <span class="text-neutral-400">—</span> {{ $muestraAVer->raza ?: 'Sin raza' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-pink-500 dark:text-pink-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Edad / Sexo / Color</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->edad }} <span class="text-neutral-400">·</span> {{ $muestraAVer->sexo == 'M' ? 'Macho' : 'Hembra' }} <span class="text-neutral-400">·</span> {{ $muestraAVer->color ?: 'Sin color' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Datos de la Muestra --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700 overflow-hidden bg-white dark:bg-neutral-800/50">
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Veterinaria</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->veterinaria->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Sucursal</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->sucursal->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-cyan-500 dark:text-cyan-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2.47 2.47a2.25 2.25 0 0 1-1.59.659H9.06a2.25 2.25 0 0 1-1.591-.659L5 14.5m14 0-3.375-3.375M5 14.5l3.375-3.375" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Tipo de Muestra</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->tipo_muestra }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-violet-500 dark:text-violet-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Fecha de Recepción</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->fecha_recepcion->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($muestraAVer->observaciones)
                        <div class="flex items-start gap-3 px-4 py-3.5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                            <svg class="w-5 h-5 text-orange-500 dark:text-orange-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-0.5">Observaciones</p>
                                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->observaciones }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Análisis Solicitados --}}
                @if($muestraAVer->analisis->count() > 0)
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">Análisis Solicitados ({{ $muestraAVer->analisis->count() }})</h3>
                        <div class="grid gap-2.5">
                            @foreach($muestraAVer->analisis as $analisis)
                                <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-100 dark:border-blue-900/30">
                                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2.47 2.47a2.25 2.25 0 0 1-1.59.659H9.06a2.25 2.25 0 0 1-1.591-.659L5 14.5m14 0-3.375-3.375M5 14.5l3.375-3.375" /></svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center rounded-full bg-blue-600 px-2.5 py-0.5 text-xs font-semibold text-white">
                                                {{ $analisis->tipoAnalisis->nombre ?? 'Sin tipo' }}
                                            </span>
                                            <span class="text-neutral-400 dark:text-neutral-500">·</span>
                                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $analisis->plantillaFormulario->nombre ?? 'Sin plantilla' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Botón cerrar --}}
                <div class="flex justify-end pt-2">
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalVer"
                        variant="primary"
                    >
                        Cerrar
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Modal de código de barras --}}
    <flux:modal wire:model="modalCodigoBarras" class="w-full max-w-2xl">
        @if($muestraCodigoBarras)
            <div class="space-y-6" 
                 wire:key="barcode-modal-{{ $muestraCodigoBarras->id }}-{{ $muestraCodigoBarras->codigo_muestra }}"
                 x-data="{ 
                    muestraId: {{ $muestraCodigoBarras->id }},
                    previousPrintWindow: null,
                    printBarcode() {
                        if (this.previousPrintWindow && !this.previousPrintWindow.closed) {
                            this.previousPrintWindow.close();
                        }
                        const timestamp = new Date().getTime();
                        const printUrl = `/muestras/${this.muestraId}/etiqueta?t=${timestamp}`;
                        const windowName = `etiqueta_${this.muestraId}_${timestamp}`;
                        this.previousPrintWindow = window.open(printUrl, windowName, 'width=800,height=600');
                        if (this.previousPrintWindow) {
                            this.previousPrintWindow.onload = function() {
                                setTimeout(() => {
                                    this.print();
                                }, 500);
                            };
                        }
                    }
                 }">
                {{-- Encabezado --}}
                <div class="text-center">
                    <flux:heading size="lg" class="mb-1">Código de Barras Generado</flux:heading>
                    <flux:subheading>Muestra registrada exitosamente</flux:subheading>
                </div>

                {{-- Tarjeta con código de barras --}}
                <div id="barcode-print-area" class="bg-white dark:bg-neutral-900 border-2 border-neutral-200 dark:border-neutral-700 rounded-lg p-6">
                    {{-- Logo y título --}}
                    <div class="text-center mb-4">
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">LABORATORIO CLÍNICO VETERINARIO</h2>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $muestraCodigoBarras->sucursal->nombre ?? 'Sucursal Principal' }}</p>
                    </div>

                    {{-- Código de barras --}}
                    <div class="flex flex-col items-center mb-4 bg-white p-4 rounded" wire:key="barcode-{{ $muestraCodigoBarras->codigo_muestra }}">
                        <div class="mb-2">
                            {!! $muestraCodigoBarras->generarCodigoBarras() !!}
                        </div>
                        <p class="text-lg font-mono font-semibold text-black">
                            {{ $muestraCodigoBarras->codigo_muestra }}
                        </p>
                    </div>

                    {{-- Información de la muestra --}}
                    <div class="grid grid-cols-2 gap-3 text-sm border-t border-neutral-200 dark:border-neutral-700 pt-4">
                        <div>
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">Paciente:</span>
                            <span class="text-neutral-900 dark:text-neutral-100">{{ $muestraCodigoBarras->paciente_nombre }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">Especie:</span>
                            <span class="text-neutral-900 dark:text-neutral-100">{{ $muestraCodigoBarras->especie->nombre ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">Propietario:</span>
                            <span class="text-neutral-900 dark:text-neutral-100">{{ $muestraCodigoBarras->propietario_nombre }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">Fecha:</span>
                            <span class="text-neutral-900 dark:text-neutral-100">{{ $muestraCodigoBarras->fecha_recepcion->format('d/m/Y') }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">Veterinaria:</span>
                            <span class="text-neutral-900 dark:text-neutral-100">{{ $muestraCodigoBarras->veterinaria->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300">Tipo de Muestra:</span>
                            <span class="text-neutral-900 dark:text-neutral-100">{{ $muestraCodigoBarras->tipo_muestra }}</span>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-3">
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalCodigoBarras"
                        variant="ghost"
                    >
                        Cerrar
                    </flux:button>
                    <flux:button 
                        type="button"
                        x-on:click="printBarcode()"
                        variant="primary"
                    >
                        <x-lucide-printer class="size-4" />
                        Imprimir
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Modal de confirmación para eliminar --}}
    <flux:modal wire:model="modalEliminar" class="w-full max-w-md">
        <div class="space-y-6">
            {{-- Ícono de advertencia --}}
            <div class="flex justify-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
            </div>

            {{-- Título y mensaje --}}
            <div class="text-center">
                <flux:heading size="lg" class="mb-2">Eliminar Muestra</flux:heading>
                <flux:subheading>
                    ¿Estás seguro de que deseas eliminar esta muestra? Esta acción no se puede deshacer y se eliminarán todos los análisis pendientes asociados.
                </flux:subheading>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarEliminar"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button"
                    wire:click="eliminar"
                    variant="danger"
                    icon="trash"
                >
                    Eliminar
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal para ver análisis de la muestra --}}
    <flux:modal wire:model="modalAnalisis" class="w-full max-w-4xl">
        @if($muestraAnalisis)
            <div class="space-y-6">
                {{-- Encabezado --}}
                <div>
                    <flux:heading size="lg" class="mb-2">Análisis de la Muestra</flux:heading>
                    <div class="flex items-center gap-3 flex-wrap">
                        <flux:badge size="lg" color="indigo" icon="qr-code" class="font-mono font-bold">
                            {{ $muestraAnalisis->codigo_muestra }}
                        </flux:badge>
                        <span class="text-neutral-500 dark:text-neutral-400">—</span>
                        <span class="text-base font-medium text-neutral-700 dark:text-neutral-300">{{ $muestraAnalisis->paciente_nombre }}</span>
                    </div>
                </div>

                {{-- Información general --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-neutral-50 dark:bg-neutral-900 rounded-lg">
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Especie</label>
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAnalisis->especie->nombre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Veterinaria</label>
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAnalisis->veterinaria->nombre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Fecha Recepción</label>
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $muestraAnalisis->fecha_recepcion->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400">Estado Muestra</label>
                        <flux:badge size="sm" color="{{ $muestraAnalisis->getColorEstado() }}" inset="top bottom">
                            {{ $muestraAnalisis->estado }}
                        </flux:badge>
                    </div>
                </div>

                {{-- Tabla de análisis --}}
                <div class="overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-700">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-900">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                                    Tipo de Análisis
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                                    Estado
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                            @forelse($muestraAnalisis->analisis as $analisis)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $analisis->tipoAnalisis->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <flux:badge size="sm" color="{{ $analisis->getColorEstado() }}" inset="top bottom">
                                            {{ $analisis->estado }}
                                        </flux:badge>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <flux:button
                                            wire:click="enviarWhatsApp({{ $analisis->id }})"
                                            variant="ghost"           
                                            icon="paper-airplane"
                                            title="{{ $analisis->puedeSerEnviado() ? 'Enviar por WhatsApp' : 'Solo se pueden enviar análisis aprobados' }}"
                                            :disabled="!$analisis->puedeSerEnviado()"
                                        >
                                            Enviar
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                        No hay análisis registrados para esta muestra
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Resumen de estados --}}
                @if($muestraAnalisis->analisis->count() > 0)
                    <div class="flex flex-wrap gap-3">
                        @php
                            $conteoEstados = $muestraAnalisis->analisis->groupBy('estado')->map->count();
                        @endphp
                        @foreach($conteoEstados as $estado => $cantidad)
                            <div class="flex items-center gap-2 px-3 py-2 bg-neutral-100 dark:bg-neutral-800 rounded-lg">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ ucfirst(str_replace('_', ' ', $estado)) }}:</span>
                                <span class="text-sm font-bold text-neutral-900 dark:text-neutral-100">{{ $cantidad }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Botones --}}
                <div class="flex justify-end gap-3">
                    <flux:button 
                        type="button"
                        wire:click="enviarTodoWhatsApp"
                        variant="primary"
                        icon="paper-airplane"
                        :disabled="!$muestraAnalisis->puedeEnviarTodosAnalisis()"
                        title="{{ $muestraAnalisis->puedeEnviarTodosAnalisis() ? 'Enviar todos los análisis' : 'Todos los análisis deben estar aprobados' }}"
                    >
                        Enviar todo
                    </flux:button>
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalAnalisis"
                        variant="ghost"
                    >
                        Cerrar
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Script para abrir WhatsApp --}}
    @script
    <script>
        $wire.on('abrir-whatsapp', ({ url }) => {
            window.open(url, '_blank');
        });
    </script>
    @endscript
</div>
