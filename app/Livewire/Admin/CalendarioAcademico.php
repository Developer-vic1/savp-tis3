<?php

namespace App\Livewire\Admin;

use App\Models\CalendarioEvento;
use App\Models\GestionAcademica;
use App\Services\CalendarioAcademicoService;
use App\Support\Academico\CalendarioAcademicoInteligente;
use App\Support\Academico\InscripcionAcademica;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CalendarioAcademico extends Component
{
    use WithPagination;

    public string $gestion = '';

    public string $estado = '';

    public bool $modalEvento = false;

    #[Locked]
    public ?string $codigo = null;

    #[Locked]
    public ?string $version = null;

    public array $form = [];

    public array $impacto = [];

    #[Locked]
    public ?string $versionImpacto = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', CalendarioEvento::class);
    }

    public function updatedGestion(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function abrir(?string $codigo = null): void
    {
        $this->resetValidation();
        $evento = $codigo ? CalendarioEvento::findOrFail($codigo) : null;
        $evento ? Gate::authorize('view', $evento) : Gate::authorize('create', CalendarioEvento::class);
        $this->codigo = $codigo;
        $this->version = $evento?->getRawOriginal('updated_at');
        $this->form = $evento ? $evento->getAttributes() : ['cod_gea' => $this->gestion, 'nom_cae' => '', 'tip_cae' => 'FERIADO', 'est_cae' => 'PREALERTA', 'efe_cae' => 'INFORMATIVO', 'fii_cae' => today()->toDateString(), 'ffi_cae' => today()->toDateString(), 'mot_cae' => ''];
        $this->impacto = [];
        $this->versionImpacto = null;
        $this->modalEvento = true;
    }

    public function analizarImpacto(): void
    {
        Gate::authorize('viewAny', CalendarioEvento::class);
        $this->validate(['form.cod_gea' => 'required|exists:gestion_academica,cod_gea', 'form.fii_cae' => 'required|date_format:Y-m-d', 'form.ffi_cae' => 'required|date_format:Y-m-d|after_or_equal:form.fii_cae']);
        $this->impacto = app(CalendarioAcademicoInteligente::class)->analizarImpactoEvento($this->form);
        $this->versionImpacto = hash('sha256', json_encode($this->form));
    }

    public function guardar(): void
    {
        if ($this->versionImpacto !== hash('sha256', json_encode($this->form))) {
            $this->analizarImpacto();
            $this->addError('evento', 'Revise el impacto calculado y vuelva a confirmar.');

            return;
        }
        $servicio = app(CalendarioAcademicoService::class);
        $this->codigo ? $servicio->modificarEvento($this->codigo, $this->form, $this->version) : $servicio->registrarEvento($this->form);
        $this->modalEvento = false;
        $this->dispatch('swal:success', title: 'Evento guardado', text: 'Se actualizó el calendario académico.');
    }

    public function render()
    {
        Gate::authorize('viewAny', CalendarioEvento::class);

        return view('livewire.admin.calendario-academico', [
            'eventos' => CalendarioEvento::when($this->gestion, fn ($q) => $q->where('cod_gea', $this->gestion))->when($this->estado, fn ($q) => $q->where('est_cae', $this->estado))->orderByDesc('fii_cae')->paginate(15),
            'gestiones' => GestionAcademica::orderByDesc('ani_gea')->get(),
            'tipos' => CalendarioAcademicoInteligente::TIPOS, 'efectos' => CalendarioAcademicoInteligente::EFECTOS, 'estados' => CalendarioAcademicoInteligente::ESTADOS,
            'catalogos' => app(InscripcionAcademica::class)->catalogos(),
        ])->extends('layouts.app')->section('content');
    }
}
