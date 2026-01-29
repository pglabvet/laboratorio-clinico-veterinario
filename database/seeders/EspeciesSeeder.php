<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EspeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especies = [
            [
                'nombre' => 'Canino',
                'descripcion' => 'Perros domésticos de todas las razas y tamaños',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Felino',
                'descripcion' => 'Gatos domésticos de todas las razas',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Ave',
                'descripcion' => 'Aves domésticas y exóticas (loros, canarios, guacamayos, etc.)',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Roedor',
                'descripcion' => 'Hamsters, conejos, cuyes, chinchillas y otros roedores',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Reptil',
                'descripcion' => 'Tortugas, iguanas, serpientes y otros reptiles',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Equino',
                'descripcion' => 'Caballos, burros y otros equinos',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Bovino',
                'descripcion' => 'Ganado vacuno',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Ovino',
                'descripcion' => 'Ovejas y corderos',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Porcino',
                'descripcion' => 'Cerdos y lechones',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Exótico',
                'descripcion' => 'Especies exóticas no clasificadas en otras categorías',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('especies')->insert($especies);
    }
}
