<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\GestionAcademica;
use App\Models\InscripcionEstudiante;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AulaVirtualProvisioningService
{
    /**
     * Genera una previsualización detallada del provisionamiento de aulas virtuales
     * a partir de los planes académicos con horario en la gestión indicada.
     */
    public function previewForGestion(?string $codGea = null): array
    {
        $gestion = $this->resolverGestion($codGea);

        if (! $gestion) {
            return [
                'valido' => false,
                'error' => 'No se encontró una gestión académica activa o válida.',
                'gestion' => null,
                'metricas' => [
                    'planes_programados' => 0,
                    'aulas_existentes' => 0,
                    'aulas_nuevas' => 0,
                    'aulas_sincronizables' => 0,
                    'bloqueadas' => 0,
                    'advertencias' => 0,
                    'asignaturas' => 0,
                    'especialidades' => 0,
                ],
                'items' => [],
            ];
        }

        $planesAsignatura = PlanAsignatura::query()
            ->with([
                'asignatura',
                'docente.personalInstitucional.persona',
                'curso',
                'paralelo',
                'turno',
                'horariosDetalle' => fn ($q) => $q->where('est_hde', 'ACTIVO'),
                'claseVirtual',
            ])
            ->where('cod_gea', $gestion->cod_gea)
            ->get();

        $planesEspecialidad = PlanEspecialidad::query()
            ->with([
                'especialidad',
                'docente.personalInstitucional.persona',
                'curso',
                'paralelo',
                'turno',
                'horariosDetalle' => fn ($q) => $q->where('est_hde', 'ACTIVO'),
                'claseVirtual',
            ])
            ->where('cod_gea', $gestion->cod_gea)
            ->get();

        $items = [];
        $nuevas = 0;
        $existentes = 0;
        $sincronizables = 0;
        $bloqueadas = 0;
        $advertenciasTotal = 0;
        $asignaturasCount = 0;
        $especialidadesCount = 0;
        $programadosCount = 0;

        // Procesar Planes de Asignatura
        foreach ($planesAsignatura as $plan) {
            $analisis = $this->analizarPlan($plan, 'ASIGNATURA');
            $items[] = $analisis;

            if ($analisis['estado_provision'] === 'BLOQUEADO') {
                $bloqueadas++;
            } else {
                $programadosCount++;
                $asignaturasCount++;

                if ($analisis['estado_provision'] === 'NUEVA') {
                    $nuevas++;
                } elseif ($analisis['estado_provision'] === 'SINCRONIZABLE') {
                    $sincronizables++;
                    $existentes++;
                } else {
                    $existentes++;
                }
            }

            $advertenciasTotal += count($analisis['advertencias']);
        }

        // Procesar Planes de Especialidad
        foreach ($planesEspecialidad as $plan) {
            $analisis = $this->analizarPlan($plan, 'ESPECIALIDAD');
            $items[] = $analisis;

            if ($analisis['estado_provision'] === 'BLOQUEADO') {
                $bloqueadas++;
            } else {
                $programadosCount++;
                $especialidadesCount++;

                if ($analisis['estado_provision'] === 'NUEVA') {
                    $nuevas++;
                } elseif ($analisis['estado_provision'] === 'SINCRONIZABLE') {
                    $sincronizables++;
                    $existentes++;
                } else {
                    $existentes++;
                }
            }

            $advertenciasTotal += count($analisis['advertencias']);
        }

        return [
            'valido' => true,
            'gestion' => [
                'cod_gea' => $gestion->cod_gea,
                'ani_gea' => $gestion->ani_gea,
                'fii_gea' => $gestion->fii_gea instanceof \DateTimeInterface ? $gestion->fii_gea->format('Y-m-d') : (string) $gestion->fii_gea,
                'ffi_gea' => $gestion->ffi_gea instanceof \DateTimeInterface ? $gestion->ffi_gea->format('Y-m-d') : (string) $gestion->ffi_gea,
                'est_gea' => $gestion->est_gea,
            ],
            'metricas' => [
                'planes_programados' => $programadosCount,
                'aulas_existentes' => $existentes,
                'aulas_nuevas' => $nuevas,
                'aulas_sincronizables' => $sincronizables,
                'bloqueadas' => $bloqueadas,
                'advertencias' => $advertenciasTotal,
                'asignaturas' => $asignaturasCount,
                'especialidades' => $especialidadesCount,
                'total_analizados' => count($items),
            ],
            'items' => $items,
        ];
    }

    /**
     * Ejecuta el provisionamiento transaccional e idempotente de aulas virtuales.
     */
    public function provisionForGestion(?string $codGea = null, ?User $actor = null): array
    {
        $preview = $this->previewForGestion($codGea);

        if (! ($preview['valido'] ?? false)) {
            return [
                'exito' => false,
                'mensaje' => $preview['error'] ?? 'No fue posible ejecutar el provisionamiento.',
                'creadas' => 0,
                'existentes' => 0,
                'sincronizadas' => 0,
                'bloqueadas' => 0,
            ];
        }

        $gestion = GestionAcademica::findOrFail($preview['gestion']['cod_gea']);
        $creadas = 0;
        $sincronizadas = 0;
        $existentes = 0;
        $bloqueadas = 0;

        DB::beginTransaction();

        try {
            foreach ($preview['items'] as $item) {
                if ($item['estado_provision'] === 'BLOQUEADO') {
                    $bloqueadas++;

                    continue;
                }

                if ($item['tipo_origen'] === 'ASIGNATURA') {
                    $plan = PlanAsignatura::with(['asignatura', 'curso', 'paralelo', 'turno', 'docente.personalInstitucional.persona'])
                        ->find($item['codigo_plan']);

                    if (! $plan) {
                        continue;
                    }

                    $claseExistente = ClaseVirtual::where('cod_pas', $plan->cod_pas)->first();

                    if ($claseExistente) {
                        $this->syncExistingClass($claseExistente, $plan, $gestion);
                        $sincronizadas++;
                        $existentes++;
                    } else {
                        $this->createClassForPlan($plan, 'ASIGNATURA', $gestion);
                        $creadas++;
                    }
                } else {
                    $plan = PlanEspecialidad::with(['especialidad', 'curso', 'paralelo', 'turno', 'docente.personalInstitucional.persona'])
                        ->find($item['codigo_plan']);

                    if (! $plan) {
                        continue;
                    }

                    $claseExistente = ClaseVirtual::where('cod_pes', $plan->cod_pes)->first();

                    if ($claseExistente) {
                        $this->syncExistingClass($claseExistente, $plan, $gestion);
                        $sincronizadas++;
                        $existentes++;
                    } else {
                        $this->createClassForPlan($plan, 'ESPECIALIDAD', $gestion);
                        $creadas++;
                    }
                }
            }

            // Registrar en bitácora
            if (class_exists(BitacoraService::class)) {
                if ($actor && ! Auth::check()) {
                    Auth::setUser($actor);
                }

                BitacoraService::registrar(
                    accion: 'CREAR_AULAS_VIRTUALES',
                    tabla: 'clase_virtual',
                    registro: $gestion->cod_gea,
                    modulo: 'HORARIOS',
                    nombreRegistro: "Gestión {$gestion->ani_gea}",
                    descripcion: "Se prepararon {$creadas} aulas virtuales para la gestión {$gestion->ani_gea} (Existentes: {$existentes}, Sincronizadas: {$sincronizadas}, Bloqueadas: {$bloqueadas})."
                );
            }

            DB::commit();

            return [
                'exito' => true,
                'mensaje' => "Se prepararon correctamente {$creadas} aulas virtuales para la gestión {$gestion->ani_gea}.",
                'gestion' => $gestion->ani_gea,
                'creadas' => $creadas,
                'existentes' => $existentes,
                'sincronizadas' => $sincronizadas,
                'bloqueadas' => $bloqueadas,
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error durante el provisionamiento de aulas virtuales: '.$e->getMessage(), [
                'cod_gea' => $gestion->cod_gea,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'exito' => false,
                'mensaje' => 'No fue posible crear las aulas virtuales: '.$e->getMessage(),
                'creadas' => 0,
                'existentes' => 0,
                'sincronizadas' => 0,
                'bloqueadas' => 0,
            ];
        }
    }

    /**
     * Asegura la existencia de un aula virtual para un PlanAsignatura.
     */
    public function ensureForPlanAsignatura(PlanAsignatura $plan, ?User $actor = null): ?ClaseVirtual
    {
        $clase = ClaseVirtual::where('cod_pas', $plan->cod_pas)->first();

        if ($clase) {
            return $this->syncExistingClass($clase, $plan, $plan->gestionAcademica);
        }

        return $this->createClassForPlan($plan, 'ASIGNATURA', $plan->gestionAcademica);
    }

    /**
     * Asegura la existencia de un aula virtual para un PlanEspecialidad.
     */
    public function ensureForPlanEspecialidad(PlanEspecialidad $plan, ?User $actor = null): ?ClaseVirtual
    {
        $clase = ClaseVirtual::where('cod_pes', $plan->cod_pes)->first();

        if ($clase) {
            return $this->syncExistingClass($clase, $plan, $plan->gestionAcademica);
        }

        return $this->createClassForPlan($plan, 'ESPECIALIDAD', $plan->gestionAcademica);
    }

    /**
     * Sincroniza información básica de un aula existente sin alterar su contenido.
     */
    public function syncExistingClass(
        ClaseVirtual $clase,
        PlanAsignatura|PlanEspecialidad $plan,
        ?GestionAcademica $gestion = null
    ): ClaseVirtual {
        $gestion = $gestion ?: $plan->gestionAcademica;
        $materiaNombre = $plan instanceof PlanAsignatura
            ? ($plan->asignatura?->nom_asi ?? 'Materia')
            : ($plan->especialidad?->nom_esp ?? 'Especialidad');

        $cursoNombre = $plan->curso?->nom_cur ?? '';
        $paraleloNombre = $plan->paralelo?->nom_par ?? '';

        $nombreGenerado = $this->generarNombreAula($materiaNombre, $cursoNombre, $paraleloNombre);

        $clase->nom_cla = $nombreGenerado;

        if ($gestion) {
            if (! empty($gestion->fii_gea)) {
                $clase->fec_ini_cla = $gestion->fii_gea;
            }
            if (! empty($gestion->ffi_gea)) {
                $clase->fec_fin_cla = $gestion->ffi_gea;
            }
        }

        $clase->save();

        // Sincronizar estudiantes inscritos activos en el curso/paralelo
        $this->sincronizarEstudiantesEnClase($clase, $plan, $gestion);

        return $clase;
    }

    /**
     * Crea una nueva ClaseVirtual a partir del plan académico.
     */
    private function createClassForPlan(
        PlanAsignatura|PlanEspecialidad $plan,
        string $tipoOrigen,
        ?GestionAcademica $gestion = null
    ): ClaseVirtual {
        $gestion = $gestion ?: $plan->gestionAcademica;
        $materiaNombre = $tipoOrigen === 'ASIGNATURA'
            ? ($plan->asignatura?->nom_asi ?? 'Materia')
            : ($plan->especialidad?->nom_esp ?? 'Especialidad');

        $cursoNombre = $plan->curso?->nom_cur ?? '';
        $paraleloNombre = $plan->paralelo?->nom_par ?? '';

        $nombreGenerado = $this->generarNombreAula($materiaNombre, $cursoNombre, $paraleloNombre);
        $aniGestion = $gestion?->ani_gea ?? date('Y');

        $clase = new ClaseVirtual;
        $clase->nom_cla = $nombreGenerado;
        $clase->des_cla = "Aula virtual institucional para {$materiaNombre} - {$cursoNombre} {$paraleloNombre}, Gestión {$aniGestion}.";

        if ($tipoOrigen === 'ASIGNATURA') {
            $clase->cod_pas = $plan->cod_pas;
            $clase->cod_pes = null;
        } else {
            $clase->cod_pas = null;
            $clase->cod_pes = $plan->cod_pes;
        }

        $clase->fec_ini_cla = $gestion?->fii_gea;
        $clase->fec_fin_cla = $gestion?->ffi_gea;
        $clase->est_cla = 'ACTIVA';
        $clase->vis_cla = false; // Inicialmente oculta para estudiantes hasta su preparación/publicación
        $clase->save();

        // Matricular estudiantes inscritos
        $this->sincronizarEstudiantesEnClase($clase, $plan, $gestion);

        return $clase;
    }

    /**
     * Sincroniza e inscribe a los estudiantes del curso/paralelo/gestión en la clase virtual.
     */
    private function sincronizarEstudiantesEnClase(
        ClaseVirtual $clase,
        PlanAsignatura|PlanEspecialidad $plan,
        ?GestionAcademica $gestion = null
    ): int {
        return DB::transaction(function () use ($clase, $plan, $gestion) {
            $codGea = $gestion?->cod_gea ?: $plan->cod_gea;
            GestionAcademica::whereKey($codGea)->lockForUpdate()->firstOrFail();

            if (! $codGea || ! $plan->cod_cur || ! $plan->cod_par) {
                return 0;
            }

            $query = InscripcionEstudiante::query()
                ->where('cod_gea', $codGea)
                ->where('cod_cur', $plan->cod_cur)
                ->where('cod_par', $plan->cod_par)
                ->where('cod_tur', $plan->cod_tur)
                ->whereIn('est_ins', ['ACTIVA', 'CONFIRMADA', 'OBSERVADA'])
                ->whereHas('vigencias', fn ($q) => $q->enFecha(today()->toDateString())
                    ->where('cod_cur', $plan->cod_cur)->where('cod_par', $plan->cod_par)->where('cod_tur', $plan->cod_tur));

            // Si es plan de especialidad, filtrar además por la especialidad técnica del estudiante si corresponde
            if ($plan instanceof PlanEspecialidad && ! empty($plan->cod_esp)) {
                $query->where('cod_esp_tec', $plan->cod_esp);
            }

            $inscripciones = $query->get(['cod_est']);
            ClaseEstudiante::where('cod_cla', $clase->cod_cla)->where('est_cla_est', 'ACTIVO')->whereNotIn('cod_est', $inscripciones->pluck('cod_est'))
                ->update(['est_cla_est' => 'INACTIVO', 'fec_ret_cla_est' => today()->toDateString(), 'updated_at' => now()]);
            $matriculados = 0;

            foreach ($inscripciones as $inscripcion) {
                $existe = ClaseEstudiante::where('cod_cla', $clase->cod_cla)
                    ->where('cod_est', $inscripcion->cod_est)
                    ->exists();

                if (! $existe) {
                    $ce = new ClaseEstudiante;
                    $ce->cod_cla = $clase->cod_cla;
                    $ce->cod_est = $inscripcion->cod_est;
                    $ce->fec_inc_cla_est = now()->toDateString();
                    $ce->est_cla_est = 'ACTIVO';
                    $ce->save();
                    $matriculados++;
                } else {
                    ClaseEstudiante::where('cod_cla', $clase->cod_cla)->where('cod_est', $inscripcion->cod_est)
                        ->where('est_cla_est', '<>', 'ACTIVO')->update(['est_cla_est' => 'ACTIVO', 'fec_ret_cla_est' => null, 'updated_at' => now()]);
                }
            }

            return $matriculados;
        }, 3);
    }

    /**
     * Analiza un plan académico individual clasificando bloqueos y advertencias.
     */
    private function analizarPlan(PlanAsignatura|PlanEspecialidad $plan, string $tipo): array
    {
        $bloqueos = [];
        $advertencias = [];

        $materia = $tipo === 'ASIGNATURA'
            ? ($plan->asignatura?->nom_asi ?? 'Sin materia')
            : ($plan->especialidad?->nom_esp ?? 'Sin especialidad');

        $curso = $plan->curso?->nom_cur ?? 'Sin curso';
        $paralelo = $plan->paralelo?->nom_par ?? 'Sin paralelo';
        $turno = $plan->turno?->nom_tur ?? 'Sin turno';

        $personaDocente = $plan->docente?->personalInstitucional?->persona;
        $docenteNombre = $personaDocente
            ? trim("{$personaDocente->nom_per} {$personaDocente->ape_pat_per} {$personaDocente->ape_mat_per}")
            : 'Sin docente asignado';

        // 1. Verificación de Estado del Plan
        $estadoPlan = $tipo === 'ASIGNATURA' ? ($plan->est_pas ?? '') : ($plan->est_pes ?? '');
        if ($estadoPlan !== 'ACTIVO') {
            $bloqueos[] = "El plan se encuentra en estado '{$estadoPlan}'.";
        }

        // 2. Verificación de Horario
        $tieneHorario = $plan->horariosDetalle->isNotEmpty();
        $totalBloques = $plan->horariosDetalle->count();

        if (! $tieneHorario) {
            $bloqueos[] = 'El plan no tiene horario asignado en la matriz semanal.';
        }

        // 3. Verificación de Docente
        if (! $plan->docente) {
            $bloqueos[] = 'No tiene docente asignado.';
        } elseif (($plan->docente->est_doc ?? '') !== 'ACTIVO') {
            $bloqueos[] = 'El docente asignado se encuentra inactivo.';
        } else {
            if ($personaDocente && empty($personaDocente->tel_per)) {
                $advertencias[] = 'El docente asignado no tiene número de teléfono registrado.';
            }
        }

        // 4. Verificación de Curso y Paralelo
        if (! $plan->curso) {
            $bloqueos[] = 'El curso asignado es inexistente.';
        } elseif (($plan->curso->est_cur ?? '') !== 'ACTIVO') {
            $bloqueos[] = 'El curso asignado se encuentra inactivo.';
        }

        if (! $plan->paralelo) {
            $bloqueos[] = 'El paralelo asignado es inexistente.';
        } elseif (($plan->paralelo->est_par ?? '') !== 'ACTIVO') {
            $bloqueos[] = 'El paralelo asignado se encuentra inactivo.';
        }

        // 5. Carga horaria atípica (advertencia)
        $horas = $tipo === 'ASIGNATURA' ? (int) ($plan->hor_pas ?? 0) : (int) ($plan->hor_pes ?? 0);
        if ($horas <= 0) {
            $advertencias[] = 'La carga horaria declarada en el plan es 0 horas.';
        }

        // Determinar estado de provisión
        $claseExistente = $plan->claseVirtual;
        $estadoProvision = 'NUEVA';

        if (! empty($bloqueos)) {
            $estadoProvision = 'BLOQUEADO';
        } elseif ($claseExistente) {
            $estadoProvision = 'EXISTENTE';
        }

        $nombreAulaSugerido = $this->generarNombreAula($materia, $curso, $paralelo);

        return [
            'tipo_origen' => $tipo,
            'codigo_plan' => $tipo === 'ASIGNATURA' ? $plan->cod_pas : $plan->cod_pes,
            'materia' => $materia,
            'curso' => $curso,
            'paralelo' => $paralelo,
            'turno' => $turno,
            'docente' => $docenteNombre,
            'bloques_horario' => $totalBloques,
            'horas_plan' => $horas,
            'tiene_clase' => (bool) $claseExistente,
            'cod_cla' => $claseExistente?->cod_cla,
            'nombre_aula' => $nombreAulaSugerido,
            'estado_provision' => $estadoProvision,
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'puede_provisionar' => empty($bloqueos),
        ];
    }

    /**
     * Genera el nombre canónico del aula virtual según estándares SAVP.
     * Ej: "Matemática - 1.º A", "Sistemas Informáticos - 5.º A".
     */
    public function generarNombreAula(string $materia, ?string $curso, ?string $paralelo): string
    {
        $cursoCorto = trim((string) $curso);

        // Normalizar "1ro de Secundaria" / "1ro" / "1°" a "1.º"
        if (preg_match('/^(\d+)(?:ro|do|er|to|vo|no|°|\.º|\.ª)?\s*(?:de\s+secundaria)?$/i', $cursoCorto, $matches)) {
            $cursoCorto = $matches[1].'.º';
        }

        $paraleloLimpio = trim((string) $paralelo);
        $materiaLimpia = trim($materia);

        if ($cursoCorto && $paraleloLimpio) {
            return "{$materiaLimpia} - {$cursoCorto} {$paraleloLimpio}";
        }

        if ($cursoCorto) {
            return "{$materiaLimpia} - {$cursoCorto}";
        }

        return $materiaLimpia;
    }

    /**
     * Resuelve la gestión académica activa o la especificada.
     */
    private function resolverGestion(?string $codGea): ?GestionAcademica
    {
        if ($codGea) {
            return GestionAcademica::find($codGea);
        }

        return GestionAcademica::where('est_gea', 'ACTIVO')
            ->orderByDesc('ani_gea')
            ->first()
            ?: GestionAcademica::orderByDesc('ani_gea')->first();
    }
}
