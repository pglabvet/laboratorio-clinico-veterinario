<?php

use App\Livewire\Muestras\FormularioMuestra;
use App\Models\Analisis;
use App\Models\Muestra;
use App\Models\Pdf;
use App\Models\Resultado;
use App\Models\TokenDescarga;
use App\Models\User;
use App\Services\AnalisisPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function correccionRegistro(string $tabla, array $datos): int
{
    return DB::table($tabla)->insertGetId($datos + ['created_at' => now(), 'updated_at' => now()]);
}

test('el diagnóstico es obligatorio al registrar una muestra', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(FormularioMuestra::class)
        ->set('diagnostico', '')
        ->call('guardar')
        ->assertHasErrors(['diagnostico' => 'required'])
        ->assertSee('El diagnóstico es obligatorio.');
});

describe('enlace de resultados enviado al cliente', function () {
    beforeEach(function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        // Los datos se crean "en el pasado" para que el archivo guardado después quede vigente
        $this->travel(-10)->minutes();

        $sucursalId = correccionRegistro('sucursales', ['nombre' => 'Centro', 'codigo' => 'CEN', 'direccion' => 'Calle 1', 'telefono' => '700']);
        $especieId = correccionRegistro('especies', ['nombre' => 'Canino']);
        $veterinariaId = correccionRegistro('veterinarias', ['nombre' => 'Vet Uno', 'responsable' => 'Dr', 'email' => 'v@vet.test', 'direccion' => 'Calle 2']);
        $tipoId = correccionRegistro('tipos_analisis', ['nombre' => 'Hemograma']);
        $plantillaId = correccionRegistro('plantillas_formulario', [
            'nombre' => 'Hemograma completo',
            'tipo_analisis_id' => $tipoId,
            'componentes' => '[]',
            'creado_por' => $user->id,
        ]);

        $muestra = Muestra::create([
            'codigo_muestra' => 'C260001',
            'paciente_nombre' => 'Firulais',
            'especie_id' => $especieId,
            'edad' => '2 años',
            'sexo' => 'M',
            'propietario_nombre' => 'Pedro',
            'veterinaria_id' => $veterinariaId,
            'sucursal_id' => $sucursalId,
            'tipo_muestra' => 'Sangre',
            'fecha_recepcion' => now(),
            'estado' => Muestra::ESTADO_ENVIADO,
            'diagnostico' => 'Control',
        ]);

        $this->analisis = Analisis::create([
            'muestra_id' => $muestra->id,
            'tipo_analisis_id' => $tipoId,
            'plantilla_formulario_id' => $plantillaId,
            'bioquimico_id' => $user->id,
            'estado' => Analisis::ESTADO_ENVIADO,
        ]);
        $this->resultado = Resultado::create(['analisis_id' => $this->analisis->id, 'tipo' => 'campo-numero', 'indice' => 0, 'valor' => ['valor' => 10]]);

        $this->pdf = Pdf::create([
            'analisis_id' => $this->analisis->id,
            'ruta_archivo' => 'pdfs/2026/11/FIRULAIS_HEMOGRAMA.PDF',
            'generado_por' => $user->id,
            'fecha_generacion' => now(),
        ]);
        $this->token = TokenDescarga::crearParaPdf($this->pdf->id);

        $this->travelBack();
        Storage::disk('public')->put($this->pdf->ruta_archivo, '%PDF-enviado');
    });

    test('si el análisis no cambió, el enlace entrega el mismo PDF sin regenerarlo', function () {
        $this->partialMock(AnalisisPdfService::class, fn ($mock) => $mock->shouldNotReceive('renderizarPdf'));

        $this->get('/r/'.$this->token->codigo_corto)->assertSuccessful();
    });

    test('si se editan los resultados después de enviarlos, el enlace regenera el PDF con los datos nuevos', function () {
        $this->travel(5)->minutes();
        $this->resultado->update(['valor' => ['valor' => 99]]);

        $this->partialMock(AnalisisPdfService::class, fn ($mock) => $mock->shouldReceive('renderizarPdf')
            ->once()
            ->withArgs(fn ($analisis, $ruta, $qrUrl, $formato) => $analisis->is($this->analisis)
                && $ruta === $this->pdf->ruta_archivo
                && $qrUrl === $this->token->getUrlDescarga()
                && $formato === 'completo'));

        $this->get('/r/'.$this->token->codigo_corto)->assertSuccessful();
    });

    test('si se corrigen los datos de la muestra, el enlace del PDF sin membrete se regenera en el mismo formato', function () {
        $this->pdf->update(['ruta_archivo' => 'pdfs/2026/11/FIRULAIS_HEMOGRAMA_L.PDF']);
        Storage::disk('public')->put($this->pdf->ruta_archivo, '%PDF-enviado');

        $this->travel(5)->minutes();
        $this->analisis->muestra->update(['paciente_nombre' => 'Firulais Corregido']);

        $this->partialMock(AnalisisPdfService::class, fn ($mock) => $mock->shouldReceive('renderizarPdf')
            ->once()
            ->withArgs(fn ($analisis, $ruta, $qrUrl, $formato) => $qrUrl === null && $formato === 'limpio'));

        $this->get('/r/'.$this->token->codigo_corto)->assertSuccessful();
    });
});
