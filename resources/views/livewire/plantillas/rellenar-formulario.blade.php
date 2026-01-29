<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Rellenar Análisis
            </h1>
            <p class="text-gray-600">Completa los campos del formulario</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form wire:submit="guardarAnalisis">
                <!-- Aquí se renderizarán los campos según la plantilla -->
                <div class="space-y-4">
                    <div class="text-center py-12 text-gray-400">
                        <i class="fas fa-clipboard-list text-5xl mb-3"></i>
                        <p>Formulario de análisis</p>
                        <p class="text-sm">Los campos se cargarán según la plantilla seleccionada</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <flux:button type="submit" variant="primary" icon="check">
                        Guardar Análisis
                    </flux:button>
                    <flux:button href="{{ route('analisis.index') }}" variant="ghost">
                        Cancelar
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
