{{-- Componente de edición: Citología --}}
@push('styles')
<style>
    .citologia-quill .ql-container {
        min-height: 120px;
        font-size: 14px;
        background-color: var(--editor-bg, #fff);
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }
    .dark .citologia-quill .ql-container {
        --editor-bg: #18181b;
        color: #f4f4f5;
    }
    .dark .citologia-quill .ql-toolbar {
        background-color: #27272a;
        border-color: #3f3f46;
    }
    .dark .citologia-quill .ql-container {
        border-color: #3f3f46;
    }
    .dark .citologia-quill .ql-toolbar button,
    .dark .citologia-quill .ql-toolbar .ql-picker-label {
        color: #f4f4f5;
    }
    .dark .citologia-quill .ql-toolbar button:hover,
    .dark .citologia-quill .ql-toolbar .ql-picker-label:hover {
        color: #3b82f6;
    }
    .dark .citologia-quill .ql-toolbar button.ql-active,
    .dark .citologia-quill .ql-toolbar .ql-picker-label.ql-active {
        color: #3b82f6;
    }
    .dark .citologia-quill .ql-stroke {
        stroke: #f4f4f5;
    }
    .dark .citologia-quill .ql-fill {
        fill: #f4f4f5;
    }
    .dark .citologia-quill .ql-picker-options {
        background-color: #27272a;
        border-color: #3f3f46;
    }
</style>
@endpush

<div
    wire:ignore
    x-data="citologiaEditor_{{ $index }}()"
    x-init="init()"
    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900"
>
    {{-- Selector de Título --}}
    @php
        $titulosDisponibles = $componente['propiedades']['titulos'] ?? [];
        if (empty($titulosDisponibles) && isset($componente['propiedades']['titulo'])) {
            $titulosDisponibles = [$componente['propiedades']['titulo']];
        }
    @endphp
    
    <div class="mb-4">
        @if(count($titulosDisponibles) > 1)
            <select
                x-model="tituloSeleccionado"
                @change="enviarDatos()"
                class="w-full px-3 py-2 text-lg font-bold text-center border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                @foreach($titulosDisponibles as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        @else
            <input 
                type="text"
                x-model="tituloSeleccionado"
                @change="enviarDatos()"
                @blur="enviarDatos()"
                placeholder="Título del componente"
                class="w-full px-3 py-2 text-lg font-bold text-center border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
            />
        @endif
    </div>

    {{-- Selector de Tumor --}}
    <div class="mb-5 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
        <label class="block text-sm font-bold text-purple-700 dark:text-purple-400 mb-2">
            <i class="fas fa-disease mr-1"></i> Tipo de Tumor
        </label>
        <select
            x-model="tumorSeleccionado"
            @change="onTumorChange()"
            class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
        >
            <option value="">-- Seleccionar tipo de tumor --</option>
            @foreach($componente['propiedades']['tumores'] ?? [] as $tumor)
                @if(!empty($tumor))
                <option value="{{ $tumor }}">{{ $tumor }}</option>
                @endif
            @endforeach
        </select>
    </div>

    {{-- Secciones dinámicas --}}
    <div class="space-y-5">
        @foreach($componente['propiedades']['secciones'] ?? [] as $si => $seccion)
        @php $tipoSeccion = $seccion['tipo'] ?? 'editable'; @endphp
        <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-gray-50 dark:bg-zinc-800/50">
            @if(!empty($seccion['titulo']))
            <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-base mb-3">
                {{ $seccion['titulo'] }}
            </h5>
            @endif

            @if($tipoSeccion === 'editable')
                {{-- Sección editable: Editor Quill --}}
                <div class="citologia-quill">
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                        <i class="fas fa-edit mr-1"></i>
                        Texto editable — puede modificar el contenido
                    </label>
                    <div x-ref="editor_{{ $si }}" class="bg-white dark:bg-zinc-800 rounded-lg"></div>
                </div>
            @elseif($tipoSeccion === 'dependiente')
                {{-- Sección dependiente: texto se carga según tumor --}}
                <div class="citologia-quill">
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                        <i class="fas fa-link mr-1"></i>
                        Texto se carga según el tumor seleccionado — puede editarlo luego
                    </label>
                    <div x-ref="editor_{{ $si }}" class="bg-white dark:bg-zinc-800 rounded-lg"></div>
                </div>
            @elseif($tipoSeccion === 'con_tumor')
                {{-- Sección con tumor: texto fijo + tumor --}}
                <div class="citologia-quill">
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-2">
                        <i class="fas fa-thumbtack mr-1"></i>
                        Texto con diagnóstico — el tumor seleccionado se incluye automáticamente
                    </label>
                    <div x-ref="editor_{{ $si }}" class="bg-white dark:bg-zinc-800 rounded-lg"></div>
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    function citologiaEditor_{{ $index }}() {
        return {
            tituloSeleccionado: @js($componente['propiedades']['titulos'][0] ?? ($componente['propiedades']['titulo'] ?? 'CITOLOGÍA')),
            tumorSeleccionado: '',
            secciones: @js($componente['propiedades']['secciones'] ?? []),
            tumores: @js($componente['propiedades']['tumores'] ?? []),
            quillEditors: {},
            contenidos: {},
            datoExistente: @js($componentesData[$index]['data'] ?? null),

            init() {
                // Cargar datos existentes si hay
                if (this.datoExistente && typeof this.datoExistente === 'object') {
                    if (this.datoExistente.titulo !== undefined) {
                        this.tituloSeleccionado = this.datoExistente.titulo;
                    }
                    this.tumorSeleccionado = this.datoExistente.tumor || '';
                    if (this.datoExistente.secciones) {
                        for (let i = 0; i < this.secciones.length; i++) {
                            this.contenidos[i] = this.datoExistente.secciones[i]?.contenido || '';
                        }
                    }
                }

                // Inicializar editores Quill
                this.$nextTick(() => {
                    this.initAllEditors();
                });

                // Escuchar evento de guardado
                window.addEventListener('antes-de-guardar', () => {
                    this.enviarDatos();
                });

                Livewire.hook('morph.updating', () => {
                    this.enviarDatos();
                });
            },

            initAllEditors() {
                for (let i = 0; i < this.secciones.length; i++) {
                    const ref = this.$refs['editor_' + i];
                    if (!ref || this.quillEditors[i]) continue;

                    const seccion = this.secciones[i];
                    const tipo = seccion.tipo || 'editable';

                    this.quillEditors[i] = new Quill(ref, {
                        theme: 'snow',
                        placeholder: 'Escriba aquí...',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered' }],
                                ['clean']
                            ]
                        }
                    });

                    // Cargar contenido inicial
                    let contenidoInicial = this.contenidos[i] || '';

                    if (!contenidoInicial) {
                        if (tipo === 'editable' || tipo === 'con_tumor') {
                            contenidoInicial = seccion.texto_base || '';
                            if (tipo === 'con_tumor' && this.tumorSeleccionado) {
                                contenidoInicial += ' <strong>' + this.tumorSeleccionado + '</strong>';
                            }
                        } else if (tipo === 'dependiente' && this.tumorSeleccionado) {
                            const textosMap = seccion.textos_por_tumor || {};
                            contenidoInicial = textosMap[this.tumorSeleccionado] || '';
                        }
                    }

                    if (contenidoInicial) {
                        if (contenidoInicial.includes('<')) {
                            this.quillEditors[i].root.innerHTML = contenidoInicial;
                        } else {
                            const lineas = contenidoInicial.split(/\r?\n/);
                            let html = '';
                            lineas.forEach(l => { html += `<p>${l || '<br>'}</p>`; });
                            this.quillEditors[i].root.innerHTML = html;
                        }
                    }

                    this.contenidos[i] = this.quillEditors[i].root.innerHTML;

                    // Sincronizar en cada cambio
                    ((idx) => {
                        this.quillEditors[idx].on('text-change', () => {
                            this.contenidos[idx] = this.quillEditors[idx].root.innerHTML;
                            this.enviarDatos();
                        });
                    })(i);
                }
            },

            onTumorChange() {
                // Actualizar secciones dependientes y con_tumor
                for (let i = 0; i < this.secciones.length; i++) {
                    const seccion = this.secciones[i];
                    const tipo = seccion.tipo || 'editable';
                    const editor = this.quillEditors[i];
                    if (!editor) continue;

                    if (tipo === 'dependiente') {
                        const textosMap = seccion.textos_por_tumor || {};
                        const texto = textosMap[this.tumorSeleccionado] || '';
                        if (texto) {
                            if (texto.includes('<')) {
                                editor.root.innerHTML = texto;
                            } else {
                                const lineas = texto.split(/\r?\n/);
                                let html = '';
                                lineas.forEach(l => { html += `<p>${l || '<br>'}</p>`; });
                                editor.root.innerHTML = html;
                            }
                        } else {
                            editor.root.innerHTML = '<p><br></p>';
                        }
                        this.contenidos[i] = editor.root.innerHTML;
                    } else if (tipo === 'con_tumor') {
                        const textoBase = seccion.texto_base || '';
                        if (this.tumorSeleccionado) {
                            editor.root.innerHTML = `<p>${textoBase} <strong>${this.tumorSeleccionado}</strong></p>`;
                        } else {
                            editor.root.innerHTML = `<p>${textoBase}</p>`;
                        }
                        this.contenidos[i] = editor.root.innerHTML;
                    }
                    // 'editable' no se toca al cambiar tumor
                }
                this.enviarDatos();
            },

            enviarDatos() {
                // Sincronizar contenido desde los editores
                for (let i in this.quillEditors) {
                    if (this.quillEditors[i]) {
                        this.contenidos[i] = this.quillEditors[i].root.innerHTML;
                    }
                }

                const seccionesData = [];
                for (let i = 0; i < this.secciones.length; i++) {
                    seccionesData.push({
                        titulo: this.secciones[i].titulo || '',
                        tipo: this.secciones[i].tipo || 'editable',
                        contenido: this.contenidos[i] || ''
                    });
                }

                const data = {
                    titulo: this.tituloSeleccionado,
                    tumor: this.tumorSeleccionado,
                    secciones: seccionesData
                };

                window.__labvetData = window.__labvetData || {};
                window.__labvetData['{{ $index }}'] = data;
                this.$wire.set('componentesData.{{ $index }}.data', data);
            }
        }
    }
</script>
@endpush
