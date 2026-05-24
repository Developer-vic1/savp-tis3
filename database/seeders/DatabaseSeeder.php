<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,

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
        ]);
    }
}