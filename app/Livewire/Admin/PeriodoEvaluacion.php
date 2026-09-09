<?php

namespace App\Livewire\Admin;

use App\Models\GestionAcademica;
use App\Models\PeriodoEvaluacion as PeriodoModel;
use App\Services\CalificacionService;
use App\Support\Academico\PeriodoEvaluacionInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class PeriodoEvaluacion extends CatalogoInstitucional
{
    public bool $modalEstadoPeriodo = false;

    #[Locked]
    public ?string $codigoEstadoPeriodo = null;

    #[Locked]
    public ?string $versionEstadoPeriodo = null;

    public string $destinoPeriodo = '';

    public string $motivoPeriodo = '';

    public array $opcionesEstadoPeriodo = [];

    public function cambiarEstado(string $codigo): void
    {
        Gate::authorize('Gestion_Academica');
        abort_unless(auth()->user()?->hasAnyRole(['Administrador', 'Director']), 403);
        $periodo = PeriodoModel::findOrFail($codigo);
        $this->codigoEstadoPeriodo = $codigo;
        $this->versionEstadoPeriodo = $periodo->getRawOriginal('updated_at');
        $this->opcionesEstadoPeriodo = PeriodoEvaluacionInteligente::TRANSICIONES[$periodo->est_pev] ?? [];
        $this->destinoPeriodo = $this->opcionesEstadoPeriodo[0] ?? '';
        $this->motivoPeriodo = '';
        $this->resetValidation();
        $this->modalEstadoPeriodo = true;
    }

    public function aplicarEstadoPeriodo(): void
    {
        app(CalificacionService::class)->cambiarEstadoPeriodo($this->codigoEstadoPeriodo, $this->destinoPeriodo, $this->motivoPeriodo, $this->versionEstadoPeriodo);
        $this->modalEstadoPeriodo = false;
        $this->dispatch('swal:success', title: 'Periodo actualizado', text: 'Se registró el cambio y su motivo.');
    }

    public function guardar(): void
    {
        Gate::authorize('Gestion_Academica');
        $this->validate([
            'form.cod_gea' => 'required|exists:gestion_academica,cod_gea',
            'form.fii_pev' => 'required|date_format:Y-m-d',
            'form.ffi_pev' => 'required|date_format:Y-m-d|after_or_equal:form.fii_pev',
        ]);
        DB::transaction(function () {
            $gestion = GestionAcademica::whereKey($this->form['cod_gea'])->lockForUpdate()->firstOrFail();
            if (! in_array($gestion->est_gea, ['ACTIVA', 'ACTIVO', 'PLANIFICADA', 'PLANIFICADO'], true)) {
                throw ValidationException::withMessages(['form.cod_gea' => 'La gestión no permite modificar periodos.']);
            }
            if ($this->seleccionado) {
                $actual = PeriodoModel::whereKey($this->seleccionado)->lockForUpdate()->firstOrFail();
                if (in_array($actual->est_pev, ['CERRADO', 'EN_CIERRE'], true)) {
                    throw ValidationException::withMessages(['form.est_pev' => 'El periodo está cerrado o en cierre; requiere una reapertura autorizada.']);
                }
            }
            if (($gestion->fii_gea && $this->form['fii_pev'] < substr((string) $gestion->fii_gea, 0, 10)) || ($gestion->ffi_gea && $this->form['ffi_pev'] > substr((string) $gestion->ffi_gea, 0, 10))) {
                throw ValidationException::withMessages(['form.fii_pev' => 'El periodo debe pertenecer al rango de la gestión.']);
            }
            parent::guardar();
        });
    }

    protected function modelo(): string
    {
        return PeriodoModel::class;
    }

    protected function soporte(): object
    {
        return app(PeriodoEvaluacionInteligente::class);
    }

    protected function clavePrimaria(): string
    {
        return 'cod_pev';
    }

    protected function campoNombre(): string
    {
        return 'nom_pev';
    }

    protected function campoEstado(): string
    {
        return 'est_pev';
    }

    protected function campoOrden(): string
    {
        return 'ord_pev';
    }

    protected function relacionConteo(): ?string
    {
        return 'calificaciones';
    }

    protected function camposBusqueda(): array
    {
        return ['cod_pev', 'nom_pev'];
    }

    protected function camposFormulario(): array
    {
        return ['nom_pev' => '', 'ord_pev' => 1, 'est_pev' => 'ACTIVO', 'cod_gea' => '', 'fii_pev' => '', 'ffi_pev' => ''];
    }

    protected function reglas(): array
    {
        return ['form.nom_pev' => ['required', 'string', 'min:4', 'max:100'], 'form.ord_pev' => ['required', 'integer', 'min:1', 'max:20'], 'form.est_pev' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])]];
    }

    protected function vista(): string
    {
        return 'livewire.admin.periodo-evaluacion';
    }

    protected function configuracion(): array
    {
        return [
            'titulo' => 'Periodo de Evaluación',
            'gestion_estado_periodo' => true,
            'campos_adicionales' => [
                'cod_gea' => ['etiqueta' => 'Gestión', 'tipo' => 'select', 'opciones' => GestionAcademica::orderByDesc('ani_gea')->pluck('ani_gea', 'cod_gea')->all()],
                'fii_pev' => ['etiqueta' => 'Inicio', 'tipo' => 'date'],
                'ffi_pev' => ['etiqueta' => 'Fin', 'tipo' => 'date'],
            ],
            'descripcion' => 'Organiza los periodos evaluativos disponibles para el registro de calificaciones.',
            'tabla' => 'periodo_evaluacion',
            'nombre' => 'nom_pev',
            'orden' => 'ord_pev',
            'estado' => 'est_pev',
            'relacion' => 'calificaciones_count',
            'relacion_etiqueta' => 'Calificaciones vinculadas',
            'columnas' => ['cod_pev' => 'Código', 'nom_pev' => 'Periodo', 'ord_pev' => 'Orden', 'est_pev' => 'Estado'],
        ];
    }
}
