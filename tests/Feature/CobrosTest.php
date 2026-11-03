<?php

use App\Livewire\Cobros\GestionarCobros;
use App\Livewire\Cobros\RegistrarCobroMuestra;
use App\Models\Cobro;
use App\Models\Muestra;
use App\Models\PagoCobro;
use App\Models\User;
use App\Services\CobroService;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

function crearSucursal(string $nombre): int
{
    return DB::table('sucursales')->insertGetId([
        'nombre' => $nombre,
        'codigo' => strtoupper($nombre),
        'direccion' => 'Calle 1',
        'telefono' => '70000000',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function crearMuestra(int $sucursalId, string $codigo = 'C260001'): Muestra
{
    $especieId = DB::table('especies')->insertGetId(['nombre' => 'Canino', 'created_at' => now(), 'updated_at' => now()]);
    $veterinariaId = DB::table('veterinarias')->insertGetId([
        'nombre' => 'Vet '.md5($codigo),
        'responsable' => 'Dr. Prueba',
        'email' => md5($codigo).'@vet.test',
        'direccion' => 'Calle 2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Muestra::create([
        'codigo_muestra' => $codigo,
        'paciente_nombre' => 'Firulais',
        'especie_id' => $especieId,
        'edad' => '2 años',
        'sexo' => 'M',
        'propietario_nombre' => 'Juan',
        'veterinaria_id' => $veterinariaId,
        'sucursal_id' => $sucursalId,
        'tipo_muestra' => 'Sangre',
        'fecha_recepcion' => now(),
        'estado' => Muestra::ESTADO_PENDIENTE,
    ]);
}

function usuarioConPermisos(array $permisos, ?int $sucursalId = null): User
{
    $user = User::factory()->create(['sucursal_id' => $sucursalId]);
    $user->givePermissionTo($permisos);

    return $user;
}

beforeEach(function () {
    $this->service = app(CobroService::class);
    $this->sucursalId = crearSucursal('Centro');
    $this->muestra = crearMuestra($this->sucursalId);
    $this->cobro = $this->service->crearParaMuestra($this->muestra);
});

test('la migración crea los permisos de cobros sin asignarlos a ningún rol', function () {
    $permisos = Permission::where('name', 'like', '%cobro%')->pluck('name');

    expect($permisos)->toHaveCount(10)
        ->and(DB::table('role_has_permissions')
            ->whereIn('permission_id', Permission::where('name', 'like', '%cobro%')->pluck('id'))
            ->count())->toBe(0);
});

test('un cobro nuevo queda pendiente y sin monto', function () {
    expect($this->cobro->estado)->toBe(Cobro::ESTADO_PENDIENTE)
        ->and((float) $this->cobro->total)->toBe(0.0)
        ->and($this->cobro->metodo)->toBeNull();
});

test('el estado se calcula según los pagos', function () {
    $this->service->actualizar($this->cobro, 120, 'Hemograma', null);
    expect($this->cobro->fresh()->estado)->toBe(Cobro::ESTADO_PENDIENTE);

    $this->service->registrarPago($this->cobro, 50, PagoCobro::METODO_QR);
    $cobro = $this->cobro->fresh();
    expect($cobro->estado)->toBe(Cobro::ESTADO_PARCIAL)
        ->and($cobro->saldo)->toBe(70.0)
        ->and($cobro->metodo)->toBe('QR');

    $this->service->registrarPago($this->cobro, 70, PagoCobro::METODO_EFECTIVO);
    $cobro = $this->cobro->fresh();
    expect($cobro->estado)->toBe(Cobro::ESTADO_PAGADO)
        ->and($cobro->metodo)->toBe('Mixto');
});

test('no se puede pagar más que el saldo ni bajar el total por debajo de lo pagado', function () {
    $this->service->actualizar($this->cobro, 100, null, null);
    $this->service->registrarPago($this->cobro, 60, PagoCobro::METODO_TARJETA);

    expect(fn () => $this->service->registrarPago($this->cobro, 50, PagoCobro::METODO_QR))
        ->toThrow(DomainException::class);

    expect(fn () => $this->service->actualizar($this->cobro, 50, null, null))
        ->toThrow(DomainException::class);
});

test('anular un pago lo deja en el historial y recalcula el estado', function () {
    $this->service->actualizar($this->cobro, 100, null, null);
    $pago = $this->service->registrarPago($this->cobro, 100, PagoCobro::METODO_EFECTIVO);

    $this->service->anularPago($pago, 'Monto equivocado');

    $cobro = $this->cobro->fresh();
    expect($cobro->estado)->toBe(Cobro::ESTADO_PENDIENTE)
        ->and((float) $cobro->total_pagado)->toBe(0.0)
        ->and($cobro->pagos()->count())->toBe(1)
        ->and($pago->fresh()->estaAnulado())->toBeTrue();
});

test('exonerar y quitar la exoneración', function () {
    $this->service->actualizar($this->cobro, 100, null, null);
    $this->service->registrarPago($this->cobro, 40, PagoCobro::METODO_QR);

    $this->service->exonerar($this->cobro->fresh(), 'Cortesía');
    expect($this->cobro->fresh()->estado)->toBe(Cobro::ESTADO_EXONERADO);

    expect(fn () => $this->service->registrarPago($this->cobro, 10, PagoCobro::METODO_QR))
        ->toThrow(DomainException::class);

    $this->service->quitarExoneracion($this->cobro->fresh());
    expect($this->cobro->fresh()->estado)->toBe(Cobro::ESTADO_PARCIAL);
});

test('sin permiso ver-cobros no se accede al módulo', function () {
    $this->actingAs(usuarioConPermisos([], $this->sucursalId));

    $this->get(route('cobros.index'))->assertForbidden();
});

test('con permiso ver-cobros se ve la tabla con sus columnas', function () {
    $this->actingAs(usuarioConPermisos(['ver-cobros'], $this->sucursalId));

    $this->get(route('cobros.index'))
        ->assertOk()
        ->assertSeeInOrder(['Fecha', 'Código', 'Paciente', 'Veterinaria', 'Sucursal', 'Examen', 'Total', 'Método', 'Estado', 'Observación', 'Acciones'])
        ->assertSee('C260001');
});

test('cada usuario ve solo los cobros de su sucursal salvo vista general', function () {
    $otraSucursal = crearSucursal('Norte');
    $otro = $this->service->crearParaMuestra(crearMuestra($otraSucursal, 'N260001'));

    $this->actingAs(usuarioConPermisos(['ver-cobros'], $this->sucursalId));
    Livewire::test(GestionarCobros::class)
        ->assertSee('C260001')
        ->assertDontSee('N260001');

    Permission::firstOrCreate(['name' => 'vista-general-sistema', 'guard_name' => 'web']);
    $this->actingAs(usuarioConPermisos(['ver-cobros', 'vista-general-sistema'], $this->sucursalId));
    Livewire::test(GestionarCobros::class)
        ->assertSee('C260001')
        ->assertSee('N260001');
});

test('los botones de acciones dependen de su permiso', function () {
    $this->actingAs(usuarioConPermisos(['ver-cobros'], $this->sucursalId));
    Livewire::test(GestionarCobros::class)
        ->assertDontSee('Registrar pago')
        ->call('abrirPago', $this->cobro->id)
        ->assertForbidden();

    $this->actingAs(usuarioConPermisos(['ver-cobros', 'registrar-pago-cobro'], $this->sucursalId));
    Livewire::test(GestionarCobros::class)
        ->assertSee('Registrar pago');
});

test('registrar pago desde la tabla', function () {
    $this->service->actualizar($this->cobro, 80, null, null);
    $this->actingAs(usuarioConPermisos(['ver-cobros', 'registrar-pago-cobro'], $this->sucursalId));

    Livewire::test(GestionarCobros::class)
        ->call('abrirPago', $this->cobro->id)
        ->assertSet('montoPago', '80.00')
        ->set('metodoPago', 'QR')
        ->call('guardarPago')
        ->assertHasNoErrors();

    expect($this->cobro->fresh()->estado)->toBe(Cobro::ESTADO_PAGADO);
});

test('no puede actuar sobre cobros de otra sucursal', function () {
    $otro = $this->service->crearParaMuestra(crearMuestra(crearSucursal('Sur'), 'S260001'));
    $this->actingAs(usuarioConPermisos(['ver-cobros', 'eliminar-cobros'], $this->sucursalId));

    expect(fn () => Livewire::test(GestionarCobros::class)->call('confirmarEliminar', $otro->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('el formulario flotante guarda total y pago inicial', function () {
    $this->actingAs(usuarioConPermisos(['registrar-cobro-muestra'], $this->sucursalId));

    Livewire::test(RegistrarCobroMuestra::class, ['muestraId' => $this->muestra->id])
        ->assertSet('modalAbierto', true)
        ->assertSee('C260001')
        ->set('total', '150')
        ->set('montoPago', '50')
        ->set('metodoPago', 'Tarjeta')
        ->set('observacion', 'Paga el propietario')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('cobro-muestra-cerrado');

    $cobro = $this->cobro->fresh();
    expect($cobro->estado)->toBe(Cobro::ESTADO_PARCIAL)
        ->and($cobro->saldo)->toBe(100.0)
        ->and($cobro->observacion)->toBe('Paga el propietario');
});

test('el formulario flotante no aparece sin permiso', function () {
    $this->actingAs(usuarioConPermisos([], $this->sucursalId));

    Livewire::test(RegistrarCobroMuestra::class, ['muestraId' => $this->muestra->id])
        ->assertSet('modalAbierto', false);
});

test('los permisos de cobros aparecen en su propia sección en roles', function () {
    Permission::firstOrCreate(['name' => 'ver-roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'crear-roles', 'guard_name' => 'web']);
    $this->actingAs(usuarioConPermisos(['ver-roles', 'crear-roles'], $this->sucursalId));

    Livewire::test(App\Livewire\Roles\GestionarRoles::class)
        ->call('crear')
        ->assertSeeInOrder(['cobros', 'anular-pago-cobro', 'editar-cobros', 'eliminar-cobros', 'exonerar-cobro']);
});

test('el filtro de método distingue un método, mixto y sin pago', function () {
    $qr = $this->service->crearParaMuestra(crearMuestra($this->sucursalId, 'C260002'));
    $mixto = $this->service->crearParaMuestra(crearMuestra($this->sucursalId, 'C260003'));
    foreach ([$qr, $mixto] as $cobro) {
        $this->service->actualizar($cobro, 100, null, null);
    }
    $this->service->registrarPago($qr, 30, PagoCobro::METODO_QR);
    $this->service->registrarPago($mixto, 30, PagoCobro::METODO_QR);
    $this->service->registrarPago($mixto, 30, PagoCobro::METODO_EFECTIVO);

    $usuario = usuarioConPermisos(['ver-cobros'], $this->sucursalId);
    $ids = fn ($metodo) => $this->service->consulta(['metodo' => $metodo], $usuario)->pluck('id')->all();

    expect($ids('QR'))->toBe([$qr->id])
        ->and($ids('Mixto'))->toBe([$mixto->id])
        ->and($ids('Sin pago'))->toBe([$this->cobro->id])
        ->and($ids('Tarjeta'))->toBe([]);

    $this->actingAs($usuario);
    Livewire::test(GestionarCobros::class)
        ->set('filtroMetodo', 'Mixto')
        ->assertSee('C260003')
        ->assertDontSee('C260002');
});

test('sin permiso exportar-cobros no se ve el botón ni se puede exportar', function () {
    $this->actingAs(usuarioConPermisos(['ver-cobros'], $this->sucursalId));

    Livewire::test(GestionarCobros::class)->assertDontSee('Exportar PDF');
    $this->get(route('cobros.exportar.pdf'))->assertForbidden();
    $this->get(route('cobros.exportar.excel'))->assertForbidden();
});

test('exporta cobros a PDF y Excel con los filtros', function () {
    $this->service->actualizar($this->cobro, 100, 'Hemograma', null);
    $this->service->registrarPago($this->cobro, 40, PagoCobro::METODO_QR);
    $this->actingAs(usuarioConPermisos(['ver-cobros', 'exportar-cobros'], $this->sucursalId));

    Livewire::test(GestionarCobros::class)->assertSee('Exportar PDF')->assertSee('Exportar Excel');

    $this->get(route('cobros.exportar.pdf', ['metodo' => 'QR']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->get(route('cobros.exportar.excel', ['estado' => 'Parcial']))
        ->assertOk()
        ->assertDownload();
});
