<?php

namespace Database\Seeders\POBLACION;

use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class Estudiantes2026Seeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 612 estudiantes oficiales,
     * sus registros en Persona, sus cuentas de Usuario en Users y la asignación del rol
     * Estudiante en Spatie.
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/estudiantes_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente de estudiantes no encontrada: {$fuente}");
        }

        $estudiantes = require $fuente;
        $now = Carbon::now();

        // Asegurar que el rol Estudiante exista
        Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);

        $passwordCache = [];
        $existingUsers = User::pluck('cod_usu')->flip()->toArray();
        $usersWithEstudianteRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Estudiante')
            ->pluck('model_has_roles.cod_usu')
            ->flip()
            ->toArray();

        $rolesToInsert = [];

        foreach ($estudiantes as $item) {
            $pData = $item['persona'];
            $eData = $item['estudiante'];
            $uData = $item['user'] ?? null;

            // 1. Persona
            Persona::updateOrCreate(
                ['cod_per' => $pData['cod_per']],
                [
                    'nom_per' => $pData['nom_per'],
                    'ape_pat_per' => $pData['ape_pat_per'],
                    'ape_mat_per' => $pData['ape_mat_per'] ?? null,
                    'ci_per' => $pData['ci_per'],
                    'com_per' => $pData['com_per'] ?? null,
                    'exp_per' => $pData['exp_per'] ?? null,
                    'fec_nac_per' => $pData['fec_nac_per'] ?? null,
                    'gen_per' => $pData['gen_per'] ?? null,
                    'tel_per' => $pData['tel_per'] ?? null,
                    'ema_per' => $pData['ema_per'] ?? null,
                    'dir_per' => $pData['dir_per'] ?? null,
                    'fot_per' => $pData['fot_per'] ?? null,
                    'est_per' => $pData['est_per'] ?? true,
                ]
            );

            // 2. Estudiante
            Estudiante::updateOrCreate(
                ['cod_est' => $eData['cod_est']],
                [
                    'rud_est' => $eData['rud_est'],
                    'cod_per' => $eData['cod_per'],
                    'cod_tve' => $eData['cod_tve'],
                    'cod_ipe' => $eData['cod_ipe'] ?? null,
                    'cod_esp' => $eData['cod_esp'] ?? null,
                    'est_est' => $eData['est_est'] ?? 'ACTIVO',
                ]
            );

            // 3. Usuario & Rol Estudiante
            if ($uData) {
                $rawPassword = strtoupper(trim($pData['ape_pat_per'])).'123';
                if (! isset($passwordCache[$rawPassword])) {
                    $passwordCache[$rawPassword] = Hash::make($rawPassword);
                }
                $password = $passwordCache[$rawPassword];

                User::updateOrCreate(
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

                if (! isset($usersWithEstudianteRole[$uData['cod_usu']])) {
                    $estudianteRole = Role::where('name', 'Estudiante')->first();
                    if ($estudianteRole) {
                        DB::table('model_has_roles')->updateOrInsert(
                            [
                                'role_id' => $estudianteRole->id,
                                'model_type' => 'App\Models\User',
                                'cod_usu' => $uData['cod_usu']
                            ],
                            []
                        );
                    }
                }
            }
        }
    }
}
