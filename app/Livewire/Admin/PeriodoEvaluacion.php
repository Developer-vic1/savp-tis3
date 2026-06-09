<?php

namespace App\Livewire\Admin;

use App\Models\PeriodoEvaluacion as PeriodoModel;
use App\Support\Academico\PeriodoEvaluacionInteligente;
use Illuminate\Validation\Rule;

class PeriodoEvaluacion extends CatalogoInstitucional
{
    protected function modelo(): string { return PeriodoModel::class; }
    protected function soporte(): object { return app(PeriodoEvaluacionInteligente::class); }
    protected function clavePrimaria(): string { return 'cod_pev'; }
    protected function campoNombre(): string { return 'nom_pev'; }
    protected function campoEstado(): string { return 'est_pev'; }
    protected function campoOrden(): string { return 'ord_pev'; }
    protected function relacionConteo(): ?string { return 'calificaciones'; }
    protected function camposBusqueda(): array { return ['cod_pev', 'nom_pev']; }
    protected function camposFormulario(): array { return ['nom_pev' => '', 'ord_pev' => 1, 'est_pev' => 'ACTIVO']; }
    protected function reglas(): array { return ['form.nom_pev' => ['required', 'string', 'min:4', 'max:100'], 'form.ord_pev' => ['required', 'integer', 'min:1', 'max:20'], 'form.est_pev' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])]]; }
    protected function vista(): string { return 'livewire.admin.periodo-evaluacion'; }
    protected function configuracion(): array
    {
        return [
            'titulo' => 'Periodo de Evaluación',
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
