<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\PeriodoEvaluacion;
use App\Support\Reportes\ReporteAcademicoInteligente;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ReportesAcademicos extends Component
{
    public string $periodoFiltro = '';
    public string $asignaturaFiltro = '';
    public string $estudianteFiltro = '';
    public string $especialidadFiltro = '';
    public string $desempenoFiltro = '';

    public function limpiarFiltros(): void
    {
        $this->periodoFiltro = '';
        $this->asignaturaFiltro = '';
        $this->estudianteFiltro = '';
        $this->especialidadFiltro = '';
        $this->desempenoFiltro = '';
    }

    public function render()
    {
        $soporte = app(ReporteAcademicoInteligente::class);

        $filtros = [
            'cod_pev' => $this->periodoFiltro,
            'cod_asi' => $this->asignaturaFiltro,
            'cod_est' => $this->estudianteFiltro,
            'cod_esp' => $this->especialidadFiltro,
            'desempeno' => $this->desempenoFiltro,
        ];

        $reporte = $soporte->generarReporte($filtros);

        // Fetch query for the table listing
        $calificaciones = Calificacion::query()
            ->with(['estudiante.persona', 'estudiante.especialidad', 'asignatura', 'periodoEvaluacion'])
            ->where('est_cal', 'ACTIVO')
            ->when($this->periodoFiltro !== '', fn (Builder $query) => $query->where('cod_pev', $this->periodoFiltro))
            ->when($this->asignaturaFiltro !== '', fn (Builder $query) => $query->where('cod_asi', $this->asignaturaFiltro))
            ->when($this->estudianteFiltro !== '', fn (Builder $query) => $query->where('cod_est', $this->estudianteFiltro))
            ->when($this->especialidadFiltro !== '', fn (Builder $query) => $query->whereHas('estudiante', fn ($q) => $q->where('cod_esp', $this->especialidadFiltro)))
            ->get()
            ->map(function (Calificacion $calificacion) use ($soporte) {
                $calificacion->setAttribute('desempeno_calculado', $soporte->clasificar((float) $calificacion->not_cal));
                return $calificacion;
            })
            ->when($this->desempenoFiltro !== '', fn ($items) => $items->where('desempeno_calculado', $this->desempenoFiltro))
            ->values();

        $totalEstudiantes = max(1, Estudiante::count());

        $hasExtended = \Illuminate\Support\Facades\Schema::hasColumn('especialidad_tecnica', 'exp_voc_esp');

        $orientaciones = collect($reporte['promedio_por_especialidad'])->map(function ($item) use ($soporte, $totalEstudiantes, $hasExtended) {
            [$area, $carreras] = $soporte->orientacionPorEspecialidad($item['nombre']);

            $explicacion = "Promedio de rendimiento técnico: {$item['promedio']} pts. Inferencia de perfil vocacional adaptada.";
            $acciones = [];

            if ($hasExtended) {
                $record = EspecialidadTecnica::where('nom_esp', $item['nombre'])->first();
                if ($record && $record->clas_bth_esp) {
                    if ($record->exp_voc_esp) {
                        $explicacion = $record->exp_voc_esp;
                    }
                    if (!empty($record->acc_rec_esp)) {
                        $acciones = (array) $record->acc_rec_esp;
                    }
                }
            }

            if (empty($acciones)) {
                $catalog = \App\Support\Academico\EspecialidadTecnicaInteligente::catalogo();
                foreach ($catalog as $nombre => $meta) {
                    similar_text(mb_strtolower($item['nombre']), mb_strtolower($nombre), $sim);
                    if ($sim >= 80) {
                        $acciones = $meta['acciones_recomendadas'] ?? [];
                        break;
                    }
                }
            }

            return [
                'especialidad' => $item['nombre'],
                'area' => $area,
                'carreras' => $carreras,
                'estudiantes' => $item['registros'],
                'porcentaje' => round(($item['registros'] / $totalEstudiantes) * 100, 1),
                'promedio' => $item['promedio'],
                'explicacion' => $explicacion,
                'acciones' => array_slice($acciones, 0, 3),
            ];
        });

        return view('livewire.admin.reportes-academicos', [
            'calificaciones' => $calificaciones,
            'rendimientoAsignatura' => collect($reporte['promedio_por_asignatura']),
            'rendimientoPeriodo' => collect($reporte['promedio_por_periodo']),
            'orientaciones' => $orientaciones,
            'periodos' => PeriodoEvaluacion::orderBy('ord_pev')->get(),
            'asignaturas' => Asignatura::orderBy('nom_asi')->get(),
            'estudiantes' => Estudiante::with('persona')->get()->sortBy(fn ($item) => $item->persona?->ape_pat_per),
            'especialidades' => EspecialidadTecnica::orderBy('nom_esp')->get(),
            'metricas' => [
                'promedio' => $reporte['promedio_general'],
                'riesgo' => count($reporte['estudiantes_en_riesgo']),
                'destacados' => count($reporte['estudiantes_destacados']),
                'registros' => $reporte['promedio_general'] > 0 ? $calificaciones->count() : 0,
            ],
            'distribucion' => $reporte['visualizaciones']['distribucion_desempeno'],
            'reporteCompleto' => $reporte,
        ]);
    }
}
