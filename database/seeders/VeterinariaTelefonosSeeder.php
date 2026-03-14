<?php

namespace Database\Seeders;

use App\Models\VeterinariaTelefono;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VeterinariaTelefonosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = 'C:\\Users\\aintu\\Downloads\\pg-labvet\\veterinarias.csv';

        if (!file_exists($csvPath)) {
            $this->command->info("CSV no encontrado en {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Lee la primera fila (headers)

        while (($row = fgetcsv($file)) !== false) {
            // Mapear los valores según el header
            $data = array_combine($header, $row);

            // Crear el registro de teléfono con el teléfono principal
            VeterinariaTelefono::create([
                'veterinaria_id' => (int) $data['id'],
                'telefono' => $data['telefono'],
                'nombre_contacto' => 'Principal',
                'es_principal' => true,
            ]);
        }

        fclose($file);
        $this->command->info('Teléfonos de veterinarias migrados exitosamente.');
    }
}

