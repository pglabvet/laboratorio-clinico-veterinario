<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@labvet.com'],
            [
                'name' => 'marcos pillco mamani',
                'password' => Hash::make('mpm210214503'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Administrador');

        // Crear usuario Bioquímico
        $bioquimico = User::firstOrCreate(
            ['email' => 'bioquimico@labvet.com'],
            [
                'name' => 'Bioquímico Principal',
                'password' => Hash::make('bioquimico2025'),
                'email_verified_at' => now(),
            ]
        );
        $bioquimico->assignRole('Bioquímico');

        $this->command->info('Usuarios creados exitosamente:');
        $this->command->info('- Administrador: admin@gmail.com / mpm210214503');
        $this->command->info('- Bioquímico: bioquimico@gmail.com / bioquimico2025');
    }
}
