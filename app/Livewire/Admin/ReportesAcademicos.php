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

        $rendimientoAsignatura = $calificaciones
            ->groupBy('cod_asi')
            ->map(fn ($items) => [
                'nombre' => $items->first()->asignatura?->nom_asi ?? 'Sin asignatura',
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
                'riesgo' => $items->where('desempeno_calculado', 'En riesgo')->count(),
            ])
            ->sortByDesc('promedio')
            ->values();

        $rendimientoPeriodo = $calificaciones
            ->groupBy('cod_pev')
            ->map(fn ($items) => [
                'nombre' => $items->first()->periodoEvaluacion?->nom_pev ?? 'Sin periodo',
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
            ])
            ->sortByDesc('promedio')
            ->values();

        $totalEstudiantes = max(1, $calificaciones->pluck('cod_est')->unique()->count());
        $orientaciones = $calificaciones
            ->filter(fn ($item) => $item->estudiante?->especialidad)
            ->groupBy(fn ($item) => $item->estudiante->especialidad->nom_esp)
            ->map(function ($items, $especialidad) use ($soporte, $totalEstudiantes) {
                [$area, $carreras] = $soporte->orientacionPorEspecialidad($especialidad);
                $cantidad = $items->pluck('cod_est')->unique()->count();

                return [
                    'especialidad' => $especialidad,
                    'area' => $area,
                    'carreras' => $carreras,
                    'estudiantes' => $cantidad,
                    'porcentaje' => round(($cantidad / $totalEstudiantes) * 100, 1),
                    'promedio' => round((float) $items->avg('not_cal'), 2),
                    'explicacion' => "La orientación se calcula con la especialidad BTH y el rendimiento académico disponible.",
                ];
            })
            ->sortByDesc('estudiantes')
            ->values();

        return view('livewire.admin.reportes-academicos', [
            'calificaciones' => $calificaciones,
            'rendimientoAsignatura' => $rendimientoAsignatura,
            'rendimientoPeriodo' => $rendimientoPeriodo,
            'orientaciones' => $orientaciones,
            'periodos' => PeriodoEvaluacion::orderBy('ord_pev')->get(),
            'asignaturas' => Asignatura::orderBy('nom_asi')->get(),
            'estudiantes' => Estudiante::with('persona')->get()->sortBy(fn ($item) => $item->persona?->ape_pat_per),
            'especialidades' => EspecialidadTecnica::orderBy('nom_esp')->get(),
            'metricas' => [
                'promedio' => round((float) $calificaciones->avg('not_cal'), 2),
                'riesgo' => $calificaciones->where('desempeno_calculado', 'En riesgo')->count(),
                'destacados' => $calificaciones->where('desempeno_calculado', 'Destacado')->count(),
                'registros' => $calificaciones->count(),
            ],
            'distribucion' => $calificaciones->countBy('desempeno_calculado'),
        ]);
    }
}
