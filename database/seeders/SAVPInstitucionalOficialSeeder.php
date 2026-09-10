<?php

namespace Database\Seeders;

use Database\Seeders\OFICIAL\CatalogosAcademicosOficialSeeder;
use Database\Seeders\OFICIAL\DocentesOficialesSeeder;
use Database\Seeders\OFICIAL\GestionAcademicaOficialSeeder;
use Database\Seeders\OFICIAL\HorariosOficialesSeeder;
use Database\Seeders\OFICIAL\PersonalInstitucionalOficialSeeder;
use Database\Seeders\OFICIAL\PlanesAsignaturaOficialesSeeder;
use Database\Seeders\OFICIAL\PlanesEspecialidadOficialesSeeder;
use Database\Seeders\OFICIAL\RolesYPermisosOficialSeeder;
use Database\Seeders\OFICIAL\TurnosYPlantillasOficialSeeder;
use Database\Seeders\OFICIAL\UsuariosInstitucionalesOficialSeeder;
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
            // 1. Roles y Permisos Spatie (6 roles, 49 permisos, 128 asignaciones)
            $this->call(RolesYPermisosOficialSeeder::class);

            // 2. Gestión Académica, Calendario y Periodos de Evaluación
            $this->call(GestionAcademicaOficialSeeder::class);

            // 3. Catálogos Académicos (Cursos, Paralelos, Asignaturas, 11 Especialidades, Turnos, Vinculación, Asistencia)
            $this->call(CatalogosAcademicosOficialSeeder::class);

            // 4. Turnos, Plantillas Horarias (6) y Bloques Horarios (51)
            $this->call(TurnosYPlantillasOficialSeeder::class);

            // 5. Personal Institucional Oficial (56 personas: Admin, Director, Secretaria, Administrativos, Docentes)
            $this->call(PersonalInstitucionalOficialSeeder::class);

            // 6. Cuentas de Usuario Institucionales y Asignación de Roles (56 usuarios, 51 roles)
            $this->call(UsuariosInstitucionalesOficialSeeder::class);

            // 7. Docentes Oficiales (48 docentes confirmados)
            $this->call(DocentesOficialesSeeder::class);

            // 8. Planes de Asignatura Oficiales (300 planes)
            $this->call(PlanesAsignaturaOficialesSeeder::class);

            // 9. Planes de Especialidad Oficiales (68 planes)
            $this->call(PlanesEspecialidadOficialesSeeder::class);

            // 10. Horarios Cabecera (136) y Horario Detalle (4,120)
            $this->call(HorariosOficialesSeeder::class);
        });
    }
}
