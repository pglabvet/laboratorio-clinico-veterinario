<div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
    {{-- Título --}}
    <div class="mb-3 border-b border-zinc-200 pb-2 dark:border-zinc-700">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Filtros</h3>
    </div>

    <div class="space-y-4">
        {{-- Filtros de Fecha --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Período:</span>
            
            <button 
                wire:click="$set('rangoFecha', 'hoy')"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $rangoFecha === 'hoy' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
            >
                Hoy
            </button>
            
            <button 
                wire:click="$set('rangoFecha', 'semana')"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $rangoFecha === 'semana' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
            >
                Esta Semana
            </button>
            
            <button 
                wire:click="$set('rangoFecha', 'mes')"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $rangoFecha === 'mes' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
            >
                Este Mes
            </button>
            
            <button 
                wire:click="$set('rangoFecha', 'todo')"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $rangoFecha === 'todo' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
            >
                Todo
            </button>
        </div>

        {{-- Filtro por Sucursal y Botón Limpiar --}}
        <div class="flex flex-wrap items-center gap-3">
            @role('Administrador')
            @if(count($sucursales) > 0)
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Sucursal:</span>
                <select 
                    wire:model.live="sucursalId"
                    class="rounded-lg border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    <option value="">Todas</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @endrole

            @if($rangoFecha !== 'todo' || $sucursalId)
            <flux:button 
                wire:click="limpiarFiltros"
                variant="ghost"
                size="sm"
                icon="x-mark"
            >
                Limpiar
            </flux:button>
            @endif
        </div>
    </div>
</div>
