<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Las contraseñas se leen desde las variables de entorno:
     *   - ADMIN_PASSWORD
     *   - BIOQUIMICO_PASSWORD
     *
     * Si no están definidas, se genera una contraseña segura aleatoria
     * y se muestra UNA SOLA VEZ en la consola.
     */
    public function run(): void
    {
        // Leer contraseñas desde .env o generar una segura
        $adminPassword = env('ADMIN_PASSWORD');
        $bioquimicoPassword = env('BIOQUIMICO_PASSWORD');

        $adminGenerada = false;
        $bioquimicoGenerada = false;

        if (empty($adminPassword)) {
            $adminPassword = Str::password(16);
            $adminGenerada = true;
        }

        if (empty($bioquimicoPassword)) {
            $bioquimicoPassword = Str::password(16);
            $bioquimicoGenerada = true;
        }

        // Crear usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@labvet.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Administrador');

        // Crear usuario Bioquímico
        $bioquimico = User::firstOrCreate(
            ['email' => 'bioquimico@labvet.com'],
            [
                'name' => 'Bioquímico Principal',
                'password' => Hash::make($bioquimicoPassword),
                'email_verified_at' => now(),
            ]
        );
        $bioquimico->assignRole('Bioquímico');

        // Informar al usuario
        $this->command->info('Usuarios creados exitosamente:');

        if ($adminGenerada) {
            $this->command->warn('⚠️  Contraseña de Administrador generada automáticamente (guárdala, no se mostrará de nuevo):');
            $this->command->line("   Email: admin@labvet.com");
            $this->command->line("   Password: {$adminPassword}");
        } else {
            $this->command->info('- Administrador: admin@labvet.com (contraseña desde .env)');
        }

        if ($bioquimicoGenerada) {
            $this->command->warn('⚠️  Contraseña de Bioquímico generada automáticamente (guárdala, no se mostrará de nuevo):');
            $this->command->line("   Email: bioquimico@labvet.com");
            $this->command->line("   Password: {$bioquimicoPassword}");
        } else {
            $this->command->info('- Bioquímico: bioquimico@labvet.com (contraseña desde .env)');
        }

        if (!$adminGenerada && !$bioquimicoGenerada) {
            $this->command->info('✅ Todas las contraseñas fueron leídas desde variables de entorno.');
        }
    }
}
