{{-- Componente de edición: Texto Libre con Editor Quill --}}
@push('styles')
<style>
    .quill-editor-container .ql-container {
        min-height: 150px;
        font-size: 14px;
        background-color: var(--editor-bg, #fff);
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }
    .dark .quill-editor-container .ql-container {
        --editor-bg: #18181b;
        color: #f4f4f5;
    }
    .dark .quill-editor-container .ql-toolbar {
        background-color: #27272a;
        border-color: #3f3f46;
    }
    .dark .quill-editor-container .ql-container {
        border-color: #3f3f46;
    }
    .dark .quill-editor-container .ql-toolbar button,
    .dark .quill-editor-container .ql-toolbar .ql-picker-label {
        color: #f4f4f5;
    }
    .dark .quill-editor-container .ql-toolbar button:hover,
    .dark .quill-editor-container .ql-toolbar .ql-picker-label:hover {
        color: #3b82f6;
    }
    .dark .quill-editor-container .ql-toolbar button.ql-active,
    .dark .quill-editor-container .ql-toolbar .ql-picker-label.ql-active {
        color: #3b82f6;
    }
    .dark .quill-editor-container .ql-stroke {
        stroke: #f4f4f5;
    }
    .dark .quill-editor-container .ql-fill {
        fill: #f4f4f5;
    }
    .dark .quill-editor-container .ql-picker-options {
        background-color: #27272a;
        border-color: #3f3f46;
    }
</style>
@endpush


<div 
    wire:ignore
    x-data="{
        datoExistente: @js($componentesData[$index]['data'] ?? null),
        contenido: '',
        quill: null,
        init() {
            // Cargar dato existente
            if (this.datoExistente && typeof this.datoExistente === 'object' && this.datoExistente.contenido) {
                this.contenido = this.datoExistente.contenido;
            } else if (typeof this.datoExistente === 'string') {
                this.contenido = this.datoExistente;
            } else {
                // Cargar texto base de la plantilla si no hay dato existente
                this.contenido = @js($componente['propiedades']['contenido'] ?? '');
            }
            
            // Inicializar Quill solo para modo párrafos
            const formato = '{{ $componente['propiedades']['formato'] ?? 'parrafos' }}';
            if (formato !== 'lista') {
                this.$nextTick(() => {
                    this.initQuill();
                });
            }
            
            // Escuchar evento de guardado para forzar sincronización
            window.addEventListener('antes-de-guardar', () => {
                this.enviarDatos();
            });
            
            // Sincronizar antes de cualquier acción de Livewire
            Livewire.hook('morph.updating', () => {
                this.enviarDatos();
            });
        },
        initQuill() {
            const container = this.$refs.quillEditor;
            if (!container || this.quill) return;
            
            // Determinar placeholder
            const customPlaceholder = @js($componente['propiedades']['contenido'] ?? '');
            
            this.quill = new Quill(container, {
                theme: 'snow',
                placeholder: customPlaceholder || 'Escriba el texto aquí...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}],
                        ['clean']
                    ]
                }
            });
            
            // Cargar contenido existente
            if (this.contenido) {
                // Si es HTML, establecerlo directamente
                if (this.contenido.includes('<')) {
                    this.quill.root.innerHTML = this.contenido;
                } else {
                    // Si es texto plano, convertir cada línea en un párrafo nativo de Quill
                    // Esto evita que Quill agregue saltos dobles indeseados.
                    const lineas = this.contenido.split(/\r?\n/);
                    let htmlContenido = '';
                    lineas.forEach(linea => {
                        htmlContenido += `<p>${linea || '<br>'}</p>`;
                    });
                    this.quill.root.innerHTML = htmlContenido;
                }
            }
            
            // Sincronizar en cada cambio de texto
            this.quill.on('text-change', () => {
                this.contenido = this.quill.root.innerHTML;
                // Sincronizar con Livewire después de cada cambio
                this.enviarDatos();
            });
        },
        enviarDatos() {
            // Si es Quill, obtener HTML
            if (this.quill) {
                this.contenido = this.quill.root.innerHTML;
            }
            const data = {
                titulo: '{{ $componente['propiedades']['titulo'] ?? '' }}',
                formato: '{{ $componente['propiedades']['formato'] ?? 'parrafos' }}',
                contenido: this.contenido
            };
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
        }
    }"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900"
>
    @if(isset($componente['propiedades']['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-lg mb-3 {{ ($componente['propiedades']['alineacion_titulo'] ?? 'left') === 'center' ? 'text-center' : 'text-left' }}">
        {{ $componente['propiedades']['titulo'] }}
    </h4>
    @endif

    @if(($componente['propiedades']['formato'] ?? 'parrafos') === 'lista')
        {{-- Editor de lista (mantiene textarea) --}}
        <div class="space-y-1">
            <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                <i class="fas fa-list-ul mr-1"></i>
                Cada línea será un elemento de la lista
            </label>
            <textarea 
                rows="8"
                x-model="contenido"
                @blur="enviarDatos()"
                placeholder="{{ $componente['propiedades']['contenido'] ?? 'Escribe cada item en una línea diferente' }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 font-mono text-sm"
            ></textarea>
        </div>
    @else
        {{-- Editor Quill para párrafos con formato --}}
        <div class="quill-editor-container">
            <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                <i class="fas fa-align-left mr-1"></i>
                Texto con formato (negrita, cursiva, listas)
            </label>
            <div 
                x-ref="quillEditor"
                class="bg-white dark:bg-zinc-800 rounded-lg"
            ></div>
        </div>
    @endif
</div>

