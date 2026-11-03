<?php

use App\Livewire\Auditorias\ListarAuditorias;
use App\Livewire\Muestras\GestionarMuestras;
use App\Models\Analisis;
use App\Models\Muestra;
use App\Models\Resultado;
use App\Models\User;
use App\Services\AuditoriaLegibleService;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

function historialUsuario(string $nombre, array $permisos = []): User
{
    $user = User::factory()->create(['name' => $nombre]);
    foreach ($permisos as $permiso) {
        Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
    }
    $user->givePermissionTo($permisos);

    return $user;
}

function historialRegistro(string $tabla, array $datos): int
{
    return DB::table($tabla)->insertGetId($datos + ['created_at' => now(), 'updated_at' => now()]);
}

beforeEach(function () {
    $this->sucursalId = historialRegistro('sucursales', ['nombre' => 'Centro', 'codigo' => 'CEN', 'direccion' => 'Calle 1', 'telefono' => '700']);
    $this->caninoId = historialRegistro('especies', ['nombre' => 'Canino']);
    $this->felinoId = historialRegistro('especies', ['nombre' => 'Felino']);
    $veterinariaId = historialRegistro('veterinarias', ['nombre' => 'Vet Uno', 'responsable' => 'Dr', 'email' => 'v@vet.test', 'direccion' => 'Calle 2']);

    $this->juan = historialUsuario('Juan Recepcion');
    $this->ana = historialUsuario('Ana Correccion');
    $this->maria = historialUsuario('Maria Bioquimica');
    $this->admin = historialUsuario('Admin Revisor');

    // Juan registra la muestra con un análisis
    $this->actingAs($this->juan);
    $this->muestra = Muestra::create([
        'codigo_muestra' => 'C260001',
        'paciente_nombre' => 'Firulay',
        'especie_id' => $this->felinoId,
        'edad' => '2 años',
        'sexo' => 'M',
        'propietario_nombre' => 'Pedro',
        'veterinaria_id' => $veterinariaId,
        'sucursal_id' => $this->sucursalId,
        'tipo_muestra' => 'Sangre',
        'fecha_recepcion' => now(),
        'estado' => Muestra::ESTADO_PENDIENTE,
    ]);
    $tipoId = historialRegistro('tipos_analisis', ['nombre' => 'Hemograma']);
    $plantillaId = historialRegistro('plantillas_formulario', [
        'nombre' => 'Hemograma completo',
        'tipo_analisis_id' => $tipoId,
        'componentes' => '[]',
        'creado_por' => $this->juan->id,
    ]);
    $this->analisis = Analisis::create([
        'muestra_id' => $this->muestra->id,
        'tipo_analisis_id' => $tipoId,
        'plantilla_formulario_id' => $plantillaId,
        'bioquimico_id' => $this->juan->id,
        'estado' => Analisis::ESTADO_PENDIENTE,
    ]);

    // Ana corrige paciente y especie
    $this->travel(5)->minutes();
    $this->actingAs($this->ana);
    $this->muestra->update(['paciente_nombre' => 'Firulais', 'especie_id' => $this->caninoId]);

    // María llena resultados y envía a revisión
    $this->travel(30)->minutes();
    $this->actingAs($this->maria);
    Resultado::create(['analisis_id' => $this->analisis->id, 'tipo' => 'campo-numero', 'valor' => ['valor' => 10]]);
    Resultado::create(['analisis_id' => $this->analisis->id, 'tipo' => 'campo-numero', 'valor' => ['valor' => 20]]);
    $this->analisis->update(['estado' => Analisis::ESTADO_EN_REVISION, 'fecha_finalizacion' => now()]);

    // El admin rechaza con motivo
    $this->travel(20)->minutes();
    $this->actingAs($this->admin);
    $this->analisis->update([
        'estado' => Analisis::ESTADO_PENDIENTE,
        'aprobador_id' => $this->admin->id,
        'observaciones_aprobador' => 'Hematocrito incorrecto',
        'fecha_aprobacion' => now(),
    ]);
});

test('el historial de la muestra cuenta quién hizo cada paso en frases simples', function () {
    $eventos = app(AuditoriaLegibleService::class)->historialMuestra($this->muestra->fresh());
    $lineas = $eventos->map(fn ($e) => $e['usuario'].': '.$e['texto'])->all();

    expect($lineas)->toBe([
        'Juan Recepcion: Registró la muestra C260001',
        'Juan Recepcion: Agregó el análisis Hemograma',
        'Ana Correccion: Corrigió los datos de la muestra C260001',
        'Maria Bioquimica: Llenó los resultados de Hemograma y los envió a revisión',
        'Admin Revisor: Rechazó el análisis Hemograma para que lo corrijan',
    ]);

    // Correcciones con nombres en español y nombres en lugar de IDs
    expect($eventos[2]['detalles'])->toContain(
        ['campo' => 'Paciente', 'antes' => 'Firulay', 'despues' => 'Firulais'],
        ['campo' => 'Especie', 'antes' => 'Felino', 'despues' => 'Canino'],
    );

    expect($eventos[4]['motivo'])->toBe('Hematocrito incorrecto');
});

test('resultados guardados sin enviar se muestran como una sola línea', function () {
    $this->travel(30)->minutes();
    $this->actingAs($this->maria);
    Resultado::create(['analisis_id' => $this->analisis->id, 'tipo' => 'campo-numero', 'valor' => ['valor' => 11]]);
    Resultado::create(['analisis_id' => $this->analisis->id, 'tipo' => 'campo-numero', 'valor' => ['valor' => 21]]);

    $ultimo = app(AuditoriaLegibleService::class)->historialMuestra($this->muestra->fresh())->last();

    expect($ultimo['usuario'])->toBe('Maria Bioquimica')
        ->and($ultimo['texto'])->toBe('Guardó resultados de Hemograma');
});

test('la pestaña Historial aparece en Ver detalle solo con su permiso', function () {
    $this->actingAs(historialUsuario('Sin Permiso', ['mostrar-detalle-muestra']));
    Livewire::test(GestionarMuestras::class)
        ->call('ver', $this->muestra->id)
        ->assertDontSee('Historial')
        ->set('pestanaDetalle', 'historial')
        ->assertDontSee('Corrigió los datos');

    $this->actingAs(historialUsuario('Con Permiso', ['mostrar-detalle-muestra', 'ver-historial-muestra']));
    Livewire::test(GestionarMuestras::class)
        ->call('ver', $this->muestra->id)
        ->assertSee('Historial')
        ->assertDontSee('Corrigió los datos')
        ->set('pestanaDetalle', 'historial')
        ->assertSee('Ana Correccion')
        ->assertSee('corrigió los datos de la muestra C260001')
        ->assertSee('Hematocrito incorrecto');
});

test('la vista de auditorías muestra frases legibles sin código', function () {
    $this->actingAs(historialUsuario('Auditor', ['ver-auditorias']));

    Livewire::test(ListarAuditorias::class)
        ->assertSee('Corrigió los datos de la muestra C260001')
        ->assertSee('Llenó los resultados de Hemograma y los envió a revisión')
        ->assertSee('Hemograma · Muestra C260001')
        ->assertDontSee('paciente_nombre')
        ->assertDontSee('Actualizó Analisis');
});

test('la migración crea el permiso de historial sin asignarlo a ningún rol', function () {
    $permiso = Permission::where('name', 'ver-historial-muestra')->first();

    expect($permiso)->not->toBeNull()
        ->and($permiso->roles()->count())->toBe(0);
});
