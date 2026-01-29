<div>
    {{-- Mensajes toast --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Muestras</flux:heading>
        <flux:subheading>Registra y administra las muestras del laboratorio</flux:subheading>
    </div>

    {{-- Barra de acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Búsqueda --}}
        <div class="w-full sm:w-96">
            <flux:input 
                wire:model.live.debounce.300ms="buscar"
                icon="magnifying-glass"
                placeholder="Buscar por código, paciente, propietario..."
                class="w-full"
            />
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

    {{-- Tabla de muestras --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow dark:border-neutral-700 dark:bg-neutral-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('codigo_muestra')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Código</span>
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
                                @php
                                    $estadoClasses = [
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                                        'EN_PROCESO' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                                        'COMPLETADO' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                                        'CANCELADO' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoClasses[$muestra->estado] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/20 dark:text-neutral-400' }}">
                                    {{ $muestra->estado }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $muestra->fecha_recepcion->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón código de barras --}}
                                    <flux:button
                                        wire:click="verCodigoBarras({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="qr-code"
                                        color="purple"
                                        title="Ver código de barras"
                                    />

                                    {{-- Botón ver --}}
                                    <flux:button
                                        wire:click="ver({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        color="neutral"
                                        title="Ver detalles"
                                    />

                                    {{-- Botón eliminar --}}
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $muestra->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        color="red"
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
    <flux:modal wire:model="modalVer" class="w-full max-w-3xl">
        @if($muestraAVer)
            <div class="space-y-6">
                {{-- Encabezado --}}
                <div>
                    <flux:heading size="lg" class="mb-1">Detalles de la Muestra</flux:heading>
                    <flux:subheading>{{ $muestraAVer->codigo_muestra }}</flux:subheading>
                </div>

                {{-- Contenido --}}
                <div class="space-y-6">
                    {{-- Datos del Paciente --}}
                    <div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Datos del Paciente</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Nombre</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->paciente_nombre }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Especie</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->especie->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Raza</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->raza ?: 'No especificada' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Edad</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->edad }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sexo</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->sexo == 'M' ? 'Macho' : 'Hembra' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->color ?: 'No especificado' }}</p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Propietario</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->propietario_nombre }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Datos de la Muestra --}}
                    <div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Datos de la Muestra</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Veterinaria</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->veterinaria->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sucursal</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->sucursal->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Tipo de Muestra</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->tipo_muestra }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Fecha de Recepción</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->fecha_recepcion->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Estado</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->estado }}</p>
                            </div>
                        </div>
                        @if($muestraAVer->observaciones)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Observaciones</label>
                                <p class="text-base text-neutral-900 dark:text-neutral-100">{{ $muestraAVer->observaciones }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Análisis Solicitados --}}
                    <div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Análisis Solicitados ({{ $muestraAVer->analisis->count() }})</h3>
                        @if($muestraAVer->analisis->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($muestraAVer->analisis as $analisis)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                        {{ $analisis->tipoAnalisis->nombre }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">No hay análisis registrados</p>
                        @endif
                    </div>
                </div>

                {{-- Botón cerrar --}}
                <div class="flex justify-end">
                    <flux:button 
                        type="button"
                        wire:click="cerrarModalVer"
                        variant="primary"
                        color="cyan"
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
            <div class="space-y-6">
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
                    <div class="flex flex-col items-center mb-4 bg-white p-4 rounded">
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

            {{-- Script para imprimir --}}
            @script
            <script>
                window.printBarcode = function() {
                    const muestraId = {{ $muestraCodigoBarras->id }};
                    const printUrl = `/muestras/${muestraId}/etiqueta`;
                    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600');
                    
                    if (printWindow) {
                        printWindow.onload = function() {
                            setTimeout(() => {
                                printWindow.print();
                            }, 500);
                        };
                    }
                }
            </script>
            @endscript
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
</div>
