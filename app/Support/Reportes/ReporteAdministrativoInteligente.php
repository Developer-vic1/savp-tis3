<?php

namespace App\Support\Reportes;

use App\Models\Estudiante;
use App\Models\Docente;
use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\EspecialidadTecnica;
use App\Models\InscripcionEstudiante;
use App\Models\User;

class ReporteAdministrativoInteligente
{
    public function diagnostico(array $metricas): array
    {
        $sinDatos = collect($metricas)->filter(fn ($valor) => (int) $valor === 0)->keys()->values()->all();

        // Calculate orphan checks
        $alertas = [];
        $recomendaciones = [];

        $totalEstudiantes = Estudiante::count();
        $totalInscripciones = InscripcionEstudiante::count();
        if ($totalEstudiantes > 0 && $totalInscripciones === 0) {
            $alertas[] = 'Incoherencia crítica: Existen estudiantes registrados, pero ninguna inscripción escolar consolidada.';
            $recomendaciones[] = 'Ejecutar el proceso de inscripción colectiva para activar el periodo lectivo actual.';
        } elseif ($totalEstudiantes > $totalInscripciones) {
            $diff = $totalEstudiantes - $totalInscripciones;
            $alertas[] = "Inscripciones pendientes: Hay {$diff} estudiantes registrados sin inscripciones activas.";
            $recomendaciones[] = 'Identificar estudiantes rezagados de inscripción mediante el módulo de inscripciones.';
        }

        $totalAsignaturas = Asignatura::count();
        $totalCalificaciones = Calificacion::count();
        if ($totalAsignaturas > 0 && $totalCalificaciones === 0) {
            $alertas[] = 'Datos pendientes: Existen asignaturas creadas en el catálogo, pero ninguna calificación registrada.';
            $recomendaciones[] = 'Recordar a los docentes ingresar las notas correspondientes al primer periodo de evaluación.';
        }

        $totalEspecialidades = EspecialidadTecnica::count();
        $estudiantesConEspecialidad = Estudiante::whereNotNull('cod_esp')->count();
        if ($totalEspecialidades > 0 && $estudiantesConEspecialidad === 0) {
            $alertas[] = 'Orientación pendiente: Hay especialidades técnicas registradas, pero ningún estudiante vinculado a ellas.';
            $recomendaciones[] = 'Actualizar el perfil BTH de los estudiantes de secundaria asociándolos a su especialidad correspondiente.';
        }

        $docentesSinPlan = Docente::whereDoesntHave('planAsignaturas')->count();
        if ($docentesSinPlan > 0) {
            $alertas[] = "Carga docente pendiente: Se registran {$docentesSinPlan} docentes sin planes de asignatura asignados.";
            $recomendaciones[] = 'Asignar materias y cursos a los docentes que figuran sin carga horaria activa.';
        }

        // Modulos analysis
        $modulosFuertes = [];
        $modulosPendientes = [];

        foreach ($metricas as $modulo => $cant) {
            if ($cant > 5) {
                $modulosFuertes[] = $modulo;
            } elseif ($cant === 0) {
                $modulosPendientes[] = $modulo;
            }
        }

        // Calculate completeness
        $sinDatosCount = count($sinDatos);
        $totalModulos = count($metricas);
        $completitud = $totalModulos === 0 ? 0 : (int) round((($totalModulos - $sinDatosCount) / $totalModulos) * 100);

        // State detection
        if ($completitud < 50 || count($alertas) >= 3) {
            $estado = 'Requiere atención urgente';
            $estadoGeneral = 'CRITICO';
        } elseif ($completitud < 80 || count($alertas) >= 1) {
            $estado = 'Operativo con advertencias';
            $estadoGeneral = 'ADVERTENCIA';
        } else {
            $estado = 'Operativo y Consolidado';
            $estadoGeneral = 'OPERATIVO';
        }

        return [
            'estado' => $estado,
            'estado_general' => $estadoGeneral,
            'completitud' => $completitud,
            'modulos_fuertes' => $modulosFuertes,
            'modulos_pendientes' => $modulosPendientes,
            'alertas' => $alertas ?: ['El sistema se encuentra en un estado saludable de carga de información.'],
            'recomendaciones' => $recomendaciones ?: ['Mantener las copias de seguridad semanales activadas.', 'Auditar el registro de bitácora mensualmente.'],
            'advertencias' => $sinDatos, // Keep for backward compatibility with view
        ];
    }
}
