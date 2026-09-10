<?php

namespace Database\Seeders\OFICIAL;

use App\Models\Administrador;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdministradorOficialSeeder extends Seeder
{
    /**
     * Registra al administrador institucional oficial del sistema SAVP.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $adminEmail = env('SAVP_ADMIN_EMAIL', 'asturizagavictor@gmail.com');
        $adminPassword = env('SAVP_ADMIN_PASSWORD', 'victor123');

        // 1. Persona Oficial
        $persona = Persona::where('cod_per', 'PER_0001')
            ->orWhere('ema_per', $adminEmail)
            ->orWhere('ci_per', '13381086')
            ->first();

        $datosPersona = [
            'nom_per' => 'Victor Grover',
            'ape_pat_per' => 'Asturizaga',
            'ape_mat_per' => 'Plata',
            'ci_per' => '13381086',
            'com_per' => null,
            'exp_per' => 'LP',
            'fec_nac_per' => '2006-06-11',
            'gen_per' => 'M',
            'tel_per' => '75836807',
            'ema_per' => $adminEmail,
            'dir_per' => 'Zona Bajo Tejar, La Paz',
            'fot_per' => null,
            'est_per' => true,
        ];

        if ($persona) {
            $persona->update($datosPersona);
        } else {
            $persona = Persona::create(array_merge(['cod_per' => 'PER_0001'], $datosPersona));
        }

        // 2. Personal Institucional
        $personal = PersonalInstitucional::where('cod_pin', 'PIN_0001')
            ->orWhere('cod_per', $persona->cod_per)
            ->first();

        $datosPersonal = [
            'cod_per' => $persona->cod_per,
            'car_pin' => 'Administrador del sistema',
            'est_pin' => 'ACTIVO',
        ];

        if ($personal) {
            $personal->update($datosPersonal);
        } else {
            $personal = PersonalInstitucional::create(array_merge(['cod_pin' => 'PIN_0001'], $datosPersonal));
        }

        // 3. Registro en tabla Administrador (si aplica)
        $adminRegistro = Administrador::where('cod_adm', 'ADM_0001')
            ->orWhere('cod_pin', $personal->cod_pin)
            ->first();

        $datosAdminRegistro = [
            'cod_pin' => $personal->cod_pin,
            'est_adm' => 'ACTIVO',
        ];

        if ($adminRegistro) {
            $adminRegistro->update($datosAdminRegistro);
        } else {
            Administrador::create(array_merge(['cod_adm' => 'ADM_0001'], $datosAdminRegistro));
        }

        // 4. Usuario del Administrador
        $user = User::where('cod_usu', 'USU_0001')
            ->orWhere('cod_per', $persona->cod_per)
            ->orWhere('email', $adminEmail)
            ->first();

        $datosUsuario = [
            'cod_per' => $persona->cod_per,
            'email' => $adminEmail,
            'email_verified_at' => $now,
            'password' => Hash::make($adminPassword),
            'est_usu' => 'ACTIVO',
        ];

        if ($user) {
            $user->update($datosUsuario);
        } else {
            $user = User::create(array_merge(['cod_usu' => 'USU_0001'], $datosUsuario));
        }

        // 5. Asignar rol Administrador
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        if (! $user->hasRole('Administrador')) {
            $user->assignRole('Administrador');
        }
    }
}
