<?php

namespace App\Livewire\Admin;

use App\Models\EspecialidadTecnica;
use App\Support\Academico\EspecialidadTecnicaInteligente;
use Illuminate\Validation\Rule;

class EspecialidadesTecnicas extends CatalogoInstitucional
{
    protected function modelo(): string { return EspecialidadTecnica::class; }
    protected function soporte(): object { return app(EspecialidadTecnicaInteligente::class); }
    protected function clavePrimaria(): string { return 'cod_esp'; }
    protected function campoNombre(): string { return 'nom_esp'; }
    protected function campoEstado(): string { return 'est_esp'; }
    protected function relacionConteo(): ?string { return 'estudiantes'; }
    protected function camposBusqueda(): array { return ['cod_esp', 'nom_esp', 'des_esp']; }
    protected function camposFormulario(): array { return ['nom_esp' => '', 'des_esp' => '', 'est_esp' => 'ACTIVO']; }
    protected function reglas(): array { return ['form.nom_esp' => ['required', 'string', 'min:3', 'max:150'], 'form.des_esp' => ['nullable', 'string', 'max:255'], 'form.est_esp' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])]]; }
    protected function vista(): string { return 'livewire.admin.especialidades-tecnicas'; }
    protected function configuracion(): array
    {
        return [
            'titulo' => 'Especialidades Técnicas',
            'descripcion' => 'Gestiona la oferta técnica BTH y su aporte a la orientación académico-profesional.',
            'tabla' => 'especialidad_tecnica',
            'nombre' => 'nom_esp',
            'descripcion_campo' => 'des_esp',
            'estado' => 'est_esp',
            'relacion' => 'estudiantes_count',
            'relacion_etiqueta' => 'Estudiantes vinculados',
            'columnas' => ['cod_esp' => 'Código', 'nom_esp' => 'Especialidad', 'des_esp' => 'Descripción', 'est_esp' => 'Estado'],
        ];
    }
}
