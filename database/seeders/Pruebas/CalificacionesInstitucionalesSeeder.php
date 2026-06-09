<?php

namespace Database\Seeders\Pruebas;

use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\PeriodoEvaluacion;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Database\Seeder;

class CalificacionesInstitucionalesSeeder extends Seeder
{
    public function run(): void
    {
        $estudiantes = Estudiante::where('est_est', 'ACTIVO')->orderBy('cod_est')->get();
        $asignaturas = Asignatura::where('est_asi', 'ACTIVO')->orderBy('cod_asi')->take(5)->get();
        $periodos = PeriodoEvaluacion::where('est_pev', 'ACTIVO')->orderBy('ord_pev')->get();

        if ($estudiantes->isEmpty() || $asignaturas->isEmpty() || $periodos->isEmpty()) {
            $this->command?->warn('No se generaron calificaciones: faltan estudiantes, asignaturas o periodos activos.');

            return;
        }

        $soporte = app(CalificacionInteligente::class);

        foreach ($estudiantes as $indiceEstudiante => $estudiante) {
            foreach ($asignaturas as $indiceAsignatura => $asignatura) {
                foreach ($periodos as $indicePeriodo => $periodo) {
                    $nota = 48 + (($indiceEstudiante * 7 + $indiceAsignatura * 9 + $indicePeriodo * 6) % 52);

                    Calificacion::updateOrCreate(
                        [
                            'cod_est' => $estudiante->cod_est,
                            'cod_asi' => $asignatura->cod_asi,
                            'cod_pev' => $periodo->cod_pev,
                        ],
                        [
                            'not_cal' => $nota,
                            'obs_cal' => $soporte->observacion($nota),
                            'est_cal' => 'ACTIVO',
                        ],
                    );
                }
            }
        }

        $this->command?->info('Calificaciones institucionales preparadas correctamente.');
    }
}
