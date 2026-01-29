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
                'telefono' => '+591 3-3334455',
                'email' => 'contacto@vetsanfrancisco.com',
                'direccion' => 'Av. Cristo Redentor 234, Santa Cruz',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Hospital Veterinario PetCare',
                'responsable' => 'Dra. María Elena Suárez',
                'telefono' => '+591 3-3445566',
                'email' => 'info@petcarehospital.com',
                'direccion' => 'Calle Palmeras 567, Barrio Urbari',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Centro Veterinario Los Ángeles',
                'responsable' => 'Dr. Roberto Paz García',
                'telefono' => '+591 3-3556677',
                'email' => 'veterinaria.losangeles@gmail.com',
                'direccion' => 'Av. Alemana 890, 3er Anillo',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Clínica Veterinaria Amigos Peludos',
                'responsable' => 'Dra. Ana Patricia Rojas',
                'telefono' => '+591 3-3667788',
                'email' => 'amigos.peludos@outlook.com',
                'direccion' => 'Calle Beni 123, Zona Central',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Hospital Veterinario VidaAnimal',
                'responsable' => 'Dr. Jorge Luis Fernández',
                'telefono' => '+591 3-3778899',
                'email' => 'contacto@vidaanimal.vet',
                'direccion' => 'Av. Banzer 456, Equipetrol',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Clínica Veterinaria El Refugio',
                'responsable' => 'Dra. Paola Sandoval Martínez',
                'telefono' => '+591 3-3889900',
                'email' => 'elrefugio.vet@gmail.com',
                'direccion' => 'Calle Warnes 789, Barrio Plan 3000',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Centro de Salud Animal VetPlus',
                'responsable' => 'Dr. Andrés Gutiérrez López',
                'telefono' => '+591 3-3990011',
                'email' => 'info@vetplus.bo',
                'direccion' => 'Av. Roca y Coronado 321, Zona Norte',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Veterinaria Mascotas Felices',
                'responsable' => 'Dra. Claudia Morales Vaca',
                'telefono' => '+591 3-3001122',
                'email' => 'mascotasfelices@hotmail.com',
                'direccion' => 'Calle Oruro 654, Barrio Hamacas',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Clínica Veterinaria Santa Bárbara',
                'responsable' => 'Dr. Fernando Roca Peña',
                'telefono' => '+591 3-3112233',
                'email' => 'santabarbara.vet@yahoo.com',
                'direccion' => 'Av. Virgen de Luján 987, Radial 26',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Hospital Veterinario Camino Verde',
                'responsable' => 'Dra. Laura Chávez Delgado',
                'telefono' => '+591 3-3223344',
                'email' => 'caminoverde.vet@gmail.com',
                'direccion' => 'Radial 13, Km 5, Zona Norte',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('veterinarias')->insert($veterinarias);
    }
}
