<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar al seeder de roles y permisos
        $this->call([
            RolesAndPermissionsSeeder::class ,
            UsersSeeder::class ,
            SucursalesSeeder::class ,
            EspeciesSeeder::class ,
            VeterinariasSeeder::class ,
            VeterinariaTelefonosSeeder::class ,
            UnidadesMedidaSeeder::class,
            TiposAnalisisSeeder::class ,
            CategoriasInsumoSeeder::class,
            InsumosSeeder::class,
            EntradasInventarioSeeder::class,
            MuestrasSeeder::class,
        ]);
    }
}
