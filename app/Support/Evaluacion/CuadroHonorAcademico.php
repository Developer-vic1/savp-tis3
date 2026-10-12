<?php

namespace App\Support\Evaluacion;

use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Propuestas de reconocimiento a partir de notas observadas; no adjudica honores ni pagos. */
class CuadroHonorAcademico
{
    public function __construct(private readonly PanelResultadosAcademicos $panel) {}

    public function consulta(string $gestion): Builder
    {
        // La búsqueda y las prioridades no deben convertir un subconjunto en los mejores de toda la gestión.
        return $this->panel->consulta(['gestion' => $gestion, 'orientacion' => false])
            ->addSelect('p.gen_per')->whereNotNull('n.promedio')
            ->whereIn('i.est_ins', ['ACTIVA', 'ARCHIVADA'])
            ->when($gestion === '', fn ($q) => $q->whereRaw('1 = 0'));
    }

    private function posiciones(Builder $consulta, array $particion, int $limite): Collection
    {
        $campos = implode(', ', array_map(fn ($campo) => 'datos.'.$campo, $particion));
        $orden = 'ROUND(CAST(datos.promedio AS NUMERIC), 2)';
        $posiciones = DB::query()->fromSub($consulta, 'datos')->select('datos.*')
            ->selectRaw("RANK() OVER (PARTITION BY $campos ORDER BY $orden DESC) AS puesto")
            ->selectRaw("COUNT(*) OVER (PARTITION BY $campos) AS total_particion")
            ->selectRaw("COUNT(*) OVER (PARTITION BY $campos, $orden) AS iguales");

        return DB::query()->fromSub($posiciones, 'honor')->where('puesto', '<=', $limite)
            ->orderBy('ani_gea', 'desc')->orderBy('puesto')->orderBy('cod_cur')->orderBy('cod_ins')->get()
            ->map(function ($e) {
                $e->empatado = (int)$e->iguales > 1;
                $e->estado_honor = $e->sinteticas > 0 ? 'Validación sintética' : ($e->promovido ? 'Cierre registrado · requiere revisión' : 'Provisional');
                $e->carreras = [];
                $e->fecha = null;
                return $e;
            });
    }

    public function resumen(string $gestion, bool $orientacion): array
    {
        $consulta = $this->consulta($gestion);
        $cursos = Curso::orderBy('ord_cur')->get()->keyBy('cod_cur');
        $mejores = $this->posiciones(clone $consulta, ['cod_gea', 'cod_cur'], 3);
        $grados = $mejores->groupBy('cod_cur')->map(fn ($alumnos, $codigo) => [
            'codigo' => $codigo, 'nombre' => $alumnos->first()->nom_cur,
            'estudiantes' => $alumnos, 'total' => (int)$alumnos->first()->total_particion,
        ])->sortBy(fn ($g) => $cursos->get($g['codigo'])?->ord_cur ?? PHP_INT_MAX)->values();

        // Solo el sexto de secundaria; nunca el mejor promedio de toda la unidad educativa.
        $sexto = $cursos->filter(fn ($c) => (int)$c->ord_cur === 6 && str_contains(mb_strtoupper($c->niv_cur ?? ''), 'SECUNDARIA'))->keys()->all();
        $promocion = $this->posiciones((clone $consulta)->whereIn('i.cod_cur', $sexto)->whereIn('p.gen_per', ['M', 'F']), ['cod_gea', 'gen_per'], 1);
        $promocion->each(function ($e) { $e->genero = $e->gen_per === 'M' ? 'Hombre' : 'Mujer'; });

        // Historia recalculada por año, no una lista de adjudicaciones institucionales guardadas.
        $historia = $this->panel->consulta(['orientacion' => false])->addSelect('p.gen_per')
            ->whereNotNull('n.promedio')->whereIn('i.est_ins', ['ACTIVA', 'ARCHIVADA']);
        $historicos = $this->posiciones($historia, ['cod_gea'], 2);

        if ($orientacion && $promocion->isNotEmpty()) {
            $actividades = OrientacionActividad::where('cod_gea', $gestion)->whereIn('cod_est', $promocion->pluck('cod_est'))
                ->whereRaw('id = (SELECT MAX(actual.id) FROM orientacion_actividades AS actual WHERE actual.cod_est = orientacion_actividades.cod_est AND actual.cod_gea = orientacion_actividades.cod_gea)')->get()->keyBy('cod_est');
            foreach ($promocion as $e) {
                $actividad = $actividades->get($e->cod_est);
                $estudio = app(RendimientoEstudiantil::class)->estudio($actividad);
                if ($estudio['estado'] === 'contrato_valido') {
                    $e->carreras = $estudio['datos']['career_evidence_profiles'];
                    $e->fecha = $actividad->analysis_completed_at?->format('d/m/Y');
                }
            }
        }

        return ['gestion' => $gestion, 'grados' => $grados,
            'promocion' => ['nombre' => $cursos->only($sexto)->pluck('nom_cur')->implode(' · '), 'estudiantes' => $promocion, 'total' => $promocion->unique('gen_per')->sum('total_particion')],
            'historial' => $historicos->groupBy('cod_gea')->map(fn ($alumnos, $codigo) => ['gestion' => $codigo, 'anio' => $alumnos->first()->ani_gea, 'estudiantes' => $alumnos])->values(),
        ];
    }
}
