<?php

namespace App\Support\Academico;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InscripcionAcademica
{
    public const CAPACIDAD_REFERENCIAL_PARALELO = 35;

    public const TIPOS_INSCRIPCION = [
        'NUEVO',
        'REGULAR',
        'TRASLADO',
        'REINSCRIPCION',
        'REPITENTE',
        'EXCEPCIONAL',
        'VULNERABILIDAD',
        'EXTRANJERO',
    ];

    public const CONDICIONES_INSCRIPCION = [
        'NORMAL',
        'OBSERVADA',
        'CONDICIONAL',
        'PROVISIONAL',
        'SOBRECUPO',
        'TRASLADO',
        'VULNERABILIDAD',
    ];

    public const ESTADOS_INSCRIPCION = [
        'PENDIENTE',
        'INSCRITO',
        'OBSERVADO',
        'CONDICIONAL',
        'PROVISIONAL',
        'ANULADO',
        'RETIRADO',
    ];

    public const ESTADOS_NO_ACTIVOS = [
        'INACTIVO',
        'ANULADO',
        'RETIRADO',
    ];

    public const ESTADOS_DOCUMENTO = [
        'PRESENTADO',
        'PENDIENTE',
        'OBSERVADO',
        'NO_APLICA',
    ];

    public const ESTADOS_ESPECIALIDAD = [
        'NO_APLICA',
        'PENDIENTE',
        'ASIGNADA',
        'OBSERVADA',
    ];

    public const CURSOS_EDAD_REFERENCIAL = [
        [
            'orden' => 1,
            'nombre' => '1ro Secundaria',
            'edad_min' => 12,
            'edad_max' => 13,
            'keywords' => ['1ro', 'primero', '1°', '1er'],
        ],
        [
            'orden' => 2,
            'nombre' => '2do Secundaria',
            'edad_min' => 13,
            'edad_max' => 14,
            'keywords' => ['2do', 'segundo', '2°'],
        ],
        [
            'orden' => 3,
            'nombre' => '3ro Secundaria',
            'edad_min' => 14,
            'edad_max' => 15,
            'keywords' => ['3ro', 'tercero', '3°'],
        ],
        [
            'orden' => 4,
            'nombre' => '4to Secundaria',
            'edad_min' => 15,
            'edad_max' => 16,
            'keywords' => ['4to', 'cuarto', '4°'],
        ],
        [
            'orden' => 5,
            'nombre' => '5to Secundaria',
            'edad_min' => 16,
            'edad_max' => 17,
            'keywords' => ['5to', 'quinto', '5°'],
        ],
        [
            'orden' => 6,
            'nombre' => '6to Secundaria',
            'edad_min' => 17,
            'edad_max' => 19,
            'keywords' => ['6to', 'sexto', '6°'],
        ],
    ];

    /* -------------------------------------------------------------------------
     | CONTEXTO GENERAL
     * ------------------------------------------------------------------------- */

