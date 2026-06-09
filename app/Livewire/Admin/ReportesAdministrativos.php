<?php

namespace App\Livewire\Admin;

use App\Models\Asignatura;
use App\Models\Bitacora;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\InscripcionEstudiante;
use App\Models\InstitucionProcedencia;
use App\Models\Paralelo;
use App\Models\PeriodoEvaluacion;
use App\Models\TipoVinculacionEstudiante;
use App\Models\Turno;
use App\Models\User;
use App\Support\Reportes\ReporteAdministrativoInteligente;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ReportesAdministrativos extends Component
{
    public string $search = '';
    public string $moduloFiltro = '';

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->moduloFiltro = '';
    }

    public function render()
    {
        $metricas = [
            'Usuarios activos' => User::where('est_usu', 'ACTIVO')->count(),
            'Estudiantes registrados' => Estudiante::count(),
            'Docentes registrados' => Docente::count(),
            'Inscripciones' => InscripcionEstudiante::count(),
            'Asignaturas' => Asignatura::count(),
            'Especialidades técnicas' => EspecialidadTecnica::count(),
            'Cursos' => Curso::count(),
            'Paralelos' => Paralelo::count(),
            'Turnos' => Turno::count(),
            'Instituciones de procedencia' => InstitucionProcedencia::count(),
            'Tipos de vinculación' => TipoVinculacionEstudiante::count(),
            'Periodos de evaluación' => PeriodoEvaluacion::count(),
        ];

        $actividad = Bitacora::query()
            ->with('usuario.persona')
            ->when($this->search !== '', function (Builder $query) {
                $search = trim($this->search);
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('acc_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('des_bit', 'ILIKE', "%{$search}%")
                        ->orWhere('nom_reg_bit', 'ILIKE', "%{$search}%");
                });
            })
            ->when($this->moduloFiltro !== '', fn (Builder $query) => $query->where('mod_bit', $this->moduloFiltro))
            ->orderByDesc('fec_bit')
            ->limit(20)
            ->get();

        return view('livewire.admin.reportes-administrativos', [
            'metricas' => $metricas,
            'diagnostico' => app(ReporteAdministrativoInteligente::class)->diagnostico($metricas),
            'actividad' => $actividad,
            'modulos' => Bitacora::query()->whereNotNull('mod_bit')->pluck('mod_bit')->unique()->sort()->values(),
            'estadosInscripcion' => InscripcionEstudiante::all()->countBy('est_ins')->sortDesc(),
        ]);
    }
}
