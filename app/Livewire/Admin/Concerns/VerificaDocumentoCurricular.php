<?php

namespace App\Livewire\Admin\Concerns;

use App\Services\BitacoraService;
use App\Services\InstitutionalAuthorityService;
use App\Support\Academico\DocumentoCambioCurricular;
use App\Support\Academico\RespaldoCursoInstitucional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

trait VerificaDocumentoCurricular
{
    use WithFileUploads;

    public $documentoCurricular;
    #[Locked] public array $revisionDocumentoCurricular = [];
    #[Locked] public string $directorCurricular = '';
    #[Locked] public int $gestionDocumentoCurricular = 0;
    #[Locked] public string $operacionCurricular = 'crear';
    public bool $firmaSelloComprobados = false;
    public string $canalComprobacion = '';
    public string $motivoCurricular = '';

    #[\Livewire\Attributes\Computed]
    public function incorporacionesCurriculares(): array
    {
        if(!Schema::hasTable('incorporacion_curricular')) return [];
        return \App\Models\Oficial\Academico\IncorporacionCurricular::where('dominio',$this instanceof \App\Livewire\Admin\GestionAsignatura?'asignatura':'especialidad')->where('estado','PROGRAMADA')->orderBy('gestion')->get(['id','nombre','gestion'])->toArray();
    }

    public function aplicarIncorporacionCurricular(string $id): void
    {
        $this->autorizarDocumentoCurricular();
        app(\App\Services\IncorporacionCurricularService::class)->aplicar($id,$this instanceof \App\Livewire\Admin\GestionAsignatura?'asignatura':'especialidad');
        unset($this->incorporacionesCurriculares);
        if($this instanceof \App\Livewire\Admin\GestionAsignatura) unset($this->materias,$this->catalogoPendiente);
    }

    protected function prepararDocumentoCurricular(string $operacion): void
    {
        $this->documentoCurricular = null;
        $this->revisionDocumentoCurricular = [];
        $this->firmaSelloComprobados = false;
        $this->canalComprobacion = '';
        $this->motivoCurricular = '';
        $this->operacionCurricular = $operacion;
        $autoridad = app(InstitutionalAuthorityService::class)->current();
        $this->directorCurricular = $autoridad['name'] ?? '';
        $gestiones = DB::table('gestion_academica')->where('est_gea','ACTIVO')->get();
        $this->gestionDocumentoCurricular = $gestiones->count() === 1 ? (int)$gestiones->first()->ani_gea + ($operacion === 'crear' ? 1 : 0) : 0;
    }

    public function updatedDocumentoCurricular(): void
    {
        $this->revisionDocumentoCurricular = [];
        $this->firmaSelloComprobados = false;
        $this->canalComprobacion = '';
    }

    protected function autorizarDocumentoCurricular(): void
    {
        Gate::authorize($this instanceof \App\Livewire\Admin\GestionAsignatura ? 'Asignaturas' : 'Especialidades_Tecnicas');
        abort_unless(auth()->user()?->hasRole('Administrador'),403);
    }

    public function escanearDocumentoCurricular(): void
    {
        $this->autorizarDocumentoCurricular();
        abort_unless(($this->modalCrear ?? false) || ($this->modalEditar ?? false) || ($this->modalFormulario ?? false),422);
        $this->resetValidation('documentoCurricular');
        $this->revisionDocumentoCurricular = [];
        $this->firmaSelloComprobados = false;
        $this->validate(['documentoCurricular'=>'required|file|mimes:pdf|max:8192']);
        if(!Schema::hasTable('bitacora')) throw ValidationException::withMessages(['documentoCurricular'=>'La bitácora no está disponible. No podemos analizar ni registrar el intento.']);
        try {
            $lectura = app(RespaldoCursoInstitucional::class)->leer($this->documentoCurricular->getRealPath(),'documentoCurricular');
            $anterior = $this->operacionCurricular === 'editar'
                ? ($this instanceof \App\Livewire\Admin\GestionAsignatura ? \App\Models\Oficial\Academico\Asignatura::findOrFail($this->asignaturaSeleccionada)->nom_asi : \App\Models\Oficial\Academico\EspecialidadTecnica::findOrFail($this->seleccionado)->nom_esp) : '';
            $r = DocumentoCambioCurricular::revisar($lectura['texto'],$this->directorCurricular,$this->gestionDocumentoCurricular,$this->operacionCurricular,$anterior,$this instanceof \App\Livewire\Admin\EspecialidadesTecnicas ? 'especialidad' : 'asignatura');
            $comparacion = app(\App\Services\ReferenciaDocumentalService::class)->comparar($this->documentoCurricular->getRealPath());
            $r['comparacion'] = $comparacion;
            $r['reglas']['Firma vigente y sello coinciden visualmente'] = $comparacion['coinciden'] ?? false;
            $r['coherente'] = $r['coherente'] && ($comparacion['coinciden'] ?? false);
            $r['sha256'] = $lectura['sha256']; $r['paginas'] = $lectura['paginas'];
            $this->revisionDocumentoCurricular = $r;
            if(!$r['coherente']) {
                $this->registrarRechazoCurricular($r);
                return;
            }
            $datos = $r['datos'];
            if($this instanceof \App\Livewire\Admin\GestionAsignatura) {
                $propiedad = $this->operacionCurricular === 'crear' ? 'form' : 'formEditar';
                $this->{$propiedad}['nom_asi'] = $datos['nombre'];
                $this->{$propiedad}['sig_asi'] = $datos['sigla'];
                $this->{$propiedad}['hor_asi'] = $datos['horas'];
                $this->operacionCurricular === 'crear' ? $this->interpretarAsignaturaCrear() : $this->interpretarAsignaturaEditar();
                $this->{$propiedad}['sig_asi'] = $datos['sigla'];
                $this->{$propiedad}['hor_asi'] = $datos['horas'];
            } else {
                $this->form['nom_esp'] = $datos['nombre'];
                $this->form['des_esp'] = $datos['fundamento'];
                $this->analizarFormulario();
            }
            $this->motivoCurricular = $datos['fundamento'];
        } catch(ValidationException $e) {
            $this->revisionDocumentoCurricular = ['coherente'=>false,'reglas'=>['PDF legible con texto extraíble'=>false]];
            $this->registrarRechazoCurricular($this->revisionDocumentoCurricular);
            throw $e;
        }
    }

