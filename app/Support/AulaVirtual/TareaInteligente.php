<?php

namespace App\Support\AulaVirtual;

use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TareaInteligente extends SoporteInteligenteBase
{
    public const MAX_TAREAS_SEMANALES_CURSO_RECOMENDADO = 3;

    /**
     * Analiza la creación o edición de una tarea académica.
     */
    public function analizar(array $datos, ?string $ignorarCodTar = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Planificación Curricular y Carga Pedagógica Secundaria'];

        $codCla = trim((string) ($datos['cod_cla'] ?? ''));
        $codDoc = trim((string) ($datos['cod_doc'] ?? ''));
        $titulo = trim((string) ($datos['tit_tar'] ?? ''));
        $descripcion = trim((string) ($datos['des_tar'] ?? ''));
        $tipo = trim((string) ($datos['tip_tar'] ?? 'TAREA'));
        $fecPub = $datos['fec_pub_tar'] ?? null;
        $fecLim = $datos['fec_lim_tar'] ?? null;
        $puntajeMax = is_numeric($datos['pun_max_tar'] ?? null) ? (float) $datos['pun_max_tar'] : 100.0;

        // 1. Verificaciones de Clase y Docente
        if ($codCla === '') {
            $msg = 'Debe seleccionar una clase virtual para asignar la tarea.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_TAREA_CLASE_REQUERIDA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        }

        // 2. Título y descripción
        if ($titulo === '') {
            $msg = 'El título de la tarea es obligatorio.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_TAREA_TITULO_REQUERIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $tituloNorm = $this->normalizarTexto($titulo);
            $datosCalculados['titulo_normalizado'] = $tituloNorm;

            if (mb_strlen($tituloNorm) < 4) {
                $adv = 'El título de la tarea es muy breve. Detalle el tema o contenido clave.';
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'AV_TAREA_TITULO_BREVE', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO);
            }

            // Duplicidad de tarea en la misma clase
            if ($codCla !== '' && Schema::hasTable('tarea')) {
                $tareasClase = DB::table('tarea')
                    ->where('cod_cla', $codCla)
                    ->when($ignorarCodTar, fn ($q) => $q->where('cod_tar', '!=', $ignorarCodTar))
                    ->pluck('tit_tar');

                foreach ($tareasClase as $titExistente) {
                    $similitud = $this->calcularSimilitud($tituloNorm, (string) $titExistente);
                    if ($similitud >= 90.0) {
                        $adv = "Ya existe una tarea con título muy similar ('{$titExistente}') en esta clase. Verifique no estar duplicando actividades.";
                        $advertencias[] = $adv;
                        $this->registrarHallazgo($hallazgos, 'AV_TAREA_DUPLICADA', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['similitud' => $similitud]);
                        break;
                    }
                }
            }
        }

        // 3. Puntaje de la tarea (escala boliviana 1 a 100)
        if ($puntajeMax <= 0 || $puntajeMax > 100) {
            $msg = 'El puntaje máximo de la tarea debe estar comprendido entre 1 y 100 puntos.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_TAREA_PUNTAJE_INVALIDO', self::TIPO_NORMATIVA, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
        }

        // 4. Fechas de Publicación y Límite
        if ($fecPub && $fecLim) {
            $dtPub = Carbon::parse($fecPub);
            $dtLim = Carbon::parse($fecLim);

            if ($dtLim->lessThanOrEqualTo($dtPub)) {
                $msg = 'La fecha límite de entrega debe ser posterior a la fecha de publicación.';
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'AV_TAREA_FECHAS_INCOHERENTES', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
            } else {
                $diasPlazo = $dtPub->diffInDays($dtLim);
                $datosCalculados['dias_plazo'] = $diasPlazo;

                if ($diasPlazo < 1) {
                    $sug = 'El plazo de entrega es menor a 24 horas. Verifique que sea adecuado para los estudiantes.';
                    $sugerencias[] = $sug;
                    $this->registrarHallazgo($hallazgos, 'AV_TAREA_PLAZO_CORTO', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
                }
            }

            // 5. Carga Pedagógica Semanal (Sobrecarga de tareas para el curso)
            if ($codCla !== '' && Schema::hasTable('tarea') && Schema::hasTable('clase_virtual')) {
                $claseActual = DB::table('clase_virtual')->where('cod_cla', $codCla)->first();
                if ($claseActual && Schema::hasTable('plan_asignatura')) {
                    $planActual = DB::table('plan_asignatura')->where('cod_pas', $claseActual->cod_pas)->first();
                    if ($planActual) {
                        // Buscar todas las clases que pertenezcan al mismo curso y paralelo
                        $clasesMismoParalelo = DB::table('clase_virtual')
                            ->join('plan_asignatura', 'clase_virtual.cod_pas', '=', 'plan_asignatura.cod_pas')
                            ->where('plan_asignatura.cod_cur', $planActual->cod_cur)
                            ->where('plan_asignatura.cod_par', $planActual->cod_par)
                            ->where('plan_asignatura.cod_gea', $planActual->cod_gea)
                            ->pluck('clase_virtual.cod_cla');

                        $inicioSemana = $dtLim->copy()->startOfWeek();
                        $finSemana = $dtLim->copy()->endOfWeek();

                        $tareasSemana = DB::table('tarea')
                            ->whereIn('cod_cla', $clasesMismoParalelo)
                            ->when($ignorarCodTar, fn ($q) => $q->where('cod_tar', '!=', $ignorarCodTar))
                            ->whereBetween('fec_lim_tar', [$inicioSemana, $finSemana])
                            ->count();

                        $datosCalculados['tareas_programadas_en_la_semana'] = $tareasSemana;

                        if ($tareasSemana >= self::MAX_TAREAS_SEMANALES_CURSO_RECOMENDADO) {
                            $adv = "Los estudiantes de este paralelo ya tienen {$tareasSemana} actividad(es) programada(s) para la misma semana. Se sugiere coordinar fechas para evitar sobrecarga.";
                            $advertencias[] = $adv;
                            $this->registrarHallazgo($hallazgos, 'AV_TAREA_CARGA_ALTA', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['tareas_semana' => $tareasSemana]);
                        }
                    }
                }
            }
        }

        $resumen = [
            'total_bloqueos' => count($bloqueos),
            'total_advertencias' => count($advertencias),
            'total_sugerencias' => count($sugerencias),
        ];

        return $this->construirResultado(
            puedeContinuar: count($bloqueos) === 0,
            puedeGuardar: count($bloqueos) === 0,
            estado: count($bloqueos) > 0 ? self::ESTADO_BLOQUEADO : (count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK),
            nivelRiesgo: count($bloqueos) > 0 ? self::RIESGO_ALTO : (count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            impacto: $impacto,
            resumen: $resumen,
            fuentesRegla: $fuentes
        );
    }
}
