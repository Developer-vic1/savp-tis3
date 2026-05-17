<?php

namespace App\Support\Academico;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GestionAcademicaInteligente
{
    // ============================================================
    // ESTADOS Y PARÁMETROS NORMATIVOS
    // ============================================================

    public const ESTADO_PLANIFICADA = 'PLANIFICADA';
    public const ESTADO_ACTIVA = 'ACTIVA';
    public const ESTADO_EN_CIERRE = 'EN_CIERRE';
    public const ESTADO_CERRADA = 'CERRADA';
    public const ESTADO_ANULADA = 'ANULADA';

    public const ESTADOS_OFICIALES = [
        self::ESTADO_PLANIFICADA,
        self::ESTADO_ACTIVA,
        self::ESTADO_EN_CIERRE,
        self::ESTADO_CERRADA,
        self::ESTADO_ANULADA,
    ];

    public const DIAS_HABILES_CURRICULARES = 200;
    public const CANTIDAD_TRIMESTRES = 3;
    public const DESCANSO_PEDAGOGICO_DIAS_HABILES = 10;

    public const DIAS_TRIMESTRE_1 = 66;
    public const DIAS_TRIMESTRE_2 = 68;
    public const DIAS_TRIMESTRE_3 = 66;

    public const DIAS_CALENDARIO_MINIMO_BLOQUEO = 180;
    public const DIAS_CALENDARIO_MINIMO_RECOMENDADO = 270;
    public const DIAS_CALENDARIO_MAXIMO_RECOMENDADO = 330;
    public const DIAS_CALENDARIO_MAXIMO_BLOQUEO = 365;

    public const MESES_INICIO_RECOMENDADOS = [1, 2];
    public const MESES_CIERRE_RECOMENDADOS = [11, 12];

    public const TIPOS_EXPORTACION = [
        'COMPLETA',
        'INSCRIPCIONES',
        'PLANIFICACION',
        'HORARIOS',
        'CALIFICACIONES',
        'REPORTES',
        'AULA_VIRTUAL',
        'ASISTENCIA',
    ];

    public const ESTADOS_LEGACY = [
        'ACTIVO' => self::ESTADO_ACTIVA,
        'PLANIFICADO' => self::ESTADO_PLANIFICADA,
        'CERRADO' => self::ESTADO_CERRADA,
        'ARCHIVADO' => self::ESTADO_CERRADA,
        'INACTIVO' => self::ESTADO_ANULADA,
    ];

    // ============================================================
    // ESTADOS
    // ============================================================

    public static function estados(): array
    {
        return self::ESTADOS_OFICIALES;
    }

    public static function estadosParaSelect(): array
    {
        return [
            self::ESTADO_PLANIFICADA => 'Planificada',
            self::ESTADO_ACTIVA => 'Activa',
            self::ESTADO_EN_CIERRE => 'En cierre',
            self::ESTADO_CERRADA => 'Cerrada',
            self::ESTADO_ANULADA => 'Anulada',
        ];
    }

    public static function estadoValido(?string $estado): bool
    {
        $normalizado = self::normalizarEstado($estado);

        return in_array($normalizado, self::ESTADOS_OFICIALES, true);
    }

    public static function normalizarEstado(?string $estado): string
    {
        $estado = strtoupper(trim((string) $estado));

        if ($estado === '') {
            return self::ESTADO_PLANIFICADA;
        }

        return self::ESTADOS_LEGACY[$estado] ?? $estado;
    }

    public static function estadosActivosCompatibles(): array
    {
        return [
            self::ESTADO_ACTIVA,
            'ACTIVO',
        ];
    }

    public static function estadosPlanificadosCompatibles(): array
    {
        return [
            self::ESTADO_PLANIFICADA,
            'PLANIFICADO',
        ];
    }

    public static function estadosCerradosCompatibles(): array
    {
        return [
            self::ESTADO_CERRADA,
            'CERRADO',
            'ARCHIVADO',
        ];
    }

    // ============================================================
    // ANÁLISIS PRINCIPAL
    // ============================================================

