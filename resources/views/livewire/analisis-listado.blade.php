<div x-data="analisisListado()" x-init="cargarAnalisis()" class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-flask mr-2 text-blue-600"></i>
                        Análisis Realizados
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Listado de todos los análisis completados
                    </p>
                </div>
                <a href="{{ route('formularios.plantillas') }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-plus mr-2"></i>Nuevo Análisis
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="busqueda"
                        placeholder="Buscar por paciente, código..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                
                <select x-model="filtroPlantilla" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas las plantillas</option>
                    <template x-for="plantilla in plantillasDisponibles" :key="plantilla.id">
                        <option :value="plantilla.id" x-text="plantilla.nombre"></option>
                    </template>
                </select>

                <select x-model="ordenar" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="reciente">Más recientes</option>
                    <option value="antiguo">Más antiguos</option>
                </select>
            </div>
        </div>

        <!-- Tabla de Análisis -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo de Análisis
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="analisis in analisisFiltrados()" :key="analisis.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono font-medium text-gray-900" x-text="analisis.codigo"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900" x-text="analisis.plantillaNombre"></div>
                                        <div class="text-xs text-gray-500" x-text="analisis.paciente || 'Sin paciente'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="formatearFecha(analisis.fecha)"></div>
                                <div class="text-xs text-gray-500" x-text="formatearHora(analisis.fecha)"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Completado
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        @click="verAnalisis(analisis.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Ver análisis">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button 
                                        @click="descargarPDF(analisis.id)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Descargar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    <button 
                                        @click="eliminarAnalisis(analisis.id)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Mensaje si no hay análisis -->
            <div x-show="analisis.length === 0" class="text-center py-20">
                <i class="fas fa-flask text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-600 mb-2">No hay análisis realizados</h3>
                <p class="text-gray-500 mb-6">Comienza creando tu primer análisis</p>
                <a href="{{ route('formularios.plantillas') }}" 
                   class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-plus mr-2"></i>Nuevo Análisis
                </a>
            </div>
        </div>

        <!-- Paginación (placeholder) -->
        <div x-show="analisis.length > 0" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando <span class="font-medium" x-text="analisis.length"></span> análisis
            </div>
        </div>
    </div>

    <script>
        function analisisListado() {
            return {
                analisis: [],
                plantillasDisponibles: [],
                busqueda: '',
                filtroPlantilla: '',
                ordenar: 'reciente',

                cargarAnalisis() {
                    // Cargar todos los análisis del localStorage
                    const keys = Object.keys(localStorage).filter(k => k.startsWith('analisis-'));
                    this.analisis = keys.map(key => {
                        const data = JSON.parse(localStorage.getItem(key));
                        const plantilla = this.obtenerPlantilla(data.plantillaId);
                        
                        // Extraer nombre del paciente de los campos fijos
                        let paciente = data.datos?.paciente || '';
                        
                        return {
                            id: key.replace('analisis-', ''),
                            codigo: `AN-${key.replace('analisis-', '').slice(-6)}`,
                            plantillaId: data.plantillaId,
                            plantillaNombre: plantilla?.nombre || 'Plantilla eliminada',
                            fecha: data.fecha,
                            datos: data.datos,
                            paciente: paciente
                        };
                    });

                    this.cargarPlantillas();
                },

                cargarPlantillas() {
                    const keys = Object.keys(localStorage).filter(k => k.startsWith('plantilla-'));
                    this.plantillasDisponibles = keys.map(key => {
                        const data = JSON.parse(localStorage.getItem(key));
                        return {
                            id: key.replace('plantilla-', ''),
                            nombre: data.nombre
                        };
                    });
                },

                obtenerPlantilla(id) {
                    const data = localStorage.getItem(`plantilla-${id}`);
                    return data ? JSON.parse(data) : null;
                },

                analisisFiltrados() {
                    let resultado = [...this.analisis];

                    // Filtro por búsqueda
                    if (this.busqueda) {
                        const term = this.busqueda.toLowerCase();
                        resultado = resultado.filter(a => 
                            a.codigo.toLowerCase().includes(term) ||
                            a.plantillaNombre.toLowerCase().includes(term) ||
                            (a.paciente && a.paciente.toLowerCase().includes(term))
                        );
                    }

                    // Filtro por plantilla
                    if (this.filtroPlantilla) {
                        resultado = resultado.filter(a => a.plantillaId === this.filtroPlantilla);
                    }

                    // Ordenar
                    if (this.ordenar === 'reciente') {
                        resultado.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
                    } else {
                        resultado.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
                    }

                    return resultado;
                },

                verAnalisis(id) {
                    window.location.href = `/analisis/ver/${id}`;
                },

                descargarPDF(id) {
                    // Aquí irá la lógica para generar y descargar el PDF
                    alert('Función de descarga PDF en desarrollo');
                },

                eliminarAnalisis(id) {
                    if (!confirm('¿Estás seguro de eliminar este análisis?')) return;

                    localStorage.removeItem(`analisis-${id}`);
                    this.cargarAnalisis();

                    this.$dispatch('notify', { 
                        type: 'success', 
                        message: 'Análisis eliminado correctamente' 
                    });
                },

                formatearFecha(fecha) {
                    return new Date(fecha).toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                },

                formatearHora(fecha) {
                    return new Date(fecha).toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    </script>
</div>
