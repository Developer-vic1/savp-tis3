<?php

namespace App\Livewire\Admin;

use App\Models\InstitucionProcedencia as InstitucionModel;
use App\Support\Comunidad\InstitucionProcedenciaInteligente;
use Illuminate\Validation\Rule;

class InstitucionProcedencia extends CatalogoInstitucional
{
    protected function modelo(): string { return InstitucionModel::class; }
    protected function soporte(): object { return app(InstitucionProcedenciaInteligente::class); }
    protected function clavePrimaria(): string { return 'cod_ipe'; }
    protected function campoNombre(): string { return 'nom_ipe'; }
    protected function campoEstado(): string { return 'est_ipe'; }
    protected function relacionConteo(): ?string { return 'estudiantes'; }
    protected function filtrosAdicionales(): array
    {
        return [
            ['campo' => 'tip_ipe', 'etiqueta' => 'Todos los tipos', 'opciones' => InstitucionModel::query()->whereNotNull('tip_ipe')->pluck('tip_ipe')->unique()->sort()->values()],
            ['campo' => 'ciu_ipe', 'etiqueta' => 'Todas las ciudades', 'opciones' => InstitucionModel::query()->whereNotNull('ciu_ipe')->pluck('ciu_ipe')->unique()->sort()->values()],
        ];
    }
    protected function camposBusqueda(): array { return ['cod_ipe', 'nom_ipe', 'tip_ipe', 'ciu_ipe']; }
    protected function camposFormulario(): array { return ['nom_ipe' => '', 'tip_ipe' => 'Pública', 'ciu_ipe' => 'La Paz', 'est_ipe' => 'ACTIVO']; }
    protected function reglas(): array { return ['form.nom_ipe' => ['required', 'string', 'min:5', 'max:150'], 'form.tip_ipe' => ['nullable', 'string', 'max:50'], 'form.ciu_ipe' => ['required', 'string', 'max:100'], 'form.est_ipe' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])]]; }
    protected function vista(): string { return 'livewire.admin.institucion-procedencia'; }
    protected function configuracion(): array
    {
        return [
            'titulo' => 'Institución de Procedencia',
            'descripcion' => 'Administra las unidades educativas de procedencia de la comunidad estudiantil.',
            'tabla' => 'institucion_procedencia',
            'nombre' => 'nom_ipe',
            'tipo' => 'tip_ipe',
            'ciudad' => 'ciu_ipe',
            'estado' => 'est_ipe',
            'relacion' => 'estudiantes_count',
            'relacion_etiqueta' => 'Estudiantes vinculados',
            'columnas' => ['cod_ipe' => 'Código', 'nom_ipe' => 'Institución', 'tip_ipe' => 'Tipo', 'ciu_ipe' => 'Ciudad', 'est_ipe' => 'Estado'],
        ];
    }
}
