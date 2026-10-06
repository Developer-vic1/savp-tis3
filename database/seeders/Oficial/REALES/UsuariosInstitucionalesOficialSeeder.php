<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Sistema\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuariosInstitucionalesOficialSeeder extends Seeder
{
    /**
     * Reconstruye los usuarios institucionales (56 usuarios) y asigna sus roles
     * determinísticos en Spatie (model_has_roles.cod_usu).
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/personal_institucional.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $nomina = require $fuente;
        $now = Carbon::now();

        $adminPassword = env('SAVP_ADMIN_PASSWORD', 'victor123');
        if (empty($adminPassword)) {
            throw new \RuntimeException("DETENER SEEDER: Variable SAVP_ADMIN_PASSWORD no está definida en .env");
        }

        $passwordCache = [];

        foreach ($nomina as $item) {
            $uData = $item['user'] ?? null;
            if (! $uData) {
                continue;
            }

            $persona = Persona::where('cod_per', $uData['cod_per'])->first();
            if (! $persona) {
                continue;
            }

            // Determinar contraseña determinística según perfil con cache de hashes
            if ($uData['cod_usu'] === 'USU_0001') {
                $rawPassword = $adminPassword;
            } else {
                $rawPassword = strtoupper(trim($persona->ape_pat_per)).'123';
            }

            if (! isset($passwordCache[$rawPassword])) {
                $passwordCache[$rawPassword] = Hash::make($rawPassword);
            }
            $password = $passwordCache[$rawPassword];

            $user = User::updateOrCreate(
                ['cod_usu' => $uData['cod_usu']],
                [
                    'cod_per' => $uData['cod_per'],
                    'email' => $uData['email'],
                    'password' => $password,
                    'est_usu' => $uData['est_usu'] ?? 'ACTIVO',
                    'auth_provider' => $uData['auth_provider'] ?? 'local',
                    'email_verified_at' => $uData['email_verified_at'] ? Carbon::parse($uData['email_verified_at']) : $now,
                    'google_id' => $uData['google_id'] ?? null,
                    'avatar' => $uData['avatar'] ?? null,
                ]
            );

            // Asignar roles correspondientes
            $roles = $item['roles'] ?? [];
            if (! empty($roles)) {
                $user->syncRoles($roles);
            }
        }
    }
}
