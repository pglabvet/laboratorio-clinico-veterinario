<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SucursalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sucursales = [
            [
                'nombre' => 'Sucursal Centro',
                'codigo' => 'SUC-CENTRO',
                'direccion' => 'Av. Principal 123, Centro',
                'telefono' => '+591 3-1234567',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Sucursal Norte',
                'codigo' => 'SUC-NORTE',
                'direccion' => 'Calle Los Álamos 456, Zona Norte',
                'telefono' => '+591 3-2345678',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Sucursal Sur',
                'codigo' => 'SUC-SUR',
                'direccion' => 'Av. Las Américas 789, Zona Sur',
                'telefono' => '+591 3-3456789',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Sucursal Este',
                'codigo' => 'SUC-ESTE',
                'direccion' => 'Calle Comercio 321, Zona Este',
                'telefono' => '+591 3-4567890',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Sucursal Equipetrol',
                'codigo' => 'SUC-EQUIP',
                'direccion' => 'Av. San Martín 555, Equipetrol',
                'telefono' => '+591 3-5678901',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('sucursales')->insert($sucursales);
    }
}