    public function analizarCreacion(array $datos): array
    {
        $anio = (int) ($datos['anio'] ?? 0);
        $inicio = $datos['fecha_inicio'] ?? null;
        $fin = $datos['fecha_fin'] ?? null;
        $estado = self::normalizarEstado($datos['estado'] ?? self::ESTADO_PLANIFICADA);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $resumen = [];

        if (! Schema::hasTable('gestion_academica')) {
            $bloqueos[] = 'No existe la tabla gestion_academica.';
        }

        if ($anio < 2020 || $anio > 2100) {
            $bloqueos[] = 'El año de gestión debe estar entre 2020 y 2100.';
        }

        if (! self::estadoValido($estado)) {
            $bloqueos[] = 'El estado de la gestión académica no es válido.';
        }

        if ($anio > 0 && $this->existeAnioGestion($anio)) {
            $bloqueos[] = "Ya existe una gestión académica registrada para el año {$anio}.";
        }

        if ($estado === self::ESTADO_ACTIVA && $this->existeGestionActiva()) {
            $bloqueos[] = 'Ya existe una gestión académica activa.';
            $sugerencias[] = 'Registra la nueva gestión como PLANIFICADA hasta cerrar o finalizar la gestión actual.';
        }

        $analisisFechas = $this->analizarRangoGestion($anio, $inicio, $fin);

        $bloqueos = array_merge($bloqueos, $analisisFechas['bloqueos']);
        $advertencias = array_merge($advertencias, $analisisFechas['advertencias']);
        $sugerencias = array_merge($sugerencias, $analisisFechas['sugerencias']);
        $resumen = array_merge($resumen, $analisisFechas['resumen']);

        if ($estado === self::ESTADO_ACTIVA && ! $this->tieneEstructuraAcademicaBasica()) {
            $advertencias[] = 'La gestión será creada como ACTIVA, pero todavía no se detecta una estructura académica completa de cursos, paralelos, turnos y asignaturas.';
            $sugerencias[] = 'Configura primero la estructura académica base antes de iniciar la operación institucional.';
        }

        if ($this->existeGestionAnteriorSinCerrar($anio)) {
            $advertencias[] = 'Existe una gestión anterior que no se encuentra cerrada.';
            $sugerencias[] = 'Revisa el cierre institucional de la gestión anterior antes de activar una nueva.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos)
                ? (empty($advertencias) ? 'VALIDO' : 'ADVERTENCIA')
                : 'BLOQUEADO',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La gestión académica puede registrarse.'
                : 'La gestión académica no puede registrarse.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: array_merge($resumen, [
                'estado_normalizado' => $estado,
                'fechas_sugeridas' => $anio > 0 ? $this->sugerirFechasGestion($anio) : [],
                'periodos_sugeridos' => $anio > 0 ? $this->sugerirPeriodosEvaluacion($anio) : [],
            ])
        );
    }

    public function analizarActivacion(string $codGea): array
    {
        $gestion = $this->obtenerGestion($codGea);

        if (! $gestion) {
            return $this->resultadoBloqueado(
                'La gestión académica seleccionada no existe.',
                ['No se encontró el registro en gestion_academica.']
            );
        }

        $estado = self::normalizarEstado($gestion->est_gea ?? '');

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if ($estado === self::ESTADO_ACTIVA) {
            $bloqueos[] = 'La gestión seleccionada ya se encuentra ACTIVA.';
        }

        if ($estado === self::ESTADO_CERRADA) {
            $bloqueos[] = 'No se puede activar una gestión académica cerrada.';
        }

        if ($estado === self::ESTADO_ANULADA) {
            $bloqueos[] = 'No se puede activar una gestión académica anulada.';
        }

        if ($this->existeGestionActiva($codGea)) {
            $bloqueos[] = 'Ya existe otra gestión académica activa.';
            $sugerencias[] = 'Cierra o pasa a EN_CIERRE la gestión activa actual antes de activar esta gestión.';
        }

        $analisisFechas = $this->analizarRangoGestion(
            (int) $gestion->ani_gea,
            $gestion->fii_gea ?? null,
            $gestion->ffi_gea ?? null
        );

        $bloqueos = array_merge($bloqueos, $analisisFechas['bloqueos']);
        $advertencias = array_merge($advertencias, $analisisFechas['advertencias']);
        $sugerencias = array_merge($sugerencias, $analisisFechas['sugerencias']);

        if (! $this->tieneEstructuraAcademicaBasica()) {
            $advertencias[] = 'No se detecta una estructura académica base completa.';
            $sugerencias[] = 'Verifica cursos, paralelos, turnos y asignaturas antes de iniciar la gestión.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos)
                ? (empty($advertencias) ? 'VALIDO' : 'ADVERTENCIA')
                : 'BLOQUEADO',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La gestión académica puede activarse.'
                : 'La gestión académica no puede activarse.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: $this->resumenGestion($codGea)
        );
    }

    public function analizarInicioCierre(string $codGea): array
    {
        $gestion = $this->obtenerGestion($codGea);

        if (! $gestion) {
            return $this->resultadoBloqueado(
                'La gestión académica seleccionada no existe.',
                ['No se encontró el registro en gestion_academica.']
            );
        }

        $estado = self::normalizarEstado($gestion->est_gea ?? '');

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! in_array($estado, [self::ESTADO_ACTIVA, self::ESTADO_EN_CIERRE], true)) {
            $bloqueos[] = 'Solo una gestión ACTIVA o EN_CIERRE puede revisarse para cierre.';
        }

        $resumen = $this->resumenGestion($codGea);
        $pendientes = $this->pendientesCierre($codGea);

        if ($this->totalPendientes($pendientes) > 0) {
            $advertencias[] = 'La gestión tiene procesos académicos pendientes.';
            $sugerencias[] = 'Puede pasar a EN_CIERRE para revisión, pero no se recomienda cerrarla definitivamente todavía.';
        }

        if (($resumen['inscripciones'] ?? 0) === 0 && ($resumen['planes_asignatura'] ?? 0) === 0) {
            $advertencias[] = 'La gestión no presenta registros académicos suficientes.';
            $sugerencias[] = 'Verifica si corresponde a una gestión planificada, de prueba o sin operación académica.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos)
                ? ($this->totalPendientes($pendientes) > 0 ? 'CIERRE_CON_PENDIENTES' : 'LISTO_PARA_CIERRE')
                : 'BLOQUEADO',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La gestión puede pasar a revisión de cierre.'
                : 'La gestión no puede iniciar revisión de cierre.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: array_merge($resumen, [
                'pendientes_cierre' => $pendientes,
            ])
        );
    }

    public function analizarCierreDefinitivo(string $codGea): array
    {
        $gestion = $this->obtenerGestion($codGea);

        if (! $gestion) {
            return $this->resultadoBloqueado(
                'La gestión académica seleccionada no existe.',
                ['No se encontró el registro en gestion_academica.']
            );
        }

        $estado = self::normalizarEstado($gestion->est_gea ?? '');

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! in_array($estado, [self::ESTADO_ACTIVA, self::ESTADO_EN_CIERRE], true)) {
            $bloqueos[] = 'Solo una gestión ACTIVA o EN_CIERRE puede cerrarse definitivamente.';
        }

        $pendientes = $this->pendientesCierre($codGea);

        foreach ($pendientes as $titulo => $valor) {
            if ((int) $valor > 0) {
                $bloqueos[] = "{$titulo}: {$valor}.";
            }
        }

        if (! empty($bloqueos)) {
            $sugerencias[] = 'Corrige los procesos pendientes antes de cerrar la gestión como expediente histórico.';
        }

        $resumen = $this->resumenGestion($codGea);

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos) ? 'LISTO_PARA_CIERRE' : 'BLOQUEADO',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La gestión académica puede cerrarse definitivamente.'
                : 'La gestión académica no puede cerrarse definitivamente.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: array_merge($resumen, [
                'pendientes_cierre' => $pendientes,
            ])
        );
    }

    public function analizarAnulacion(string $codGea): array
    {
        $gestion = $this->obtenerGestion($codGea);

        if (! $gestion) {
            return $this->resultadoBloqueado(
                'La gestión académica seleccionada no existe.',
                ['No se encontró el registro en gestion_academica.']
            );
        }

        $estado = self::normalizarEstado($gestion->est_gea ?? '');
        $resumen = $this->resumenGestion($codGea);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if ($estado === self::ESTADO_CERRADA) {
            $bloqueos[] = 'No se recomienda anular una gestión cerrada porque ya forma parte del historial institucional.';
        }

        $usoAcademico = (
            ($resumen['inscripciones'] ?? 0) +
            ($resumen['planes_asignatura'] ?? 0) +
            ($resumen['planes_especialidad'] ?? 0) +
            ($resumen['horarios'] ?? 0) +
            ($resumen['calificaciones'] ?? 0)
        );

        if ($usoAcademico > 0) {
            $bloqueos[] = 'La gestión tiene uso académico registrado.';
            $sugerencias[] = 'No anules la gestión. Usa EN_CIERRE o CERRADA para conservar trazabilidad.';
        }

        if ($estado === self::ESTADO_ANULADA) {
            $advertencias[] = 'La gestión ya se encuentra anulada.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos) ? 'VALIDO' : 'EN_USO',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La gestión puede anularse si corresponde a un error administrativo.'
                : 'La gestión no debería anularse porque tiene trazabilidad académica.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: $resumen
        );
    }

    public function analizarExportacion(string $codGea, string $tipo = 'COMPLETA'): array
    {
        $gestion = $this->obtenerGestion($codGea);
        $tipo = strtoupper(trim($tipo));

        if (! $gestion) {
            return $this->resultadoBloqueado(
                'La gestión académica seleccionada no existe.',
                ['No se encontró el registro en gestion_academica.']
            );
        }

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! in_array($tipo, self::TIPOS_EXPORTACION, true)) {
            $bloqueos[] = 'El tipo de exportación solicitado no es válido.';
        }

        $estado = self::normalizarEstado($gestion->est_gea ?? '');
        $resumen = $this->resumenGestion($codGea);

        $totalRegistros = (
            ($resumen['inscripciones'] ?? 0) +
            ($resumen['planes_asignatura'] ?? 0) +
            ($resumen['planes_especialidad'] ?? 0) +
            ($resumen['horarios'] ?? 0) +
            ($resumen['calificaciones'] ?? 0) +
            ($resumen['reportes'] ?? 0) +
            ($resumen['clases_virtuales'] ?? 0) +
            ($resumen['asistencias'] ?? 0)
        );

        if ($totalRegistros === 0) {
            $advertencias[] = 'La gestión no tiene registros académicos asociados.';
            $sugerencias[] = 'La exportación podría generarse como respaldo vacío o preliminar.';
        }

        if ($estado === self::ESTADO_ACTIVA) {
            $advertencias[] = 'La gestión está activa. La exportación será preliminar.';
        }

        if ($estado === self::ESTADO_EN_CIERRE) {
            $advertencias[] = 'La gestión está en cierre. La exportación puede usarse para revisión institucional.';
        }

        if ($estado === self::ESTADO_CERRADA) {
            $sugerencias[] = 'La gestión cerrada puede exportarse como expediente histórico institucional.';
        }

        if ($estado === self::ESTADO_ANULADA) {
            $advertencias[] = 'La gestión está anulada. La exportación debe considerarse solo administrativa.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos)
                ? ($totalRegistros > 0 ? 'EXPORTABLE' : 'SIN_DATOS')
                : 'NO_EXPORTABLE',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La gestión académica puede prepararse para exportación.'
                : 'La gestión académica no puede exportarse con los parámetros actuales.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: array_merge($resumen, [
                'tipo_exportacion' => $tipo,
                'estado_normalizado' => $estado,
                'total_registros_exportables' => $totalRegistros,
            ])
        );
    }

    // ============================================================
    // FECHAS, GESTIÓN Y PERIODOS
    // ============================================================

    public function sugerirFechasGestion(int $anio): array
    {
        return [
            'anio' => $anio,
            'inicio_institucional' => "{$anio}-01-19",
            'inicio_curricular' => "{$anio}-02-02",
            'cierre_curricular' => "{$anio}-12-02",
            'cierre_institucional' => "{$anio}-12-11",
            'dias_habiles_curriculares_referencia' => self::DIAS_HABILES_CURRICULARES,
            'cantidad_trimestres' => self::CANTIDAD_TRIMESTRES,
            'descanso_pedagogico_dias_habiles' => self::DESCANSO_PEDAGOGICO_DIAS_HABILES,
            'recomendacion' => 'Para gestión académica institucional se recomienda usar inicio institucional y cierre institucional.',
        ];
    }

    public function sugerirPeriodosEvaluacion(int $anio): array
    {
        return [
            [
                'nombre' => 'Primer trimestre',
                'orden' => 1,
                'fecha_inicio' => "{$anio}-02-02",
                'fecha_fin' => "{$anio}-05-08",
                'dias_habiles_referencia' => self::DIAS_TRIMESTRE_1,
                'incluye_descanso_pedagogico' => false,
                'descanso_pedagogico_dias_habiles' => 0,
            ],
            [
                'nombre' => 'Segundo trimestre',
                'orden' => 2,
                'fecha_inicio' => "{$anio}-05-11",
                'fecha_fin' => "{$anio}-08-31",
                'dias_habiles_referencia' => self::DIAS_TRIMESTRE_2,
                'incluye_descanso_pedagogico' => true,
                'descanso_pedagogico_dias_habiles' => self::DESCANSO_PEDAGOGICO_DIAS_HABILES,
            ],
            [
                'nombre' => 'Tercer trimestre',
                'orden' => 3,
                'fecha_inicio' => "{$anio}-09-01",
                'fecha_fin' => "{$anio}-12-02",
                'dias_habiles_referencia' => self::DIAS_TRIMESTRE_3,
                'incluye_descanso_pedagogico' => false,
                'descanso_pedagogico_dias_habiles' => 0,
            ],
        ];
    }

    public function analizarRangoGestion(int $anio, ?string $inicio, ?string $fin): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $resumen = [
            'anio' => $anio,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'duracion_calendario_dias' => 0,
            'clasificacion_duracion' => 'SIN_DATOS',
        ];

        if (! $inicio || ! $fin) {
            $bloqueos[] = 'La fecha de inicio y la fecha de cierre de la gestión son obligatorias.';
            $sugerencias[] = 'Usa el inicio institucional y cierre institucional sugeridos para la gestión.';
            return compact('bloqueos', 'advertencias', 'sugerencias', 'resumen');
        }

        try {
            $fechaInicio = Carbon::parse($inicio)->startOfDay();
            $fechaFin = Carbon::parse($fin)->startOfDay();
        } catch (Throwable) {
            $bloqueos[] = 'Las fechas ingresadas no tienen un formato válido.';
            return compact('bloqueos', 'advertencias', 'sugerencias', 'resumen');
        }

        if ($fechaFin->lessThanOrEqualTo($fechaInicio)) {
            $bloqueos[] = 'La fecha de cierre debe ser posterior a la fecha de inicio.';
        }

        if ($anio > 0 && (int) $fechaInicio->year !== $anio) {
            $bloqueos[] = 'El año de la fecha de inicio no coincide con el año de la gestión académica.';
        }

        if ($anio > 0 && (int) $fechaFin->year !== $anio) {
            $bloqueos[] = 'El año de la fecha de cierre no coincide con el año de la gestión académica.';
        }

        $duracion = $fechaInicio->diffInDays($fechaFin) + 1;

        $resumen['duracion_calendario_dias'] = $duracion;

        if ($duracion < self::DIAS_CALENDARIO_MINIMO_BLOQUEO) {
            $bloqueos[] = "La gestión académica dura {$duracion} días calendario. Una gestión educativa regular no puede representar un periodo tan corto.";
            $resumen['clasificacion_duracion'] = 'BLOQUEADA_CORTA';
        } elseif ($duracion < self::DIAS_CALENDARIO_MINIMO_RECOMENDADO) {
            $advertencias[] = "La gestión académica dura {$duracion} días calendario, por debajo del rango recomendado.";
            $sugerencias[] = 'Verifica si corresponde a una adecuación excepcional. Para una gestión regular se recomienda cubrir el ciclo anual institucional.';
            $resumen['clasificacion_duracion'] = 'ADVERTENCIA_CORTA';
        } elseif ($duracion <= self::DIAS_CALENDARIO_MAXIMO_RECOMENDADO) {
            $resumen['clasificacion_duracion'] = 'VALIDA';
        } elseif ($duracion <= self::DIAS_CALENDARIO_MAXIMO_BLOQUEO) {
            $advertencias[] = "La gestión académica dura {$duracion} días calendario, por encima del rango recomendado.";
            $sugerencias[] = 'Verifica que el cierre extendido responda a adecuación institucional, climática, regional o sanitaria.';
            $resumen['clasificacion_duracion'] = 'ADVERTENCIA_LARGA';
        } else {
            $bloqueos[] = "La gestión académica dura {$duracion} días calendario, superando el máximo razonable de un ciclo escolar.";
            $resumen['clasificacion_duracion'] = 'BLOQUEADA_LARGA';
        }

        if (! in_array((int) $fechaInicio->month, self::MESES_INICIO_RECOMENDADOS, true)) {
            $advertencias[] = 'La fecha de inicio está fuera del periodo habitual de inscripción, planificación o inicio curricular.';
            $sugerencias[] = 'Para una gestión regular se recomienda iniciar entre enero y febrero.';
        }

        if (! in_array((int) $fechaFin->month, self::MESES_CIERRE_RECOMENDADOS, true)) {
            $advertencias[] = 'La fecha de cierre está fuera del periodo habitual de cierre curricular o cierre institucional.';
            $sugerencias[] = 'Para una gestión regular se recomienda cerrar entre noviembre y diciembre.';
        }

        $resumen['fechas_sugeridas'] = $anio > 0 ? $this->sugerirFechasGestion($anio) : [];

        return compact('bloqueos', 'advertencias', 'sugerencias', 'resumen');
    }

    public function analizarRangoCurricular(int $anio, ?string $inicio, ?string $fin): array
    {
        $resultado = $this->analizarRangoGestion($anio, $inicio, $fin);

        if (! $inicio || ! $fin) {
            return $resultado;
        }

        try {
            $diasHabiles = $this->calcularDiasHabilesReferenciales($inicio, $fin);
        } catch (Throwable) {
            $resultado['bloqueos'][] = 'No se pudo calcular los días hábiles referenciales.';
            return $resultado;
        }

        $resultado['resumen']['dias_habiles_referenciales'] = $diasHabiles;
        $resultado['resumen']['dias_habiles_curriculares_esperados'] = self::DIAS_HABILES_CURRICULARES;

        if ($diasHabiles < self::DIAS_HABILES_CURRICULARES - 20) {
            $resultado['advertencias'][] = 'El rango curricular parece estar por debajo de los 200 días hábiles referenciales.';
            $resultado['sugerencias'][] = 'Revisa si el calendario debe extenderse o si existen periodos no considerados.';
        }

        if ($diasHabiles > self::DIAS_HABILES_CURRICULARES + 40) {
            $resultado['advertencias'][] = 'El rango curricular supera ampliamente los 200 días hábiles referenciales.';
            $resultado['sugerencias'][] = 'Verifica si el rango representa desarrollo curricular o gestión institucional completa.';
        }

        return $resultado;
    }

    public function analizarPeriodosEvaluacion(array $periodos, int $anio): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $resumen = [
            'anio' => $anio,
            'cantidad_periodos' => count($periodos),
            'dias_habiles_referencia' => 0,
            'periodos' => [],
        ];

        if (count($periodos) !== self::CANTIDAD_TRIMESTRES) {
            $bloqueos[] = 'La gestión educativa regular debe organizarse en tres trimestres.';
            $sugerencias[] = 'Configura Primer trimestre, Segundo trimestre y Tercer trimestre.';
        }

        usort($periodos, function ($a, $b) {
            return ((int) ($a['orden'] ?? 0)) <=> ((int) ($b['orden'] ?? 0));
        });

        $fechaFinAnterior = null;
        $ordenEsperado = 1;
        $incluyeDescanso = false;

        foreach ($periodos as $periodo) {
            $nombre = (string) ($periodo['nombre'] ?? 'Periodo sin nombre');
            $orden = (int) ($periodo['orden'] ?? 0);
            $inicio = $periodo['fecha_inicio'] ?? null;
            $fin = $periodo['fecha_fin'] ?? null;
            $diasReferencia = (int) ($periodo['dias_habiles_referencia'] ?? 0);
            $descanso = (bool) ($periodo['incluye_descanso_pedagogico'] ?? false);

            if ($orden !== $ordenEsperado) {
                $advertencias[] = "El periodo {$nombre} no respeta el orden trimestral esperado.";
            }

            if (! $inicio || ! $fin) {
                $advertencias[] = "El periodo {$nombre} no tiene fechas completas.";
            } else {
                try {
                    $fechaInicio = Carbon::parse($inicio)->startOfDay();
                    $fechaFin = Carbon::parse($fin)->startOfDay();

                    if ((int) $fechaInicio->year !== $anio || (int) $fechaFin->year !== $anio) {
                        $advertencias[] = "El periodo {$nombre} tiene fechas fuera del año de gestión.";
                    }

                    if ($fechaFin->lessThanOrEqualTo($fechaInicio)) {
                        $bloqueos[] = "El periodo {$nombre} tiene una fecha final inválida.";
                    }

                    if ($fechaFinAnterior && $fechaInicio->lessThanOrEqualTo($fechaFinAnterior)) {
                        $bloqueos[] = "El periodo {$nombre} se solapa con el periodo anterior.";
                    }

                    $fechaFinAnterior = $fechaFin;
                } catch (Throwable) {
                    $bloqueos[] = "El periodo {$nombre} tiene fechas con formato inválido.";
                }
            }

            if ($descanso) {
                $incluyeDescanso = true;
            }

            $resumen['dias_habiles_referencia'] += $diasReferencia;
            $resumen['periodos'][] = [
                'nombre' => $nombre,
                'orden' => $orden,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'dias_habiles_referencia' => $diasReferencia,
                'incluye_descanso_pedagogico' => $descanso,
            ];

            $ordenEsperado++;
        }

        if ($resumen['dias_habiles_referencia'] !== self::DIAS_HABILES_CURRICULARES) {
            $advertencias[] = 'La suma de días hábiles referenciales de los trimestres no coincide con los 200 días esperados.';
            $sugerencias[] = 'Usa la referencia 66 + 68 + 66 días hábiles para los tres trimestres.';
        }

        if (! $incluyeDescanso) {
            $advertencias[] = 'No se detectó descanso pedagógico de invierno en los periodos.';
            $sugerencias[] = 'El segundo trimestre debe contemplar el descanso pedagógico referencial de 10 días hábiles.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos)
                ? (empty($advertencias) ? 'VALIDO' : 'ADVERTENCIA')
                : 'BLOQUEADO',
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'Los periodos de evaluación pueden utilizarse como referencia académica.'
                : 'Los periodos de evaluación presentan inconsistencias críticas.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: $resumen
        );
    }

    public function calcularDiasHabilesReferenciales(string $inicio, string $fin, array $descansos = []): int
    {
        $fechaInicio = Carbon::parse($inicio)->startOfDay();
        $fechaFin = Carbon::parse($fin)->startOfDay();

        if ($fechaFin->lessThan($fechaInicio)) {
            return 0;
        }

        $diasHabiles = 0;
        $periodo = CarbonPeriod::create($fechaInicio, $fechaFin);

        foreach ($periodo as $fecha) {
            if ($fecha->isWeekend()) {
                continue;
            }

            if ($this->fechaDentroDeDescansos($fecha, $descansos)) {
                continue;
            }

            $diasHabiles++;
        }

        return $diasHabiles;
    }

    // ============================================================
    // RESUMEN Y PENDIENTES
    // ============================================================

    public function resumenGestion(string $codGea): array
    {
        $gestion = $this->obtenerGestion($codGea);

        if (! $gestion) {
            return [
                'existe' => false,
                'mensaje' => 'Gestión no encontrada.',
            ];
        }

        return [
            'existe' => true,
            'cod_gea' => $gestion->cod_gea,
            'anio' => (int) $gestion->ani_gea,
            'estado_original' => $gestion->est_gea ?? null,
            'estado_normalizado' => self::normalizarEstado($gestion->est_gea ?? null),
            'fecha_inicio' => $gestion->fii_gea ?? null,
            'fecha_fin' => $gestion->ffi_gea ?? null,

            'inscripciones' => $this->contarPorGestion('inscripcion_estudiante', $codGea),
            'planes_asignatura' => $this->contarPorGestion('plan_asignatura', $codGea),
            'planes_especialidad' => $this->contarPorGestion('plan_especialidad', $codGea),
            'horarios' => $this->contarPorGestion('horario', $codGea),
            'calificaciones' => $this->contarCalificacionesPorGestion($codGea),
            'reportes' => $this->contarPorGestion('reporte', $codGea),

            'periodos_catalogo' => $this->contarTabla('periodo_evaluacion'),
            'cursos_activos' => $this->contarActivos('curso', 'est_cur'),
            'paralelos_activos' => $this->contarActivos('paralelo', 'est_par'),
            'turnos_activos' => $this->contarActivos('turno', 'est_tur'),
            'asignaturas_activas' => $this->contarActivos('asignatura', 'est_asi'),
            'docentes_activos' => $this->contarActivos('docente', 'est_doc'),

            'clases_virtuales' => $this->contarPorGestionIndirectaAulaVirtual($codGea),
            'tareas' => $this->contarTareasPorGestion($codGea),
            'asistencias' => $this->contarAsistenciasPorGestion($codGea),

            'fechas_sugeridas' => $this->sugerirFechasGestion((int) $gestion->ani_gea),
            'periodos_sugeridos' => $this->sugerirPeriodosEvaluacion((int) $gestion->ani_gea),
        ];
    }

    public function pendientesCierre(string $codGea): array
    {
        return [
            'Inscripciones pendientes u observadas' => $this->contarInscripcionesPendientes($codGea),
            'Planes de asignatura incompletos' => $this->contarPlanesAsignaturaIncompletos($codGea),
            'Planes de especialidad incompletos' => $this->contarPlanesEspecialidadIncompletos($codGea),
            'Horarios en borrador o inconsistentes' => $this->contarHorariosPendientes($codGea),
            'Reportes pendientes' => $this->contarReportesPendientes(),
            'Clases virtuales abiertas' => $this->contarClasesVirtualesAbiertas($codGea),
            'Tareas abiertas' => $this->contarTareasAbiertas($codGea),
            'Asistencias abiertas' => $this->contarAsistenciasAbiertas($codGea),
        ];
    }

    // ============================================================
    // CONSULTAS SEGURAS
    // ============================================================

    private function obtenerGestion(string $codGea): ?object
    {
        if (! Schema::hasTable('gestion_academica')) {
            return null;
        }

        return DB::table('gestion_academica')
            ->where('cod_gea', $codGea)
            ->first();
    }

    private function existeAnioGestion(int $anio): bool
    {
        return Schema::hasTable('gestion_academica')
            && DB::table('gestion_academica')
            ->where('ani_gea', $anio)
            ->exists();
    }

    private function existeGestionActiva(?string $exceptoCodGea = null): bool
    {
        if (! Schema::hasTable('gestion_academica')) {
            return false;
        }

        return DB::table('gestion_academica')
            ->whereIn('est_gea', self::estadosActivosCompatibles())
            ->when($exceptoCodGea, fn($query) => $query->where('cod_gea', '!=', $exceptoCodGea))
            ->exists();
    }

    private function existeGestionAnteriorSinCerrar(int $anio): bool
    {
        if (! Schema::hasTable('gestion_academica')) {
            return false;
        }

        return DB::table('gestion_academica')
            ->where('ani_gea', '<', $anio)
            ->whereNotIn('est_gea', array_merge(
                self::estadosCerradosCompatibles(),
                [self::ESTADO_ANULADA, 'ANULADO']
            ))
            ->exists();
    }

    private function tieneEstructuraAcademicaBasica(): bool
    {
        return $this->contarActivos('curso', 'est_cur') > 0
            && $this->contarActivos('paralelo', 'est_par') > 0
            && $this->contarActivos('turno', 'est_tur') > 0
            && $this->contarActivos('asignatura', 'est_asi') > 0;
    }

    private function contarTabla(string $tabla): int
    {
        if (! Schema::hasTable($tabla)) {
            return 0;
        }

        return DB::table($tabla)->count();
    }

    private function contarActivos(string $tabla, string $columnaEstado): int
    {
        if (! Schema::hasTable($tabla)) {
            return 0;
        }

        if (! Schema::hasColumn($tabla, $columnaEstado)) {
            return DB::table($tabla)->count();
        }

        return DB::table($tabla)
            ->where($columnaEstado, 'ACTIVO')
            ->count();
    }

    private function contarPorGestion(string $tabla, string $codGea): int
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'cod_gea')) {
            return 0;
        }

        return DB::table($tabla)
            ->where('cod_gea', $codGea)
            ->count();
    }

    private function contarInscripcionesPendientes(string $codGea): int
    {
        if (! Schema::hasTable('inscripcion_estudiante')) {
            return 0;
        }

        if (! Schema::hasColumn('inscripcion_estudiante', 'est_ins')) {
            return 0;
        }

        return DB::table('inscripcion_estudiante')
            ->where('cod_gea', $codGea)
            ->whereIn('est_ins', [
                'PENDIENTE',
                'OBSERVADO',
                'INACTIVO',
                'RETIRADO',
                'TRASLADADO',
                'ANULADO',
            ])
            ->count();
    }

    private function contarPlanesAsignaturaIncompletos(string $codGea): int
    {
        if (! Schema::hasTable('plan_asignatura')) {
            return 0;
        }

        return DB::table('plan_asignatura')
            ->where('cod_gea', $codGea)
            ->where(function ($query) {
                if (Schema::hasColumn('plan_asignatura', 'est_pas')) {
                    $query->where('est_pas', '!=', 'ACTIVO');
                }

                if (Schema::hasColumn('plan_asignatura', 'hor_pas')) {
                    $query->orWhereNull('hor_pas')
                        ->orWhere('hor_pas', '<=', 0);
                }
            })
            ->count();
    }

    private function contarPlanesEspecialidadIncompletos(string $codGea): int
    {
        if (! Schema::hasTable('plan_especialidad')) {
            return 0;
        }

        return DB::table('plan_especialidad')
            ->where('cod_gea', $codGea)
            ->where(function ($query) {
                if (Schema::hasColumn('plan_especialidad', 'est_pes')) {
                    $query->where('est_pes', '!=', 'ACTIVO');
                }

                if (Schema::hasColumn('plan_especialidad', 'hor_pes')) {
                    $query->orWhereNull('hor_pes')
                        ->orWhere('hor_pes', '<=', 0);
                }
            })
            ->count();
    }

    private function contarHorariosPendientes(string $codGea): int
    {
        if (! Schema::hasTable('horario')) {
            return 0;
        }

        if (! Schema::hasColumn('horario', 'est_hor')) {
            return 0;
        }

        return DB::table('horario')
            ->where('cod_gea', $codGea)
            ->whereIn('est_hor', [
                'BORRADOR',
                'PENDIENTE',
                'OBSERVADO',
                'INACTIVO',
            ])
            ->count();
    }

    private function contarReportesPendientes(): int
    {
        if (! Schema::hasTable('reporte') || ! Schema::hasColumn('reporte', 'est_rep')) {
            return 0;
        }

        return DB::table('reporte')
            ->whereIn('est_rep', [
                'PENDIENTE',
                'OBSERVADO',
                'NO_GENERADO',
            ])
            ->count();
    }

    private function contarCalificacionesPorGestion(string $codGea): int
    {
        if (Schema::hasTable('calificacion') && Schema::hasColumn('calificacion', 'cod_gea')) {
            return $this->contarPorGestion('calificacion', $codGea);
        }

        if (Schema::hasTable('calificacion_estudiante') && Schema::hasColumn('calificacion_estudiante', 'cod_gea')) {
            return $this->contarPorGestion('calificacion_estudiante', $codGea);
        }

        return 0;
    }

    private function contarPorGestionIndirectaAulaVirtual(string $codGea): int
    {
        if (! Schema::hasTable('clase_virtual')) {
            return 0;
        }

        if (Schema::hasColumn('clase_virtual', 'cod_gea')) {
            return $this->contarPorGestion('clase_virtual', $codGea);
        }

        if (
            Schema::hasTable('plan_asignatura')
            && Schema::hasColumn('clase_virtual', 'cod_pas')
            && Schema::hasColumn('plan_asignatura', 'cod_gea')
        ) {
            return DB::table('clase_virtual')
                ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
                ->where('plan_asignatura.cod_gea', $codGea)
                ->count();
        }

        return 0;
    }

    private function contarTareasPorGestion(string $codGea): int
    {
        if (
            ! Schema::hasTable('tarea')
            || ! Schema::hasTable('clase_virtual')
            || ! Schema::hasTable('plan_asignatura')
            || ! Schema::hasColumn('tarea', 'cod_cla')
            || ! Schema::hasColumn('clase_virtual', 'cod_pas')
        ) {
            return 0;
        }

        return DB::table('tarea')
            ->join('clase_virtual', 'tarea.cod_cla', '=', 'clase_virtual.cod_cla')
            ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
            ->where('plan_asignatura.cod_gea', $codGea)
            ->count();
    }

    private function contarAsistenciasPorGestion(string $codGea): int
    {
        if (
            ! Schema::hasTable('asistencia_clase')
            || ! Schema::hasTable('clase_virtual')
            || ! Schema::hasTable('plan_asignatura')
            || ! Schema::hasColumn('asistencia_clase', 'cod_cla')
            || ! Schema::hasColumn('clase_virtual', 'cod_pas')
        ) {
            return 0;
        }

        return DB::table('asistencia_clase')
            ->join('clase_virtual', 'asistencia_clase.cod_cla', '=', 'clase_virtual.cod_cla')
            ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
            ->where('plan_asignatura.cod_gea', $codGea)
            ->count();
    }

    private function contarClasesVirtualesAbiertas(string $codGea): int
    {
        if (
            ! Schema::hasTable('clase_virtual')
            || ! Schema::hasTable('plan_asignatura')
            || ! Schema::hasColumn('clase_virtual', 'cod_pas')
            || ! Schema::hasColumn('clase_virtual', 'est_cla')
        ) {
            return 0;
        }

        return DB::table('clase_virtual')
            ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
            ->where('plan_asignatura.cod_gea', $codGea)
            ->whereIn('clase_virtual.est_cla', [
                'ACTIVA',
                'ACTIVO',
                'ABIERTA',
                'PUBLICADA',
            ])
            ->count();
    }

    private function contarTareasAbiertas(string $codGea): int
    {
        if (
            ! Schema::hasTable('tarea')
            || ! Schema::hasTable('clase_virtual')
            || ! Schema::hasTable('plan_asignatura')
            || ! Schema::hasColumn('tarea', 'cod_cla')
            || ! Schema::hasColumn('tarea', 'est_tar')
        ) {
            return 0;
        }

        return DB::table('tarea')
            ->join('clase_virtual', 'tarea.cod_cla', '=', 'clase_virtual.cod_cla')
            ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
            ->where('plan_asignatura.cod_gea', $codGea)
            ->whereIn('tarea.est_tar', [
                'BORRADOR',
                'PUBLICADA',
            ])
            ->count();
    }

    private function contarAsistenciasAbiertas(string $codGea): int
    {
        if (
            ! Schema::hasTable('asistencia_clase')
            || ! Schema::hasTable('clase_virtual')
            || ! Schema::hasTable('plan_asignatura')
            || ! Schema::hasColumn('asistencia_clase', 'est_asi_cla')
        ) {
            return 0;
        }

        return DB::table('asistencia_clase')
            ->join('clase_virtual', 'asistencia_clase.cod_cla', '=', 'clase_virtual.cod_cla')
            ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
            ->where('plan_asignatura.cod_gea', $codGea)
            ->whereIn('asistencia_clase.est_asi_cla', [
                'BORRADOR',
                'ABIERTA',
            ])
            ->count();
    }

    // ============================================================
    // UTILIDADES
    // ============================================================

    private function fechaDentroDeDescansos(Carbon $fecha, array $descansos): bool
    {
        foreach ($descansos as $descanso) {
            $inicio = $descanso['inicio'] ?? null;
            $fin = $descanso['fin'] ?? null;

            if (! $inicio || ! $fin) {
                continue;
            }

            try {
                $inicioCarbon = Carbon::parse($inicio)->startOfDay();
                $finCarbon = Carbon::parse($fin)->startOfDay();

                if ($fecha->betweenIncluded($inicioCarbon, $finCarbon)) {
                    return true;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return false;
    }

    private function totalPendientes(array $pendientes): int
    {
        return array_sum(array_map(fn($valor) => (int) $valor, $pendientes));
    }

    private function nivelRiesgo(array $bloqueos, array $advertencias): string
    {
        if (count($bloqueos) >= 3) {
            return 'CRITICO';
        }

        if (count($bloqueos) > 0) {
            return 'ALTO';
        }

        if (count($advertencias) >= 3) {
            return 'MEDIO';
        }

        if (count($advertencias) > 0) {
            return 'BAJO';
        }

        return 'BAJO';
    }

    private function resultado(
        bool $puedeContinuar,
        string $estadoInteligente,
        string $nivelRiesgo,
        string $mensaje,
        array $bloqueos = [],
        array $advertencias = [],
        array $sugerencias = [],
        array $resumen = []
    ): array {
        return [
            'puede_continuar' => $puedeContinuar,
            'estado_inteligente' => $estadoInteligente,
            'nivel_riesgo' => $nivelRiesgo,
            'mensaje' => $mensaje,
            'bloqueos' => array_values($bloqueos),
            'advertencias' => array_values($advertencias),
            'sugerencias' => array_values($sugerencias),
            'resumen' => $resumen,
        ];
    }

    private function resultadoBloqueado(string $mensaje, array $bloqueos): array
    {
        return $this->resultado(
            puedeContinuar: false,
            estadoInteligente: 'BLOQUEADO',
            nivelRiesgo: 'ALTO',
            mensaje: $mensaje,
            bloqueos: $bloqueos,
            advertencias: [],
            sugerencias: [],
            resumen: []
        );
    }
}
