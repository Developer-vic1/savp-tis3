<?php

namespace App\Support\Academico;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class TurnoInteligente
{
    public const ESTADO_VALIDO = 'VALIDO';
    public const ESTADO_ADVERTENCIA = 'ADVERTENCIA';
    public const ESTADO_OBSERVADO = 'OBSERVADO';
    public const ESTADO_BLOQUEADO = 'BLOQUEADO';
    public const ESTADO_INCOMPLETO = 'INCOMPLETO';
    public const ESTADO_CORREGIBLE = 'CORREGIBLE';

    public const ACTIVO = 'ACTIVO';
    public const INACTIVO = 'INACTIVO';

    public const TIPO_REGULAR = 'REGULAR';
    public const TIPO_INVIERNO = 'INVIERNO';
    public const TIPO_AJUSTE = 'AJUSTE';
    public const TIPO_EMERGENCIA = 'EMERGENCIA';

    public const TIPOS_PLANTILLA_OPERATIVOS = [
        self::TIPO_REGULAR,
        self::TIPO_INVIERNO,
    ];

    public const TIPOS_PLANTILLA_COMPATIBLES = [
        self::TIPO_REGULAR,
        self::TIPO_INVIERNO,
        self::TIPO_AJUSTE,
        self::TIPO_EMERGENCIA,
    ];

    public const TIPOS_BLOQUE = [
        'CLASE',
        'RECREO',
        'DESCANSO',
        'FORMACION',
        'SALIDA',
        'OTRO',
    ];

    public const TURNOS_REGULARES = [
        'MANANA',
        'MAÑANA',
        'TARDE',
    ];

    public const DIAS_HABILES_CURRICULARES = 200;
    public const CANTIDAD_TRIMESTRES = 3;
    public const DESCANSO_PEDAGOGICO_DIAS_HABILES = 10;

    public const DURACION_BLOQUE_REGULAR_REFERENCIAL = 45;
    public const DURACION_BLOQUE_INVIERNO_REFERENCIAL = 30;

    public const DURACION_MINIMA_BLOQUE = 5;
    public const DURACION_MAXIMA_BLOQUE = 120;

    public const DURACION_MINIMA_TURNO = 30;
    public const DURACION_MAXIMA_TURNO_RECOMENDADA = 420;

    // ============================================================
    // AUDITORÍA Y CORRECCIÓN DE ESTRUCTURA HORARIA
    // ============================================================

    public function auditarEstructuraHoraria(): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $acciones = [];

        if (! $this->tablaExiste('turno')) {
            return $this->resultadoBloqueado(
                'No existe la tabla de turnos.',
                ['La tabla turno no está disponible.'],
                ['Ejecuta o revisa las migraciones académicas base.']
            );
        }

        if (! $this->tablaExiste('horario_bloque')) {
            return $this->resultadoBloqueado(
                'No existe la tabla de bloques horarios.',
                ['La tabla horario_bloque no está disponible.'],
                ['Ejecuta o revisa la migración de horarios.']
            );
        }

        if (! $this->tablaExiste('plantilla_horaria')) {
            return $this->resultadoBloqueado(
                'No existe la tabla de plantillas horarias.',
                ['La tabla plantilla_horaria no está disponible.'],
                ['Crea la tabla plantilla_horaria antes de administrar bloques por plantilla.']
            );
        }

        if (! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return $this->resultadoBloqueado(
                'Los bloques horarios todavía no tienen vínculo con plantilla.',
                ['La tabla horario_bloque no contiene el atributo cod_pho.'],
                ['Agrega el atributo cod_pho para poder asociar bloques a plantillas horarias.']
            );
        }

        $turnos = $this->turnosActivos();

        if ($turnos->isEmpty()) {
            $advertencias[] = 'No se encontraron turnos activos configurados.';
            $sugerencias[] = 'Registra al menos los turnos Mañana y Tarde antes de crear plantillas horarias.';
        }

        $diagnosticoTurnos = [];

        foreach ($turnos as $turno) {
            $codTur = (string) $turno->cod_tur;
            $nombreTurno = (string) ($turno->nom_tur ?? 'Turno');

            $plantillaRegular = $this->obtenerPlantillaRegular($codTur);
            $plantillaInvierno = $this->obtenerPlantillaInvierno($codTur);
            $bloquesSinPlantilla = $this->bloquesSinPlantillaPorTurno($codTur);
            $bloquesTotales = $this->bloquesPorTurno($codTur);
            $bloquesFueraTurno = $this->detectarBloquesFueraDelTurno($codTur);
            $plantillasSolapadas = $this->detectarPlantillasInviernoSolapadas($codTur);

            if (! $plantillaRegular) {
                $advertencias[] = "El turno {$nombreTurno} no tiene plantilla regular.";
                $sugerencias[] = "Crea una plantilla regular para el turno {$nombreTurno}.";
                $acciones[] = "Crear Plantilla Regular - {$nombreTurno}.";
            }

            if ($bloquesSinPlantilla->isNotEmpty()) {
                $advertencias[] = "El turno {$nombreTurno} tiene bloques horarios sin plantilla asociada.";
                $sugerencias[] = 'Asocia los bloques existentes a la plantilla regular correspondiente.';
                $acciones[] = "Asociar {$bloquesSinPlantilla->count()} bloque(s) del turno {$nombreTurno} a su plantilla regular.";
            }

            if (! empty($bloquesFueraTurno)) {
                $advertencias[] = "El turno {$nombreTurno} tiene bloques fuera de su rango horario.";
                $sugerencias[] = 'Revisa los bloques que sobrepasan la hora de inicio o fin del turno.';
            }

            if (! empty($plantillasSolapadas)) {
                $bloqueos[] = "El turno {$nombreTurno} tiene plantillas de invierno con fechas solapadas.";
                $sugerencias[] = 'Corrige las fechas de las plantillas de invierno para evitar reemplazos ambiguos del horario regular.';
            }

            $diagnosticoTurnos[] = [
                'turno' => $nombreTurno,
                'rango' => $this->rangoTurnoTexto($turno),
                'es_regular' => $this->esTurnoRegular($nombreTurno),
                'plantilla_regular_existe' => (bool) $plantillaRegular,
                'plantilla_invierno_existe' => (bool) $plantillaInvierno,
                'bloques_totales' => $bloquesTotales->count(),
                'bloques_sin_plantilla' => $bloquesSinPlantilla->count(),
                'bloques_fuera_turno' => count($bloquesFueraTurno),
                'plantillas_invierno_solapadas' => count($plantillasSolapadas),
                'accion_sugerida' => $this->accionSugeridaAuditoria(
                    $plantillaRegular,
                    $bloquesSinPlantilla->count(),
                    count($bloquesFueraTurno),
                    count($plantillasSolapadas)
                ),
            ];
        }

        $bloquesSinPlantillaGlobal = $this->detectarBloquesSinPlantilla();
        $plantillasSinBloques = $this->detectarPlantillasSinBloques();
        $gestion = $this->obtenerGestionTrabajo();

        if ($bloquesSinPlantillaGlobal->isNotEmpty()) {
            $advertencias[] = 'Existen bloques horarios sin plantilla asociada.';
            $sugerencias[] = 'Ejecuta la corrección segura para asociar bloques existentes a plantillas regulares.';
        }

        if (! empty($plantillasSinBloques)) {
            $advertencias[] = 'Existen plantillas horarias sin bloques configurados.';
            $sugerencias[] = 'Completa los bloques antes de usar una plantilla en horarios académicos.';
        }

        if (! $gestion) {
            $advertencias[] = 'No se detectó una gestión académica activa o planificada para validar rangos de fechas.';
            $sugerencias[] = 'Configura o activa una gestión académica antes de planificar horario regular e invierno.';
        }

