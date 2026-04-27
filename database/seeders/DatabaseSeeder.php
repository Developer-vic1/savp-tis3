<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AsignaturaSeeder::class,
            EspecialidadTecnicaSeeder::class,
            CursoSeeder::class,
            GestionAcademicaSeeder::class,
            InstitucionProcedenciaSeeder::class,
            ParaleloSeeder::class,
            PeriodoEvaluacionSeeder::class,
            PersonaSeeder::class,
            RolSeeder::class,
            TipoVinculacionSeeder::class,
            TurnoSeeder::class,
            UsuarioAdminSeeder::class,
        ]);
    }
}
