<?php

namespace App\Livewire\Admin;

use App\Models\TipoVinculacionEstudiante as TipoModel;
use App\Support\Comunidad\TipoVinculacionEstudianteInteligente;
use Illuminate\Validation\Rule;

class TipoVinculacionEstudiante extends CatalogoInstitucional
{
    protected function modelo(): string { return TipoModel::class; }
    protected function soporte(): object { return app(TipoVinculacionEstudianteInteligente::class); }
    protected function clavePrimaria(): string { return 'cod_tve'; }
    protected function campoNombre(): string { return 'nom_tve'; }
    protected function campoEstado(): string { return 'est_tve'; }
    protected function relacionConteo(): ?string { return 'estudiantes'; }
    protected function camposBusqueda(): array { return ['cod_tve', 'nom_tve', 'des_tve']; }
    protected function camposFormulario(): array { return ['nom_tve' => '', 'des_tve' => '', 'est_tve' => 'ACTIVO']; }
    protected function reglas(): array { return ['form.nom_tve' => ['required', 'string', 'min:3', 'max:100'], 'form.des_tve' => ['nullable', 'string', 'max:255'], 'form.est_tve' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])]]; }
    protected function vista(): string { return 'livewire.admin.tipo-vinculacion-estudiante'; }
    protected function configuracion(): array
    {
        return [
            'titulo' => 'Tipo de Vinculación del Estudiante',
            'descripcion' => 'Define condiciones institucionales claras para el vínculo académico del estudiante.',
            'tabla' => 'tipo_vinculacion_estudiante',
            'nombre' => 'nom_tve',
            'descripcion_campo' => 'des_tve',
            'estado' => 'est_tve',
            'relacion' => 'estudiantes_count',
            'relacion_etiqueta' => 'Estudiantes vinculados',
            'columnas' => ['cod_tve' => 'Código', 'nom_tve' => 'Tipo', 'des_tve' => 'Descripción', 'est_tve' => 'Estado'],
        ];
    }
}