    private function registrarRechazoCurricular(array $revision): void
    {
        BitacoraService::registrar(accion:'INTENTO_CAMBIO_CURRICULAR_RECHAZADO',tabla:'incorporacion_curricular',modulo:'Catálogo académico',
            descripcion:'Lectura documental rechazada: '.implode('; ',array_keys(array_filter($revision['reglas'],fn($v)=>!$v))),
            nivel:'WARNING',resultado:'BLOQUEADO',valoresNuevos:['operacion'=>$this->operacionCurricular,'revision'=>$revision]);
    }

    protected function conservarDocumentoCurricular(array $revision): array
    {
        $ruta = $this->documentoCurricular->storeAs('curricular/ediciones', (string)\Illuminate\Support\Str::uuid().'.pdf', 'local');
        if (!$ruta || !\Illuminate\Support\Facades\Storage::disk('local')->exists($ruta)) throw ValidationException::withMessages(['documentoCurricular'=>'No pudimos conservar el respaldo. No se guardó el cambio.']);
        return $revision + ['ruta_pdf'=>$ruta];
    }

    protected function comprobarDocumentoCurricular(array $datos): array
    {
        $this->autorizarDocumentoCurricular();
        $this->validate(['documentoCurricular'=>'required|file|mimes:pdf|max:8192','firmaSelloComprobados'=>'accepted',
            'canalComprobacion'=>'required|string|min:15|max:300','motivoCurricular'=>'required|string|min:20|max:1500']);
        if(!\App\Support\Academico\ExpedienteParaleloInstitucional::justificacionComprensible($this->motivoCurricular)) throw ValidationException::withMessages(['motivoCurricular'=>'Describe la necesidad real en al menos cinco palabras.']);
        $r = $this->revisionDocumentoCurricular;
        if(!($r['coherente']??false) || !hash_equals($r['sha256']??'',hash_file('sha256',$this->documentoCurricular->getRealPath()))) throw ValidationException::withMessages(['documentoCurricular'=>'Escanea el documento actual antes de continuar.']);
        // La autoridad y gestión se vuelven a consultar al guardar.
        $autoridad = app(InstitutionalAuthorityService::class)->current();
        $referencias = app(\App\Services\ReferenciaDocumentalService::class)->actuales();
        foreach (['firma', 'sello'] as $tipo) {
            if (($referencias[$tipo]->id ?? null) !== ($r['comparacion']['referencias'][$tipo]['id'] ?? null)) throw ValidationException::withMessages(['documentoCurricular'=>'Cambió la firma vigente o el sello. Vuelve a escanear el documento.']);
        }
        $gestiones = DB::table('gestion_academica')->where('est_gea','ACTIVO')->get();
        if(($autoridad['name']??'') !== $this->directorCurricular || $gestiones->count() !== 1 || (int)$gestiones->first()->ani_gea + ($this->operacionCurricular==='crear'?1:0) !== $this->gestionDocumentoCurricular) throw ValidationException::withMessages(['documentoCurricular'=>'Cambió la autoridad o la gestión activa. Abre el formulario y vuelve a escanear.']);
        if(($datos['nom_asi']??$datos['nom_esp']??'') !== $r['datos']['nombre'] || (isset($datos['sig_asi']) && (mb_strtoupper($datos['sig_asi'])!==$r['datos']['sigla'] || (string)$datos['hor_asi'] !== $r['datos']['horas']))) throw ValidationException::withMessages(['documentoCurricular'=>'Los datos difieren de la autorización leída. Corrige el documento y vuelve a escanear.']);
        return $r + ['canal_comprobacion'=>trim($this->canalComprobacion),'firma_sello_comprobados_por'=>auth()->id(),'motivo'=>trim($this->motivoCurricular)];
    }
}
