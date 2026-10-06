<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\EspecialidadTecnica;
use App\Support\Academico\EspecialidadTecnicaInteligente;
use Illuminate\Validation\Rule;

class EspecialidadesTecnicas extends CatalogoInstitucional
{
    use \App\Livewire\Admin\Concerns\VerificaDocumentoCurricular;
    public string $tipoOferta = '';
    public string $vinculacion = '';

    public function updatedTipoOferta(): void { $this->resetPage(); }
    public function updatedVinculacion(): void { $this->resetPage(); }
    public function limpiarFiltros(): void
    {
        parent::limpiarFiltros();
        $this->tipoOferta = '';
        $this->vinculacion = '';
    }

    protected function consultaBase(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::consultaBase()->withCount('planesEspecialidad');
        if ($this->tipoOferta !== '') {
            $codigos = $this->distribucionEspecialidades->filter(fn ($e) => (($e->clasificacion['reconocida'] ?? false) ? 'tecnica' : 'revisar') === $this->tipoOferta)->pluck('cod_esp');
            $query->whereIn('cod_esp', $codigos);
        }
        if ($this->vinculacion === 'con') $query->has('estudiantes');
        if ($this->vinculacion === 'sin') $query->doesntHave('estudiantes');
        return $query;
    }

    #[\Livewire\Attributes\Computed]
    public function distribucionEspecialidades()
    {
        return EspecialidadTecnica::query()
            ->withCount('estudiantes')
            ->orderByDesc('estudiantes_count')
            ->orderBy('nom_esp')
            ->get(['cod_esp', 'nom_esp', 'est_esp'])
            ->each(function ($especialidad) {
                $especialidad->setAttribute('clasificacion', app(EspecialidadTecnicaInteligente::class)->orientacion($especialidad->nom_esp));
            });
    }

    public function updatedPerPage(): void
    {
        if (!in_array($this->perPage, [10,20,50], true)) $this->perPage = 10;
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        $this->autorizarDocumentoCurricular();
        parent::abrirCrear();
        $this->prepararDocumentoCurricular('crear');
    }
    public function abrirEditar(string $codigo): void
    {
        $this->autorizarDocumentoCurricular();
        parent::abrirEditar($codigo);
        $this->prepararDocumentoCurricular('editar');
    }
    public function cambiarEstado(string $codigo): void
    {
        $this->autorizarDocumentoCurricular();
        parent::cambiarEstado($codigo);
    }
    public function guardar(): void
    {
        $this->autorizarDocumentoCurricular();
        $revision=$this->comprobarDocumentoCurricular($this->form);
        $this->analizarFormulario();
        if(!($this->analisis['puede_guardar']??false)){
            $this->addError('form.nom_esp',implode(' ',$this->analisis['bloqueos']));
            return;
        }
        $this->validate($this->reglas());
        if(!$this->editando){
            app(\App\Services\IncorporacionCurricularService::class)->programar('especialidad',$this->form,$revision,$this->documentoCurricular);
        }else{
            $revision = $this->conservarDocumentoCurricular($revision);
            try {
            \Illuminate\Support\Facades\DB::transaction(function()use($revision){
                if(!\Illuminate\Support\Facades\Schema::hasTable('bitacora')) throw \Illuminate\Validation\ValidationException::withMessages(['documentoCurricular'=>'La bitácora no está disponible.']);
                $r=EspecialidadTecnica::lockForUpdate()->findOrFail($this->seleccionado);
                $antes=$r->toArray();
                // No se cambia el estado ni se reemplaza la identidad de una oferta con historia.
                if(!\App\Support\Academico\AsignaturaInteligente::esCorreccionMenor($r->nom_esp,$this->form['nom_esp']) && ($r->estudiantes()->exists() || \Illuminate\Support\Facades\DB::table('plan_especialidad')->where('cod_esp',$r->cod_esp)->exists())) throw \Illuminate\Validation\ValidationException::withMessages(['form.nom_esp'=>'La especialidad tiene trayectoria académica. Conserva su identidad; registra una incorporación diferente.']);
                $r->update(['nom_esp'=>$this->form['nom_esp'],'des_esp'=>$this->form['des_esp']]);
                \App\Services\BitacoraService::registrar(accion:'EDITAR_ESPECIALIDAD_DOCUMENTADA',tabla:'especialidad_tecnica',registro:$r->getKey(),modulo:'Especialidades técnicas',nombreRegistro:$r->nom_esp,descripcion:$this->motivoCurricular,nivel:'SUCCESS',valoresAnteriores:$antes,valoresNuevos:['especialidad'=>$r->fresh()->toArray(),'documento'=>$revision]);
            });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($revision['ruta_pdf']);
                throw $e;
            }
        }
        $this->cerrarFormulario();
        $this->dispatch('swal:success',title:'Proceso completado',text:$this->editando?'Cambio documentado y guardado.':'Incorporación programada para la siguiente gestión; aviso publicado.');
    }
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
            'documentado' => true,
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
