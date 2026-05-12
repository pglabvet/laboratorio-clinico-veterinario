<div>
    {{-- Redirigir al componente de gestión con el modal abierto --}}
    <script>
        window.location.href = "{{ route('muestras.index') }}?crear=1";
    </script>
    
    <div class="flex items-center justify-center min-h-[400px]">
        <div class="text-center">
            <flux:heading size="lg">Redirigiendo...</flux:heading>
            <flux:subheading class="mt-2">Será redirigido al formulario de registro de muestras</flux:subheading>
        </div>
    </div>
</div>
