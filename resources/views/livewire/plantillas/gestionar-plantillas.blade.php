<div x-data="formularioConstructor()" x-init="init()" class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-6">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <a href="{{ route('plantillas.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Volver a Plantillas
            </a>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <!-- TÃ­tulo -->
            <h1 class="text-2xl font-bold text-gray-800 mb-4">
                @if($plantillaId)
                    <i class="fas fa-edit mr-2 text-blue-600"></i>
                    Editar Plantilla
                @else
                    <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
                    Nueva Plantilla
                @endif
            </h1>

            <!-- Mensajes de Ã©xito/error -->
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2 text-green-800">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            
            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2 text-red-800">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @error('nombreFormulario')
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2 text-red-800">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $message }}</span>
                </div>
            @enderror

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

                <div>
                    <flux:button 
                        wire:click="guardarFormulario"
                        variant="primary"
                        icon="check">
                        {{ $plantillaId ? 'Actualizar' : 'Guardar' }} Plantilla
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Layout de 3 columnas -->
        <div class="grid grid-cols-12 gap-6">
            
            <!-- Panel Izquierdo: Componentes Disponibles -->
            <div class="col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-puzzle-piece mr-2 text-blue-600"></i>
                        Componentes
                    </h3>
                    <div class="space-y-2">
                        @foreach($tiposComponentes as $tipo => $nombre)
                        <button 
                            wire:click="agregarComponente('{{ $tipo }}')"
                            class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-blue-50 rounded-lg border border-gray-200 hover:border-blue-300 transition-all group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">{{ $nombre }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Panel Central: Vista Previa -->
            <div class="col-span-7">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-eye mr-2 text-blue-600"></i>
                        Vista Previa
                    </h3>
                    
                    <!-- Debug: Estado del array de componentes -->
                    <div class="text-xs bg-yellow-100 p-2 rounded mb-2 max-h-32 overflow-auto">
                        @foreach($componentes as $i => $c)
                            <div>{{ $i }}: {{ $c['tipo'] }} = "{{ $c['propiedades']['titulo'] ?? $c['propiedades']['texto'] ?? 'N/A' }}"</div>
                        @endforeach
                    </div>
                    
                    <div class="space-y-4" x-ref="contenedorComponentes">
                        @if(count($componentes) > 0)
                            @foreach($componentes as $index => $componente)
                            <div 
                                wire:key="componente-{{ $componente['id'] }}"
                                @click="seleccionar('{{ $componente['id'] }}')"
                                class="border-2 rounded-lg p-4 cursor-pointer transition-all relative"
                                :class="@this.componenteSeleccionado === '{{ $componente['id'] }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                
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
                            <div class="text-center py-12 text-gray-400">
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
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
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
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-mouse-pointer text-4xl mb-3"></i>
                            <p class="text-sm">Selecciona un componente para editar sus propiedades</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function formularioConstructor() {
            return {
                init() {
                    // Sincronizar con Livewire
                    this.$watch('$wire.componenteSeleccionado', () => {
                        // AquÃ­ puedes agregar lÃ³gica adicional si es necesaria
                    });
                },
                
                seleccionar(id) {
                    @this.seleccionarComponente(id);
                },
                
                guardar() {
                    if (!@this.nombreFormulario || @this.nombreFormulario.trim() === '') {
                        alert('Por favor ingresa un nombre para la plantilla');
                        return;
                    }
                    
                    if (@this.componentes.length === 0) {
                        alert('Agrega al menos un componente al formulario');
                        return;
                    }
                    
                    @this.guardarFormulario();
                }
            }
        }
    </script>
</div>
