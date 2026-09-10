<?php

namespace Database\Seeders;

use Database\Seeders\OFICIAL\AdministradorOficialSeeder;
use Database\Seeders\OFICIAL\AsignaturasOficialesSeeder;
use Database\Seeders\OFICIAL\ConfiguracionCalendario2026Seeder;
use Database\Seeders\OFICIAL\CursosOficialesSeeder;
use Database\Seeders\OFICIAL\EspecialidadesOficialesSeeder;
use Database\Seeders\OFICIAL\Gestion2026Seeder;
use Database\Seeders\OFICIAL\ParalelosOficialesSeeder;
use Database\Seeders\OFICIAL\PlantillasHorariasOficialesSeeder;
use Database\Seeders\OFICIAL\RolesYPermisosOficialSeeder;
use Database\Seeders\OFICIAL\TurnosOficialesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SAVPInstitucionalOficialSeeder extends Seeder
{
    /**
     * Seeder Maestro Institucional Oficial de SAVP.
     * Carga exclusivamente los datos institucionales reales y confirmados
     * de la U.E.T.H. Franz Tamayo 3 de forma transaccional e idempotente.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Roles y permisos indispensables
            $this->call(RolesYPermisosOficialSeeder::class);

            // 2. Administrador oficial
            $this->call(AdministradorOficialSeeder::class);

            // 3. Gestión 2026
            $this->call(Gestion2026Seeder::class);

            // 4. Configuración calendario 2026 (200 días / 66-68-66)
            $this->call(ConfiguracionCalendario2026Seeder::class);

            // 5. Turnos oficiales (Mañana 07:45-13:20, Tarde 14:00-18:00)
            $this->call(TurnosOficialesSeeder::class);

            // 6. Plantillas horarias conocidas
            $this->call(PlantillasHorariasOficialesSeeder::class);

            // 7. Cursos oficiales (1ro a 6to de Secundaria)
            $this->call(CursosOficialesSeeder::class);

            // 8. Paralelos oficiales (A, B, C, D)
            $this->call(ParalelosOficialesSeeder::class);

            // 9. Asignaturas oficiales (14 asignaturas institucionales)
            $this->call(AsignaturasOficialesSeeder::class);

            // 10. Especialidades oficiales (9 especialidades técnicas)
            $this->call(EspecialidadesOficialesSeeder::class);
        });
    }
}
