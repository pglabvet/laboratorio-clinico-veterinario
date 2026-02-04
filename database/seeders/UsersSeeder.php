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
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Administrador');

        // Crear usuario Bioquímico
        $bioquimico = User::firstOrCreate(
            ['email' => 'bioquimico@labvet.com'],
            [
                'name' => 'Bioquímico Principal',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $bioquimico->assignRole('Bioquímico');

        $this->command->info('Usuarios creados exitosamente:');
        $this->command->info('- Administrador: admin@labvet.com / password123');
        $this->command->info('- Bioquímico: bioquimico@labvet.com / password123');
    }
}