    public function gestionTrabajo(): ?array
    {
        if (! Schema::hasTable('gestion_academica')) {
            return null;
        }

        $gestion = DB::table('gestion_academica')
            ->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])
            ->orderByDesc('ani_gea')
            ->first();

        if (! $gestion) {
            $gestion = DB::table('gestion_academica')
                ->whereIn('est_gea', ['PLANIFICADA', 'PLANIFICADO'])
                ->orderByDesc('ani_gea')
                ->first();
        }

        if (! $gestion) {
            return null;
        }

        return [
            'cod_gea' => $gestion->cod_gea ?? null,
            'anio' => $gestion->ani_gea ?? null,
            'inicio' => $gestion->fii_gea ?? null,
            'fin' => $gestion->ffi_gea ?? null,
            'estado' => $gestion->est_gea ?? null,
            'rango' => $this->fechaTexto($gestion->fii_gea ?? null) . ' - ' . $this->fechaTexto($gestion->ffi_gea ?? null),
            'permite_inscripcion' => in_array($gestion->est_gea ?? '', ['ACTIVA', 'ACTIVO', 'PLANIFICADA', 'PLANIFICADO'], true),
        ];
    }

    public function catalogos(): array
    {
        return [
            'gestion_trabajo' => $this->gestionTrabajo(),
            'gestiones' => $this->gestiones(),
            'cursos' => $this->cursos(),
            'paralelos' => $this->paralelos(),
            'turnos' => $this->turnos(),
            'turno_manana' => $this->turnoManana(),
            'especialidades_tecnicas' => $this->especialidadesTecnicas(),
            'tipos_inscripcion' => self::TIPOS_INSCRIPCION,
            'condiciones_inscripcion' => self::CONDICIONES_INSCRIPCION,
            'estados_inscripcion' => self::ESTADOS_INSCRIPCION,
            'estados_documento' => self::ESTADOS_DOCUMENTO,
            'estados_especialidad' => self::ESTADOS_ESPECIALIDAD,
            'cursos_edad_referencial' => self::CURSOS_EDAD_REFERENCIAL,
        ];
    }

    public function resumenGeneral(?string $codGea = null): array
    {
        $gestion = $this->gestionTrabajo();
        $codGea = $codGea ?: ($gestion['cod_gea'] ?? null);

        if (! $codGea || ! Schema::hasTable('inscripcion_estudiante')) {
            return $this->resumenVacio();
        }

        $base = DB::table('inscripcion_estudiante')->where('cod_gea', $codGea);
        $activos = (clone $base)->whereNotIn('est_ins', self::ESTADOS_NO_ACTIVOS);

        return [
            'total' => (clone $base)->count(),
            'activos' => (clone $activos)->count(),
            'inscritos' => (clone $base)->where('est_ins', 'INSCRITO')->count(),
            'pendientes' => (clone $base)->where('est_ins', 'PENDIENTE')->count(),
            'observados' => (clone $base)->where('est_ins', 'OBSERVADO')->count(),
            'condicionales' => (clone $base)->where('est_ins', 'CONDICIONAL')->count(),
            'provisionales' => (clone $base)->where('est_ins', 'PROVISIONAL')->count(),
            'anulados' => (clone $base)->where('est_ins', 'ANULADO')->count(),
            'retirados' => (clone $base)->where('est_ins', 'RETIRADO')->count(),
            'nuevos' => (clone $base)->where('tip_ins', 'NUEVO')->count(),
            'regulares' => (clone $base)->where('tip_ins', 'REGULAR')->count(),
            'traslados' => (clone $base)->where('tip_ins', 'TRASLADO')->count(),
            'sobrecupos' => (clone $base)->where('con_ins', 'SOBRECUPO')->count(),
            'sin_inscripcion' => $this->contarEstudiantesSinInscripcion($codGea),
            'documentos_pendientes' => $this->contarDocumentosPorGestion($codGea, 'PENDIENTE'),
            'documentos_observados' => $this->contarDocumentosPorGestion($codGea, 'OBSERVADO'),
            'especialidades_pendientes' => $this->contarEspecialidadesPendientes($codGea),
        ];
    }

    /* -------------------------------------------------------------------------
     | ESTUDIANTES
     * ------------------------------------------------------------------------- */

    public function buscarEstudiantes(string $termino, int $limite = 10): array
    {
        $termino = trim($termino);

        if ($termino === '' || mb_strlen($termino) < 2) {
            return [];
        }

        if (! Schema::hasTable('estudiante') || ! Schema::hasTable('persona')) {
            return [];
        }

        $buscar = '%' . mb_strtolower($termino) . '%';

        return $this->queryEstudiantesConPersona()
            ->where(function ($query) use ($buscar) {
                $query->whereRaw('LOWER(persona.nom_per) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(persona.ape_pat_per) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ape_mat_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ci_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.tel_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ema_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(estudiante.rud_est, \'\')) LIKE ?', [$buscar]);
            })
            ->orderBy('persona.ape_pat_per')
            ->orderBy('persona.ape_mat_per')
            ->orderBy('persona.nom_per')
            ->limit($limite)
            ->get()
            ->map(fn($row) => $this->mapearEstudiante($row))
            ->values()
            ->all();
    }

    public function obtenerEstudiante(?string $codEst): ?array
    {
        if (! $codEst || ! Schema::hasTable('estudiante') || ! Schema::hasTable('persona')) {
            return null;
        }

        $row = $this->queryEstudiantesConPersona()
            ->where('estudiante.cod_est', $codEst)
            ->first();

        return $row ? $this->mapearEstudiante($row) : null;
    }

    public function estudiantesSinInscripcion(?string $codGea = null, string $busqueda = '', int $limite = 20): array
    {
        $gestion = $this->gestionTrabajo();
        $codGea = $codGea ?: ($gestion['cod_gea'] ?? null);

        if (! $codGea || ! Schema::hasTable('estudiante') || ! Schema::hasTable('persona') || ! Schema::hasTable('inscripcion_estudiante')) {
            return [];
        }

        $query = $this->queryEstudiantesConPersona()
            ->where('estudiante.est_est', 'ACTIVO')
            ->whereNotExists(function ($sub) use ($codGea) {
                $sub->from('inscripcion_estudiante')
                    ->whereColumn('inscripcion_estudiante.cod_est', 'estudiante.cod_est')
                    ->where('inscripcion_estudiante.cod_gea', $codGea)
                    ->whereNotIn('inscripcion_estudiante.est_ins', self::ESTADOS_NO_ACTIVOS);
            });

        $busqueda = trim($busqueda);

        if ($busqueda !== '') {
            $buscar = '%' . mb_strtolower($busqueda) . '%';

            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('LOWER(persona.nom_per) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(persona.ape_pat_per) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ape_mat_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(persona.ci_per, \'\')) LIKE ?', [$buscar])
                    ->orWhereRaw('LOWER(COALESCE(estudiante.rud_est, \'\')) LIKE ?', [$buscar]);
            });
        }

        return $query
            ->orderBy('persona.ape_pat_per')
            ->orderBy('persona.ape_mat_per')
            ->orderBy('persona.nom_per')
            ->limit($limite)
            ->get()
            ->map(function ($row) {
                $estudiante = $this->mapearEstudiante($row);
                $estudiante['curso_sugerido'] = $this->sugerirCursoPorEdad($estudiante['edad']);
                return $estudiante;
            })
            ->values()
            ->all();
    }

    public function contarEstudiantesSinInscripcion(?string $codGea = null): int
    {
        $gestion = $this->gestionTrabajo();
        $codGea = $codGea ?: ($gestion['cod_gea'] ?? null);

        if (! $codGea || ! Schema::hasTable('estudiante') || ! Schema::hasTable('inscripcion_estudiante')) {
            return 0;
        }

        return DB::table('estudiante')
            ->where('estudiante.est_est', 'ACTIVO')
            ->whereNotExists(function ($sub) use ($codGea) {
                $sub->from('inscripcion_estudiante')
                    ->whereColumn('inscripcion_estudiante.cod_est', 'estudiante.cod_est')
                    ->where('inscripcion_estudiante.cod_gea', $codGea)
                    ->whereNotIn('inscripcion_estudiante.est_ins', self::ESTADOS_NO_ACTIVOS);
            })
            ->count();
    }

    public function historialEstudiante(?string $codEst): array
    {
        if (! $codEst || ! Schema::hasTable('inscripcion_estudiante')) {
            return [];
        }

        return DB::table('inscripcion_estudiante')
            ->leftJoin('gestion_academica', 'gestion_academica.cod_gea', '=', 'inscripcion_estudiante.cod_gea')
            ->leftJoin('curso', 'curso.cod_cur', '=', 'inscripcion_estudiante.cod_cur')
            ->leftJoin('paralelo', 'paralelo.cod_par', '=', 'inscripcion_estudiante.cod_par')
            ->leftJoin('turno', 'turno.cod_tur', '=', 'inscripcion_estudiante.cod_tur')
            ->select([
                'inscripcion_estudiante.cod_ins',
                'inscripcion_estudiante.fei_ins',
                'inscripcion_estudiante.tip_ins',
                'inscripcion_estudiante.con_ins',
                'inscripcion_estudiante.est_ins',
                'inscripcion_estudiante.obs_ins',
                'gestion_academica.ani_gea',
                'curso.nom_cur',
                'paralelo.nom_par',
                'turno.nom_tur',
            ])
            ->where('inscripcion_estudiante.cod_est', $codEst)
            ->orderByDesc('gestion_academica.ani_gea')
            ->orderByDesc('inscripcion_estudiante.fei_ins')
            ->get()
            ->map(fn($row) => [
                'cod_ins' => $row->cod_ins,
                'gestion' => $row->ani_gea,
                'curso' => $row->nom_cur,
                'paralelo' => $row->nom_par,
                'turno' => $row->nom_tur,
                'fecha' => $this->fechaTexto($row->fei_ins),
                'tipo' => $row->tip_ins,
                'condicion' => $row->con_ins,
                'estado' => $row->est_ins,
                'observacion' => $row->obs_ins,
            ])
            ->values()
            ->all();
    }

    public function clasificarSituacionEstudiante(?string $codEst, ?string $codGea = null): array
    {
        if (! $codEst) {
            return [
                'situacion' => 'SIN_ESTUDIANTE',
                'tipo_sugerido' => 'REGULAR',
                'mensaje' => 'Selecciona un estudiante para clasificar su situación.',
            ];
        }

        $historial = $this->historialEstudiante($codEst);
        $duplicidad = $this->verificarInscripcionExistente($codEst, $codGea ?: ($this->gestionTrabajo()['cod_gea'] ?? null));

        if ($duplicidad['existe']) {
            return [
                'situacion' => 'YA_INSCRITO',
                'tipo_sugerido' => $duplicidad['registro']['tipo'] ?? 'REGULAR',
                'mensaje' => 'El estudiante ya tiene una inscripción activa en la gestión seleccionada.',
            ];
        }

        if (empty($historial)) {
            return [
                'situacion' => 'SIN_HISTORIAL',
                'tipo_sugerido' => 'NUEVO',
                'mensaje' => 'El estudiante no presenta historial de inscripción previo. Se sugiere inscripción nueva.',
            ];
        }

        $ultimo = $historial[0];

        if (($ultimo['estado'] ?? null) === 'RETIRADO') {
            return [
                'situacion' => 'RETORNO',
                'tipo_sugerido' => 'REINSCRIPCION',
                'mensaje' => 'El estudiante tiene retiro anterior. Se sugiere reinscripción con revisión institucional.',
            ];
        }

        if (($ultimo['estado'] ?? null) === 'ANULADO') {
            return [
                'situacion' => 'REVISAR',
                'tipo_sugerido' => 'REINSCRIPCION',
                'mensaje' => 'El estudiante tiene una inscripción anulada anteriormente. Revisa el historial antes de continuar.',
            ];
        }

        return [
            'situacion' => 'REGULAR',
            'tipo_sugerido' => 'REGULAR',
            'mensaje' => 'El estudiante tiene historial previo. Se sugiere inscripción regular.',
        ];
    }

    public function sugerirTipoInscripcion(?string $codEst, array $historial = []): array
    {
        if (! $codEst) {
            return [
                'tipo' => 'REGULAR',
                'mensaje' => 'Selecciona un estudiante para sugerir tipo de inscripción.',
                'confianza' => 'BAJA',
            ];
        }

        $historial = $historial ?: $this->historialEstudiante($codEst);

        if (empty($historial)) {
            return [
                'tipo' => 'NUEVO',
                'mensaje' => 'No se encontró historial académico institucional. Se sugiere inscripción nueva.',
                'confianza' => 'MEDIA',
            ];
        }

        $ultimo = $historial[0];

        return match ($ultimo['estado'] ?? null) {
            'RETIRADO' => [
                'tipo' => 'REINSCRIPCION',
                'mensaje' => 'El estudiante tuvo retiro anterior. Se sugiere reinscripción.',
                'confianza' => 'MEDIA',
            ],
            'ANULADO' => [
                'tipo' => 'REINSCRIPCION',
                'mensaje' => 'Tiene inscripción anulada en historial. Revisar antes de inscribir.',
                'confianza' => 'BAJA',
            ],
            default => [
                'tipo' => 'REGULAR',
                'mensaje' => 'El estudiante cuenta con historial previo. Se sugiere inscripción regular.',
                'confianza' => 'ALTA',
            ],
        };
    }

    /* -------------------------------------------------------------------------
     | CURSO, EDAD, TURNO MAÑANA Y ESPECIALIDAD BTH
     * ------------------------------------------------------------------------- */

    public function sugerirCursoPorEdad(?int $edad): array
    {
        if (! $edad || $edad < 5) {
            return [
                'disponible' => false,
                'curso_sugerido' => null,
                'rango_edad' => null,
                'coincidencia' => 'SIN_DATOS',
                'mensaje' => 'No existe edad suficiente para sugerir curso.',
                'bloquea' => false,
            ];
        }

        foreach (self::CURSOS_EDAD_REFERENCIAL as $curso) {
            if ($edad >= $curso['edad_min'] && $edad <= $curso['edad_max']) {
                return [
                    'disponible' => true,
                    'curso_sugerido' => $curso['nombre'],
                    'orden' => $curso['orden'],
                    'rango_edad' => $curso['edad_min'] . ' a ' . $curso['edad_max'] . ' años',
                    'coincidencia' => 'REFERENCIAL',
                    'mensaje' => 'La edad coincide referencialmente con ' . $curso['nombre'] . '.',
                    'bloquea' => false,
                ];
            }
        }

        if ($edad < 12) {
            return [
                'disponible' => true,
                'curso_sugerido' => '1ro Secundaria',
                'orden' => 1,
                'rango_edad' => '12 a 13 años',
                'coincidencia' => 'REVISAR',
                'mensaje' => 'La edad es menor al rango habitual de secundaria. Verifica documentación o situación académica.',
                'bloquea' => false,
            ];
        }

        return [
            'disponible' => true,
            'curso_sugerido' => '6to Secundaria',
            'orden' => 6,
            'rango_edad' => '17 a 19 años',
            'coincidencia' => 'REVISAR',
            'mensaje' => 'La edad supera el rango habitual. Verifica si corresponde a rezago, traslado, repitencia o caso especial.',
            'bloquea' => false,
        ];
    }

    public function compararCursoConEdad(?string $codCur, ?int $edad): array
    {
        if (! $codCur || ! $edad) {
            return [
                'estado' => 'SIN_DATOS',
                'mensaje' => 'Selecciona curso y estudiante con edad registrada para comparar.',
                'bloquea' => false,
            ];
        }

        $curso = $this->obtenerCurso($codCur);

        if (! $curso) {
            return [
                'estado' => 'BLOQUEADO',
                'mensaje' => 'El curso seleccionado no existe o no está activo.',
                'bloquea' => true,
            ];
        }

        $ordenCurso = $this->extraerOrdenCurso($curso['nombre'] ?? '');
        $sugerido = $this->sugerirCursoPorEdad($edad);

        if (! $ordenCurso || ! ($sugerido['orden'] ?? null)) {
            return [
                'estado' => 'REVISAR',
                'curso' => $curso,
                'sugerido' => $sugerido,
                'mensaje' => 'No se pudo comparar automáticamente el curso seleccionado con la edad. Verifica manualmente.',
                'bloquea' => false,
            ];
        }

        $diferencia = abs($ordenCurso - (int) $sugerido['orden']);

        if ($diferencia === 0) {
            return [
                'estado' => 'ADECUADO',
                'curso' => $curso,
                'sugerido' => $sugerido,
                'mensaje' => 'La edad coincide con el curso seleccionado de forma referencial.',
                'bloquea' => false,
            ];
        }

        if ($diferencia === 1) {
            return [
                'estado' => 'OBSERVADO',
                'curso' => $curso,
                'sugerido' => $sugerido,
                'mensaje' => 'La edad está cerca del curso seleccionado. Verifica si corresponde a repitencia, traslado o avance académico.',
                'bloquea' => false,
            ];
        }

        return [
            'estado' => 'REVISAR',
            'curso' => $curso,
            'sugerido' => $sugerido,
            'mensaje' => 'La edad no coincide con el curso seleccionado. Requiere revisión institucional, pero no se bloquea automáticamente.',
            'bloquea' => false,
        ];
    }

    public function cursoSugeridoDisponible(?int $edad): ?array
    {
        $sugerido = $this->sugerirCursoPorEdad($edad);

        if (! ($sugerido['disponible'] ?? false) || ! Schema::hasTable('curso')) {
            return null;
        }

        $orden = $sugerido['orden'] ?? null;

        if (! $orden) {
            return null;
        }

        foreach ($this->cursos() as $curso) {
            if ($this->extraerOrdenCurso($curso['nombre'] ?? '') === $orden) {
                return [
                    'cod_cur' => $curso['cod_cur'],
                    'nombre' => $curso['nombre'],
                    'sugerencia' => $sugerido,
                ];
            }
        }

        return null;
    }

    public function turnoManana(): ?array
    {
        if (! Schema::hasTable('turno')) {
            return null;
        }

        $turno = DB::table('turno')
            ->where('est_tur', 'ACTIVO')
            ->where(function ($query) {
                $query->whereRaw('LOWER(nom_tur) LIKE ?', ['%mañana%'])
                    ->orWhereRaw('LOWER(nom_tur) LIKE ?', ['%manana%'])
                    ->orWhere('hor_ini_tur', '<=', '09:00:00');
            })
            ->orderBy('hor_ini_tur')
            ->first();

        if (! $turno) {
            return null;
        }

        return [
            'cod_tur' => $turno->cod_tur,
            'nombre' => $turno->nom_tur,
            'inicio' => $this->horaTexto($turno->hor_ini_tur ?? null),
            'fin' => $this->horaTexto($turno->hor_fin_tur ?? null),
            'rango' => $this->horaTexto($turno->hor_ini_tur ?? null) . ' - ' . $this->horaTexto($turno->hor_fin_tur ?? null),
            'estado' => $turno->est_tur,
        ];
    }

    public function validarTurnoPrincipal(?string $codTur): array
    {
        $turnoManana = $this->turnoManana();

        if (! $codTur) {
            return [
                'estado' => $turnoManana ? 'OBSERVADO' : 'BLOQUEADO',
                'bloquea' => ! $turnoManana,
                'mensaje' => $turnoManana
                    ? 'No se seleccionó turno. Se recomienda usar el turno Mañana como turno principal de inscripción.'
                    : 'No existe turno Mañana configurado. Revisa Gestión de Turnos antes de inscribir.',
                'turno_manana' => $turnoManana,
            ];
        }

        if (! $turnoManana) {
            return [
                'estado' => 'OBSERVADO',
                'bloquea' => false,
                'mensaje' => 'No se detectó turno Mañana para comparar. Se permite continuar con revisión institucional.',
                'turno_manana' => null,
            ];
        }

        if ($codTur !== $turnoManana['cod_tur']) {
            return [
                'estado' => 'OBSERVADO',
                'bloquea' => false,
                'mensaje' => 'La inscripción normalmente debe registrarse en turno Mañana. Si corresponde a un caso especial, deja observación institucional.',
                'turno_manana' => $turnoManana,
            ];
        }

        return [
            'estado' => 'VALIDO',
            'bloquea' => false,
            'mensaje' => 'El estudiante será asignado al turno Mañana como jornada principal.',
            'turno_manana' => $turnoManana,
        ];
    }

    public function cursoRequiereEspecialidad(?string $codCur): array
    {
        $curso = $this->obtenerCurso($codCur);

        if (! $curso) {
            return [
                'requiere' => false,
                'estado' => 'SIN_CURSO',
                'mensaje' => 'Selecciona un curso para evaluar si corresponde especialidad técnica.',
                'curso' => null,
            ];
        }

        $orden = $curso['orden'] ?? $this->extraerOrdenCurso($curso['nombre'] ?? '');

        if ($orden >= 4) {
            return [
                'requiere' => true,
                'estado' => 'APLICA',
                'mensaje' => 'Desde 4to de secundaria se puede asignar especialidad técnica BTH. La elección puede quedar pendiente.',
                'curso' => $curso,
            ];
        }

        return [
            'requiere' => false,
            'estado' => 'NO_APLICA',
            'mensaje' => 'La especialidad técnica no aplica para este curso.',
            'curso' => $curso,
        ];
    }

    public function especialidadesTecnicas(): array
    {
        $tabla = $this->tablaEspecialidades();

        if (! $tabla) {
            return [];
        }

        $codCol = $this->primeraColumna($tabla, ['cod_esp_tec', 'cod_esp', 'id']);
        $nomCol = $this->primeraColumna($tabla, ['nom_esp_tec', 'nom_esp', 'nombre', 'nombre_especialidad']);
        $estCol = $this->primeraColumna($tabla, ['est_esp_tec', 'est_esp', 'estado']);

        if (! $codCol || ! $nomCol) {
            return [];
        }

        $query = DB::table($tabla);

        if ($estCol) {
            $query->where($estCol, 'ACTIVO');
        }

        return $query
            ->orderBy($nomCol)
            ->get()
            ->map(fn($row) => [
                'cod_esp_tec' => (string) ($row->{$codCol} ?? ''),
                'nombre' => (string) ($row->{$nomCol} ?? ''),
                'estado' => $estCol ? ($row->{$estCol} ?? 'ACTIVO') : 'ACTIVO',
            ])
            ->filter(fn($item) => $item['cod_esp_tec'] !== '' && $item['nombre'] !== '')
            ->values()
            ->all();
    }

    public function validarEspecialidadTecnica(array $form): array
    {
        $revisionCurso = $this->cursoRequiereEspecialidad($form['cod_cur'] ?? null);
        $codEsp = $form['cod_esp_tec'] ?? null;
        $estadoActual = $this->normalizarMayuscula($form['est_esp_tec_ins'] ?? '');

        if (! ($revisionCurso['requiere'] ?? false)) {
            return [
                'requiere' => false,
                'estado' => 'NO_APLICA',
                'estado_sugerido' => 'NO_APLICA',
                'bloquea' => false,
                'advertencia' => null,
                'mensaje' => $revisionCurso['mensaje'],
            ];
        }

        if ($codEsp) {
            return [
                'requiere' => true,
                'estado' => 'ASIGNADA',
                'estado_sugerido' => 'ASIGNADA',
                'bloquea' => false,
                'advertencia' => null,
                'mensaje' => 'La especialidad técnica fue asignada para el estudiante.',
            ];
        }

        return [
            'requiere' => true,
            'estado' => $estadoActual ?: 'PENDIENTE',
            'estado_sugerido' => 'PENDIENTE',
            'bloquea' => false,
            'advertencia' => 'La especialidad técnica puede quedar pendiente. No bloquea la inscripción, pero debe completarse posteriormente.',
            'mensaje' => 'El curso permite especialidad técnica BTH, pero aún no se asignó una especialidad.',
        ];
    }

    /* -------------------------------------------------------------------------
     | ANÁLISIS PRINCIPAL
     * ------------------------------------------------------------------------- */

    public function analizarInscripcion(
        array $form,
        array $documentos = [],
        ?string $ignorarCodIns = null,
        bool $modoLive = true
    ): array {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $gestion = $this->gestionTrabajo();

        $codGea = $form['cod_gea'] ?? ($gestion['cod_gea'] ?? null);
        $codEst = $form['cod_est'] ?? null;
        $codCur = $form['cod_cur'] ?? null;
        $codPar = $form['cod_par'] ?? null;
        $codTur = $form['cod_tur'] ?? null;

        $tipo = $this->normalizarMayuscula($form['tip_ins'] ?? 'REGULAR');
        $condicion = $this->normalizarMayuscula($form['con_ins'] ?? 'NORMAL');
        $estadoManual = $this->normalizarMayuscula($form['est_ins'] ?? '');
        $fecha = $form['fei_ins'] ?? now()->toDateString();
        $sobrecupoAutorizado = (bool) ($form['sob_aut_ins'] ?? false);

        if (! $gestion) {
            $bloqueos[] = 'No existe una gestión académica activa o planificada para registrar inscripciones.';
        } elseif (! ($gestion['permite_inscripcion'] ?? false)) {
            $bloqueos[] = 'La gestión académica no permite registrar nuevas inscripciones.';
        }

        if (! $codGea) {
            $bloqueos[] = 'Selecciona una gestión académica válida.';
        }

        if (! $codEst) {
            $bloqueos[] = 'Selecciona un estudiante antes de continuar.';
        }

        if (! $codCur) {
            $bloqueos[] = 'Selecciona el curso de inscripción.';
        }

        if (! $codPar) {
            $bloqueos[] = 'Selecciona el paralelo de inscripción.';
        }

        if (! $codTur) {
            $turnoManana = $this->turnoManana();

            if ($turnoManana) {
                $advertencias[] = 'No se seleccionó turno. Se recomienda asignar el turno Mañana como jornada principal.';
            } else {
                $bloqueos[] = 'Selecciona el turno de inscripción. No se detectó turno Mañana configurado.';
            }
        }

        if (! in_array($tipo, self::TIPOS_INSCRIPCION, true)) {
            $bloqueos[] = 'El tipo de inscripción seleccionado no es válido.';
        }

        if (! in_array($condicion, self::CONDICIONES_INSCRIPCION, true)) {
            $bloqueos[] = 'La condición de inscripción seleccionada no es válida.';
        }

        if ($estadoManual !== '' && ! in_array($estadoManual, self::ESTADOS_INSCRIPCION, true)) {
            $bloqueos[] = 'El estado de inscripción seleccionado no es válido.';
        }

        if ($gestion && $fecha) {
            $revisionFecha = $this->validarFechaEnGestion($fecha, $gestion);

            if (! $revisionFecha['valida']) {
                $advertencias[] = $revisionFecha['mensaje'];
            }
        }

        $estudiante = $codEst ? $this->obtenerEstudiante($codEst) : null;

        if ($codEst && ! $estudiante) {
            $bloqueos[] = 'El estudiante seleccionado no existe o no está vinculado correctamente a una persona.';
        }

        if ($estudiante && ! ($estudiante['activo'] ?? false)) {
            $advertencias[] = 'El estudiante o la persona asociada no se encuentra activa. Revisa su estado antes de confirmar.';
        }

        if ($estudiante) {
            foreach ($this->validarDatosMinimosEstudiante($estudiante) as $mensaje) {
                $advertencias[] = $mensaje;
            }
        }

        $duplicidad = $this->verificarInscripcionExistente($codEst, $codGea, $ignorarCodIns);

        if ($duplicidad['existe']) {
            $bloqueos[] = 'El estudiante ya tiene una inscripción activa en la gestión seleccionada.';
        }

        $situacion = $this->clasificarSituacionEstudiante($codEst, $codGea);
        $tipoSugerido = $this->sugerirTipoInscripcion($codEst, $codEst ? $this->historialEstudiante($codEst) : []);

        if (($tipoSugerido['tipo'] ?? null) && $tipo !== ($tipoSugerido['tipo'] ?? null) && ! in_array($tipo, ['EXCEPCIONAL', 'VULNERABILIDAD', 'EXTRANJERO'], true)) {
            $sugerencias[] = 'Tipo sugerido por historial: ' . $tipoSugerido['tipo'] . '. ' . $tipoSugerido['mensaje'];
        }

        $edadCurso = $estudiante
            ? $this->compararCursoConEdad($codCur, $estudiante['edad'] ?? null)
            : [
                'estado' => 'SIN_DATOS',
                'mensaje' => 'Selecciona un estudiante para comparar curso y edad.',
                'bloquea' => false,
            ];

        if (($edadCurso['estado'] ?? null) === 'BLOQUEADO') {
            $bloqueos[] = $edadCurso['mensaje'];
        } elseif (in_array($edadCurso['estado'] ?? '', ['OBSERVADO', 'REVISAR'], true)) {
            $advertencias[] = $edadCurso['mensaje'];
        } elseif (($edadCurso['estado'] ?? null) === 'ADECUADO') {
            $sugerencias[] = $edadCurso['mensaje'];
        }

        $revisionTurno = $this->validarTurnoPrincipal($codTur);

        if ($revisionTurno['bloquea'] ?? false) {
            $bloqueos[] = $revisionTurno['mensaje'];
        } elseif (($revisionTurno['estado'] ?? null) === 'OBSERVADO') {
            $advertencias[] = $revisionTurno['mensaje'];
        } else {
            $sugerencias[] = $revisionTurno['mensaje'];
        }

        $combinacion = $this->validarCombinacionAcademica($codGea, $codCur, $codPar, $codTur);

        foreach ($combinacion['bloqueos'] as $mensaje) {
            $bloqueos[] = $mensaje;
        }

        foreach ($combinacion['advertencias'] as $mensaje) {
            $advertencias[] = $mensaje;
        }

        foreach ($combinacion['sugerencias'] as $mensaje) {
            $sugerencias[] = $mensaje;
        }

        $especialidad = $this->validarEspecialidadTecnica($form);

        if (! empty($especialidad['advertencia'])) {
            $advertencias[] = $especialidad['advertencia'];
        } elseif (($especialidad['estado'] ?? null) === 'ASIGNADA') {
            $sugerencias[] = 'La especialidad técnica BTH quedó asignada correctamente.';
        } elseif (($especialidad['estado'] ?? null) === 'NO_APLICA') {
            $sugerencias[] = 'La especialidad técnica no aplica para el curso seleccionado.';
        }

        $cupo = $this->calcularCupo($codGea, $codCur, $codPar, $codTur, $ignorarCodIns);

        if ($cupo['estado'] === 'LLENO' && ! $sobrecupoAutorizado) {
            $bloqueos[] = 'El curso, paralelo y turno seleccionado no tiene cupos disponibles.';
        }

        if ($cupo['estado'] === 'CASI_LLENO') {
            $advertencias[] = 'El cupo está cerca del límite. Revisa la disponibilidad antes de confirmar.';
        }

        if ($cupo['estado'] === 'LLENO' && $sobrecupoAutorizado) {
            $advertencias[] = 'La inscripción se registrará con sobrecupo autorizado y quedará como condición especial.';
        }

        if ($tipo === 'TRASLADO' && $this->estaVacio($form['pro_ins'] ?? null)) {
            $advertencias[] = 'Para una inscripción por traslado se recomienda registrar la unidad educativa de procedencia.';
        }

        if (in_array($tipo, ['EXCEPCIONAL', 'VULNERABILIDAD', 'EXTRANJERO'], true)) {
            $advertencias[] = 'Este tipo de inscripción requiere seguimiento documental y observación institucional.';
        }

        if (in_array($tipo, ['EXCEPCIONAL', 'VULNERABILIDAD', 'EXTRANJERO'], true) && $this->estaVacio($form['mot_obs_ins'] ?? null)) {
            $advertencias[] = 'Registra una justificación u observación para respaldar el caso especial.';
        }

        $revisionDocumentos = $this->analizarDocumentos($documentos, $tipo);

        foreach ($revisionDocumentos['advertencias'] as $mensaje) {
            $advertencias[] = $mensaje;
        }

        foreach ($revisionDocumentos['sugerencias'] as $mensaje) {
            $sugerencias[] = $mensaje;
        }

        if (empty($documentos) && ! $modoLive) {
            $advertencias[] = 'No se cargó una checklist documental. Se recomienda generar documentos iniciales.';
        }

        $estadoSugerido = $this->sugerirEstadoFinal(
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            tipo: $tipo,
            cupo: $cupo,
            documentos: $revisionDocumentos,
            sobrecupoAutorizado: $sobrecupoAutorizado
        );

        $condicionSugerida = $this->sugerirCondicion($estadoSugerido, $tipo, $cupo, $sobrecupoAutorizado);

        if (empty($bloqueos) && empty($advertencias)) {
            $sugerencias[] = 'La inscripción puede confirmarse como inscrita.';
        }

        if (empty($bloqueos) && ! empty($advertencias)) {
            $sugerencias[] = 'La inscripción puede continuar, pero debería guardarse con seguimiento institucional.';
        }

        $estadoRevision = $this->estadoRevision($bloqueos, $advertencias);

        $resultado = [
            'puede_continuar' => empty($bloqueos),
            'estado' => $estadoRevision,
            'nivel_riesgo' => $this->nivelRiesgo($bloqueos, $advertencias),
            'mensaje' => $this->mensajeRevision($estadoRevision),
            'estado_sugerido' => $estadoSugerido,
            'condicion_sugerida' => $condicionSugerida,
            'bloqueos' => array_values(array_unique($bloqueos)),
            'advertencias' => array_values(array_unique($advertencias)),
            'sugerencias' => array_values(array_unique($sugerencias)),
            'resumen' => [
                'gestion' => $gestion['anio'] ?? null,
                'fecha_inscripcion' => $fecha,
                'tipo' => $tipo,
                'condicion' => $condicionSugerida,
                'estado_sugerido' => $estadoSugerido,
                'estudiante' => $estudiante['nombre_completo'] ?? null,
                'duplicidad' => $duplicidad['existe'],
                'situacion' => $situacion['situacion'] ?? null,
                'cupo_estado' => $cupo['estado'],
                'cupo_capacidad' => $cupo['capacidad'],
                'cupo_inscritos' => $cupo['inscritos'],
                'cupo_disponible' => $cupo['disponibles'],
                'documentos_pendientes' => $revisionDocumentos['pendientes'],
                'documentos_observados' => $revisionDocumentos['observados'],
                'documentos_completos' => $revisionDocumentos['completos'],
                'especialidad_estado' => $especialidad['estado_sugerido'] ?? 'NO_APLICA',
            ],
            'gestion' => $gestion,
            'estudiante' => $estudiante,
            'situacion_estudiante' => $situacion,
            'tipo_sugerido' => $tipoSugerido,
            'curso_edad' => $edadCurso,
            'curso_sugerido_disponible' => $estudiante ? $this->cursoSugeridoDisponible($estudiante['edad'] ?? null) : null,
            'turno_principal' => $revisionTurno,
            'especialidad_tecnica' => $especialidad,
            'combinacion_academica' => $combinacion,
            'duplicidad' => $duplicidad,
            'cupo' => $cupo,
            'documentos' => $revisionDocumentos,
        ];

        $resultado['pasos'] = $this->estadoPasos($form, $documentos, $estudiante, $resultado);

        return $resultado;
    }

    public function evaluarConfirmacionFinal(array $form, array $documentos, ?string $codIns = null): array
    {
        $analisis = $this->analizarInscripcion($form, $documentos, $codIns, false);

        if (! ($analisis['puede_continuar'] ?? false)) {
            return [
                'puede_confirmar' => false,
                'accion_recomendada' => 'CORREGIR',
                'texto_boton' => 'No se puede confirmar',
                'mensaje' => 'Existen bloqueos que deben corregirse antes de confirmar.',
                'estado_final' => 'PENDIENTE',
                'condicion_final' => $analisis['condicion_sugerida'] ?? 'OBSERVADA',
            ];
        }

        $estado = $analisis['estado_sugerido'] ?? 'PENDIENTE';
        $condicion = $analisis['condicion_sugerida'] ?? 'NORMAL';

        return [
            'puede_confirmar' => true,
            'accion_recomendada' => $estado === 'INSCRITO' ? 'CONFIRMAR' : 'GUARDAR_CON_SEGUIMIENTO',
            'texto_boton' => match ($estado) {
                'INSCRITO' => 'Confirmar inscripción',
                'OBSERVADO' => 'Guardar como observada',
                'CONDICIONAL' => 'Guardar como condicional',
                'PROVISIONAL' => 'Guardar como provisional',
                default => 'Guardar pendiente',
            },
            'mensaje' => $estado === 'INSCRITO'
                ? 'La inscripción puede confirmarse sin observaciones críticas.'
                : 'La inscripción puede guardarse con seguimiento institucional.',
            'estado_final' => $estado,
            'condicion_final' => $condicion,
        ];
    }

    /* -------------------------------------------------------------------------
     | PASOS
     * ------------------------------------------------------------------------- */

    public function estadoPasos(array $form, array $documentos, ?array $estudiante, array $analisis): array
    {
        $pasos = [];

        $pasos[1] = [
            'nombre' => 'Identificación',
            'estado' => $estudiante ? 'COMPLETO' : 'PENDIENTE',
            'mensaje' => $estudiante ? 'Estudiante seleccionado correctamente.' : 'Selecciona un estudiante para iniciar.',
        ];

        $tipoValido = in_array($this->normalizarMayuscula($form['tip_ins'] ?? ''), self::TIPOS_INSCRIPCION, true);

        $pasos[2] = [
            'nombre' => 'Tipo',
            'estado' => $tipoValido ? 'COMPLETO' : 'PENDIENTE',
            'mensaje' => $tipoValido ? 'Tipo de inscripción definido.' : 'Selecciona el tipo de inscripción.',
        ];

        $asignacionCompleta = ! empty($form['cod_gea']) && ! empty($form['cod_cur']) && ! empty($form['cod_par']) && ! empty($form['cod_tur']) && ! empty($form['fei_ins']);

        $pasos[3] = [
            'nombre' => 'Asignación',
            'estado' => $asignacionCompleta ? (($analisis['combinacion_academica']['estado'] ?? 'VALIDO') === 'BLOQUEADO' ? 'BLOQUEADO' : 'COMPLETO') : 'PENDIENTE',
            'mensaje' => $asignacionCompleta ? 'Asignación académica registrada.' : 'Completa gestión, curso, paralelo, turno y fecha.',
        ];

        $docAnalisis = $this->analizarDocumentos($documentos, $form['tip_ins'] ?? 'REGULAR');

        $pasos[4] = [
            'nombre' => 'Documentos',
            'estado' => empty($documentos) ? 'PENDIENTE' : (($docAnalisis['pendientes'] > 0 || $docAnalisis['observados'] > 0) ? 'OBSERVADO' : 'COMPLETO'),
            'mensaje' => empty($documentos)
                ? 'Genera o registra la checklist documental.'
                : (($docAnalisis['pendientes'] > 0 || $docAnalisis['observados'] > 0)
                    ? 'Existen documentos pendientes u observados.'
                    : 'Documentación sin pendientes críticos.'),
        ];

        $pasos[5] = [
            'nombre' => 'Revisión',
            'estado' => $analisis['estado'] ?? 'PENDIENTE',
            'mensaje' => $analisis['mensaje'] ?? 'Completa la información para revisar.',
        ];

        $pasos[6] = [
            'nombre' => 'Confirmación',
            'estado' => ($analisis['puede_continuar'] ?? false) ? 'COMPLETO' : 'BLOQUEADO',
            'mensaje' => ($analisis['puede_continuar'] ?? false)
                ? 'La inscripción puede guardarse según el estado sugerido.'
                : 'Corrige los bloqueos antes de confirmar.',
        ];

        return $pasos;
    }

    public function puedeAvanzarPaso(int $paso, array $form, array $documentos, ?array $estudiante, array $analisis): array
    {
        if ($paso === 1 && ! $estudiante) {
            return [
                'puede' => false,
                'mensaje' => 'Selecciona un estudiante para continuar.',
            ];
        }

        if ($paso === 2 && ! in_array($this->normalizarMayuscula($form['tip_ins'] ?? ''), self::TIPOS_INSCRIPCION, true)) {
            return [
                'puede' => false,
                'mensaje' => 'Selecciona un tipo de inscripción válido.',
            ];
        }

        if ($paso === 3) {
            $faltan = [];

            foreach (
                [
                    'cod_gea' => 'gestión',
                    'cod_cur' => 'curso',
                    'cod_par' => 'paralelo',
                    'cod_tur' => 'turno',
                    'fei_ins' => 'fecha de inscripción',
                ] as $campo => $label
            ) {
                if (empty($form[$campo])) {
                    $faltan[] = $label;
                }
            }

            if (! empty($faltan)) {
                return [
                    'puede' => false,
                    'mensaje' => 'Completa: ' . implode(', ', $faltan) . '.',
                ];
            }
        }

        if ($paso === 4 && empty($documentos)) {
            return [
                'puede' => true,
                'mensaje' => 'Puedes continuar, pero se recomienda generar la checklist documental.',
            ];
        }

        if ($paso === 5 && ! ($analisis['puede_continuar'] ?? false)) {
            return [
                'puede' => false,
                'mensaje' => 'Existen bloqueos en la revisión. Corrige antes de confirmar.',
            ];
        }

        return [
            'puede' => true,
            'mensaje' => 'Puede continuar.',
        ];
    }

    /* -------------------------------------------------------------------------
     | DOCUMENTOS
     * ------------------------------------------------------------------------- */

    public function documentosBase(string $tipoInscripcion = 'REGULAR'): array
    {
        $tipoInscripcion = $this->normalizarMayuscula($tipoInscripcion);

        $documentos = [
            [
                'nom_die' => 'Formulario RUDE',
                'tip_die' => 'RUDE',
                'est_die' => 'PENDIENTE',
                'obs_die' => null,
                'obligatorio' => true,
            ],
            [
                'nom_die' => 'Certificado de nacimiento',
                'tip_die' => 'IDENTIDAD',
                'est_die' => in_array($tipoInscripcion, ['NUEVO', 'EXTRANJERO'], true) ? 'PENDIENTE' : 'NO_APLICA',
                'obs_die' => null,
                'obligatorio' => in_array($tipoInscripcion, ['NUEVO', 'EXTRANJERO'], true),
            ],
            [
                'nom_die' => 'Cédula de identidad del estudiante',
                'tip_die' => 'IDENTIDAD',
                'est_die' => 'PENDIENTE',
                'obs_die' => null,
                'obligatorio' => true,
            ],
            [
                'nom_die' => 'Cédula de identidad del padre, madre o tutor',
                'tip_die' => 'TUTOR',
                'est_die' => 'PENDIENTE',
                'obs_die' => null,
                'obligatorio' => true,
            ],
            [
                'nom_die' => 'Libreta o boletín anterior',
                'tip_die' => 'ACADEMICO',
                'est_die' => in_array($tipoInscripcion, ['REGULAR', 'REINSCRIPCION'], true) ? 'NO_APLICA' : 'PENDIENTE',
                'obs_die' => null,
                'obligatorio' => ! in_array($tipoInscripcion, ['REGULAR', 'REINSCRIPCION'], true),
            ],
            [
                'nom_die' => 'Carnet de vacunas',
                'tip_die' => 'SALUD',
                'est_die' => 'PENDIENTE',
                'obs_die' => 'Puede regularizarse durante la gestión con seguimiento institucional.',
                'obligatorio' => false,
            ],
        ];

        if ($tipoInscripcion === 'TRASLADO') {
            $documentos[] = [
                'nom_die' => 'Documento o respaldo de traslado',
                'tip_die' => 'TRASLADO',
                'est_die' => 'PENDIENTE',
                'obs_die' => null,
                'obligatorio' => true,
            ];
        }

        if (in_array($tipoInscripcion, ['EXCEPCIONAL', 'VULNERABILIDAD', 'EXTRANJERO'], true)) {
            $documentos[] = [
                'nom_die' => 'Declaración jurada o respaldo institucional',
                'tip_die' => 'EXCEPCIONAL',
                'est_die' => 'PENDIENTE',
                'obs_die' => 'Permite registrar seguimiento sin bloquear abruptamente el acceso educativo.',
                'obligatorio' => false,
            ];
        }

        return $documentos;
    }

    public function analizarDocumentos(array $documentos, string $tipoInscripcion = 'REGULAR'): array
    {
        $pendientes = 0;
        $observados = 0;
        $presentados = 0;
        $noAplica = 0;
        $obligatoriosPendientes = 0;
        $advertencias = [];
        $sugerencias = [];

        foreach ($documentos as $documento) {
            $estado = $this->normalizarMayuscula($documento['est_die'] ?? 'PENDIENTE');
            $obligatorio = (bool) ($documento['obligatorio'] ?? false);

            match ($estado) {
                'PRESENTADO' => $presentados++,
                'OBSERVADO' => $observados++,
                'NO_APLICA' => $noAplica++,
                default => $pendientes++,
            };

            if ($obligatorio && in_array($estado, ['PENDIENTE', 'OBSERVADO'], true)) {
                $obligatoriosPendientes++;
            }
        }

        if ($pendientes > 0) {
            $advertencias[] = "Existen {$pendientes} documento(s) pendiente(s).";
        }

        if ($observados > 0) {
            $advertencias[] = "Existen {$observados} documento(s) observado(s).";
        }

        if ($obligatoriosPendientes > 0) {
            $advertencias[] = "Existen {$obligatoriosPendientes} documento(s) obligatorio(s) pendiente(s) u observado(s).";
        }

        if ($pendientes === 0 && $observados === 0 && count($documentos) > 0) {
            $sugerencias[] = 'La documentación registrada no presenta pendientes críticos.';
        }

        if (empty($documentos)) {
            $sugerencias[] = 'Genera la checklist documental inicial para controlar la inscripción.';
        }

        return [
            'total' => count($documentos),
            'presentados' => $presentados,
            'pendientes' => $pendientes,
            'observados' => $observados,
            'no_aplica' => $noAplica,
            'obligatorios_pendientes' => $obligatoriosPendientes,
            'completos' => $pendientes === 0 && $observados === 0 && count($documentos) > 0,
            'advertencias' => $advertencias,
            'sugerencias' => $sugerencias,
        ];
    }

    public function previsualizarChecklistPorTipo(string $tipo, array $documentosActuales): array
    {
        $recomendados = $this->documentosBase($tipo);

        return [
            'tipo' => $this->normalizarMayuscula($tipo),
            'actuales' => $documentosActuales,
            'recomendados' => $recomendados,
            'faltantes' => $this->documentosFaltantes($documentosActuales, $recomendados),
            'requiere_confirmacion' => $this->requiereConfirmacionRegenerarDocumentos($documentosActuales),
            'accion_recomendada' => empty($documentosActuales) ? 'GENERAR' : 'AGREGAR_FALTANTES',
            'mensaje' => empty($documentosActuales)
                ? 'No existe checklist actual. Puedes generar la recomendada.'
                : 'Ya existen documentos configurados. Se recomienda agregar faltantes sin borrar lo registrado.',
        ];
    }

    public function fusionarDocumentosSinBorrar(array $actuales, array $recomendados): array
    {
        $resultado = $actuales;
        $nombresActuales = collect($actuales)
            ->map(fn($doc) => $this->claveDocumento($doc['nom_die'] ?? ''))
            ->filter()
            ->values()
            ->all();

        foreach ($recomendados as $documento) {
            $clave = $this->claveDocumento($documento['nom_die'] ?? '');

            if (! in_array($clave, $nombresActuales, true)) {
                $resultado[] = $documento;
            }
        }

        return array_values($resultado);
    }

    public function requiereConfirmacionRegenerarDocumentos(array $documentosActuales): bool
    {
        return count($documentosActuales) > 0;
    }

    public function normalizarDocumentosParaGuardar(array $documentos): array
    {
        return collect($documentos)
            ->map(function (array $documento) {
                $estado = $this->normalizarMayuscula($documento['est_die'] ?? 'PENDIENTE');

                if (! in_array($estado, self::ESTADOS_DOCUMENTO, true)) {
                    $estado = 'PENDIENTE';
                }

                return [
                    'nom_die' => $this->limpiarTexto($documento['nom_die'] ?? 'Documento') ?: 'Documento',
                    'tip_die' => $this->normalizarMayuscula($documento['tip_die'] ?? 'GENERAL'),
                    'est_die' => $estado,
                    'obs_die' => $this->limpiarTexto($documento['obs_die'] ?? null),
                    'fec_pre_die' => $estado === 'PRESENTADO'
                        ? ($documento['fec_pre_die'] ?? now()->toDateString())
                        : ($documento['fec_pre_die'] ?? null),
                    'registrado_por' => auth()->user()->cod_usu ?? auth()->id(),
                ];
            })
            ->values()
            ->all();
    }

    /* -------------------------------------------------------------------------
     | CUPO Y COHERENCIA
     * ------------------------------------------------------------------------- */

    public function validarCombinacionAcademica(?string $codGea, ?string $codCur, ?string $codPar, ?string $codTur): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $curso = $this->obtenerCurso($codCur);
        $paralelo = $this->obtenerParalelo($codPar);
        $turno = $this->obtenerTurno($codTur);

        if (! $codGea) {
            $bloqueos[] = 'Selecciona una gestión académica.';
        }

        if ($codCur && ! $curso) {
            $bloqueos[] = 'El curso seleccionado no existe o no está activo.';
        }

        if ($codPar && ! $paralelo) {
            $bloqueos[] = 'El paralelo seleccionado no existe o no está activo.';
        }

        if ($codTur && ! $turno) {
            $bloqueos[] = 'El turno seleccionado no existe o no está activo.';
        }

        if ($codGea && $codCur && $codPar && $codTur && Schema::hasTable('horario')) {
            $existeHorario = DB::table('horario')
                ->where('cod_gea', $codGea)
                ->where('cod_cur', $codCur)
                ->where('cod_par', $codPar)
                ->where('cod_tur', $codTur)
                ->exists();

            if (! $existeHorario) {
                $advertencias[] = 'No existe horario registrado para esta combinación. Puede inscribirse, pero debe revisarse la planificación horaria.';
            }
        }

        if (empty($bloqueos) && empty($advertencias)) {
            $sugerencias[] = 'La combinación académica es coherente para continuar.';
        }

        return [
            'puede_continuar' => empty($bloqueos),
            'estado' => $this->estadoRevision($bloqueos, $advertencias),
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => $sugerencias,
            'curso' => $curso,
            'paralelo' => $paralelo,
            'turno' => $turno,
        ];
    }

    public function calcularCupo(?string $codGea, ?string $codCur, ?string $codPar, ?string $codTur, ?string $ignorarCodIns = null): array
    {
        $capacidad = $this->capacidadParalelo($codPar);
        $inscritos = 0;

        if ($codGea && $codCur && $codPar && $codTur && Schema::hasTable('inscripcion_estudiante')) {
            $query = DB::table('inscripcion_estudiante')
                ->where('cod_gea', $codGea)
                ->where('cod_cur', $codCur)
                ->where('cod_par', $codPar)
                ->where('cod_tur', $codTur)
                ->whereNotIn('est_ins', self::ESTADOS_NO_ACTIVOS);

            if ($ignorarCodIns) {
                $query->where('cod_ins', '!=', $ignorarCodIns);
            }

            $inscritos = $query->count();
        }

        $disponibles = max(0, $capacidad - $inscritos);
        $porcentaje = $capacidad > 0 ? round(($inscritos / $capacidad) * 100, 2) : 0;

        $estado = match (true) {
            $inscritos >= $capacidad => 'LLENO',
            $porcentaje >= 85 => 'CASI_LLENO',
            default => 'DISPONIBLE',
        };

        return [
            'capacidad' => $capacidad,
            'inscritos' => $inscritos,
            'disponibles' => $disponibles,
            'porcentaje' => $porcentaje,
            'estado' => $estado,
            'mensaje' => match ($estado) {
                'LLENO' => 'No existen cupos disponibles para esta combinación.',
                'CASI_LLENO' => 'El paralelo está cerca del límite de capacidad.',
                default => 'Existe disponibilidad para inscripción.',
            },
        ];
    }

    public function verificarInscripcionExistente(?string $codEst, ?string $codGea, ?string $ignorarCodIns = null): array
    {
        if (! $codEst || ! $codGea || ! Schema::hasTable('inscripcion_estudiante')) {
            return [
                'existe' => false,
                'registro' => null,
            ];
        }

        $query = DB::table('inscripcion_estudiante')
            ->where('cod_est', $codEst)
            ->where('cod_gea', $codGea)
            ->whereNotIn('est_ins', self::ESTADOS_NO_ACTIVOS);

        if ($ignorarCodIns) {
            $query->where('cod_ins', '!=', $ignorarCodIns);
        }

        $registro = $query->first();

        return [
            'existe' => (bool) $registro,
            'registro' => $registro ? [
                'cod_ins' => $registro->cod_ins ?? null,
                'fecha' => $this->fechaTexto($registro->fei_ins ?? null),
                'estado' => $registro->est_ins ?? null,
                'tipo' => $registro->tip_ins ?? null,
                'condicion' => $registro->con_ins ?? null,
            ] : null,
        ];
    }

    /* -------------------------------------------------------------------------
     | ACCIONES SENSIBLES
     * ------------------------------------------------------------------------- */

    public function obtenerInscripcion(?string $codIns): ?array
    {
        if (! $codIns || ! Schema::hasTable('inscripcion_estudiante')) {
            return null;
        }

        $row = DB::table('inscripcion_estudiante')
            ->leftJoin('estudiante', 'estudiante.cod_est', '=', 'inscripcion_estudiante.cod_est')
            ->leftJoin('persona', 'persona.cod_per', '=', 'estudiante.cod_per')
            ->leftJoin('gestion_academica', 'gestion_academica.cod_gea', '=', 'inscripcion_estudiante.cod_gea')
            ->leftJoin('curso', 'curso.cod_cur', '=', 'inscripcion_estudiante.cod_cur')
            ->leftJoin('paralelo', 'paralelo.cod_par', '=', 'inscripcion_estudiante.cod_par')
            ->leftJoin('turno', 'turno.cod_tur', '=', 'inscripcion_estudiante.cod_tur')
            ->select([
                'inscripcion_estudiante.*',
                'estudiante.rud_est',
                'persona.nom_per',
                'persona.ape_pat_per',
                'persona.ape_mat_per',
                'persona.ci_per',
                'gestion_academica.ani_gea',
                'curso.nom_cur',
                'paralelo.nom_par',
                'turno.nom_tur',
            ])
            ->where('inscripcion_estudiante.cod_ins', $codIns)
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'cod_ins' => $row->cod_ins,
            'cod_est' => $row->cod_est,
            'cod_gea' => $row->cod_gea,
            'cod_cur' => $row->cod_cur,
            'cod_par' => $row->cod_par,
            'cod_tur' => $row->cod_tur,
            'estudiante' => trim(collect([$row->nom_per, $row->ape_pat_per, $row->ape_mat_per])->filter()->implode(' ')),
            'ci' => $row->ci_per,
            'rude' => $row->rud_est,
            'gestion' => $row->ani_gea,
            'curso' => $row->nom_cur,
            'paralelo' => $row->nom_par,
            'turno' => $row->nom_tur,
            'fecha' => $this->fechaTexto($row->fei_ins),
            'tipo' => $row->tip_ins ?? 'REGULAR',
            'condicion' => $row->con_ins ?? 'NORMAL',
            'estado' => $row->est_ins ?? 'PENDIENTE',
            'observacion' => $row->obs_ins ?? null,
            'documentos_pendientes' => $this->contarDocumentosPorInscripcion($row->cod_ins, 'PENDIENTE'),
            'documentos_observados' => $this->contarDocumentosPorInscripcion($row->cod_ins, 'OBSERVADO'),
        ];
    }

    public function evaluarEdicion(?string $codIns): array
    {
        $inscripcion = $this->obtenerInscripcion($codIns);

        if (! $inscripcion) {
            return [
                'puede_continuar' => false,
                'estado' => 'BLOQUEADO',
                'mensaje' => 'No se encontró la inscripción seleccionada.',
                'bloqueos' => ['No se encontró la inscripción seleccionada.'],
                'advertencias' => [],
                'sugerencias' => [],
            ];
        }

        $advertencias = [];

        if (in_array($inscripcion['estado'], ['ANULADO', 'RETIRADO'], true)) {
            $advertencias[] = 'La inscripción está anulada o retirada. Se recomienda reactivar o revisar antes de editar.';
        }

        return [
            'puede_continuar' => true,
            'estado' => empty($advertencias) ? 'VALIDO' : 'OBSERVADO',
            'mensaje' => empty($advertencias)
                ? 'La inscripción puede editarse.'
                : 'La edición requiere revisión institucional.',
            'bloqueos' => [],
            'advertencias' => $advertencias,
            'sugerencias' => ['Toda edición debe conservar trazabilidad mediante bitácora.'],
            'inscripcion' => $inscripcion,
        ];
    }

    public function evaluarAnulacion(?string $codIns, string $motivo = ''): array
    {
        return $this->evaluarAccionLogica($codIns, $motivo, 'anulación', ['ANULADO', 'RETIRADO']);
    }

    public function evaluarRetiro(?string $codIns, string $motivo = ''): array
    {
        return $this->evaluarAccionLogica($codIns, $motivo, 'retiro', ['ANULADO', 'RETIRADO']);
    }

    public function prepararDatosAnulacion(string $motivo): array
    {
        return [
            'est_ins' => 'ANULADO',
            'con_ins' => 'OBSERVADA',
            'fec_anu_ins' => now(),
            'anulado_por' => auth()->user()->cod_usu ?? auth()->id(),
            'mot_anu_ins' => $this->limpiarTexto($motivo),
            'updated_at' => now(),
        ];
    }

    public function prepararDatosRetiro(string $motivo): array
    {
        return [
            'est_ins' => 'RETIRADO',
            'con_ins' => 'OBSERVADA',
            'fec_anu_ins' => now(),
            'anulado_por' => auth()->user()->cod_usu ?? auth()->id(),
            'mot_anu_ins' => $this->limpiarTexto($motivo),
            'updated_at' => now(),
        ];
    }

    /* -------------------------------------------------------------------------
     | NORMALIZACIÓN PARA GUARDAR
     * ------------------------------------------------------------------------- */

    public function normalizarDatosInscripcion(array $form, ?array $analisis = null): array
    {
        $gestion = $this->gestionTrabajo();
        $turnoManana = $this->turnoManana();
        $especialidad = $this->validarEspecialidadTecnica($form);

        $datos = [
            'cod_est' => $form['cod_est'] ?? null,
            'cod_gea' => $form['cod_gea'] ?? ($gestion['cod_gea'] ?? null),
            'cod_cur' => $form['cod_cur'] ?? null,
            'cod_par' => $form['cod_par'] ?? null,
            'cod_tur' => $form['cod_tur'] ?? ($turnoManana['cod_tur'] ?? null),
            'fei_ins' => $form['fei_ins'] ?? now()->toDateString(),
            'tip_ins' => $this->normalizarMayuscula($form['tip_ins'] ?? 'REGULAR'),
            'con_ins' => $this->normalizarMayuscula($form['con_ins'] ?? ($analisis['condicion_sugerida'] ?? 'NORMAL')),
            'est_ins' => $this->normalizarMayuscula($form['est_ins'] ?? ($analisis['estado_sugerido'] ?? 'PENDIENTE')),
            'obs_ins' => $this->limpiarTexto($form['obs_ins'] ?? null),
            'mot_obs_ins' => $this->limpiarTexto($form['mot_obs_ins'] ?? null),
            'pro_ins' => $this->limpiarTexto($form['pro_ins'] ?? null),
            'doc_com_ins' => (bool) ($form['doc_com_ins'] ?? ($analisis['documentos']['completos'] ?? false)),
            'sob_aut_ins' => (bool) ($form['sob_aut_ins'] ?? false),
            'registrado_por' => auth()->user()->cod_usu ?? auth()->id(),
        ];

        if (Schema::hasColumn('inscripcion_estudiante', 'cod_esp_tec')) {
            $datos['cod_esp_tec'] = $especialidad['estado_sugerido'] === 'ASIGNADA'
                ? ($form['cod_esp_tec'] ?? null)
                : null;
        }

        if (Schema::hasColumn('inscripcion_estudiante', 'est_esp_tec_ins')) {
            $datos['est_esp_tec_ins'] = $especialidad['estado_sugerido'] ?? 'NO_APLICA';
        }

        if (Schema::hasColumn('inscripcion_estudiante', 'obs_esp_tec_ins')) {
            $datos['obs_esp_tec_ins'] = $this->limpiarTexto($form['obs_esp_tec_ins'] ?? null);
        }

        if (Schema::hasColumn('inscripcion_estudiante', 'fec_asi_esp_tec_ins')) {
            $datos['fec_asi_esp_tec_ins'] = ($especialidad['estado_sugerido'] ?? null) === 'ASIGNADA'
                ? now()->toDateString()
                : null;
        }

        return $datos;
    }

    public function estadoFinalManualPermitido(string $estado): bool
    {
        return in_array($this->normalizarMayuscula($estado), self::ESTADOS_INSCRIPCION, true);
    }

    public function condicionPermitida(string $condicion): bool
    {
        return in_array($this->normalizarMayuscula($condicion), self::CONDICIONES_INSCRIPCION, true);
    }

    /* -------------------------------------------------------------------------
     | PANELES Y REPORTES
     * ------------------------------------------------------------------------- */

    public function panelGestion(): array
    {
        $gestion = $this->gestionTrabajo();

        if (! $gestion) {
            return [
                'estado' => 'BLOQUEADO',
                'titulo' => 'Sin gestión de trabajo',
                'descripcion' => 'Activa o planifica una gestión académica antes de registrar inscripciones.',
                'gestion' => null,
            ];
        }

        return [
            'estado' => ($gestion['permite_inscripcion'] ?? false) ? 'VALIDO' : 'OBSERVADO',
            'titulo' => 'Gestión ' . $gestion['anio'],
            'descripcion' => 'Rango ' . $gestion['rango'] . '. Las inscripciones se validan contra esta gestión.',
            'gestion' => $gestion,
        ];
    }

    public function panelCupo(array $form): array
    {
        $cupo = $this->calcularCupo(
            $form['cod_gea'] ?? null,
            $form['cod_cur'] ?? null,
            $form['cod_par'] ?? null,
            $form['cod_tur'] ?? null
        );

        return [
            'titulo' => 'Cupo del paralelo',
            'estado' => $cupo['estado'],
            'descripcion' => $cupo['mensaje'],
            'cupo' => $cupo,
        ];
    }

    public function panelDocumental(array $documentos): array
    {
        $analisis = $this->analizarDocumentos($documentos);

        return [
            'titulo' => 'Control documental',
            'estado' => $analisis['completos'] ? 'VALIDO' : (empty($documentos) ? 'PENDIENTE' : 'OBSERVADO'),
            'descripcion' => $analisis['completos']
                ? 'La documentación registrada no presenta pendientes críticos.'
                : 'Existen documentos pendientes, observados o checklist sin generar.',
            'analisis' => $analisis,
        ];
    }

    public function panelRevision(array $analisis): array
    {
        return [
            'titulo' => 'Revisión del sistema',
            'estado' => $analisis['estado'] ?? 'PENDIENTE',
            'descripcion' => $analisis['mensaje'] ?? 'Completa la información para revisar.',
            'estado_sugerido' => $analisis['estado_sugerido'] ?? 'PENDIENTE',
            'condicion_sugerida' => $analisis['condicion_sugerida'] ?? 'NORMAL',
            'bloqueos' => $analisis['bloqueos'] ?? [],
            'advertencias' => $analisis['advertencias'] ?? [],
            'sugerencias' => $analisis['sugerencias'] ?? [],
        ];
    }

    public function reportesDisponibles(?string $codGea = null): array
    {
        $resumen = $this->resumenGeneral($codGea);

        return [
            'general' => ($resumen['total'] ?? 0) > 0,
            'por_curso' => ($resumen['total'] ?? 0) > 0,
            'por_paralelo' => ($resumen['total'] ?? 0) > 0,
            'observados' => ($resumen['observados'] ?? 0) > 0,
            'documentos_pendientes' => ($resumen['documentos_pendientes'] ?? 0) > 0,
            'especialidades_pendientes' => ($resumen['especialidades_pendientes'] ?? 0) > 0,
            'constancia' => false,
            'nomina' => ($resumen['activos'] ?? 0) > 0,
        ];
    }

    public function prepararConstancia(string $codIns): array
    {
        $inscripcion = $this->obtenerInscripcion($codIns);

        if (! $inscripcion) {
            return [
                'disponible' => false,
                'mensaje' => 'No se encontró la inscripción para generar constancia.',
            ];
        }

        return [
            'disponible' => true,
            'mensaje' => 'Constancia preparada correctamente.',
            'inscripcion' => $inscripcion,
            'documentos' => $this->documentosPorInscripcion($codIns),
            'responsable' => auth()->user()->name ?? auth()->user()->nom_usu ?? 'Usuario del sistema',
            'fecha_emision' => now()->format('d/m/Y H:i'),
        ];
    }

    public function descripcionBitacora(string $accion, array $datos = []): string
    {
        $estudiante = $this->textoSeguro($datos['estudiante'] ?? null, 'el estudiante seleccionado');
        $gestion = $this->textoSeguro($datos['gestion'] ?? null, 'la gestión correspondiente');
        $curso = $this->textoSeguro($datos['curso'] ?? null, 'el curso asignado');
        $paralelo = $this->textoSeguro($datos['paralelo'] ?? null, 'el paralelo asignado');
        $turno = $this->textoSeguro($datos['turno'] ?? null, 'el turno asignado');
        $estado = $this->textoSeguro($datos['estado'] ?? null, 'estado pendiente');
        $condicion = $this->textoSeguro($datos['condicion'] ?? null, 'condición normal');
        $motivo = $this->textoSeguro($datos['motivo'] ?? null, '');

        return match ($accion) {
            'CREAR_INSCRIPCION' =>
            "Se registró la inscripción de {$estudiante} en {$curso}, paralelo {$paralelo}, turno {$turno}, para la gestión {$gestion}.",

            'GUARDAR_INSCRIPCION_PENDIENTE' =>
            "Se guardó como pendiente la inscripción de {$estudiante} para completar información antes de su confirmación.",

            'CONFIRMAR_INSCRIPCION' =>
            "Se confirmó la inscripción de {$estudiante} en {$curso}, paralelo {$paralelo}, turno {$turno}, para la gestión {$gestion}.",

            'CREAR_INSCRIPCION_OBSERVADA' =>
            "Se registró la inscripción de {$estudiante} como observada para seguimiento administrativo.",

            'CREAR_INSCRIPCION_CONDICIONAL' =>
            "Se registró la inscripción condicional de {$estudiante} en {$curso}, paralelo {$paralelo}, turno {$turno}.",

            'CREAR_INSCRIPCION_PROVISIONAL' =>
            "Se registró la inscripción provisional de {$estudiante}, conservando seguimiento institucional.",

            'ACTUALIZAR_INSCRIPCION' =>
            "Se actualizó la inscripción de {$estudiante}. Estado actual: {$estado}; condición: {$condicion}.",

            'ACTUALIZAR_DOCUMENTOS_INSCRIPCION' =>
            "Se actualizó el control documental de {$estudiante}.",

            'ANULAR_INSCRIPCION' =>
            $motivo
                ? "Se anuló la inscripción de {$estudiante}. Motivo registrado: {$motivo}. El historial se conserva para trazabilidad institucional."
                : "Se anuló la inscripción de {$estudiante}. El historial se conserva para trazabilidad institucional.",

            'REGISTRAR_RETIRO_INSCRIPCION' =>
            $motivo
                ? "Se registró el retiro académico de {$estudiante}. Motivo registrado: {$motivo}. Se conserva su historial institucional."
                : "Se registró el retiro académico de {$estudiante}, conservando su historial institucional.",

            'REACTIVAR_INSCRIPCION' =>
            "Se reactivó la inscripción de {$estudiante} como pendiente para revisión administrativa.",

            'APLICAR_CURSO_SUGERIDO' =>
            "Se aplicó la sugerencia de curso para {$estudiante}: {$curso}.",

            'GENERAR_CHECKLIST_DOCUMENTAL' =>
            "Se generó la checklist documental para la inscripción de {$estudiante}.",

            'AGREGAR_DOCUMENTOS_RECOMENDADOS' =>
            "Se agregaron documentos recomendados a la checklist de {$estudiante} sin borrar los registros existentes.",

            default =>
            "Se registró una actualización en el módulo de inscripciones para {$estudiante}.",
        };
    }

    /* -------------------------------------------------------------------------
     | CONSULTAS PRIVADAS
     * ------------------------------------------------------------------------- */

    private function queryEstudiantesConPersona()
    {
        return DB::table('estudiante')
            ->join('persona', 'persona.cod_per', '=', 'estudiante.cod_per')
            ->select($this->selectEstudiantePersona());
    }

    private function selectEstudiantePersona(): array
    {
        return [
            'estudiante.cod_est',
            'estudiante.rud_est',
            'estudiante.est_est',
            'persona.cod_per',
            'persona.nom_per',
            'persona.ape_pat_per',
            'persona.ape_mat_per',
            'persona.ci_per',
            $this->selectColumn('persona', 'com_per', 'com_per'),
            $this->selectColumn('persona', 'exp_per', 'exp_per'),
            'persona.fec_nac_per',
            $this->selectColumn('persona', 'gen_per', 'gen_per'),
            $this->selectColumn('persona', 'tel_per', 'tel_per'),
            $this->selectColumn('persona', 'ema_per', 'ema_per'),
            $this->selectColumn('persona', 'dir_per', 'dir_per'),
            'persona.est_per',
        ];
    }

    private function gestiones(): array
    {
        if (! Schema::hasTable('gestion_academica')) {
            return [];
        }

        return DB::table('gestion_academica')
            ->orderByDesc('ani_gea')
            ->get()
            ->map(fn($gestion) => [
                'cod_gea' => $gestion->cod_gea,
                'anio' => $gestion->ani_gea ?? null,
                'rango' => $this->fechaTexto($gestion->fii_gea ?? null) . ' - ' . $this->fechaTexto($gestion->ffi_gea ?? null),
                'estado' => $gestion->est_gea ?? null,
            ])
            ->values()
            ->all();
    }

    private function cursos(): array
    {
        if (! Schema::hasTable('curso')) {
            return [];
        }

        return DB::table('curso')
            ->where('est_cur', 'ACTIVO')
            ->orderBy('nom_cur')
            ->get()
            ->map(fn($curso) => [
                'cod_cur' => $curso->cod_cur,
                'nombre' => $curso->nom_cur,
                'nivel' => $curso->niv_cur ?? null,
                'estado' => $curso->est_cur,
                'orden' => $this->extraerOrdenCurso($curso->nom_cur ?? ''),
                'requiere_especialidad' => ($this->extraerOrdenCurso($curso->nom_cur ?? '') ?? 0) >= 4,
            ])
            ->values()
            ->all();
    }

    private function paralelos(): array
    {
        if (! Schema::hasTable('paralelo')) {
            return [];
        }

        return DB::table('paralelo')
            ->where('est_par', 'ACTIVO')
            ->orderBy('nom_par')
            ->get()
            ->map(fn($paralelo) => [
                'cod_par' => $paralelo->cod_par,
                'nombre' => $paralelo->nom_par,
                'estado' => $paralelo->est_par,
                'capacidad' => $this->capacidadParalelo($paralelo->cod_par),
            ])
            ->values()
            ->all();
    }

    private function turnos(): array
    {
        if (! Schema::hasTable('turno')) {
            return [];
        }

        return DB::table('turno')
            ->where('est_tur', 'ACTIVO')
            ->orderBy('hor_ini_tur')
            ->get()
            ->map(fn($turno) => [
                'cod_tur' => $turno->cod_tur,
                'nombre' => $turno->nom_tur,
                'inicio' => $this->horaTexto($turno->hor_ini_tur ?? null),
                'fin' => $this->horaTexto($turno->hor_fin_tur ?? null),
                'rango' => $this->horaTexto($turno->hor_ini_tur ?? null) . ' - ' . $this->horaTexto($turno->hor_fin_tur ?? null),
                'estado' => $turno->est_tur,
            ])
            ->values()
            ->all();
    }

    private function obtenerCurso(?string $codCur): ?array
    {
        if (! $codCur || ! Schema::hasTable('curso')) {
            return null;
        }

        $row = DB::table('curso')
            ->where('cod_cur', $codCur)
            ->where('est_cur', 'ACTIVO')
            ->first();

        return $row ? [
            'cod_cur' => $row->cod_cur,
            'nombre' => $row->nom_cur,
            'nivel' => $row->niv_cur ?? null,
            'estado' => $row->est_cur,
            'orden' => $this->extraerOrdenCurso($row->nom_cur ?? ''),
        ] : null;
    }

    private function obtenerParalelo(?string $codPar): ?array
    {
        if (! $codPar || ! Schema::hasTable('paralelo')) {
            return null;
        }

        $row = DB::table('paralelo')
            ->where('cod_par', $codPar)
            ->where('est_par', 'ACTIVO')
            ->first();

        return $row ? [
            'cod_par' => $row->cod_par,
            'nombre' => $row->nom_par,
            'estado' => $row->est_par,
            'capacidad' => $this->capacidadParalelo($row->cod_par),
        ] : null;
    }

    private function obtenerTurno(?string $codTur): ?array
    {
        if (! $codTur || ! Schema::hasTable('turno')) {
            return null;
        }

        $row = DB::table('turno')
            ->where('cod_tur', $codTur)
            ->where('est_tur', 'ACTIVO')
            ->first();

        return $row ? [
            'cod_tur' => $row->cod_tur,
            'nombre' => $row->nom_tur,
            'rango' => $this->horaTexto($row->hor_ini_tur ?? null) . ' - ' . $this->horaTexto($row->hor_fin_tur ?? null),
            'estado' => $row->est_tur,
        ] : null;
    }

    private function tablaEspecialidades(): ?string
    {
        foreach (['especialidad_tecnica', 'especialidades_tecnicas', 'especialidad'] as $tabla) {
            if (Schema::hasTable($tabla)) {
                return $tabla;
            }
        }

        return null;
    }

    private function capacidadParalelo(?string $codPar): int
    {
        if (! $codPar || ! Schema::hasTable('paralelo')) {
            return self::CAPACIDAD_REFERENCIAL_PARALELO;
        }

        foreach (['cap_par', 'cup_par', 'capacidad_par', 'cup_max_par'] as $columna) {
            if (Schema::hasColumn('paralelo', $columna)) {
                $valor = DB::table('paralelo')->where('cod_par', $codPar)->value($columna);

                return $valor ? (int) $valor : self::CAPACIDAD_REFERENCIAL_PARALELO;
            }
        }

        return self::CAPACIDAD_REFERENCIAL_PARALELO;
    }

    /* -------------------------------------------------------------------------
     | UTILIDADES
     * ------------------------------------------------------------------------- */

    private function validarFechaEnGestion(string $fecha, array $gestion): array
    {
        try {
            $fechaInscripcion = Carbon::parse($fecha)->startOfDay();
            $inicio = ! empty($gestion['inicio']) ? Carbon::parse($gestion['inicio'])->startOfDay() : null;
            $fin = ! empty($gestion['fin']) ? Carbon::parse($gestion['fin'])->endOfDay() : null;

            if ($inicio && $fechaInscripcion->lt($inicio)) {
                return [
                    'valida' => false,
                    'mensaje' => 'La fecha de inscripción es anterior al inicio de la gestión académica.',
                ];
            }

            if ($fin && $fechaInscripcion->gt($fin)) {
                return [
                    'valida' => false,
                    'mensaje' => 'La fecha de inscripción supera el cierre de la gestión académica.',
                ];
            }

            return [
                'valida' => true,
                'mensaje' => null,
            ];
        } catch (Throwable) {
            return [
                'valida' => false,
                'mensaje' => 'La fecha de inscripción no tiene un formato válido.',
            ];
        }
    }

    private function validarDatosMinimosEstudiante(array $estudiante): array
    {
        $mensajes = [];

        if ($this->estaVacio($estudiante['rud'] ?? null)) {
            $mensajes[] = 'El estudiante no tiene RUDE registrado.';
        }

        if ($this->estaVacio($estudiante['ci'] ?? null)) {
            $mensajes[] = 'El estudiante no tiene CI registrado.';
        }

        if ($this->estaVacio($estudiante['telefono'] ?? null)) {
            $mensajes[] = 'No existe teléfono de contacto registrado.';
        }

        if ($this->estaVacio($estudiante['direccion'] ?? null)) {
            $mensajes[] = 'No existe dirección registrada.';
        }

        if ($this->estaVacio($estudiante['fecha_nacimiento'] ?? null)) {
            $mensajes[] = 'No existe fecha de nacimiento registrada.';
        }

        return $mensajes;
    }

    private function sugerirEstadoFinal(array $bloqueos, array $advertencias, string $tipo, array $cupo, array $documentos, bool $sobrecupoAutorizado): string
    {
        if (! empty($bloqueos)) {
            return 'PENDIENTE';
        }

        if ($cupo['estado'] === 'LLENO' && $sobrecupoAutorizado) {
            return 'CONDICIONAL';
        }

        if (in_array($tipo, ['EXCEPCIONAL', 'VULNERABILIDAD', 'EXTRANJERO'], true)) {
            return 'PROVISIONAL';
        }

        if (($documentos['pendientes'] ?? 0) > 0 || ($documentos['observados'] ?? 0) > 0) {
            return 'OBSERVADO';
        }

        if (! empty($advertencias)) {
            return 'OBSERVADO';
        }

        return 'INSCRITO';
    }

    private function sugerirCondicion(string $estado, string $tipo, array $cupo, bool $sobrecupoAutorizado): string
    {
        if ($cupo['estado'] === 'LLENO' && $sobrecupoAutorizado) {
            return 'SOBRECUPO';
        }

        if ($tipo === 'TRASLADO') {
            return 'TRASLADO';
        }

        if ($tipo === 'VULNERABILIDAD') {
            return 'VULNERABILIDAD';
        }

        return match ($estado) {
            'OBSERVADO' => 'OBSERVADA',
            'CONDICIONAL' => 'CONDICIONAL',
            'PROVISIONAL' => 'PROVISIONAL',
            default => 'NORMAL',
        };
    }

    private function estadoRevision(array $bloqueos, array $advertencias): string
    {
        if (! empty($bloqueos)) {
            return 'BLOQUEADO';
        }

        if (! empty($advertencias)) {
            return 'OBSERVADO';
        }

        return 'VALIDO';
    }

    private function nivelRiesgo(array $bloqueos, array $advertencias): string
    {
        if (! empty($bloqueos)) {
            return 'ALTO';
        }

        if (count($advertencias) >= 3) {
            return 'MEDIO';
        }

        if (! empty($advertencias)) {
            return 'BAJO';
        }

        return 'SIN_RIESGO';
    }

    private function mensajeRevision(string $estadoRevision): string
    {
        return match ($estadoRevision) {
            'VALIDO' => 'La inscripción cumple las condiciones principales y puede confirmarse.',
            'OBSERVADO' => 'La inscripción puede continuar, pero requiere observación o seguimiento institucional.',
            'BLOQUEADO' => 'La inscripción presenta bloqueos que deben corregirse antes de continuar.',
            default => 'Completa los datos para revisar la inscripción.',
        };
    }

    private function evaluarAccionLogica(?string $codIns, string $motivo, string $accion, array $estadosBloqueados): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $inscripcion = $this->obtenerInscripcion($codIns);

        if (! $inscripcion) {
            $bloqueos[] = 'No se encontró la inscripción seleccionada.';
        } else {
            if (in_array($inscripcion['estado'], $estadosBloqueados, true)) {
                $bloqueos[] = 'La inscripción ya se encuentra anulada o retirada.';
            }

            if ($this->estaVacio($motivo)) {
                $advertencias[] = "Se recomienda registrar el motivo de {$accion} para mantener trazabilidad.";
            }

            if (($inscripcion['documentos_pendientes'] ?? 0) > 0 || ($inscripcion['documentos_observados'] ?? 0) > 0) {
                $advertencias[] = 'La inscripción tiene documentos pendientes u observados. La acción conservará ese historial.';
            }

            $sugerencias[] = "La {$accion} será lógica; no se eliminará físicamente ningún registro.";
        }

        $estado = $this->estadoRevision($bloqueos, $advertencias);

        return [
            'puede_continuar' => empty($bloqueos),
            'estado' => $estado,
            'nivel_riesgo' => $this->nivelRiesgo($bloqueos, $advertencias),
            'mensaje' => match ($estado) {
                'VALIDO' => "La {$accion} puede registrarse conservando trazabilidad.",
                'OBSERVADO' => "La {$accion} puede continuar, pero requiere observación institucional.",
                'BLOQUEADO' => "La {$accion} presenta bloqueos que deben corregirse antes de continuar.",
                default => "Revisa la información antes de continuar con la {$accion}.",
            },
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => $sugerencias,
            'inscripcion' => $inscripcion,
        ];
    }

    private function mapearEstudiante(object $row): array
    {
        $nombreCompleto = trim(collect([
            $row->nom_per ?? '',
            $row->ape_pat_per ?? '',
            $row->ape_mat_per ?? '',
        ])->filter()->implode(' '));

        $ciCompleto = trim(
            ($row->ci_per ?? '')
                . (($row->com_per ?? '') ? '-' . $row->com_per : '')
                . (($row->exp_per ?? '') ? ' ' . $row->exp_per : '')
        );

        return [
            'cod_est' => $row->cod_est,
            'cod_per' => $row->cod_per,
            'rud' => $row->rud_est ?? null,
            'nombre_completo' => $nombreCompleto,
            'ci' => $row->ci_per ?? null,
            'ci_completo' => $ciCompleto,
            'fecha_nacimiento' => $row->fec_nac_per ?? null,
            'edad' => $this->edad($row->fec_nac_per ?? null),
            'genero' => $row->gen_per ?? null,
            'telefono' => $row->tel_per ?? null,
            'correo' => $row->ema_per ?? null,
            'direccion' => $row->dir_per ?? null,
            'estado_estudiante' => $row->est_est ?? null,
            'estado_persona' => $row->est_per ?? null,
            'activo' => ($row->est_est ?? 'ACTIVO') === 'ACTIVO' && (bool) ($row->est_per ?? true),
        ];
    }

    private function resumenVacio(): array
    {
        return [
            'total' => 0,
            'activos' => 0,
            'inscritos' => 0,
            'pendientes' => 0,
            'observados' => 0,
            'condicionales' => 0,
            'provisionales' => 0,
            'anulados' => 0,
            'retirados' => 0,
            'nuevos' => 0,
            'regulares' => 0,
            'traslados' => 0,
            'sobrecupos' => 0,
            'sin_inscripcion' => 0,
            'documentos_pendientes' => 0,
            'documentos_observados' => 0,
            'especialidades_pendientes' => 0,
        ];
    }

    private function documentosPorInscripcion(string $codIns): array
    {
        if (! Schema::hasTable('documento_inscripcion_estudiante')) {
            return [];
        }

        return DB::table('documento_inscripcion_estudiante')
            ->where('cod_ins', $codIns)
            ->orderBy('created_at')
            ->get()
            ->map(fn($doc) => [
                'nombre' => $doc->nom_die,
                'tipo' => $doc->tip_die,
                'estado' => $doc->est_die,
                'observacion' => $doc->obs_die,
                'fecha_presentacion' => $this->fechaTexto($doc->fec_pre_die ?? null),
            ])
            ->values()
            ->all();
    }

    private function contarDocumentosPorGestion(string $codGea, string $estado): int
    {
        if (! Schema::hasTable('documento_inscripcion_estudiante')) {
            return 0;
        }

        return DB::table('documento_inscripcion_estudiante')
            ->join('inscripcion_estudiante', 'inscripcion_estudiante.cod_ins', '=', 'documento_inscripcion_estudiante.cod_ins')
            ->where('inscripcion_estudiante.cod_gea', $codGea)
            ->where('documento_inscripcion_estudiante.est_die', $estado)
            ->count();
    }

    private function contarDocumentosPorInscripcion(string $codIns, string $estado): int
    {
        if (! Schema::hasTable('documento_inscripcion_estudiante')) {
            return 0;
        }

        return DB::table('documento_inscripcion_estudiante')
            ->where('cod_ins', $codIns)
            ->where('est_die', $estado)
            ->count();
    }

    private function contarEspecialidadesPendientes(string $codGea): int
    {
        if (! Schema::hasTable('inscripcion_estudiante')) {
            return 0;
        }

        if (! Schema::hasColumn('inscripcion_estudiante', 'est_esp_tec_ins')) {
            return 0;
        }

        return DB::table('inscripcion_estudiante')
            ->where('cod_gea', $codGea)
            ->where('est_esp_tec_ins', 'PENDIENTE')
            ->whereNotIn('est_ins', self::ESTADOS_NO_ACTIVOS)
            ->count();
    }

    private function documentosFaltantes(array $actuales, array $recomendados): array
    {
        $actualesClaves = collect($actuales)
            ->map(fn($doc) => $this->claveDocumento($doc['nom_die'] ?? ''))
            ->filter()
            ->values()
            ->all();

        return collect($recomendados)
            ->filter(fn($doc) => ! in_array($this->claveDocumento($doc['nom_die'] ?? ''), $actualesClaves, true))
            ->values()
            ->all();
    }

    private function claveDocumento(string $nombre): string
    {
        return str($nombre)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    private function extraerOrdenCurso(string $nombre): ?int
    {
        $texto = str($nombre)->lower()->ascii()->toString();

        foreach (self::CURSOS_EDAD_REFERENCIAL as $curso) {
            foreach ($curso['keywords'] as $keyword) {
                if (str_contains($texto, str($keyword)->lower()->ascii()->toString())) {
                    return (int) $curso['orden'];
                }
            }
        }

        if (preg_match('/\b([1-6])\b/', $texto, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function primeraColumna(string $tabla, array $opciones): ?string
    {
        foreach ($opciones as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }

        return null;
    }

    private function selectColumn(string $table, string $column, string $alias)
    {
        return Schema::hasColumn($table, $column)
            ? "{$table}.{$column}"
            : DB::raw("NULL as {$alias}");
    }

    private function edad(mixed $fechaNacimiento): ?int
    {
        if (! $fechaNacimiento) {
            return null;
        }

        try {
            return Carbon::parse($fechaNacimiento)->age;
        } catch (Throwable) {
            return null;
        }
    }

    private function fechaTexto(mixed $fecha): string
    {
        if (! $fecha) {
            return 'Sin fecha';
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y');
        } catch (Throwable) {
            return 'Sin fecha';
        }
    }

    private function horaTexto(mixed $hora): string
    {
        if (! $hora) {
            return '--:--';
        }

        try {
            return Carbon::parse($hora)->format('H:i');
        } catch (Throwable) {
            return '--:--';
        }
    }

    private function limpiarTexto(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor !== '' ? preg_replace('/\s+/', ' ', $valor) : null;
    }

    private function normalizarMayuscula(?string $valor): string
    {
        return mb_strtoupper(trim((string) $valor));
    }

    private function textoSeguro(?string $valor, ?string $fallback = 'registro seleccionado'): string
    {
        $valor = $this->limpiarTexto($valor);

        return $valor ?: ($fallback ?? 'registro seleccionado');
    }

    private function estaVacio(mixed $valor): bool
    {
        return trim((string) $valor) === '';
    }
}
