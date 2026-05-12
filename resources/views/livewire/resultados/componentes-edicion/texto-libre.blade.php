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
        incluirEnPdf: true,
        quill: null,
        usandoFallback: false,
        init() {
            // Cargar dato existente
            if (this.datoExistente && typeof this.datoExistente === 'object' && this.datoExistente.contenido) {
                this.contenido = this.datoExistente.contenido;
                this.incluirEnPdf = this.datoExistente.incluir_en_pdf ?? true;
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
            
            // Verificar que Quill está disponible globalmente
            if (typeof Quill === 'undefined') {
                console.warn('[TextoLibre] Quill no está disponible. Usando editor de respaldo.');
                this.mostrarFallback(container);
                return;
            }
            
            try {
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
                
                // Verificar que el editor se creó correctamente
                if (!this.quill || !this.quill.root) {
                    throw new Error('Quill no inicializó correctamente');
                }
                
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
                
                // Verificación post-inicialización: asegurar que el editor es funcional
                // Chrome 148 puede cargar Quill sin error pero el contenteditable no responde
                setTimeout(() => {
                    if (!this.quill || this.usandoFallback) return;
                    try {
                        const textoAntes = this.quill.getText();
                        this.quill.insertText(0, '\u200B'); // Zero-width space (invisible)
                        const textoDespues = this.quill.getText();
                        // Limpiar el carácter de prueba
                        this.quill.deleteText(0, 1);
                        
                        if (textoAntes === textoDespues) {
                            // El editor no respondió a la inserción — está inutilizable
                            console.warn('[TextoLibre] Quill cargó pero no responde. Activando fallback.');
                            this.quill = null;
                            this.mostrarFallback(container);
                        }
                    } catch (e) {
                        console.warn('[TextoLibre] Verificación post-init falló:', e);
                        this.quill = null;
                        this.mostrarFallback(container);
                    }
                }, 300);
            } catch (e) {
                console.error('[TextoLibre] Error al inicializar Quill:', e);
                // Limpiar intento fallido
                this.quill = null;
                this.mostrarFallback(container);
            }
        },
        mostrarFallback(container) {
            this.usandoFallback = true;
            container.innerHTML = '';
            const ta = document.createElement('textarea');
            ta.rows = 8;
            ta.placeholder = @js($componente['propiedades']['contenido'] ?? '') || 'Escriba el texto aquí...';
            ta.style.cssText = 'width:100%;min-height:150px;padding:12px;border:1px solid #3f3f46;border-radius:0.5rem;background:#18181b;color:#f4f4f5;font-size:14px;resize:vertical;font-family:inherit;line-height:1.6;';
            
            // Cargar contenido existente (convertir HTML a texto plano si es necesario)
            if (this.contenido) {
                if (this.contenido.includes('<')) {
                    const temp = document.createElement('div');
                    temp.innerHTML = this.contenido;
                    ta.value = temp.textContent || temp.innerText || '';
                } else {
                    ta.value = this.contenido;
                }
            }
            
            ta.addEventListener('input', () => {
                this.contenido = ta.value;
                this.enviarDatos();
            });
            ta.addEventListener('blur', () => {
                this.enviarDatos();
            });
            
            container.appendChild(ta);
        },
        enviarDatos() {
            // Si es Quill, obtener HTML
            if (this.quill) {
                this.contenido = this.quill.root.innerHTML;
            }
            const data = {
                titulo: '{{ $componente['propiedades']['titulo'] ?? '' }}',
                formato: '{{ $componente['propiedades']['formato'] ?? 'parrafos' }}',
                contenido: this.contenido,
                incluir_en_pdf: this.incluirEnPdf
            };
            window.__labvetData = window.__labvetData || {};
            window.__labvetData['{{ $index }}'] = data;
            $wire.set('componentesData.{{ $index }}.data', data);
            window.dispatchEvent(new CustomEvent('datos-sincronizados', { detail: { index: {{ $index }} } }));
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
        {{-- Editor Quill para párrafos con formato (con fallback automático si falla) --}}
        <div class="quill-editor-container">
            <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                <i class="fas fa-align-left mr-1"></i>
                <span x-show="!usandoFallback">Texto con formato (negrita, cursiva, listas)</span>
                <span x-show="usandoFallback" x-cloak>Texto libre (modo compatible)</span>
            </label>
            <div 
                x-ref="quillEditor"
                class="bg-white dark:bg-zinc-800 rounded-lg"
            ></div>
        </div>
    @endif

    {{-- Checkbox para incluir/excluir del PDF --}}
    <label class="flex items-center gap-2 mt-3 cursor-pointer select-none group">
        <input 
            type="checkbox" 
            x-model="incluirEnPdf" 
            @change="enviarDatos()"
            class="rounded border-gray-300 dark:border-zinc-600 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-zinc-800 dark:checked:bg-blue-600"
        >
        <span class="text-sm text-gray-600 dark:text-zinc-400 group-hover:text-gray-800 dark:group-hover:text-zinc-200 transition-colors">
            <i class="fas fa-file-pdf mr-1"></i>
            Incluir este contenido en el PDF
        </span>
    </label>

    {{-- Repeticiones (solo si hay reactivos asignados a nivel componente) --}}
    @include('livewire.resultados.componentes-edicion._repeticiones-reactivos', [
        'componente' => $componente,
        'index' => $index,
    ])
</div>

