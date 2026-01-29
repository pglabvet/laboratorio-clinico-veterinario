<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('mensaje')" />
    <x-toast type="error" :message="session('error')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Gestión de Insumos</flux:heading>
        <flux:subheading>Administra los insumos utilizados en los análisis del laboratorio</flux:subheading>
    </div>

    {{-- Barra de acciones y filtros --}}
    <div class="mb-6 flex flex-col gap-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{-- Búsqueda --}}
            <div class="w-full sm:w-96">
                <flux:input 
                    wire:model.live.debounce.300ms="buscar"
                    icon="magnifying-glass"
                    placeholder="Buscar insumos..."
                    class="w-full"
                />
            </div>

            {{-- Botón crear --}}
            <flux:button 
                wire:click="crear"
                icon="plus"
                variant="primary"
            >
                Nuevo Insumo
            </flux:button>
        </div>

        {{-- Filtros adicionales --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="w-full sm:w-64">
                <flux:select wire:model.live="filtroSucursal" placeholder="Filtrar por sucursal">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>

            @if($filtroSucursal)
                <flux:checkbox wire:model.live="mostrarSoloStockBajo" label="Solo stock bajo" />
            @endif
        </div>
    </div>

    {{-- Tabla de insumos --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow dark:border-neutral-700 dark:bg-neutral-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('nombre')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Nombre</span>
                                @if($sortBy === 'nombre')
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
                            Unidad de Medida
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Categoría
                        </th>
                        @if($filtroSucursal)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                                Stock Actual
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                                Stock Mínimo
                            </th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($insumos as $insumo)
                        @php
                            $inventario = $filtroSucursal 
                                ? $insumo->inventarios->firstWhere('sucursal_id', $filtroSucursal)
                                : null;
                            $stockBajo = $inventario && $inventario->tieneStockBajo();
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 {{ $stockBajo ? 'bg-red-50 dark:bg-red-900/10' : '' }}" wire:key="insumo-{{ $insumo->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                <div class="flex items-center gap-2">
                                    @if($stockBajo)
                                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    {{ $insumo->nombre }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $insumo->unidadMedida->abreviatura }}
                                <span class="text-xs text-neutral-500">({{ $insumo->unidadMedida->nombre }})</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                @if($insumo->categoria)
                                    {{ $insumo->categoria->nombre }}
                                @else
                                    <span class="text-neutral-400 italic">Sin categoría</span>
                                @endif
                            </td>
                            @if($filtroSucursal && $inventario)
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                    <span class="font-medium {{ $stockBajo ? 'text-red-700 dark:text-red-400' : '' }}">
                                        {{ number_format($inventario->stock_actual, 2) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                    {{ number_format($inventario->stock_minimo, 2) }}
                                </td>
                            @endif
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <button type="button" wire:click="confirmarCambiarEstado({{ $insumo->id }})" class="cursor-pointer group outline-none focus:outline-none">
                                    <div class="pointer-events-none">
                                        <flux:switch 
                                            :checked="$insumo->estado"
                                            wire:key="switch-{{ $insumo->id }}-{{ $insumo->estado ? 'active' : 'inactive' }}"
                                        />
                                    </div>
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @if($stockBajo)
                                        <flux:badge color="red" size="sm">Stock bajo</flux:badge>
                                    @endif
                                    
                                    {{-- Botón editar --}}
                                    <flux:button
                                        wire:click="editar({{ $insumo->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        color="cyan"
                                        title="Editar"
                                    />
                                    
                                    {{-- Botón eliminar --}}
                                    <flux:button
                                        wire:click="confirmarEliminar({{ $insumo->id }})"
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
                            <td colspan="{{ $filtroSucursal ? '7' : '5' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    <flux:heading size="lg" class="mb-1">No hay insumos</flux:heading>
                                    <flux:subheading>
                                        @if ($buscar)
                                            No se encontraron insumos con el término "{{ $buscar }}"
                                        @elseif ($mostrarSoloStockBajo)
                                            No hay insumos con stock bajo en la sucursal seleccionada
                                        @else
                                            Comienza creando un nuevo insumo
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
        @if($insumos->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-3 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $insumos->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Crear/Editar --}}
    <flux:modal wire:model="modalAbierto" class="md:w-[600px]">
        <form wire:submit.prevent="guardar">
            <div>
                <flux:heading size="lg">{{ $modoEdicion ? 'Editar' : 'Nuevo' }} Insumo</flux:heading>
                <flux:subheading class="mb-4">
                    {{ $modoEdicion ? 'Modifica los datos del insumo' : 'Completa los datos para crear un nuevo insumo' }}
                </flux:subheading>
            </div>

            {{-- Datos básicos --}}
            <div class="space-y-4 mb-6">
                <flux:input.group label="Nombre del insumo" :error="$errors->first('nombre')" required>
                    <flux:input wire:model="nombre" placeholder="Ej: Alcohol etílico" />
                </flux:input.group>

                <flux:input.group label="Categoría" :error="$errors->first('categoria_id')">
                    <flux:select wire:model="categoria_id" placeholder="Selecciona una categoría (opcional)">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </flux:select>
                </flux:input.group>

                <flux:input.group label="Unidad de medida" :error="$errors->first('unidad_medida_id')" required>
                    <flux:select wire:model="unidad_medida_id" placeholder="Selecciona una unidad">
                        @foreach($unidadesMedida as $unidad)
                            <option value="{{ $unidad->id }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                        @endforeach
                    </flux:select>
                </flux:input.group>

                <flux:checkbox wire:model="estado" label="Activo" />
            </div>

            {{-- Stock mínimo por sucursal --}}
            <div class="border-t border-neutral-200 pt-4 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-3">Stock Mínimo por Sucursal</flux:heading>
                <flux:subheading class="mb-4 text-xs">Define el stock mínimo para cada sucursal (el stock actual inicia en 0)</flux:subheading>

                <div class="space-y-3 max-h-60 overflow-y-auto">
                    @foreach($inventarios as $sucursalId => $inventario)
                        <div class="flex items-center gap-3 rounded-lg bg-neutral-50 p-3 dark:bg-neutral-800">
                            <div class="flex-1">
                                <flux:subheading class="text-sm font-medium">
                                    {{ $inventario['sucursal_nombre'] }}
                                </flux:subheading>
                            </div>
                            <div class="w-32">
                                <flux:input 
                                    wire:model="inventarios.{{ $sucursalId }}.stock_minimo" 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                        @error("inventarios.{$sucursalId}.stock_minimo")
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2 mt-6">
                <flux:spacer />
                <flux:button type="button" wire:click="cerrarModal" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Cambiar Estado --}}
    <flux:modal wire:model="modalCambiarEstado" class="md:w-96">
        <div>
            <flux:heading size="lg">Cambiar Estado</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas {{ $estadoActual ? 'desactivar' : 'activar' }} este insumo?
            </flux:subheading>
        </div>

        @if($estadoActual && $insumoACambiar)
            <div class="mb-4 rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Nota:</strong> Al desactivar este insumo, no podrá ser utilizado en nuevos análisis.
                </p>
            </div>
        @endif

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalCambiarEstado" variant="ghost">Cancelar</flux:button>
            <flux:button type="button" wire:click="cambiarEstado" variant="primary">
                Confirmar
            </flux:button>
        </div>
    </flux:modal>

    {{-- Modal Eliminar --}}
    <flux:modal wire:model="modalEliminar" class="md:w-96">
        <div>
            <flux:heading size="lg">Eliminar Insumo</flux:heading>
            <flux:subheading class="mb-4">
                ¿Estás seguro de que deseas eliminar este insumo?
            </flux:subheading>
        </div>

        <div class="mb-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-200">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminará el insumo y todos sus registros de inventario.
            </p>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" wire:click="cerrarModalEliminar" variant="ghost">Cancelar</flux:button>
            <flux:button type="button" wire:click="eliminar" variant="danger">
                Eliminar
            </flux:button>
        </div>
    </flux:modal>
</div>
