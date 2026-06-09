<?php

namespace Database\Seeders\Pruebas;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\GestionAcademica;
use App\Models\Paralelo;
use App\Models\PlanAsignatura;
use App\Models\Turno;
use Illuminate\Database\Seeder;

class PlanesAsignaturaInstitucionalesSeeder extends Seeder
{
    public function run(): void
    {
        $asignaturas = Asignatura::where('est_asi', 'ACTIVO')->orderBy('cod_asi')->take(8)->get();
        $docentes = Docente::where('est_doc', 'ACTIVO')->orderBy('cod_doc')->get();
        $cursos = Curso::where('est_cur', 'ACTIVO')->orderBy('cod_cur')->get();
        $paralelos = Paralelo::where('est_par', 'ACTIVO')->orderBy('cod_par')->get();
        $turnos = Turno::where('est_tur', 'ACTIVO')->orderBy('cod_tur')->get();
        $gestion = GestionAcademica::orderByDesc('ani_gea')->first();

        if ($asignaturas->isEmpty() || $docentes->isEmpty() || $cursos->isEmpty() || $paralelos->isEmpty() || $turnos->isEmpty() || ! $gestion) {
            $this->command?->warn('No se generaron planes: faltan relaciones académicas obligatorias.');

            return;
        }

        foreach ($asignaturas as $indice => $asignatura) {
            $docente = $docentes[$indice % $docentes->count()];
            $curso = $cursos[$indice % $cursos->count()];
            $paralelo = $paralelos[$indice % $paralelos->count()];
            $turno = $turnos[$indice % $turnos->count()];

            PlanAsignatura::updateOrCreate(
                [
                    'cod_asi' => $asignatura->cod_asi,
                    'cod_doc' => $docente->cod_doc,
                    'cod_cur' => $curso->cod_cur,
                    'cod_par' => $paralelo->cod_par,
                    'cod_tur' => $turno->cod_tur,
                    'cod_gea' => $gestion->cod_gea,
                ],
                [
                    'hor_pas' => max(1, (int) ($asignatura->hor_asi ?: 4)),
                    'est_pas' => 'ACTIVO',
                ],
            );
        }

        $this->command?->info('Planes de asignatura institucionales preparados correctamente.');
    }
}
