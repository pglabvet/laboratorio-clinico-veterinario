<div x-data="formularioConstructor()" x-init="init()" class="min-h-screen bg-gray-50 dark:bg-zinc-800">
    <div class="px-4">
        <!-- Título -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-zinc-100">
                @if($plantillaId)
                    Editar Plantilla
                @else
                    Nueva Plantilla
                @endif
            </h1>
            <p class="text-gray-600 dark:text-zinc-400">
                @if($plantillaId)
                    Modifica los campos y estructura de la plantilla de formulario
                @else
                    Crea una plantilla personalizada para análisis clínicos veterinarios
                @endif
            </p>
        </div>

        <!-- Mensajes de éxito/error -->
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-2 text-green-800 dark:text-green-300">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-2 text-red-800 dark:text-red-300">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300">
                <div class="flex items-center gap-2 mb-2 font-semibold">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Faltan datos por completar:</span>
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de Datos de la Plantilla -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-zinc-100 mb-4">Datos de la Plantilla</h2>
            
            <div class="grid grid-cols-1 gap-4">
                <flux:input 
                    wire:model.live="nombreFormulario"
                    label="Nombre del Formulario *"
                    placeholder="Ej: Raspado Cutáneo, Química Sanguínea"
                    required
                />
                
                <flux:textarea 
                    wire:model.live="descripcionFormulario"
                    label="Descripción (opcional)"
                    placeholder="Ej: Análisis completo para detección de parásitos externos..."
                    rows="2"
                />

                <flux:select 
                    wire:model.live="tipo_analisis_id"
                    label="Tipo de Análisis"
                    placeholder="Selecciona un tipo de análisis"
                >
                    <option value="">Sin tipo de análisis</option>
                    @foreach($tiposAnalisis as $tipoAnalisis)
                        <option value="{{ $tipoAnalisis->id }}">{{ $tipoAnalisis->nombre }}</option>
                    @endforeach
                </flux:select>

                {{-- Sección de Insumos Requeridos --}}
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-4">
                    <flux:heading size="sm" class="mb-3">Insumos Requeridos</flux:heading>

                    @error('insumos')
                        <div class="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Formulario para agregar nuevo insumo --}}
                    <div class="mb-4 rounded-lg border border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-800">
                        <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Agregar Insumo</p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                            {{-- Categoría --}}
                            <div class="sm:flex-[0.8] min-w-0">
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                    Categoría
                                </label>
                                <flux:select 
                                    wire:model.live="nuevaCategoria"
                                    placeholder="Todas las categorías"
                                    class="text-sm"
                                >
                                    <option value="">Todas las categorías</option>
                                    @foreach($categoriasInsumos as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </flux:select>
                                <div class="mt-1 h-4"></div>
                            </div>

                            {{-- Insumo --}}
                            <div class="sm:flex-[0.8] min-w-0">
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                    Insumo *
                                </label>
                                <flux:select 
                                    wire:model="nuevoInsumo"
                                    placeholder="Seleccionar"
                                    class="text-sm"
                                >
                                    <option value="">Seleccionar insumo</option>
                                    @php
                                        $insumosFiltrados = $nuevaCategoria 
                                            ? $insumosDisponibles->where('categoria_id', $nuevaCategoria)
                                            : $insumosDisponibles;
                                    @endphp
                                    @foreach($insumosFiltrados as $ins)
                                        <option value="{{ $ins->id }}">
                                            {{ $ins->nombre }} ({{ $ins->unidadMedida->abreviatura }})
                                        </option>
                                    @endforeach
                                </flux:select>
                                <div class="mt-1 h-4">
                                    @error('nuevoInsumo')
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Cantidad --}}
                            <div class="w-full sm:w-48">
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                    Cantidad *
                                </label>
                                <flux:input 
                                    wire:model="nuevaCantidad"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="1"
                                    class="text-sm"
                                />
                                <div class="mt-1 min-h-4">
                                    @error('nuevaCantidad')
                                        <span class="text-xs text-red-500 break-words">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Botón agregar --}}
                            <div class="sm:pt-5">
                                <flux:button 
                                    wire:click="agregarInsumo"
                                    variant="primary"
                                    icon="plus">
                                    Agregar
                                </flux:button>
                                <div class="mt-1 h-4"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Lista de insumos agregados --}}
                    @if(empty($insumos))
                        <div class="rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-600 p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                No se han agregado insumos
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-500">
                                Esta plantilla no consumirá inventario
                            </p>
                        </div>
                    @else
                        <div>
                            <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Insumos agregados ({{ count($insumos) }})</p>
                            <div class="space-y-2">
                                @foreach($insumos as $index => $insumo)
                                    @php
                                        $insumoData = $insumosDisponibles->firstWhere('id', $insumo['insumo_id']);
                                    @endphp
                                    <div class="flex items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                                        {{-- Icono --}}
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                        
                                        {{-- Información del insumo --}}
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ $insumoData->nombre ?? 'Insumo no encontrado' }}
                                            </p>
                                            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                                                <span class="flex items-center gap-1">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                    {{ $insumoData->categoria->nombre ?? 'Sin categoría' }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    Cantidad: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $insumo['cantidad_requerida'] }} {{ $insumoData->unidadMedida->abreviatura ?? '' }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Botón eliminar --}}
                                        <flux:button 
                                            wire:click="eliminarInsumo({{ $index }})"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square>
                                        </flux:button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Layout de 3 columnas -->
        <div class="grid grid-cols-12 gap-6 pb-20">
            
            <!-- Panel Izquierdo: Componentes Disponibles -->
            <div class="col-span-2">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-zinc-100 mb-4 flex items-center">
                        <i class="fas fa-puzzle-piece mr-2 text-blue-600"></i>
                        Componentes
                    </h3>
                    <div class="space-y-2">
                        @foreach($tiposComponentes as $tipo => $nombre)
                        <button 
                            wire:click="agregarComponente('{{ $tipo }}')"
                            class="w-full text-left px-4 py-3 bg-gray-50 dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg border border-gray-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all group">
                            <span class="text-sm font-medium text-gray-700 dark:text-zinc-300 group-hover:text-blue-700 dark:group-hover:text-blue-400">{{ $nombre }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Panel Central: Vista Previa -->
            <div class="col-span-7">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-zinc-100 mb-4 flex items-center">
                        <i class="fas fa-eye mr-2 text-blue-600"></i>
                        Vista Previa
                    </h3>
                    
                    <div class="space-y-4" x-ref="contenedorComponentes">
                        @if(count($componentes) > 0)
                            @foreach($componentes as $index => $componente)
                            <div 
                                wire:key="componente-{{ $componente['id'] }}"
                                @click="seleccionar('{{ $componente['id'] }}')"
                                class="border-2 rounded-lg p-4 cursor-pointer transition-all relative"
                                :class="@this.componenteSeleccionado === '{{ $componente['id'] }}' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600'">
                                
                                <!-- Botón eliminar -->
                                <div class="absolute top-2 right-2">
                                    <flux:button 
                                        wire:click.stop="eliminarComponente('{{ $componente['id'] }}')"
                                        variant="ghost"
                                        size="xs"
                                        icon="trash"
                                        title="Eliminar">
                                    </flux:button>
                                </div>

                                <!-- Vista previa del componente -->
                                @include('livewire.constructor.preview.' . $componente['tipo'], ['props' => $componente['propiedades'], 'componente' => $componente, 'index' => $index])
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-12 text-gray-400 dark:text-zinc-500">
                                <i class="fas fa-inbox text-5xl mb-3"></i>
                                <p>No hay componentes agregados</p>
                                <p class="text-sm">Selecciona un componente del panel izquierdo</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Propiedades -->
            <div class="col-span-3">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-zinc-100 mb-4 flex items-center">
                        <i class="fas fa-sliders-h mr-2 text-blue-600"></i>
                        Propiedades
                    </h3>
                    
                    @if($componenteSeleccionado)
                        @php
                            $componente = collect($componentes)->firstWhere('id', $componenteSeleccionado);
                            // Buscar el índice dinámicamente en cada render
                            $indiceComponente = null;
                            foreach ($componentes as $idx => $comp) {
                                if ($comp['id'] === $componenteSeleccionado) {
                                    $indiceComponente = $idx;
                                    break;
                                }
                            }
                        @endphp
                        
                        @if($componente && $indiceComponente !== null)
                            <div wire:key="propiedades-{{ $componente['id'] }}">
                                @include('livewire.constructor.propiedades.' . $componente['tipo'], [
                                    'props' => $componente['propiedades'], 
                                    'componente' => $componente,
                                    'componenteId' => $componente['id'],
                                    'componentes' => $componentes,
                                    'indiceComponente' => $indiceComponente
                                ])
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 text-gray-400 dark:text-zinc-500">
                            <i class="fas fa-mouse-pointer text-4xl mb-3"></i>
                            <p class="text-sm">Selecciona un componente para editar sus propiedades</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Barra Sticky inferior: Botón Guardar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] px-6 py-3 z-50">
        <div class="flex items-center justify-between max-w-full">
            <div class="flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                <span>{{ count($componentes) }} componente(s)</span>
                <span>·</span>
                <span>{{ count($insumos) }} insumo(s)</span>
            </div>
            <flux:button 
                wire:click="guardarFormulario"
                variant="primary"
                icon="check"
                size="sm">
                {{ $plantillaId ? 'Actualizar' : 'Guardar' }} Plantilla
            </flux:button>
        </div>
    </div>

    <script>
        function formularioConstructor() {
            return {
                init() {
                    // Sincronizar con Livewire
                    this.$watch('$wire.componenteSeleccionado', () => {
                        // Aqui puedes agregar lógica adicional si es necesaria
                    });
                },
                
                seleccionar(id) {
                    this.$wire.seleccionarComponente(id);
                },
                
                guardar() {
                    if (!this.$wire.nombreFormulario || this.$wire.nombreFormulario.trim() === '') {
                        alert('Por favor ingresa un nombre para la plantilla');
                        return;
                    }
                    
                    if (this.$wire.componentes.length === 0) {
                        alert('Agrega al menos un componente al formulario');
                        return;
                    }
                    
                    this.$wire.guardarFormulario();
                }
            }
        }
    </script>

</div>
