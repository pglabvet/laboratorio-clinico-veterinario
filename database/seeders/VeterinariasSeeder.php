<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VeterinariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $veterinarias = [
            [
                'nombre' => 'Clínica Veterinaria San Francisco',
                'responsable' => 'Dr. Carlos Mendoza Rivera',
                'telefono' => '75102023',
                'email' => 'contacto@vetsanfrancisco.com',
                'direccion' => 'Av. Cristo Redentor 234, Santa Cruz',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Hospital Veterinario PetCare',
                'responsable' => 'Dra. María Elena Suárez',
                'telefono' => '75102023',
                'email' => 'info@petcarehospital.com',
                'direccion' => 'Calle Palmeras 567, Barrio Urbari',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Centro Veterinario Los Ángeles',
                'responsable' => 'Dr. Roberto Paz García',
                'telefono' => '75102023',
                'email' => 'veterinaria.losangeles@gmail.com',
                'direccion' => 'Av. Alemana 890, 3er Anillo',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('veterinarias')->insert($veterinarias);
    }
}
