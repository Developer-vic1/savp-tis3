<?php

namespace App\Support\Academico;

use App\Models\InscripcionEstudiante;
use App\Models\NovedadEstudiante;
use App\Models\SeguimientoAcademico;
use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;

class SeguimientoAcademicoInteligente extends SoporteInteligenteBase
{
    public const TIPOS_NOVEDAD = ['LICENCIA', 'AUSENCIA_JUSTIFICADA_PROLONGADA', 'PERMISO_TEMPORAL', 'ADAPTACION_HORARIA', 'PARTICIPACION_OFICIAL', 'MOVILIDAD_TEMPORAL', 'OTRO'];

    public function novedadesVigentes(string $estudiante, string $gestion, string $fecha)
    {
        return NovedadEstudiante::where('cod_est', $estudiante)->where('cod_gea', $gestion)
            ->whereIn('est_nes', ['ACTIVA', 'FINALIZADA'])->whereDate('fii_nes', '<=', $fecha)->whereDate('ffi_nes', '>=', $fecha)->get();
    }

    public function analizar(string $estudiante, string $gestion): array
    {
        $inscripcion = InscripcionEstudiante::where('cod_est', $estudiante)->where('cod_gea', $gestion)->first();
        $advertencias = [];
        $bloqueos = [];
        if (! $inscripcion) {
            return $this->construirResultado(bloqueos: ['No existe inscripción para esta gestión.']);
        }
        if (! $inscripcion->doc_com_ins) {
            $advertencias[] = 'DOCUMENTACION_PENDIENTE';
        }
        if ($inscripcion->sie_ins && ! $inscripcion->fec_sie_ins) {
            $advertencias[] = 'SIE_INCOHERENTE: falta la fecha del reporte SIE.';
        }
        if (in_array($inscripcion->est_ins, ['ACTIVA', 'CONFIRMADA', 'OBSERVADA'], true) && ! $inscripcion->vigencias()->enFecha(today()->toDateString())->exists()) {
            $bloqueos[] = 'VIGENCIA_INCONSISTENTE';
        }
        if (SeguimientoAcademico::where('cod_est', $estudiante)->where('cod_gea', $gestion)->whereIn('est_seg', ['ABIERTO', 'EN_SEGUIMIENTO'])->whereDate('fec_pro_seg', '<', today())->exists()) {
            $advertencias[] = 'SEGUIMIENTO_VENCIDO: existe una revisión pendiente.';
        }
        $notas = DB::table('calificacion')->where('cod_est', $estudiante)->where('est_cal', 'ACTIVO')
            ->whereIn('cod_pas', DB::table('plan_asignatura')->where('cod_gea', $gestion)->select('cod_pas'))->pluck('not_cal');
        if ($notas->isNotEmpty() && $notas->avg() < 51) {
            $advertencias[] = 'BAJO_RENDIMIENTO: revisar las calificaciones registradas.';
        }
        if (DB::table('calificacion')->where('cod_est', $estudiante)->whereNull('cod_pas')->exists()) {
            $advertencias[] = 'DATOS_DESACTUALIZADOS: existen notas históricas sin plan asignado; no se utilizan para calcular riesgo de esta gestión.';
        }
        if ($notas->filter(fn ($nota) => $nota < 51)->count() >= 3) {
            $advertencias[] = 'VARIAS_MATERIAS_AFECTADAS';
        }

        $asistencias = DB::table('asistencia_estudiante as e')
            ->join('asistencia_clase as c', 'c.cod_asi_cla', '=', 'e.cod_asi_cla')
            ->join('clase_virtual as a', 'a.cod_cla', '=', 'c.cod_cla')
            ->join('plan_asignatura as p', 'p.cod_pas', '=', 'a.cod_pas')
            ->join('estado_asistencia as s', 's.cod_est_asi', '=', 'e.cod_est_asi')
            ->where('e.cod_est', $estudiante)->where('p.cod_gea', $gestion)->where('e.est_asi_est', '<>', 'ANULADO')
            ->whereDate('c.fec_asi_cla', '>=', today()->subDays(30))->whereDate('c.fec_asi_cla', '<=', today())
            ->orderBy('c.fec_asi_cla')->get(['c.cod_asi_cla', 'c.fec_asi_cla', 's.nom_est_asi', 'e.min_retraso', 'p.cod_cur', 'p.cod_par', 'p.cod_tur']);
        $faltas = [];
        $atrasos = 0;
        foreach ($asistencias as $asistencia) {
            $fecha = substr($asistencia->fec_asi_cla, 0, 10);
            $calendario = app(CalendarioAcademicoInteligente::class)->analizarFecha($gestion, $fecha, (array) $asistencia);
            if (! $calendario['puede_continuar']) {
                $advertencias[] = 'ASISTENCIA_EN_JORNADA_SUSPENDIDA: '.$fecha;

                continue;
            }
            if (! $inscripcion->vigencias()->enFecha($fecha)->exists()) {
                $advertencias[] = 'ACTIVIDAD_FUERA_DE_VIGENCIA: '.$fecha;

                continue;
            }
            if ($this->novedadesVigentes($estudiante, $gestion, $fecha)->isNotEmpty()) {
                continue;
            }
            $grupo = DB::table('asistencia_estudiante as e')->join('estado_asistencia as s', 's.cod_est_asi', '=', 'e.cod_est_asi')
                ->where('e.cod_asi_cla', $asistencia->cod_asi_cla)->where('e.est_asi_est', '<>', 'ANULADO')->pluck('s.nom_est_asi');
            $esAusencia = fn ($nombre) => preg_match('/ausen|falta/i', $nombre) === 1;
            if ($grupo->count() >= 5 && $grupo->filter($esAusencia)->count() / $grupo->count() >= 0.5) {
                $advertencias[] = 'ANOMALIA_GRUPAL: revisar posible causa común o inconsistencia de datos antes de iniciar seguimientos individuales.';

                continue;
            }
            $faltas[$fecha][] = $esAusencia($asistencia->nom_est_asi);
            $atrasos += $asistencia->min_retraso > 0 ? 1 : 0;
        }
        $diasAusente = collect($faltas)->map(fn ($marcas) => ! in_array(false, $marcas, true))->values();
        if ($diasAusente->count() >= 3 && $diasAusente->take(-3)->every(fn ($ausente) => $ausente)) {
            $advertencias[] = 'AUSENCIAS_CONSECUTIVAS';
        }
        if ($atrasos >= 3) {
            $advertencias[] = 'ATRASOS_REITERADOS';
        }

        return $this->construirResultado(bloqueos: $bloqueos, advertencias: $advertencias,
            datosCalculados: ['novedades_vigentes' => $this->novedadesVigentes($estudiante, $gestion, today()->toDateString())->count()],
            sugerencias: ['Revisar posible causa común o inconsistencia de datos antes de iniciar un seguimiento individual.']);
    }
}
