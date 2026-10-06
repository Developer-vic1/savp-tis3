<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\Asignatura;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\EspecialidadTecnica;
use App\Models\Oficial\Academico\Paralelo;
use App\Models\Oficial\Academico\TipoVinculacionEstudiante;
use App\Models\Oficial\Academico\Turno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosAcademicosOficialSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los catálogos académicos:
     * - Cursos (6)
     * - Paralelos (4)
     * - Asignaturas (14)
     * - Especialidades Técnicas (11)
     * - Turnos (2)
     * - Tipos de Vinculación de Estudiantes (3)
     * - Estados de Asistencia (5)
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/catalogos_academicos_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $data = require $fuente;

        // 1. Cursos (6)
        foreach ($data['curso'] as $row) {
            Curso::updateOrCreate(
                ['cod_cur' => $row['cod_cur']],
                [
                    'nom_cur' => $row['nom_cur'],
                    'niv_cur' => $row['niv_cur'] ?? 'Secundaria',
                    'est_cur' => $row['est_cur'],
                ]
            );
        }

        // 2. Paralelos (4)
        foreach ($data['paralelo'] as $row) {
            Paralelo::updateOrCreate(
                ['cod_par' => $row['cod_par']],
                [
                    'nom_par' => $row['nom_par'],
                    'est_par' => $row['est_par'],
                ]
            );
        }

        // 3. Asignaturas (14)
        foreach ($data['asignatura'] as $row) {
            Asignatura::updateOrCreate(
                ['cod_asi' => $row['cod_asi']],
                [
                    'nom_asi' => $row['nom_asi'],
                    'sig_asi' => $row['sig_asi'] ?? null,
                    'hor_asi' => $row['hor_asi'] ?? null,
                    'est_asi' => $row['est_asi'],
                ]
            );
        }

        // 4. Especialidades Técnicas (11)
        foreach ($data['especialidad_tecnica'] as $row) {
            EspecialidadTecnica::updateOrCreate(
                ['cod_esp' => $row['cod_esp']],
                [
                    'nom_esp' => $row['nom_esp'],
                    'des_esp' => $row['des_esp'] ?? null,
                    'est_esp' => $row['est_esp'],
                ]
            );
        }

        // 5. Turnos (2)
        foreach ($data['turno'] as $row) {
            Turno::updateOrCreate(
                ['cod_tur' => $row['cod_tur']],
                [
                    'nom_tur' => $row['nom_tur'],
                    'hor_ini_tur' => $row['hor_ini_tur'],
                    'hor_fin_tur' => $row['hor_fin_tur'],
                    'est_tur' => $row['est_tur'],
                ]
            );
        }

        // 6. Tipos de Vinculación de Estudiantes (3)
        foreach ($data['tipo_vinculacion_estudiante'] as $row) {
            TipoVinculacionEstudiante::updateOrCreate(
                ['cod_tve' => $row['cod_tve']],
                [
                    'nom_tve' => $row['nom_tve'],
                    'des_tve' => $row['des_tve'] ?? null,
                    'est_tve' => $row['est_tve'],
                ]
            );
        }

        // 7. Estados de Asistencia (5)
        foreach ($data['estado_asistencia'] as $row) {
            DB::table('estado_asistencia')->updateOrInsert(
                ['cod_est_asi' => $row['cod_est_asi']],
                [
                    'nom_est_asi' => $row['nom_est_asi'],
                    'abr_est_asi' => $row['abr_est_asi'],
                    'des_est_asi' => $row['des_est_asi'] ?? null,
                    'color_est_asi' => $row['color_est_asi'] ?? null,
                    'valor_porcentual' => $row['valor_porcentual'] ?? 0,
                    'afecta_asistencia' => $row['afecta_asistencia'] ?? false,
                    'requiere_observacion' => $row['requiere_observacion'] ?? false,
                    'est_est_asi' => $row['est_est_asi'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }
}
