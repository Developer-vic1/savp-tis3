<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\Asignatura;
use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\EspecialidadTecnica;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\InstitucionProcedencia;
use App\Models\Oficial\Academico\Paralelo;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Models\Oficial\Academico\TipoVinculacionEstudiante;
use App\Models\Oficial\Academico\Turno;
use App\Models\Oficial\Sistema\User;
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
        // El resumen necesita conteos, no todos los modelos y sus atributos de inscripción.
        $estadosInscripcion = InscripcionEstudiante::query()->toBase()
            ->selectRaw("COALESCE(est_ins, '') AS estado, COUNT(*) AS cantidad")
            ->groupByRaw("COALESCE(est_ins, '')")->pluck('cantidad', 'estado')
            ->map(fn ($cantidad) => (int) $cantidad)->sortDesc();
        $metricas = [
            'Usuarios activos' => User::where('est_usu', 'ACTIVO')->count(),
            'Estudiantes registrados' => Estudiante::count(),
            'Docentes registrados' => Docente::count(),
            'Inscripciones' => $estadosInscripcion->sum(),
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
            // La tabla no presenta usuarios ni los JSON de valores anteriores/nuevos.
            ->select(['cod_bit', 'fec_bit', 'mod_bit', 'acc_bit', 'nom_reg_bit', 'reg_bit', 'res_bit'])
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
            'modulos' => Bitacora::query()->whereNotNull('mod_bit')->distinct()->pluck('mod_bit')->sort()->values(),
            'estadosInscripcion' => $estadosInscripcion,
        ]);
    }
}