        $puedeCorregir = empty($bloqueos) && (
            $bloquesSinPlantillaGlobal->isNotEmpty()
            || collect($diagnosticoTurnos)->contains(fn($item) => ! $item['plantilla_regular_existe'])
        );

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: ! empty($bloqueos)
                ? self::ESTADO_BLOQUEADO
                : ($puedeCorregir ? self::ESTADO_CORREGIBLE : (empty($advertencias) ? self::ESTADO_VALIDO : self::ESTADO_OBSERVADO)),
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: ! empty($bloqueos)
                ? 'La estructura horaria tiene inconsistencias críticas.'
                : ($puedeCorregir
                    ? 'La estructura horaria puede corregirse de forma segura.'
                    : 'La estructura horaria fue revisada.'),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'puede_corregir' => $puedeCorregir,
                'acciones_sugeridas' => array_values(array_unique($acciones)),
                'gestion_trabajo' => $gestion ? $this->mapearGestion($gestion) : null,
                'turnos' => $diagnosticoTurnos,
                'bloques_sin_plantilla' => $bloquesSinPlantillaGlobal->count(),
                'plantillas_sin_bloques' => count($plantillasSinBloques),
                'vista_previa_aplicacion' => $this->generarVistaPreviaAplicacion(),
            ]
        );
    }

    public function corregirEstructuraHoraria(): array
    {
        $auditoria = $this->auditarEstructuraHoraria();

        if (! ($auditoria['puede_continuar'] ?? false)) {
            return $auditoria;
        }

        $creadas = 0;
        $asociados = 0;
        $sinCambios = 0;
        $acciones = [];

        try {
            DB::transaction(function () use (&$creadas, &$asociados, &$sinCambios, &$acciones) {
                foreach ($this->turnosActivos() as $turno) {
                    $codTur = (string) $turno->cod_tur;
                    $nombreTurno = (string) ($turno->nom_tur ?? 'Turno');

                    $resultadoPlantilla = $this->asegurarPlantillaRegularPorTurno($codTur);

                    if ($resultadoPlantilla['creada']) {
                        $creadas++;
                        $acciones[] = "Se creó la plantilla regular para {$nombreTurno}.";
                    } else {
                        $sinCambios++;
                    }

                    $codPho = $resultadoPlantilla['cod_pho'];

                    $cantidadAsociada = $this->asociarBloquesSinPlantilla($codTur, $codPho);

                    if ($cantidadAsociada > 0) {
                        $asociados += $cantidadAsociada;
                        $acciones[] = "Se asociaron {$cantidadAsociada} bloque(s) del turno {$nombreTurno} a su plantilla regular.";
                    }
                }

                $this->registrarBitacoraSeguro(
                    accion: 'CORREGIR_ESTRUCTURA_HORARIA',
                    tabla: 'horario_bloque',
                    registro: 'ESTRUCTURA_HORARIA',
                    descripcion: 'Se corrigió la asociación de bloques horarios a plantillas regulares sin eliminar registros.'
                );
            });
        } catch (Throwable $e) {
            report($e);

            return $this->resultado(
                puedeContinuar: false,
                estado: self::ESTADO_BLOQUEADO,
                nivelRiesgo: 'ALTO',
                mensaje: 'No se pudo corregir la estructura horaria.',
                bloqueos: ['Ocurrió un error al asociar bloques con plantillas. Revisa storage/logs/laravel.log.'],
                advertencias: [],
                sugerencias: ['Verifica que existan las columnas cod_tur y cod_pho, y que no existan restricciones duplicadas incompatibles.'],
                resumen: [
                    'error' => $e->getMessage(),
                ]
            );
        }

        $postAuditoria = $this->auditarEstructuraHoraria();

        return $this->resultado(
            puedeContinuar: true,
            estado: empty($postAuditoria['bloqueos']) ? self::ESTADO_VALIDO : self::ESTADO_OBSERVADO,
            nivelRiesgo: $postAuditoria['nivel_riesgo'] ?? 'BAJO',
            mensaje: 'La estructura horaria fue corregida sin eliminación física.',
            bloqueos: $postAuditoria['bloqueos'] ?? [],
            advertencias: $postAuditoria['advertencias'] ?? [],
            sugerencias: array_merge(
                ['Revisa la vista previa de aplicación regular/invierno antes de crear horarios académicos.'],
                $postAuditoria['sugerencias'] ?? []
            ),
            resumen: [
                'plantillas_regulares_creadas' => $creadas,
                'bloques_asociados' => $asociados,
                'turnos_sin_cambios' => $sinCambios,
                'acciones_realizadas' => $acciones,
                'auditoria_actualizada' => $postAuditoria['resumen'] ?? [],
            ]
        );
    }

    public function asegurarPlantillaRegularPorTurno(string $codTur): array
    {
        $turno = $this->obtenerTurno($codTur);

        if (! $turno) {
            throw new \RuntimeException("No se encontró el turno {$codTur}.");
        }

        $existente = $this->obtenerPlantillaRegular($codTur);

        if ($existente) {
            return [
                'cod_pho' => $existente->cod_pho,
                'creada' => false,
                'plantilla' => $existente,
            ];
        }

        $codPho = $this->generarCodigo('plantilla_horaria', 'cod_pho', 'PHO');
        $nombreTurno = $this->normalizarTitulo($turno->nom_tur ?? 'Turno');

        $payload = [
            'cod_pho' => $codPho,
            'cod_tur' => $codTur,
            'nom_pho' => "Plantilla Regular - {$nombreTurno}",
            'tip_pho' => self::TIPO_REGULAR,
            'des_pho' => 'Plantilla base regular generada para organizar los bloques horarios del turno.',
            'fec_ini_pho' => null,
            'fec_fin_pho' => null,
            'dur_blo_pho' => self::DURACION_BLOQUE_REGULAR_REFERENCIAL,
            'ord_pho' => 1,
            'act_pho' => true,
            'est_pho' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('plantilla_horaria')->insert($this->filtrarColumnas('plantilla_horaria', $payload));

        return [
            'cod_pho' => $codPho,
            'creada' => true,
            'plantilla' => DB::table('plantilla_horaria')->where('cod_pho', $codPho)->first(),
        ];
    }

    public function asociarBloquesSinPlantilla(string $codTur, string $codPho): int
    {
        if (! $this->tablaExiste('horario_bloque') || ! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return 0;
        }

        $query = DB::table('horario_bloque')
            ->where('cod_tur', $codTur)
            ->where(function ($query) {
                $query->whereNull('cod_pho')
                    ->orWhere('cod_pho', '');
            });

        $cantidad = (clone $query)->count();

        if ($cantidad === 0) {
            return 0;
        }

        $query->update($this->filtrarColumnas('horario_bloque', [
            'cod_pho' => $codPho,
            'updated_at' => now(),
        ]));

        return $cantidad;
    }

    // ============================================================
    // ANÁLISIS DE TURNO
    // ============================================================

    public function analizarTurno(array $datos, ?string $codTurIgnorado = null, bool $modoTiempoReal = true): array
    {
        $datos = $this->normalizarDatosTurno($datos, normalizacionFuerte: false);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! $this->tablaExiste('turno')) {
            return $this->resultadoBloqueado(
                'No existe la tabla de turnos.',
                ['La tabla turno no está disponible.'],
                ['Verifica las migraciones académicas.']
            );
        }

        $nombre = $datos['nom_tur'] ?? null;
        $horaInicio = $datos['hor_ini_tur'] ?? null;
        $horaFin = $datos['hor_fin_tur'] ?? null;
        $estado = $this->normalizarEstadoRegistro($datos['est_tur'] ?? self::ACTIVO);

        if (! $this->tieneValor($nombre)) {
            $bloqueos[] = 'El nombre del turno es obligatorio.';
        } elseif (mb_strlen((string) $nombre) < 3) {
            $bloqueos[] = 'El nombre del turno debe tener al menos 3 caracteres.';
        }

        if ($this->tieneValor($nombre) && $this->existeTurnoDuplicado($nombre, $codTurIgnorado)) {
            $bloqueos[] = 'Ya existe un turno registrado con un nombre similar.';
            $sugerencias[] = 'Edita el turno existente en lugar de crear un duplicado.';
        }

        if ($this->tieneValor($nombre) && ! $this->esTurnoRegular($nombre)) {
            $advertencias[] = 'El turno no corresponde a la jornada regular Mañana/Tarde.';
            $sugerencias[] = 'Úsalo solo para actividades especiales, apoyo, nivelación o eventos institucionales.';
        }

        if ($this->pareceTurnoNocturno($nombre, $horaInicio, $horaFin)) {
            $advertencias[] = 'El turno parece corresponder a horario nocturno.';
            $sugerencias[] = 'No se bloquea, pero debe justificarse como actividad extraordinaria.';
        }

        if (! $this->horaValida($horaInicio)) {
            $bloqueos[] = 'La hora de inicio del turno no es válida.';
        }

        if (! $this->horaValida($horaFin)) {
            $bloqueos[] = 'La hora de finalización del turno no es válida.';
        }

        if ($this->horaValida($horaInicio) && $this->horaValida($horaFin)) {
            $duracion = $this->calcularDuracionMinutos($horaInicio, $horaFin);

            if ($duracion <= 0) {
                $bloqueos[] = 'La hora de finalización debe ser mayor a la hora de inicio.';
            }

            if ($duracion > 0 && $duracion < self::DURACION_MINIMA_TURNO) {
                $advertencias[] = 'La duración del turno es demasiado corta para una jornada académica.';
            }

            if ($duracion > self::DURACION_MAXIMA_TURNO_RECOMENDADA) {
                $advertencias[] = 'La duración del turno es extensa para una jornada académica regular.';
                $sugerencias[] = 'Verifica si el turno debería dividirse o tratarse como jornada especial.';
            }

            $solapamiento = $this->detectarSolapamientoTurno($horaInicio, $horaFin, $codTurIgnorado);

            if ($solapamiento['existe']) {
                $advertencias[] = 'El rango horario del turno se cruza con otro turno registrado.';
                $sugerencias[] = 'Revisa si la superposición es intencional o si corresponde a turnos separados.';
            }
        }

        if (! in_array($estado, [self::ACTIVO, self::INACTIVO], true)) {
            $bloqueos[] = 'El estado del turno no es válido.';
        }

        $estadoAnalisis = $this->determinarEstado($bloqueos, $advertencias, $modoTiempoReal, $datos);

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: $estadoAnalisis,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? (empty($advertencias) ? 'El turno puede registrarse.' : 'El turno puede registrarse con observaciones.')
                : 'El turno requiere corrección.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'datos_normalizados_sugeridos' => $this->normalizarDatosTurno($datos, normalizacionFuerte: true),
                'tipo_detectado' => $this->esTurnoRegular($nombre) ? 'REGULAR' : 'ESPECIAL',
                'parece_nocturno' => $this->pareceTurnoNocturno($nombre, $horaInicio, $horaFin),
                'duracion_minutos' => $this->duracionSegura($horaInicio, $horaFin),
                'gestion_trabajo' => $this->gestionTrabajoResumen(),
            ]
        );
    }

    // ============================================================
    // ANÁLISIS DE PLANTILLA HORARIA
    // ============================================================

    public function analizarPlantilla(array $datos, ?string $codPhoIgnorado = null, bool $modoTiempoReal = true): array
    {
        $datos = $this->normalizarDatosPlantilla($datos, normalizacionFuerte: false);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! $this->tablaExiste('plantilla_horaria')) {
            return $this->resultadoBloqueado(
                'No existe la tabla de plantillas horarias.',
                ['La tabla plantilla_horaria no está disponible.'],
                ['Ejecuta la migración de plantilla horaria.']
            );
        }

        $gestion = $this->obtenerGestionTrabajo();
        $codTur = $datos['cod_tur'] ?? null;
        $nombre = $datos['nom_pho'] ?? null;
        $tipo = $this->normalizarTipoPlantilla($datos['tip_pho'] ?? self::TIPO_REGULAR);
        $fechaInicio = $datos['fec_ini_pho'] ?? null;
        $fechaFin = $datos['fec_fin_pho'] ?? null;
        $duracionBase = $datos['dur_blo_pho'] ?? null;

        if (! $gestion) {
            $advertencias[] = 'No se detectó una gestión académica activa o planificada.';
            $sugerencias[] = 'Activa o planifica una gestión académica para validar rangos de fechas.';
        }

        if (! $this->tieneValor($codTur)) {
            $bloqueos[] = 'Debes seleccionar el turno al que pertenece la plantilla.';
        } elseif (! $this->obtenerTurno($codTur)) {
            $bloqueos[] = 'El turno seleccionado no existe.';
        }

        if (! $this->tieneValor($nombre)) {
            $bloqueos[] = 'El nombre de la plantilla es obligatorio.';
        }

        if (! in_array($tipo, self::TIPOS_PLANTILLA_COMPATIBLES, true)) {
            $bloqueos[] = 'El tipo de plantilla no es válido.';
        }

        if (! in_array($tipo, self::TIPOS_PLANTILLA_OPERATIVOS, true)) {
            $advertencias[] = 'El sistema está optimizado principalmente para plantilla Regular e Invierno.';
            $sugerencias[] = 'Usa Ajuste o Emergencia solo como excepción institucional.';
        }

        if ($this->tieneValor($codTur) && $this->tieneValor($nombre)) {
            if ($this->existePlantillaDuplicada($codTur, $tipo, $nombre, $codPhoIgnorado)) {
                $bloqueos[] = 'Ya existe una plantilla con el mismo turno, tipo y nombre.';
            }
        }

        if ($tipo === self::TIPO_REGULAR) {
            if ($this->tieneValor($fechaInicio) || $this->tieneValor($fechaFin)) {
                $advertencias[] = 'La plantilla regular normalmente representa la base de toda la gestión.';
                $sugerencias[] = 'Puedes dejar sus fechas vacías para interpretarla como plantilla base anual.';
            }

            if ($this->existePlantillaRegularAplicada($codTur, $codPhoIgnorado) && (bool) ($datos['act_pho'] ?? false)) {
                $advertencias[] = 'Ya existe una plantilla regular aplicada para este turno.';
                $sugerencias[] = 'Si aplicas esta plantilla, la anterior debe quedar como no aplicada.';
            }
        }

        if ($tipo === self::TIPO_INVIERNO) {
            if (! $this->tieneValor($fechaInicio) || ! $this->tieneValor($fechaFin)) {
                $bloqueos[] = 'La plantilla de invierno debe tener fecha de inicio y fecha de fin.';
                $sugerencias[] = 'Usa la sugerencia del sistema basada en el segundo trimestre y el descanso pedagógico.';
            }

            if ($this->tieneValor($codTur) && $this->tieneValor($fechaInicio) && $this->tieneValor($fechaFin)) {
                $cruces = $this->detectarCrucePlantillaInvierno($codTur, $fechaInicio, $fechaFin, $codPhoIgnorado);

                if (! empty($cruces)) {
                    $bloqueos[] = 'Existe otra plantilla de invierno con fechas solapadas para este turno.';
                    $sugerencias[] = 'Ajusta las fechas para que solo una plantilla de invierno reemplace el horario regular en ese rango.';
                }
            }

            if ($this->tieneValor($duracionBase) && (int) $duracionBase > self::DURACION_BLOQUE_REGULAR_REFERENCIAL) {
                $advertencias[] = 'La duración base de invierno es mayor al bloque regular referencial.';
                $sugerencias[] = 'El horario de invierno normalmente reduce o ajusta el tiempo de permanencia, no lo amplía.';
            }
        }

        if ($this->tieneValor($fechaInicio) || $this->tieneValor($fechaFin)) {
            $analisisFechas = $this->validarPlantillaContraGestion($datos, $gestion);

            $bloqueos = array_merge($bloqueos, $analisisFechas['bloqueos']);
            $advertencias = array_merge($advertencias, $analisisFechas['advertencias']);
            $sugerencias = array_merge($sugerencias, $analisisFechas['sugerencias']);
        }

        if ($this->tieneValor($duracionBase)) {
            $duracionBase = (int) $duracionBase;

            if ($duracionBase < self::DURACION_MINIMA_BLOQUE) {
                $bloqueos[] = 'La duración base del bloque es demasiado corta.';
            }

            if ($duracionBase > self::DURACION_MAXIMA_BLOQUE) {
                $bloqueos[] = 'La duración base del bloque supera el máximo permitido.';
            }

            if ($tipo === self::TIPO_REGULAR && $duracionBase !== self::DURACION_BLOQUE_REGULAR_REFERENCIAL) {
                $advertencias[] = 'La plantilla regular suele trabajar con bloques cercanos a 45 minutos.';
            }

            if ($tipo === self::TIPO_INVIERNO && $duracionBase !== self::DURACION_BLOQUE_INVIERNO_REFERENCIAL) {
                $advertencias[] = 'La plantilla de invierno suele trabajar con bloques cercanos a 30 minutos.';
            }
        }

        $estadoAnalisis = $this->determinarEstado($bloqueos, $advertencias, $modoTiempoReal, $datos);

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: $estadoAnalisis,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? (empty($advertencias) ? 'La plantilla horaria puede registrarse.' : 'La plantilla puede registrarse con observaciones.')
                : 'La plantilla horaria requiere corrección.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'datos_normalizados_sugeridos' => $this->normalizarDatosPlantilla($datos, normalizacionFuerte: true),
                'gestion_trabajo' => $gestion ? $this->mapearGestion($gestion) : null,
                'rango_invierno_sugerido' => $this->sugerirRangoInvierno($gestion),
                'vista_previa_aplicacion' => $this->generarVistaPreviaAplicacionConFormulario($datos),
                'tipo_operativo' => in_array($tipo, self::TIPOS_PLANTILLA_OPERATIVOS, true),
            ]
        );
    }

    public function validarPlantillaContraGestion(array $datos, ?object $gestion = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $gestion = $gestion ?: $this->obtenerGestionTrabajo();

        if (! $gestion) {
            return compact('bloqueos', 'advertencias', 'sugerencias');
        }

        $inicioGestion = $this->fechaDesdeGestion($gestion, 'inicio');
        $finGestion = $this->fechaDesdeGestion($gestion, 'fin');

        $inicio = $datos['fec_ini_pho'] ?? null;
        $fin = $datos['fec_fin_pho'] ?? null;

        if (! $this->fechaValida($inicio)) {
            $bloqueos[] = 'La fecha de inicio de la plantilla no es válida.';
        }

        if (! $this->fechaValida($fin)) {
            $bloqueos[] = 'La fecha de fin de la plantilla no es válida.';
        }

        if (! empty($bloqueos)) {
            return compact('bloqueos', 'advertencias', 'sugerencias');
        }

        $fechaInicio = Carbon::parse($inicio)->startOfDay();
        $fechaFin = Carbon::parse($fin)->startOfDay();

        if ($fechaFin->lessThan($fechaInicio)) {
            $bloqueos[] = 'La fecha final de la plantilla no puede ser anterior a la fecha inicial.';
        }

        if ($inicioGestion && $fechaInicio->lessThan($inicioGestion)) {
            $bloqueos[] = 'La plantilla no puede iniciar antes del inicio de la gestión académica.';
            $sugerencias[] = 'Ajusta la fecha de inicio al rango de la gestión activa o planificada.';
        }

        if ($finGestion && $fechaFin->greaterThan($finGestion)) {
            $bloqueos[] = 'La plantilla no puede finalizar después del cierre de la gestión académica.';
            $sugerencias[] = 'Ajusta la fecha de fin al rango oficial de la gestión.';
        }

        if ($inicioGestion && $finGestion) {
            $duracionGestion = $inicioGestion->diffInDays($finGestion) + 1;
            $duracionPlantilla = $fechaInicio->diffInDays($fechaFin) + 1;

            if ($duracionPlantilla > $duracionGestion) {
                $bloqueos[] = 'La plantilla no puede durar más que la gestión académica.';
            }

            if (($datos['tip_pho'] ?? null) === self::TIPO_INVIERNO && $duracionPlantilla > 90) {
                $advertencias[] = 'La plantilla de invierno tiene una duración extensa.';
                $sugerencias[] = 'Verifica si el rango corresponde realmente a horario de invierno o a otro tipo de ajuste.';
            }
        }

        return compact('bloqueos', 'advertencias', 'sugerencias');
    }

    // ============================================================
    // ANÁLISIS DE BLOQUE HORARIO
    // ============================================================

    public function analizarBloque(array $datos, ?string $codHblIgnorado = null, bool $modoTiempoReal = true): array
    {
        $datos = $this->normalizarDatosBloque($datos, normalizacionFuerte: false);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! $this->tablaExiste('horario_bloque')) {
            return $this->resultadoBloqueado(
                'No existe la tabla de bloques horarios.',
                ['La tabla horario_bloque no está disponible.'],
                ['Verifica la migración de horarios.']
            );
        }

        $codTur = $datos['cod_tur'] ?? null;
        $codPho = $datos['cod_pho'] ?? null;
        $numero = $datos['num_hbl'] ?? null;
        $nombre = $datos['nom_hbl'] ?? null;
        $tipo = $this->normalizarTipoBloque($datos['tip_hbl'] ?? 'CLASE');
        $horaInicio = $datos['hor_ini_hbl'] ?? null;
        $horaFin = $datos['hor_fin_hbl'] ?? null;

        if (! $this->tieneValor($codTur)) {
            $bloqueos[] = 'Debes seleccionar el turno del bloque.';
        } elseif (! $this->obtenerTurno($codTur)) {
            $bloqueos[] = 'El turno seleccionado no existe.';
        }

        if (Schema::hasColumn('horario_bloque', 'cod_pho')) {
            if (! $this->tieneValor($codPho)) {
                $bloqueos[] = 'Debes seleccionar la plantilla horaria del bloque.';
                $sugerencias[] = 'Los bloques ya no deben quedar sueltos por turno; deben pertenecer a una plantilla regular o de invierno.';
            } elseif (! $this->obtenerPlantilla($codPho)) {
                $bloqueos[] = 'La plantilla horaria seleccionada no existe.';
            }
        }

        if ($this->tieneValor($codPho)) {
            $plantilla = $this->obtenerPlantilla($codPho);

            if ($plantilla && $this->tieneValor($codTur) && $plantilla->cod_tur !== $codTur) {
                $bloqueos[] = 'El turno del bloque no coincide con el turno de la plantilla seleccionada.';
            }
        }

        if (! $this->tieneValor($numero)) {
            $bloqueos[] = 'El número de bloque es obligatorio.';
        } elseif ((int) $numero <= 0) {
            $bloqueos[] = 'El número de bloque debe ser mayor a cero.';
        }

        if (! $this->tieneValor($nombre)) {
            $advertencias[] = 'El bloque no tiene un nombre visible.';
            $sugerencias[] = 'Usa nombres como 1er bloque, 2do bloque, Recreo o Salida.';
        }

        if (! in_array($tipo, self::TIPOS_BLOQUE, true)) {
            $bloqueos[] = 'El tipo de bloque no es válido.';
        }

        if (! $this->horaValida($horaInicio)) {
            $bloqueos[] = 'La hora de inicio del bloque no es válida.';
        }

        if (! $this->horaValida($horaFin)) {
            $bloqueos[] = 'La hora de finalización del bloque no es válida.';
        }

        $duracion = null;

        if ($this->horaValida($horaInicio) && $this->horaValida($horaFin)) {
            $duracion = $this->calcularDuracionMinutos($horaInicio, $horaFin);

            if ($duracion <= 0) {
                $bloqueos[] = 'La hora de finalización del bloque debe ser mayor a la hora de inicio.';
            }

            if ($duracion > 0 && $duracion < self::DURACION_MINIMA_BLOQUE) {
                $bloqueos[] = 'La duración del bloque es demasiado corta.';
            }

            if ($duracion > self::DURACION_MAXIMA_BLOQUE) {
                $bloqueos[] = 'La duración del bloque supera el máximo permitido.';
            }

            $turno = $this->obtenerTurno($codTur);

            if ($turno && ! $this->bloqueDentroDelTurno($horaInicio, $horaFin, $turno)) {
                $bloqueos[] = 'El bloque está fuera del rango horario del turno.';
                $sugerencias[] = 'Ajusta el bloque para que no sobrepase la jornada definida del turno.';
            }

            if ($this->tieneValor($codPho)) {
                $solapamiento = $this->detectarSolapamientoBloque($codPho, $horaInicio, $horaFin, $codHblIgnorado);

                if ($solapamiento['existe']) {
                    $bloqueos[] = 'El bloque se solapa con otro bloque de la misma plantilla.';
                    $sugerencias[] = 'Los bloques pueden ser consecutivos, pero no deben cruzarse.';
                }

                if ($this->existeNumeroBloqueDuplicado($codPho, (int) $numero, $codHblIgnorado)) {
                    $bloqueos[] = 'Ya existe un bloque con ese número dentro de la plantilla.';
                }

                $plantilla = $this->obtenerPlantilla($codPho);

                if ($plantilla && $plantilla->dur_blo_pho && ! in_array($tipo, ['RECREO', 'DESCANSO', 'SALIDA'], true)) {
                    $duracionBase = (int) $plantilla->dur_blo_pho;

                    if ($duracion !== $duracionBase) {
                        $advertencias[] = "La duración del bloque ({$duracion} min) no coincide con la duración base de la plantilla ({$duracionBase} min).";
                        $sugerencias[] = 'Si corresponde a clase regular, ajusta la duración. Si es recreo o salida, usa el tipo de bloque adecuado.';
                    }
                }
            }
        }

        if ($codHblIgnorado && $this->bloqueTieneUso($codHblIgnorado)) {
            $advertencias[] = 'Este bloque ya está usado en horarios académicos.';
            $sugerencias[] = 'Si necesitas cambios fuertes, crea una nueva plantilla para conservar trazabilidad.';
        }

        $estadoAnalisis = $this->determinarEstado($bloqueos, $advertencias, $modoTiempoReal, $datos);

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: $estadoAnalisis,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? (empty($advertencias) ? 'El bloque horario puede registrarse.' : 'El bloque puede registrarse con observaciones.')
                : 'El bloque horario requiere corrección.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'datos_normalizados_sugeridos' => $this->normalizarDatosBloque($datos, normalizacionFuerte: true),
                'duracion_minutos' => $duracion,
                'tiene_uso' => $codHblIgnorado ? $this->bloqueTieneUso($codHblIgnorado) : false,
                'plantilla' => $this->tieneValor($codPho) ? $this->mapearPlantilla($this->obtenerPlantilla($codPho)) : null,
            ]
        );
    }

    public function validarBloquesDePlantilla(string $codPho): array
    {
        $plantilla = $this->obtenerPlantilla($codPho);

        if (! $plantilla) {
            return $this->resultadoBloqueado(
                'La plantilla seleccionada no existe.',
                ['No se encontró la plantilla horaria.'],
                ['Selecciona una plantilla válida.']
            );
        }

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $bloques = $this->bloquesPorPlantilla($codPho);

        if ($bloques->isEmpty()) {
            $advertencias[] = 'La plantilla no tiene bloques configurados.';
            $sugerencias[] = 'Crea bloques antes de usar la plantilla para horarios académicos.';
        }

        foreach ($bloques as $bloque) {
            $analisis = $this->analizarBloque([
                'cod_hbl' => $bloque->cod_hbl ?? null,
                'cod_tur' => $bloque->cod_tur ?? null,
                'cod_pho' => $bloque->cod_pho ?? null,
                'num_hbl' => $bloque->num_hbl ?? null,
                'hor_ini_hbl' => $this->normalizarHora($bloque->hor_ini_hbl ?? null),
                'hor_fin_hbl' => $this->normalizarHora($bloque->hor_fin_hbl ?? null),
                'nom_hbl' => $bloque->nom_hbl ?? null,
                'tip_hbl' => $bloque->tip_hbl ?? 'CLASE',
                'obs_hbl' => $bloque->obs_hbl ?? null,
                'est_hbl' => $bloque->est_hbl ?? self::ACTIVO,
            ], $bloque->cod_hbl ?? null, false);

            $bloqueos = array_merge($bloqueos, $analisis['bloqueos'] ?? []);
            $advertencias = array_merge($advertencias, $analisis['advertencias'] ?? []);
            $sugerencias = array_merge($sugerencias, $analisis['sugerencias'] ?? []);
        }

        $solapamientos = $this->detectarSolapamientosPorPlantilla($codPho);

        if (! empty($solapamientos)) {
            $bloqueos[] = 'La plantilla tiene bloques solapados.';
            $sugerencias[] = 'Corrige los rangos horarios para que cada bloque ocupe un tramo único.';
        }

        $estado = empty($bloqueos)
            ? (empty($advertencias) ? self::ESTADO_VALIDO : self::ESTADO_OBSERVADO)
            : self::ESTADO_BLOQUEADO;

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: $estado,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La estructura de bloques puede utilizarse.'
                : 'La estructura de bloques tiene errores.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'plantilla' => $this->mapearPlantilla($plantilla),
                'cantidad_bloques' => $bloques->count(),
                'solapamientos' => $solapamientos,
                'cobertura_turno' => $this->calcularCoberturaTurnoPorPlantilla($codPho),
            ]
        );
    }

    // ============================================================
    // GESTIÓN ACADÉMICA Y SUGERENCIAS DE INVIERNO
    // ============================================================

    public function obtenerGestionTrabajo(): ?object
    {
        if (! $this->tablaExiste('gestion_academica')) {
            return null;
        }

        $activa = DB::table('gestion_academica')
            ->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])
            ->orderByDesc('ani_gea')
            ->first();

        if ($activa) {
            return $activa;
        }

        return DB::table('gestion_academica')
            ->whereIn('est_gea', ['PLANIFICADA', 'PLANIFICADO'])
            ->orderByDesc('ani_gea')
            ->first();
    }

    public function sugerirRangoInvierno(?object $gestion = null): array
    {
        $gestion = $gestion ?: $this->obtenerGestionTrabajo();

        if (! $gestion) {
            $anio = now()->year;

            return [
                'disponible' => false,
                'anio' => $anio,
                'fecha_inicio' => "{$anio}-06-15",
                'fecha_fin' => "{$anio}-07-25",
                'mensaje' => 'No se detectó gestión académica. Se muestra una referencia general entre junio y julio.',
            ];
        }

        $anio = (int) ($gestion->ani_gea ?? now()->year);
        $inicioGestion = $this->fechaDesdeGestion($gestion, 'inicio');
        $finGestion = $this->fechaDesdeGestion($gestion, 'fin');

        $inicioSugerido = Carbon::create($anio, 6, 15)->startOfDay();
        $finSugerido = Carbon::create($anio, 7, 25)->startOfDay();

        if ($inicioGestion && $inicioSugerido->lessThan($inicioGestion)) {
            $inicioSugerido = $inicioGestion->copy();
        }

        if ($finGestion && $finSugerido->greaterThan($finGestion)) {
            $finSugerido = $finGestion->copy();
        }

        return [
            'disponible' => true,
            'anio' => $anio,
            'fecha_inicio' => $inicioSugerido->format('Y-m-d'),
            'fecha_fin' => $finSugerido->format('Y-m-d'),
            'gestion' => $this->mapearGestion($gestion),
            'referencia' => 'Segundo trimestre y descanso pedagógico de invierno.',
            'mensaje' => 'Rango sugerido para configurar horario de invierno. Puede ajustarse según disposición institucional o departamental.',
        ];
    }

    public function generarVistaPreviaAplicacion(?string $codTur = null): array
    {
        $gestion = $this->obtenerGestionTrabajo();

        if (! $gestion) {
            return [
                'disponible' => false,
                'mensaje' => 'No existe gestión académica activa o planificada para construir la vista previa.',
                'segmentos' => [],
            ];
        }

        $inicioGestion = $this->fechaDesdeGestion($gestion, 'inicio');
        $finGestion = $this->fechaDesdeGestion($gestion, 'fin');

        if (! $inicioGestion || ! $finGestion) {
            return [
                'disponible' => false,
                'mensaje' => 'La gestión académica no tiene rango de fechas completo.',
                'segmentos' => [],
            ];
        }

        $turnos = $codTur
            ? collect([$this->obtenerTurno($codTur)])->filter()
            : $this->turnosActivos();

        $segmentos = [];

        foreach ($turnos as $turno) {
            $plantillaRegular = $this->obtenerPlantillaRegular($turno->cod_tur);
            $plantillasInvierno = $this->plantillasInviernoPorTurno($turno->cod_tur)
                ->filter(fn($plantilla) => $this->fechaValida($plantilla->fec_ini_pho ?? null) && $this->fechaValida($plantilla->fec_fin_pho ?? null))
                ->sortBy('fec_ini_pho')
                ->values();

            if ($plantillasInvierno->isEmpty()) {
                $segmentos[] = [
                    'turno' => $turno->nom_tur ?? 'Turno',
                    'tipo' => self::TIPO_REGULAR,
                    'plantilla' => $plantillaRegular->nom_pho ?? 'Plantilla Regular',
                    'fecha_inicio' => $inicioGestion->format('Y-m-d'),
                    'fecha_fin' => $finGestion->format('Y-m-d'),
                    'mensaje' => 'La plantilla regular cubre toda la gestión.',
                ];

                continue;
            }

            $cursor = $inicioGestion->copy();

            foreach ($plantillasInvierno as $invierno) {
                $inicioInvierno = Carbon::parse($invierno->fec_ini_pho)->startOfDay();
                $finInvierno = Carbon::parse($invierno->fec_fin_pho)->startOfDay();

                if ($cursor->lessThan($inicioInvierno)) {
                    $segmentos[] = [
                        'turno' => $turno->nom_tur ?? 'Turno',
                        'tipo' => self::TIPO_REGULAR,
                        'plantilla' => $plantillaRegular->nom_pho ?? 'Plantilla Regular',
                        'fecha_inicio' => $cursor->format('Y-m-d'),
                        'fecha_fin' => $inicioInvierno->copy()->subDay()->format('Y-m-d'),
                        'mensaje' => 'Horario regular vigente.',
                    ];
                }

                $segmentos[] = [
                    'turno' => $turno->nom_tur ?? 'Turno',
                    'tipo' => self::TIPO_INVIERNO,
                    'plantilla' => $invierno->nom_pho ?? 'Plantilla Invierno',
                    'fecha_inicio' => $inicioInvierno->format('Y-m-d'),
                    'fecha_fin' => $finInvierno->format('Y-m-d'),
                    'mensaje' => 'Horario de invierno reemplaza temporalmente al regular.',
                ];

                $cursor = $finInvierno->copy()->addDay();
            }

            if ($cursor->lessThanOrEqualTo($finGestion)) {
                $segmentos[] = [
                    'turno' => $turno->nom_tur ?? 'Turno',
                    'tipo' => self::TIPO_REGULAR,
                    'plantilla' => $plantillaRegular->nom_pho ?? 'Plantilla Regular',
                    'fecha_inicio' => $cursor->format('Y-m-d'),
                    'fecha_fin' => $finGestion->format('Y-m-d'),
                    'mensaje' => 'Horario regular retoma vigencia.',
                ];
            }
        }

        return [
            'disponible' => true,
            'gestion' => $this->mapearGestion($gestion),
            'segmentos' => $segmentos,
        ];
    }

    public function generarVistaPreviaAplicacionConFormulario(array $datos): array
    {
        $vista = $this->generarVistaPreviaAplicacion($datos['cod_tur'] ?? null);

        if (! ($vista['disponible'] ?? false)) {
            return $vista;
        }

        if (($datos['tip_pho'] ?? null) !== self::TIPO_INVIERNO) {
            return $vista;
        }

        if (! $this->fechaValida($datos['fec_ini_pho'] ?? null) || ! $this->fechaValida($datos['fec_fin_pho'] ?? null)) {
            return $vista;
        }

        $turno = $this->obtenerTurno($datos['cod_tur'] ?? null);

        if (! $turno) {
            return $vista;
        }

        $vista['simulacion_formulario'] = [
            'turno' => $turno->nom_tur ?? 'Turno',
            'tipo' => self::TIPO_INVIERNO,
            'plantilla' => $datos['nom_pho'] ?? 'Plantilla Invierno',
            'fecha_inicio' => Carbon::parse($datos['fec_ini_pho'])->format('Y-m-d'),
            'fecha_fin' => Carbon::parse($datos['fec_fin_pho'])->format('Y-m-d'),
            'mensaje' => 'Simulación del rango de invierno que se está editando.',
        ];

        return $vista;
    }

    // ============================================================
    // DESACTIVACIÓN / USO
    // ============================================================

    public function puedeDesactivarTurno(string $codTur): array
    {
        $turno = $this->obtenerTurno($codTur);

        if (! $turno) {
            return $this->resultadoBloqueado(
                'El turno seleccionado no existe.',
                ['No se encontró el turno.'],
                ['Selecciona un turno válido.']
            );
        }

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $uso = $this->usoTurno($codTur);

        if (($uso['horarios'] ?? 0) > 0) {
            $bloqueos[] = 'El turno tiene horarios académicos registrados.';
            $sugerencias[] = 'No lo elimines ni lo desactives sin revisar la planificación académica.';
        }

        if (($uso['plantillas'] ?? 0) > 0) {
            $advertencias[] = 'El turno tiene plantillas horarias asociadas.';
        }

        if (($uso['bloques'] ?? 0) > 0) {
            $advertencias[] = 'El turno tiene bloques horarios configurados.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: empty($bloqueos) ? (empty($advertencias) ? self::ESTADO_VALIDO : self::ESTADO_OBSERVADO) : self::ESTADO_BLOQUEADO,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'El turno puede desactivarse conservando trazabilidad.'
                : 'No se recomienda desactivar el turno por uso académico.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'turno' => $turno->nom_tur ?? 'Turno',
                'uso' => $uso,
            ]
        );
    }

    public function puedeDesactivarPlantilla(string $codPho): array
    {
        $plantilla = $this->obtenerPlantilla($codPho);

        if (! $plantilla) {
            return $this->resultadoBloqueado(
                'La plantilla seleccionada no existe.',
                ['No se encontró la plantilla horaria.'],
                ['Selecciona una plantilla válida.']
            );
        }

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $uso = $this->usoPlantilla($codPho);

        if (($uso['detalles_horario'] ?? 0) > 0) {
            $bloqueos[] = 'La plantilla tiene bloques usados en horarios académicos.';
            $sugerencias[] = 'Crea una nueva plantilla si necesitas reorganizar bloques sin afectar el historial.';
        }

        if (($uso['bloques'] ?? 0) > 0) {
            $advertencias[] = 'La plantilla tiene bloques configurados.';
        }

        if ((bool) ($plantilla->act_pho ?? false)) {
            $advertencias[] = 'La plantilla está marcada como aplicada actualmente.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: empty($bloqueos) ? (empty($advertencias) ? self::ESTADO_VALIDO : self::ESTADO_OBSERVADO) : self::ESTADO_BLOQUEADO,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: empty($bloqueos)
                ? 'La plantilla puede desactivarse conservando trazabilidad.'
                : 'No se recomienda desactivar la plantilla porque tiene uso académico.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'plantilla' => $this->mapearPlantilla($plantilla),
                'uso' => $uso,
            ]
        );
    }

    public function puedeDesactivarBloque(string $codHbl): array
    {
        $bloque = $this->obtenerBloque($codHbl);

        if (! $bloque) {
            return $this->resultadoBloqueado(
                'El bloque seleccionado no existe.',
                ['No se encontró el bloque horario.'],
                ['Selecciona un bloque válido.']
            );
        }

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if ($this->bloqueTieneUso($codHbl)) {
            $advertencias[] = 'El bloque ya está usado en horarios académicos.';
            $sugerencias[] = 'Se permite desactivar, pero no eliminar físicamente.';
            $sugerencias[] = 'Si requieres cambios fuertes, crea una nueva plantilla.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estado: empty($advertencias) ? self::ESTADO_VALIDO : self::ESTADO_OBSERVADO,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias),
            mensaje: 'El bloque puede desactivarse conservando trazabilidad.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            resumen: [
                'bloque' => $this->mapearBloque($bloque),
                'tiene_uso' => $this->bloqueTieneUso($codHbl),
            ]
        );
    }

    // ============================================================
    // DETECTORES
    // ============================================================

    public function detectarBloquesSinPlantilla(): Collection
    {
        if (! $this->tablaExiste('horario_bloque') || ! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return collect();
        }

        return DB::table('horario_bloque')
            ->where(function ($query) {
                $query->whereNull('cod_pho')
                    ->orWhere('cod_pho', '');
            })
            ->orderBy('cod_tur')
            ->orderBy('num_hbl')
            ->get();
    }

    public function detectarBloquesFueraDelTurno(string $codTur): array
    {
        $turno = $this->obtenerTurno($codTur);

        if (! $turno) {
            return [];
        }

        return $this->bloquesPorTurno($codTur)
            ->filter(function ($bloque) use ($turno) {
                return ! $this->bloqueDentroDelTurno(
                    $bloque->hor_ini_hbl ?? null,
                    $bloque->hor_fin_hbl ?? null,
                    $turno
                );
            })
            ->map(fn($bloque) => $this->mapearBloque($bloque))
            ->values()
            ->all();
    }

    public function detectarSolapamientosPorPlantilla(string $codPho): array
    {
        $bloques = $this->bloquesPorPlantilla($codPho)
            ->filter(fn($bloque) => $this->horaValida($bloque->hor_ini_hbl ?? null) && $this->horaValida($bloque->hor_fin_hbl ?? null))
            ->sortBy('hor_ini_hbl')
            ->values();

        $solapamientos = [];

        for ($i = 0; $i < $bloques->count(); $i++) {
            for ($j = $i + 1; $j < $bloques->count(); $j++) {
                $actual = $bloques[$i];
                $comparado = $bloques[$j];

                $inicioActual = $this->minutosDesdeMedianoche($actual->hor_ini_hbl);
                $finActual = $this->minutosDesdeMedianoche($actual->hor_fin_hbl);
                $inicioComparado = $this->minutosDesdeMedianoche($comparado->hor_ini_hbl);
                $finComparado = $this->minutosDesdeMedianoche($comparado->hor_fin_hbl);

                if ($inicioActual < $finComparado && $finActual > $inicioComparado) {
                    $solapamientos[] = [
                        'bloque_a' => $this->mapearBloque($actual),
                        'bloque_b' => $this->mapearBloque($comparado),
                    ];
                }
            }
        }

        return $solapamientos;
    }

    public function detectarSolapamientoBloque(
        string $codPho,
        ?string $horaInicio,
        ?string $horaFin,
        ?string $codHblIgnorado = null
    ): array {
        if (! $this->horaValida($horaInicio) || ! $this->horaValida($horaFin)) {
            return [
                'existe' => false,
                'bloques' => [],
            ];
        }

        $inicio = $this->minutosDesdeMedianoche($horaInicio);
        $fin = $this->minutosDesdeMedianoche($horaFin);

        $bloques = $this->bloquesPorPlantilla($codPho)
            ->when($codHblIgnorado, fn($collection) => $collection->where('cod_hbl', '!=', $codHblIgnorado))
            ->filter(function ($bloque) use ($inicio, $fin) {
                if (! $this->horaValida($bloque->hor_ini_hbl ?? null) || ! $this->horaValida($bloque->hor_fin_hbl ?? null)) {
                    return false;
                }

                $inicioExistente = $this->minutosDesdeMedianoche($bloque->hor_ini_hbl);
                $finExistente = $this->minutosDesdeMedianoche($bloque->hor_fin_hbl);

                return $inicio < $finExistente && $fin > $inicioExistente;
            })
            ->map(fn($bloque) => $this->mapearBloque($bloque))
            ->values()
            ->all();

        return [
            'existe' => ! empty($bloques),
            'bloques' => $bloques,
        ];
    }

    public function detectarSolapamientoTurno(?string $horaInicio, ?string $horaFin, ?string $codTurIgnorado = null): array
    {
        if (! $this->horaValida($horaInicio) || ! $this->horaValida($horaFin)) {
            return [
                'existe' => false,
                'turnos' => [],
            ];
        }

        $inicio = $this->minutosDesdeMedianoche($horaInicio);
        $fin = $this->minutosDesdeMedianoche($horaFin);

        $turnos = DB::table('turno')
            ->when($codTurIgnorado, fn($query) => $query->where('cod_tur', '!=', $codTurIgnorado))
            ->get()
            ->filter(function ($turno) use ($inicio, $fin) {
                if (! $this->horaValida($turno->hor_ini_tur ?? null) || ! $this->horaValida($turno->hor_fin_tur ?? null)) {
                    return false;
                }

                $inicioExistente = $this->minutosDesdeMedianoche($turno->hor_ini_tur);
                $finExistente = $this->minutosDesdeMedianoche($turno->hor_fin_tur);

                return $inicio < $finExistente && $fin > $inicioExistente;
            })
            ->map(fn($turno) => [
                'turno' => $turno->nom_tur ?? 'Turno',
                'rango' => $this->rangoTurnoTexto($turno),
            ])
            ->values()
            ->all();

        return [
            'existe' => ! empty($turnos),
            'turnos' => $turnos,
        ];
    }

    public function detectarPlantillasInviernoSolapadas(string $codTur): array
    {
        $plantillas = $this->plantillasInviernoPorTurno($codTur)
            ->filter(fn($p) => $this->fechaValida($p->fec_ini_pho ?? null) && $this->fechaValida($p->fec_fin_pho ?? null))
            ->sortBy('fec_ini_pho')
            ->values();

        $solapadas = [];

        for ($i = 0; $i < $plantillas->count(); $i++) {
            for ($j = $i + 1; $j < $plantillas->count(); $j++) {
                $a = $plantillas[$i];
                $b = $plantillas[$j];

                $inicioA = Carbon::parse($a->fec_ini_pho)->startOfDay();
                $finA = Carbon::parse($a->fec_fin_pho)->startOfDay();
                $inicioB = Carbon::parse($b->fec_ini_pho)->startOfDay();
                $finB = Carbon::parse($b->fec_fin_pho)->startOfDay();

                if ($inicioA->lessThanOrEqualTo($finB) && $finA->greaterThanOrEqualTo($inicioB)) {
                    $solapadas[] = [
                        'plantilla_a' => $this->mapearPlantilla($a),
                        'plantilla_b' => $this->mapearPlantilla($b),
                    ];
                }
            }
        }

        return $solapadas;
    }

    public function detectarCrucePlantillaInvierno(
        string $codTur,
        ?string $fechaInicio,
        ?string $fechaFin,
        ?string $codPhoIgnorado = null
    ): array {
        if (! $this->fechaValida($fechaInicio) || ! $this->fechaValida($fechaFin)) {
            return [];
        }

        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();

        return $this->plantillasInviernoPorTurno($codTur)
            ->when($codPhoIgnorado, fn($collection) => $collection->where('cod_pho', '!=', $codPhoIgnorado))
            ->filter(function ($plantilla) use ($inicio, $fin) {
                if (! $this->fechaValida($plantilla->fec_ini_pho ?? null) || ! $this->fechaValida($plantilla->fec_fin_pho ?? null)) {
                    return false;
                }

                $inicioExistente = Carbon::parse($plantilla->fec_ini_pho)->startOfDay();
                $finExistente = Carbon::parse($plantilla->fec_fin_pho)->startOfDay();

                return $inicio->lessThanOrEqualTo($finExistente) && $fin->greaterThanOrEqualTo($inicioExistente);
            })
            ->map(fn($plantilla) => $this->mapearPlantilla($plantilla))
            ->values()
            ->all();
    }

    public function detectarPlantillasSinBloques(): array
    {
        if (! $this->tablaExiste('plantilla_horaria') || ! $this->tablaExiste('horario_bloque')) {
            return [];
        }

        return DB::table('plantilla_horaria')
            ->whereNotExists(function ($query) {
                $query->from('horario_bloque')
                    ->whereColumn('horario_bloque.cod_pho', 'plantilla_horaria.cod_pho');
            })
            ->orderBy('cod_tur')
            ->orderBy('ord_pho')
            ->get()
            ->map(fn($plantilla) => $this->mapearPlantilla($plantilla))
            ->values()
            ->all();
    }

    // ============================================================
    // NORMALIZACIÓN PÚBLICA
    // ============================================================

    public function normalizarDatosTurno(array $datos, bool $normalizacionFuerte = false): array
    {
        return [
            'cod_tur' => $this->limpiarTexto($datos['cod_tur'] ?? null),
            'nom_tur' => $normalizacionFuerte
                ? $this->normalizarNombreTurno($datos['nom_tur'] ?? null)
                : $this->limpiarTexto($datos['nom_tur'] ?? null),
            'hor_ini_tur' => $this->normalizarHora($datos['hor_ini_tur'] ?? null),
            'hor_fin_tur' => $this->normalizarHora($datos['hor_fin_tur'] ?? null),
            'est_tur' => $this->normalizarEstadoRegistro($datos['est_tur'] ?? self::ACTIVO),
        ];
    }

    public function normalizarDatosPlantilla(array $datos, bool $normalizacionFuerte = false): array
    {
        $tipo = $this->normalizarTipoPlantilla($datos['tip_pho'] ?? self::TIPO_REGULAR);

        return [
            'cod_pho' => $this->limpiarTexto($datos['cod_pho'] ?? null),
            'cod_tur' => $this->limpiarTexto($datos['cod_tur'] ?? null),
            'nom_pho' => $normalizacionFuerte
                ? $this->normalizarTitulo($datos['nom_pho'] ?? null)
                : $this->limpiarTexto($datos['nom_pho'] ?? null),
            'tip_pho' => $tipo,
            'des_pho' => $this->limpiarTexto($datos['des_pho'] ?? null),
            'fec_ini_pho' => $this->normalizarFecha($datos['fec_ini_pho'] ?? null),
            'fec_fin_pho' => $this->normalizarFecha($datos['fec_fin_pho'] ?? null),
            'dur_blo_pho' => $this->normalizarEnteroNullable($datos['dur_blo_pho'] ?? $this->duracionSugeridaPorTipo($tipo)),
            'ord_pho' => $this->normalizarEnteroNullable($datos['ord_pho'] ?? 1) ?: 1,
            'act_pho' => (bool) ($datos['act_pho'] ?? false),
            'est_pho' => $this->normalizarEstadoPlantilla($datos['est_pho'] ?? true),
        ];
    }

    public function normalizarDatosBloque(array $datos, bool $normalizacionFuerte = false): array
    {
        return [
            'cod_hbl' => $this->limpiarTexto($datos['cod_hbl'] ?? null),
            'cod_tur' => $this->limpiarTexto($datos['cod_tur'] ?? null),
            'cod_pho' => $this->limpiarTexto($datos['cod_pho'] ?? null),
            'num_hbl' => $this->normalizarEnteroNullable($datos['num_hbl'] ?? null),
            'hor_ini_hbl' => $this->normalizarHora($datos['hor_ini_hbl'] ?? null),
            'hor_fin_hbl' => $this->normalizarHora($datos['hor_fin_hbl'] ?? null),
            'nom_hbl' => $normalizacionFuerte
                ? $this->normalizarTitulo($datos['nom_hbl'] ?? null)
                : $this->limpiarTexto($datos['nom_hbl'] ?? null),
            'tip_hbl' => $this->normalizarTipoBloque($datos['tip_hbl'] ?? 'CLASE'),
            'obs_hbl' => $this->limpiarTexto($datos['obs_hbl'] ?? null),
            'est_hbl' => $this->normalizarEstadoRegistro($datos['est_hbl'] ?? self::ACTIVO),
        ];
    }

    public function normalizarNombreTurno(?string $nombre): ?string
    {
        $nombre = $this->limpiarTexto($nombre);

        if (! $nombre) {
            return null;
        }

        $canonico = $this->normalizarCanonico($nombre);

        return match ($canonico) {
            'MANANA', 'MAÑANA', 'TURNO MANANA', 'TURNO MAÑANA' => 'Mañana',
            'TARDE', 'TURNO TARDE' => 'Tarde',
            'NOCHE', 'NOCTURNO', 'TURNO NOCHE', 'TURNO NOCTURNO' => 'Noche',
            'ESPECIAL', 'CONTRATURNO', 'APOYO' => 'Especial',
            default => $this->normalizarTitulo($nombre),
        };
    }

    public function normalizarTipoPlantilla(?string $tipo): string
    {
        $tipo = $this->normalizarMayuscula($tipo) ?: self::TIPO_REGULAR;

        return in_array($tipo, self::TIPOS_PLANTILLA_COMPATIBLES, true)
            ? $tipo
            : self::TIPO_REGULAR;
    }

    public function normalizarTipoBloque(?string $tipo): string
    {
        $tipo = $this->normalizarMayuscula($tipo) ?: 'CLASE';

        return in_array($tipo, self::TIPOS_BLOQUE, true)
            ? $tipo
            : 'CLASE';
    }

    public function normalizarEstadoRegistro(mixed $estado): string
    {
        if (is_bool($estado)) {
            return $estado ? self::ACTIVO : self::INACTIVO;
        }

        $estado = $this->normalizarMayuscula((string) $estado);

        return match ($estado) {
            'ACTIVA', 'ACTIVO', 'TRUE', '1', 'SI', 'SÍ' => self::ACTIVO,
            'INACTIVA', 'INACTIVO', 'FALSE', '0', 'NO' => self::INACTIVO,
            default => self::ACTIVO,
        };
    }

    public function normalizarEstadoPlantilla(mixed $estado): bool
    {
        if (is_bool($estado)) {
            return $estado;
        }

        $estado = $this->normalizarMayuscula((string) $estado);

        return ! in_array($estado, ['INACTIVO', 'INACTIVA', 'FALSE', '0', 'NO'], true);
    }

    // ============================================================
    // CONSULTAS BASE
    // ============================================================

    private function turnosActivos(): Collection
    {
        if (! $this->tablaExiste('turno')) {
            return collect();
        }

        return DB::table('turno')
            ->where(function ($query) {
                $query->where('est_tur', self::ACTIVO)
                    ->orWhere('est_tur', 'ACTIVA')
                    ->orWhere('est_tur', true);
            })
            ->orderBy('hor_ini_tur')
            ->orderBy('nom_tur')
            ->get();
    }

    private function obtenerTurno(?string $codTur): ?object
    {
        if (! $this->tieneValor($codTur) || ! $this->tablaExiste('turno')) {
            return null;
        }

        return DB::table('turno')
            ->where('cod_tur', $codTur)
            ->first();
    }

    private function obtenerPlantilla(?string $codPho): ?object
    {
        if (! $this->tieneValor($codPho) || ! $this->tablaExiste('plantilla_horaria')) {
            return null;
        }

        return DB::table('plantilla_horaria')
            ->where('cod_pho', $codPho)
            ->first();
    }

    private function obtenerBloque(?string $codHbl): ?object
    {
        if (! $this->tieneValor($codHbl) || ! $this->tablaExiste('horario_bloque')) {
            return null;
        }

        return DB::table('horario_bloque')
            ->where('cod_hbl', $codHbl)
            ->first();
    }

    private function obtenerPlantillaRegular(?string $codTur): ?object
    {
        if (! $this->tieneValor($codTur) || ! $this->tablaExiste('plantilla_horaria')) {
            return null;
        }

        return DB::table('plantilla_horaria')
            ->where('cod_tur', $codTur)
            ->where('tip_pho', self::TIPO_REGULAR)
            ->orderByDesc('act_pho')
            ->orderBy('ord_pho')
            ->first();
    }

    private function obtenerPlantillaInvierno(?string $codTur): ?object
    {
        if (! $this->tieneValor($codTur) || ! $this->tablaExiste('plantilla_horaria')) {
            return null;
        }

        return DB::table('plantilla_horaria')
            ->where('cod_tur', $codTur)
            ->where('tip_pho', self::TIPO_INVIERNO)
            ->orderByDesc('act_pho')
            ->orderByDesc('fec_ini_pho')
            ->first();
    }

    private function plantillasInviernoPorTurno(string $codTur): Collection
    {
        if (! $this->tablaExiste('plantilla_horaria')) {
            return collect();
        }

        return DB::table('plantilla_horaria')
            ->where('cod_tur', $codTur)
            ->where('tip_pho', self::TIPO_INVIERNO)
            ->orderBy('fec_ini_pho')
            ->get();
    }

    private function bloquesPorTurno(string $codTur): Collection
    {
        if (! $this->tablaExiste('horario_bloque')) {
            return collect();
        }

        return DB::table('horario_bloque')
            ->where('cod_tur', $codTur)
            ->orderBy('num_hbl')
            ->orderBy('hor_ini_hbl')
            ->get();
    }

    private function bloquesPorPlantilla(string $codPho): Collection
    {
        if (! $this->tablaExiste('horario_bloque') || ! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return collect();
        }

        return DB::table('horario_bloque')
            ->where('cod_pho', $codPho)
            ->orderBy('num_hbl')
            ->orderBy('hor_ini_hbl')
            ->get();
    }

    private function bloquesSinPlantillaPorTurno(string $codTur): Collection
    {
        if (! $this->tablaExiste('horario_bloque') || ! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return collect();
        }

        return DB::table('horario_bloque')
            ->where('cod_tur', $codTur)
            ->where(function ($query) {
                $query->whereNull('cod_pho')
                    ->orWhere('cod_pho', '');
            })
            ->orderBy('num_hbl')
            ->get();
    }

    // ============================================================
    // VALIDACIONES DE EXISTENCIA Y DUPLICIDAD
    // ============================================================

    private function existeTurnoDuplicado(?string $nombre, ?string $codTurIgnorado = null): bool
    {
        if (! $this->tieneValor($nombre) || ! $this->tablaExiste('turno')) {
            return false;
        }

        $canonico = $this->normalizarCanonico($nombre);

        return DB::table('turno')
            ->when($codTurIgnorado, fn($query) => $query->where('cod_tur', '!=', $codTurIgnorado))
            ->get()
            ->contains(fn($turno) => $this->normalizarCanonico($turno->nom_tur ?? '') === $canonico);
    }

    private function existePlantillaDuplicada(?string $codTur, string $tipo, ?string $nombre, ?string $codPhoIgnorado = null): bool
    {
        if (! $this->tieneValor($codTur) || ! $this->tieneValor($nombre) || ! $this->tablaExiste('plantilla_horaria')) {
            return false;
        }

        return DB::table('plantilla_horaria')
            ->where('cod_tur', $codTur)
            ->where('tip_pho', $tipo)
            ->whereRaw('LOWER(nom_pho) = ?', [mb_strtolower((string) $nombre)])
            ->when($codPhoIgnorado, fn($query) => $query->where('cod_pho', '!=', $codPhoIgnorado))
            ->exists();
    }

    private function existePlantillaRegularAplicada(?string $codTur, ?string $codPhoIgnorado = null): bool
    {
        if (! $this->tieneValor($codTur) || ! $this->tablaExiste('plantilla_horaria')) {
            return false;
        }

        return DB::table('plantilla_horaria')
            ->where('cod_tur', $codTur)
            ->where('tip_pho', self::TIPO_REGULAR)
            ->where('act_pho', true)
            ->when($codPhoIgnorado, fn($query) => $query->where('cod_pho', '!=', $codPhoIgnorado))
            ->exists();
    }

    private function existeNumeroBloqueDuplicado(string $codPho, int $numero, ?string $codHblIgnorado = null): bool
    {
        if (! $this->tablaExiste('horario_bloque') || ! Schema::hasColumn('horario_bloque', 'cod_pho')) {
            return false;
        }

        return DB::table('horario_bloque')
            ->where('cod_pho', $codPho)
            ->where('num_hbl', $numero)
            ->when($codHblIgnorado, fn($query) => $query->where('cod_hbl', '!=', $codHblIgnorado))
            ->exists();
    }

    // ============================================================
    // USO Y TRAZABILIDAD
    // ============================================================

    private function usoTurno(string $codTur): array
    {
        return [
            'plantillas' => $this->tablaExiste('plantilla_horaria')
                ? DB::table('plantilla_horaria')->where('cod_tur', $codTur)->count()
                : 0,
            'bloques' => $this->tablaExiste('horario_bloque')
                ? DB::table('horario_bloque')->where('cod_tur', $codTur)->count()
                : 0,
            'horarios' => $this->tablaExiste('horario') && Schema::hasColumn('horario', 'cod_tur')
                ? DB::table('horario')->where('cod_tur', $codTur)->count()
                : 0,
            'detalles_horario' => $this->cantidadDetallesPorTurno($codTur),
        ];
    }

    private function usoPlantilla(string $codPho): array
    {
        $bloques = $this->bloquesPorPlantilla($codPho)->pluck('cod_hbl');

        return [
            'bloques' => $bloques->count(),
            'detalles_horario' => $this->tablaExiste('horario_detalle') && $bloques->isNotEmpty()
                ? DB::table('horario_detalle')->whereIn('cod_hbl', $bloques)->count()
                : 0,
        ];
    }

    private function bloqueTieneUso(?string $codHbl): bool
    {
        if (! $this->tieneValor($codHbl) || ! $this->tablaExiste('horario_detalle')) {
            return false;
        }

        return DB::table('horario_detalle')
            ->where('cod_hbl', $codHbl)
            ->exists();
    }

    private function cantidadDetallesPorTurno(string $codTur): int
    {
        if (! $this->tablaExiste('horario_detalle') || ! $this->tablaExiste('horario_bloque')) {
            return 0;
        }

        $bloques = DB::table('horario_bloque')
            ->where('cod_tur', $codTur)
            ->pluck('cod_hbl');

        if ($bloques->isEmpty()) {
            return 0;
        }

        return DB::table('horario_detalle')
            ->whereIn('cod_hbl', $bloques)
            ->count();
    }

    // ============================================================
    // CÁLCULOS Y MAPEO
    // ============================================================

    public function calcularDuracionMinutos(?string $horaInicio, ?string $horaFin): int
    {
        if (! $this->horaValida($horaInicio) || ! $this->horaValida($horaFin)) {
            return 0;
        }

        return $this->minutosDesdeMedianoche($horaFin) - $this->minutosDesdeMedianoche($horaInicio);
    }

    private function duracionSegura(?string $horaInicio, ?string $horaFin): ?int
    {
        if (! $this->horaValida($horaInicio) || ! $this->horaValida($horaFin)) {
            return null;
        }

        return $this->calcularDuracionMinutos($horaInicio, $horaFin);
    }

    private function minutosDesdeMedianoche(?string $hora): int
    {
        $hora = $this->normalizarHora($hora);

        if (! $hora) {
            return 0;
        }

        [$h, $m] = explode(':', $hora);

        return ((int) $h * 60) + (int) $m;
    }

    private function bloqueDentroDelTurno(?string $horaInicio, ?string $horaFin, object $turno): bool
    {
        if (! $this->horaValida($horaInicio) || ! $this->horaValida($horaFin)) {
            return false;
        }

        if (! $this->horaValida($turno->hor_ini_tur ?? null) || ! $this->horaValida($turno->hor_fin_tur ?? null)) {
            return true;
        }

        $inicioBloque = $this->minutosDesdeMedianoche($horaInicio);
        $finBloque = $this->minutosDesdeMedianoche($horaFin);
        $inicioTurno = $this->minutosDesdeMedianoche($turno->hor_ini_tur);
        $finTurno = $this->minutosDesdeMedianoche($turno->hor_fin_tur);

        return $inicioBloque >= $inicioTurno && $finBloque <= $finTurno;
    }

    private function calcularCoberturaTurnoPorPlantilla(string $codPho): array
    {
        $plantilla = $this->obtenerPlantilla($codPho);

        if (! $plantilla) {
            return [
                'disponible' => false,
            ];
        }

        $turno = $this->obtenerTurno($plantilla->cod_tur ?? null);

        if (! $turno) {
            return [
                'disponible' => false,
            ];
        }

        $bloques = $this->bloquesPorPlantilla($codPho)
            ->filter(fn($b) => $this->horaValida($b->hor_ini_hbl ?? null) && $this->horaValida($b->hor_fin_hbl ?? null));

        $minutosBloques = $bloques->sum(fn($b) => max(0, $this->calcularDuracionMinutos($b->hor_ini_hbl, $b->hor_fin_hbl)));
        $minutosTurno = $this->calcularDuracionMinutos($turno->hor_ini_tur ?? null, $turno->hor_fin_tur ?? null);

        return [
            'disponible' => true,
            'minutos_turno' => $minutosTurno,
            'minutos_bloques' => $minutosBloques,
            'diferencia' => $minutosTurno - $minutosBloques,
            'porcentaje' => $minutosTurno > 0 ? round(($minutosBloques / $minutosTurno) * 100, 2) : 0,
        ];
    }

    private function mapearGestion(?object $gestion): ?array
    {
        if (! $gestion) {
            return null;
        }

        return [
            'anio' => (int) ($gestion->ani_gea ?? now()->year),
            'inicio' => $gestion->fii_gea ?? null,
            'fin' => $gestion->ffi_gea ?? null,
            'estado' => $gestion->est_gea ?? null,
        ];
    }

    private function mapearPlantilla(?object $plantilla): ?array
    {
        if (! $plantilla) {
            return null;
        }

        return [
            'nombre' => $plantilla->nom_pho ?? 'Plantilla',
            'tipo' => $plantilla->tip_pho ?? self::TIPO_REGULAR,
            'fecha_inicio' => $plantilla->fec_ini_pho ?? null,
            'fecha_fin' => $plantilla->fec_fin_pho ?? null,
            'duracion_base' => $plantilla->dur_blo_pho ?? null,
            'aplicada' => (bool) ($plantilla->act_pho ?? false),
            'activa' => (bool) ($plantilla->est_pho ?? true),
        ];
    }

    private function mapearBloque(?object $bloque): ?array
    {
        if (! $bloque) {
            return null;
        }

        return [
            'numero' => $bloque->num_hbl ?? null,
            'nombre' => $bloque->nom_hbl ?? 'Bloque',
            'tipo' => $bloque->tip_hbl ?? 'CLASE',
            'hora_inicio' => $this->normalizarHora($bloque->hor_ini_hbl ?? null),
            'hora_fin' => $this->normalizarHora($bloque->hor_fin_hbl ?? null),
            'rango' => $this->rangoTexto($bloque->hor_ini_hbl ?? null, $bloque->hor_fin_hbl ?? null),
            'estado' => $bloque->est_hbl ?? self::ACTIVO,
            'tiene_uso' => $this->bloqueTieneUso($bloque->cod_hbl ?? null),
        ];
    }

    private function gestionTrabajoResumen(): ?array
    {
        return $this->mapearGestion($this->obtenerGestionTrabajo());
    }

    private function fechaDesdeGestion(object $gestion, string $tipo): ?Carbon
    {
        $campo = $tipo === 'inicio' ? 'fii_gea' : 'ffi_gea';
        $fecha = $gestion->{$campo} ?? null;

        if (! $this->fechaValida($fecha)) {
            return null;
        }

        return Carbon::parse($fecha)->startOfDay();
    }

    private function rangoTurnoTexto(object $turno): string
    {
        return $this->rangoTexto($turno->hor_ini_tur ?? null, $turno->hor_fin_tur ?? null);
    }

    private function rangoTexto(?string $inicio, ?string $fin): string
    {
        $inicio = $this->normalizarHora($inicio);
        $fin = $this->normalizarHora($fin);

        if (! $inicio || ! $fin) {
            return 'Sin rango definido';
        }

        return "{$inicio} - {$fin}";
    }

    // ============================================================
    // HELPERS DE DECISIÓN
    // ============================================================

    private function esTurnoRegular(?string $nombre): bool
    {
        return in_array($this->normalizarCanonico($nombre), self::TURNOS_REGULARES, true);
    }

    private function pareceTurnoNocturno(?string $nombre, ?string $horaInicio = null, ?string $horaFin = null): bool
    {
        $canonico = $this->normalizarCanonico($nombre);

        if (str_contains($canonico, 'NOCHE') || str_contains($canonico, 'NOCTURNO')) {
            return true;
        }

        if ($this->horaValida($horaInicio) && $this->minutosDesdeMedianoche($horaInicio) >= 18 * 60) {
            return true;
        }

        if ($this->horaValida($horaFin) && $this->minutosDesdeMedianoche($horaFin) > 19 * 60) {
            return true;
        }

        return false;
    }

    private function duracionSugeridaPorTipo(string $tipo): int
    {
        return $tipo === self::TIPO_INVIERNO
            ? self::DURACION_BLOQUE_INVIERNO_REFERENCIAL
            : self::DURACION_BLOQUE_REGULAR_REFERENCIAL;
    }

    private function accionSugeridaAuditoria(
        ?object $plantillaRegular,
        int $bloquesSinPlantilla,
        int $bloquesFueraTurno,
        int $plantillasSolapadas
    ): string {
        if ($plantillasSolapadas > 0) {
            return 'Corregir fechas solapadas de invierno antes de aplicar plantillas.';
        }

        if (! $plantillaRegular && $bloquesSinPlantilla > 0) {
            return 'Crear plantilla regular y asociar bloques existentes.';
        }

        if (! $plantillaRegular) {
            return 'Crear plantilla regular.';
        }

        if ($bloquesSinPlantilla > 0) {
            return 'Asociar bloques existentes a la plantilla regular.';
        }

        if ($bloquesFueraTurno > 0) {
            return 'Revisar bloques fuera del rango del turno.';
        }

        return 'Sin corrección automática requerida.';
    }

    // ============================================================
    // FORMATO DE RESPUESTA
    // ============================================================

    private function resultado(
        bool $puedeContinuar,
        string $estado,
        string $nivelRiesgo,
        string $mensaje,
        array $bloqueos = [],
        array $advertencias = [],
        array $sugerencias = [],
        array $resumen = []
    ): array {
        return [
            'puede_continuar' => $puedeContinuar,
            'estado' => $estado,
            'estado_inteligente' => $estado,
            'nivel_riesgo' => $nivelRiesgo,
            'mensaje' => $mensaje,
            'bloqueos' => array_values(array_unique($bloqueos)),
            'advertencias' => array_values(array_unique($advertencias)),
            'sugerencias' => array_values(array_unique($sugerencias)),
            'resumen' => $resumen,
        ];
    }

    private function resultadoBloqueado(string $mensaje, array $bloqueos = [], array $sugerencias = []): array
    {
        return $this->resultado(
            puedeContinuar: false,
            estado: self::ESTADO_BLOQUEADO,
            nivelRiesgo: 'ALTO',
            mensaje: $mensaje,
            bloqueos: $bloqueos,
            advertencias: [],
            sugerencias: $sugerencias,
            resumen: []
        );
    }

    private function determinarEstado(array $bloqueos, array $advertencias, bool $modoTiempoReal, array $datos): string
    {
        if (! empty($bloqueos)) {
            return self::ESTADO_BLOQUEADO;
        }

        if ($modoTiempoReal && $this->datosMinimosVacios($datos)) {
            return self::ESTADO_INCOMPLETO;
        }

        if (! empty($advertencias)) {
            return self::ESTADO_OBSERVADO;
        }

        return self::ESTADO_VALIDO;
    }

    private function nivelRiesgo(array $bloqueos, array $advertencias): string
    {
        if (count($bloqueos) >= 2) {
            return 'CRITICO';
        }

        if (count($bloqueos) === 1) {
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

    // ============================================================
    // UTILIDADES GENERALES
    // ============================================================

    private function tablaExiste(string $tabla): bool
    {
        try {
            return Schema::hasTable($tabla);
        } catch (Throwable) {
            return false;
        }
    }

    private function generarCodigo(string $tabla, string $columna, string $prefijo): string
    {
        $ultimo = DB::table($tabla)
            ->where($columna, 'like', "{$prefijo}_%")
            ->orderByDesc($columna)
            ->value($columna);

        $numero = $ultimo
            ? (int) str_replace("{$prefijo}_", '', $ultimo)
            : 0;

        do {
            $numero++;
            $codigo = $prefijo . '_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
        } while (DB::table($tabla)->where($columna, $codigo)->exists());

        return $codigo;
    }

    private function filtrarColumnas(string $tabla, array $datos): array
    {
        if (! $this->tablaExiste($tabla)) {
            return [];
        }

        return collect($datos)
            ->filter(fn($value, $key) => Schema::hasColumn($tabla, $key))
            ->all();
    }

    private function registrarBitacoraSeguro(string $accion, string $tabla, string $registro, string $descripcion): void
    {
        try {
            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'modulo' => 'Gestión de Turnos',
                        'tabla' => $tabla,
                        'registro' => $registro,
                        'descripcion' => $descripcion,
                    ])
                    ->log($accion . ' - ' . $descripcion);
            }
        } catch (Throwable) {
            //
        }

        if (! $this->tablaExiste('bitacora')) {
            return;
        }

        try {
            $payload = [
                'cod_bit' => $this->generarCodigo('bitacora', 'cod_bit', 'BIT'),
                'acc_bit' => $accion,
                'tab_bit' => $tabla,
                'reg_bit' => $registro,
                'cod_usu' => auth()->user()->cod_usu ?? auth()->id(),
                'fec_bit' => now(),
                'est_bit' => self::ACTIVO,
            ];

            DB::table('bitacora')->insert($this->filtrarColumnas('bitacora', $payload));
        } catch (Throwable) {
            //
        }
    }

    private function datosMinimosVacios(array $datos): bool
    {
        return collect($datos)
            ->filter(fn($valor) => $this->tieneValor($valor))
            ->count() <= 1;
    }

    private function tieneValor(mixed $valor): bool
    {
        return trim((string) $valor) !== '';
    }

    private function limpiarTexto(?string $texto): ?string
    {
        $texto = trim(preg_replace('/\s+/', ' ', (string) $texto));

        return $texto !== '' ? $texto : null;
    }

    private function normalizarTitulo(?string $texto): ?string
    {
        $texto = $this->limpiarTexto($texto);

        if (! $texto) {
            return null;
        }

        return Str::of($texto)->lower()->title()->toString();
    }

    private function normalizarMayuscula(?string $texto): ?string
    {
        $texto = $this->limpiarTexto($texto);

        return $texto ? mb_strtoupper($texto, 'UTF-8') : null;
    }

    private function normalizarCanonico(?string $texto): string
    {
        $texto = $this->normalizarMayuscula($texto) ?? '';

        $texto = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú'],
            ['A', 'E', 'I', 'O', 'U'],
            $texto
        );

        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    private function normalizarHora(?string $hora): ?string
    {
        $hora = trim((string) $hora);

        if ($hora === '') {
            return null;
        }

        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $hora, $m)) {
            return str_pad((string) $m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
        }

        return $hora;
    }

    private function normalizarFecha(?string $fecha): ?string
    {
        if (! $this->tieneValor($fecha)) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->format('Y-m-d');
        } catch (Throwable) {
            return trim((string) $fecha);
        }
    }

    private function normalizarEnteroNullable(mixed $valor): ?int
    {
        if (! $this->tieneValor($valor)) {
            return null;
        }

        return (int) $valor;
    }

    private function horaValida(?string $hora): bool
    {
        if (! $this->tieneValor($hora)) {
            return false;
        }

        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $this->normalizarHora($hora));
    }

    private function fechaValida(?string $fecha): bool
    {
        if (! $this->tieneValor($fecha)) {
            return false;
        }

        try {
            Carbon::parse($fecha);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
