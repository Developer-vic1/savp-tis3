<?php
namespace App\Livewire\Admin\Concerns;

use App\Support\Comunidad\PerfilEspecialidadDocente;
use Livewire\Attributes\Locked;

trait SeleccionaEspecialidadDocente
{
    #[Locked] public string $perfilDocenteCodigo='';
    public array $seleccionEspecialidad=[];
    public bool $habilitarOtraEspecialidad=false;
    public string $otraEspecialidad='';

    #[\Livewire\Attributes\Computed]
    public function editorEspecialidad(): array
    {
        $servicio=app(PerfilEspecialidadDocente::class);$opciones=$servicio->opciones();$error='';$propuesta='';
        try{$propuesta=$servicio->componer($this->seleccionEspecialidad,$this->habilitarOtraEspecialidad,$this->otraEspecialidad,$opciones);}catch(\Illuminate\Validation\ValidationException $e){$error=collect($e->errors())->flatten()->first();}
        return compact('opciones','propuesta','error');
    }
    protected function prepararSeleccionEspecialidad($docente): void
    {
        $servicio=app(PerfilEspecialidadDocente::class);
        $perfil=$servicio->interpretar((string)$docente->esp_doc,$servicio->opciones());
        $this->perfilDocenteCodigo=$docente->cod_doc;
        $this->seleccionEspecialidad=$perfil['seleccion'];$this->otraEspecialidad=$perfil['otro'];$this->habilitarOtraEspecialidad=$perfil['otro']!=='';
    }
    public function guardarPerfilEspecialidad(): void
    {
        abort_unless($this->modalEditar && $this->perfilDocenteCodigo!=='',422);
        app(PerfilEspecialidadDocente::class)->guardar($this->perfilDocenteCodigo,$this->seleccionEspecialidad,$this->habilitarOtraEspecialidad,$this->otraEspecialidad);
        $this->cerrarModalEditar();$this->dispatch('docente-actualizado');$this->dispatch('success-general',mensaje:'Especialidades profesionales actualizadas.');
    }
}
