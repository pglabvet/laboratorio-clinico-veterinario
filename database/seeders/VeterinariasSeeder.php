<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VeterinariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = 'C:\\Users\\aintu\\Downloads\\pg-labvet\\veterinarias.csv';

        // Si existe el CSV, lee desde ahí. Si no, usa datos por defecto.
        if (file_exists($csvPath)) {
            $this->seedFromCsv($csvPath);
        } else {
            $this->seedDefaults();
        }
    }

    /**
     * Seed de datos por defecto (para desarrollo local sin CSV)
     */
    private function seedDefaults(): void
    {
        $veterinarias = [
            [
                'nombre' => 'Clínica Veterinaria San Francisco',
                'responsable' => 'Dr. Carlos Mendoza Rivera',
                'email' => 'contacto@vetsanfrancisco.com',
                'direccion' => 'Av. Cristo Redentor 234, Santa Cruz',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Hospital Veterinario PetCare',
                'responsable' => 'Dra. María Elena Suárez',
                'email' => 'info@petcarehospital.com',
                'direccion' => 'Calle Palmeras 567, Barrio Urbari',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Centro Veterinario Los Ángeles',
                'responsable' => 'Dr. Roberto Paz García',
                'email' => 'veterinaria.losangeles@gmail.com',
                'direccion' => 'Av. Alemana 890, 3er Anillo',
                'estado' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('veterinarias')->insert($veterinarias);
        $this->command->info('Veterinarias por defecto creadas.');
    }

    /**
     * Seed desde el CSV de datos reales
     */
    private function seedFromCsv(string $csvPath): void
    {
        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Lee los headers

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            // Crear veterinaria sin el campo telefono (ya no existe)
            DB::table('veterinarias')->insert([
                'id' => (int) $data['id'],
                'nombre' => $data['nombre'],
                'responsable' => $data['responsable'],
                'email' => $data['email'],
                'direccion' => $data['direccion'],
                'estado' => $data['estado'] === 'True' ? true : false,
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ]);
        }

        fclose($file);
        $this->command->info('Veterinarias migradas desde CSV.');
    }
}

