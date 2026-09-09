<?php

namespace Database\Seeders;

use Database\Seeders\DATOS\DatosSAVPTIS3Seeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            AulaVirtualPermissionSeeder::class,

            TurnoSeeder::class,
            CursoSeeder::class,
            ParaleloSeeder::class,
            AsignaturaSeeder::class,
            EspecialidadTecnicaSeeder::class,
            InstitucionProcedenciaSeeder::class,
            PeriodoEvaluacionSeeder::class,
            TipoVinculacionSeeder::class,
            PersonaSeeder::class,
            PersonalInstitucionalSeeder::class,
            UsuarioAdminSeeder::class,
            DatosSAVPTIS3Seeder::class,
        ]);
    }
}
