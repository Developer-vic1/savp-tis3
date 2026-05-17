<?php

namespace App\Livewire\Admin;

use App\Models\Persona;
use App\Services\BitacoraService;
use App\Support\Personas\PersonaInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GestionPersonas extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected string $paginationTheme = 'tailwind';

    // ============================================================
    // FILTROS
    // ============================================================

    public string $search = '';
    public string $genero = '';
    public string $estado = '';
    public string $cuentaUsuario = '';
    public string $direccion = '';
    public int $perPage = 10;

    // ============================================================
    // MODALES
    // ============================================================

    public bool $modalCrear = false;
    public bool $modalVer = false;
    public bool $modalEditar = false;

    public ?Persona $personaDetalle = null;

    // ============================================================
    // ARCHIVOS
    // ============================================================

    public $foto = null;
    public $fotoEditar = null;

    // ============================================================
    // INTELIGENCIA EN TIEMPO REAL
    // ============================================================

    public array $analisisPersona = [];
    public array $analisisPersonaEditar = [];

    public bool $direccionManualCrear = false;
    public bool $direccionManualEditar = false;

    public string $modoDireccionCrear = 'inteligente';
    public string $modoDireccionEditar = 'inteligente';

    // ============================================================
    // FORMULARIOS
    // ============================================================

    public array $form = [
        'nom_per' => '',
        'ape_pat_per' => '',
        'ape_mat_per' => '',
        'ci_per' => '',
        'com_per' => '',
        'exp_per' => '',
        'fec_nac_per' => '',
        'gen_per' => '',
        'tel_per' => '',
        'ema_per' => '',

        'dir_per' => '',
        'zona_per' => '',
        'ave_per' => '',
        'cal_per' => '',
        'num_per' => '',
        'ref_per' => '',
        'ciu_per' => '',
        'mun_per' => '',
        'dep_per' => '',

        'fot_per' => null,
        'est_per' => 1,
    ];

    public array $formEditar = [
        'cod_per' => '',
        'nom_per' => '',
        'ape_pat_per' => '',
        'ape_mat_per' => '',
        'ci_per' => '',
        'com_per' => '',
        'exp_per' => '',
        'fec_nac_per' => '',
        'gen_per' => '',
        'tel_per' => '',
        'ema_per' => '',

        'dir_per' => '',
        'zona_per' => '',
        'ave_per' => '',
        'cal_per' => '',
        'num_per' => '',
        'ref_per' => '',
        'ciu_per' => '',
        'mun_per' => '',
        'dep_per' => '',

        'fot_per' => null,
        'est_per' => 1,
    ];

    // ============================================================
    // MENSAJES
    // ============================================================

    protected array $messages = [
        'form.nom_per.required' => 'El nombre es obligatorio.',
        'form.nom_per.min' => 'El nombre debe tener al menos 2 caracteres.',
        'form.nom_per.max' => 'El nombre no debe superar los 100 caracteres.',

        'form.ape_pat_per.required' => 'El apellido paterno es obligatorio.',
        'form.ape_pat_per.min' => 'El apellido paterno debe tener al menos 2 caracteres.',
        'form.ape_pat_per.max' => 'El apellido paterno no debe superar los 100 caracteres.',

        'form.ape_mat_per.max' => 'El apellido materno no debe superar los 100 caracteres.',

        'form.ci_per.required' => 'El CI es obligatorio.',
        'form.ci_per.regex' => 'El CI debe contener solo números y tener entre 4 y 12 dígitos.',
        'form.ci_per.unique' => 'Ese CI ya está registrado.',

        'form.com_per.max' => 'El complemento no debe superar los 10 caracteres.',
        'form.com_per.regex' => 'El complemento solo puede contener letras, números o guion.',

        'form.exp_per.required' => 'Debes seleccionar el lugar de expedición.',
        'form.exp_per.in' => 'La expedición seleccionada no es válida.',

        'form.fec_nac_per.required' => 'La fecha de nacimiento es obligatoria.',
        'form.fec_nac_per.date' => 'La fecha de nacimiento no es válida.',
        'form.fec_nac_per.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'form.fec_nac_per.after_or_equal' => 'La fecha de nacimiento no es coherente.',

        'form.gen_per.required' => 'Debes seleccionar el género.',
        'form.gen_per.in' => 'El género seleccionado no es válido.',

        'form.tel_per.regex' => 'El teléfono solo puede contener números, espacios, guiones o +.',
        'form.tel_per.max' => 'El teléfono no debe superar los 30 caracteres.',

        'form.ema_per.email' => 'Debes ingresar un correo válido.',
        'form.ema_per.max' => 'El correo no debe superar los 150 caracteres.',
        'form.ema_per.unique' => 'Ese correo ya está registrado.',

        'form.dir_per.max' => 'La dirección completa no debe superar los 255 caracteres.',
        'form.zona_per.max' => 'La zona no debe superar los 100 caracteres.',
        'form.ave_per.max' => 'La avenida no debe superar los 120 caracteres.',
        'form.cal_per.max' => 'La calle no debe superar los 120 caracteres.',
        'form.num_per.max' => 'El número no debe superar los 30 caracteres.',
        'form.ref_per.max' => 'La referencia no debe superar los 255 caracteres.',
        'form.ciu_per.max' => 'La ciudad no debe superar los 100 caracteres.',
        'form.mun_per.max' => 'El municipio no debe superar los 100 caracteres.',
        'form.dep_per.max' => 'El departamento no debe superar los 100 caracteres.',

        'form.est_per.required' => 'Debes seleccionar el estado.',
        'form.est_per.boolean' => 'El estado seleccionado no es válido.',

        'foto.image' => 'El archivo debe ser una imagen.',
        'foto.max' => 'La imagen no debe superar los 2 MB.',

        'formEditar.cod_per.required' => 'No se pudo identificar a la persona.',
        'formEditar.cod_per.exists' => 'La persona seleccionada no existe.',

        'formEditar.nom_per.required' => 'El nombre es obligatorio.',
        'formEditar.nom_per.min' => 'El nombre debe tener al menos 2 caracteres.',
        'formEditar.nom_per.max' => 'El nombre no debe superar los 100 caracteres.',

        'formEditar.ape_pat_per.required' => 'El apellido paterno es obligatorio.',
        'formEditar.ape_pat_per.min' => 'El apellido paterno debe tener al menos 2 caracteres.',
        'formEditar.ape_pat_per.max' => 'El apellido paterno no debe superar los 100 caracteres.',

        'formEditar.ape_mat_per.max' => 'El apellido materno no debe superar los 100 caracteres.',

        'formEditar.ci_per.required' => 'El CI es obligatorio.',
        'formEditar.ci_per.regex' => 'El CI debe contener solo números y tener entre 4 y 12 dígitos.',
        'formEditar.ci_per.unique' => 'Ese CI ya está registrado por otra persona.',

        'formEditar.com_per.max' => 'El complemento no debe superar los 10 caracteres.',
        'formEditar.com_per.regex' => 'El complemento solo puede contener letras, números o guion.',

        'formEditar.exp_per.required' => 'Debes seleccionar el lugar de expedición.',
        'formEditar.exp_per.in' => 'La expedición seleccionada no es válida.',

        'formEditar.fec_nac_per.required' => 'La fecha de nacimiento es obligatoria.',
        'formEditar.fec_nac_per.date' => 'La fecha de nacimiento no es válida.',
        'formEditar.fec_nac_per.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'formEditar.fec_nac_per.after_or_equal' => 'La fecha de nacimiento no es coherente.',

        'formEditar.gen_per.required' => 'Debes seleccionar el género.',
        'formEditar.gen_per.in' => 'El género seleccionado no es válido.',

        'formEditar.tel_per.regex' => 'El teléfono solo puede contener números, espacios, guiones o +.',
        'formEditar.tel_per.max' => 'El teléfono no debe superar los 30 caracteres.',

        'formEditar.ema_per.email' => 'Debes ingresar un correo válido.',
        'formEditar.ema_per.max' => 'El correo no debe superar los 150 caracteres.',
        'formEditar.ema_per.unique' => 'Ese correo ya está registrado por otra persona.',

        'formEditar.dir_per.max' => 'La dirección completa no debe superar los 255 caracteres.',
        'formEditar.zona_per.max' => 'La zona no debe superar los 100 caracteres.',
        'formEditar.ave_per.max' => 'La avenida no debe superar los 120 caracteres.',
        'formEditar.cal_per.max' => 'La calle no debe superar los 120 caracteres.',
        'formEditar.num_per.max' => 'El número no debe superar los 30 caracteres.',
        'formEditar.ref_per.max' => 'La referencia no debe superar los 255 caracteres.',
        'formEditar.ciu_per.max' => 'La ciudad no debe superar los 100 caracteres.',
        'formEditar.mun_per.max' => 'El municipio no debe superar los 100 caracteres.',
        'formEditar.dep_per.max' => 'El departamento no debe superar los 100 caracteres.',

        'formEditar.est_per.required' => 'Debes seleccionar el estado.',
        'formEditar.est_per.boolean' => 'El estado seleccionado no es válido.',

        'fotoEditar.image' => 'El archivo debe ser una imagen.',
        'fotoEditar.max' => 'La imagen no debe superar los 2 MB.',
    ];

    // ============================================================
    // CICLO DE VIDA
    // ============================================================

    public function mount(): void
    {
        $this->analizarFormularioCrear();
    }

    public function render()
    {
        $personas = $this->personasQuery()->paginate($this->perPage);

        return view('livewire.admin.gestion-personas', [
            'personas' => $personas,
            'totalPersonas' => $this->totalPersonas,
            'totalActivas' => $this->totalActivas,
            'totalInactivas' => $this->totalInactivas,
            'totalConUsuario' => $this->totalConUsuario,
            'totalSinUsuario' => $this->totalSinUsuario,
            'datosGraficos' => $this->datosGraficos,
            'analisisPersona' => $this->analisisPersona,
            'analisisPersonaEditar' => $this->analisisPersonaEditar,
        ]);
    }

    // ============================================================
    // FILTROS
    // ============================================================

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->actualizarGraficos();
    }

    public function updatingGenero(): void
    {
        $this->resetPage();
    }

    public function updatedGenero(): void
    {
        $this->actualizarGraficos();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->actualizarGraficos();
    }

    public function updatingCuentaUsuario(): void
    {
        $this->resetPage();
    }

    public function updatedCuentaUsuario(): void
    {
        $this->actualizarGraficos();
    }

    public function updatingDireccion(): void
    {
        $this->resetPage();
    }

    public function updatedDireccion(): void
    {
        $this->actualizarGraficos();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'genero',
            'estado',
            'cuentaUsuario',
            'direccion',
        ]);

        $this->resetPage();
        $this->actualizarGraficos();
    }

    // ============================================================
    // REACTIVIDAD CREAR
    // ============================================================

    public function updatedFormNomPer(): void
    {
        $this->form['nom_per'] = $this->normalizarNombre($this->form['nom_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormApePatPer(): void
    {
        $this->form['ape_pat_per'] = $this->normalizarNombre($this->form['ape_pat_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormApeMatPer(): void
    {
        $this->form['ape_mat_per'] = $this->normalizarNombre($this->form['ape_mat_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormCiPer(): void
    {
        $this->form['ci_per'] = $this->normalizarCi($this->form['ci_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormComPer(): void
    {
        $this->form['com_per'] = $this->normalizarMayuscula($this->form['com_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormExpPer(): void
    {
        $this->form['exp_per'] = $this->normalizarExpedido($this->form['exp_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormFecNacPer(): void
    {
        $this->analizarFormularioCrear();
    }

    public function updatedFormGenPer(): void
    {
        $this->form['gen_per'] = $this->normalizarGenero($this->form['gen_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormTelPer(): void
    {
        $this->form['tel_per'] = $this->normalizarTelefono($this->form['tel_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormEmaPer(): void
    {
        $this->form['ema_per'] = $this->normalizarCorreo($this->form['ema_per']);
        $this->analizarFormularioCrear();
    }

    public function updatedFormDirPer(): void
    {
        $this->form['dir_per'] = $this->limpiarTexto($this->form['dir_per']);

        if ($this->modoDireccionCrear === 'inteligente' && ! $this->direccionManualCrear) {
            $this->aplicarDireccionInteligenteCrear();
        }

        $this->analizarFormularioCrear();
    }

    public function updatedFormZonaPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['zona_per'] = $this->normalizarTitulo($this->form['zona_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormAvePer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['ave_per'] = $this->normalizarTitulo($this->form['ave_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormCalPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['cal_per'] = $this->normalizarTitulo($this->form['cal_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormNumPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['num_per'] = $this->normalizarNumeroDomicilio($this->form['num_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormRefPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['ref_per'] = $this->limpiarTexto($this->form['ref_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormCiuPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['ciu_per'] = $this->normalizarTitulo($this->form['ciu_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormMunPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['mun_per'] = $this->normalizarTitulo($this->form['mun_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormDepPer(): void
    {
        $this->direccionManualCrear = true;
        $this->form['dep_per'] = $this->normalizarTitulo($this->form['dep_per']);
        $this->sincronizarDireccionCompletaCrear();
        $this->analizarFormularioCrear();
    }

    public function updatedFormEstPer(): void
    {
        $this->form['est_per'] = (int) (bool) $this->form['est_per'];
        $this->analizarFormularioCrear();
    }

    public function updatedFoto(): void
    {
        $this->validateOnly('foto', [
            'foto' => ['nullable', 'image', 'max:2048'],
        ], $this->messages);
    }

    // ============================================================
    // REACTIVIDAD EDITAR
    // ============================================================

    public function updatedFormEditarNomPer(): void
    {
        $this->formEditar['nom_per'] = $this->normalizarNombre($this->formEditar['nom_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarApePatPer(): void
    {
        $this->formEditar['ape_pat_per'] = $this->normalizarNombre($this->formEditar['ape_pat_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarApeMatPer(): void
    {
        $this->formEditar['ape_mat_per'] = $this->normalizarNombre($this->formEditar['ape_mat_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarCiPer(): void
    {
        $this->formEditar['ci_per'] = $this->normalizarCi($this->formEditar['ci_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarComPer(): void
    {
        $this->formEditar['com_per'] = $this->normalizarMayuscula($this->formEditar['com_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarExpPer(): void
    {
        $this->formEditar['exp_per'] = $this->normalizarExpedido($this->formEditar['exp_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarFecNacPer(): void
    {
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarGenPer(): void
    {
        $this->formEditar['gen_per'] = $this->normalizarGenero($this->formEditar['gen_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarTelPer(): void
    {
        $this->formEditar['tel_per'] = $this->normalizarTelefono($this->formEditar['tel_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarEmaPer(): void
    {
        $this->formEditar['ema_per'] = $this->normalizarCorreo($this->formEditar['ema_per']);
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarDirPer(): void
    {
        $this->formEditar['dir_per'] = $this->limpiarTexto($this->formEditar['dir_per']);

        if ($this->modoDireccionEditar === 'inteligente' && ! $this->direccionManualEditar) {
            $this->aplicarDireccionInteligenteEditar();
        }

        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarZonaPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['zona_per'] = $this->normalizarTitulo($this->formEditar['zona_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarAvePer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['ave_per'] = $this->normalizarTitulo($this->formEditar['ave_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarCalPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['cal_per'] = $this->normalizarTitulo($this->formEditar['cal_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarNumPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['num_per'] = $this->normalizarNumeroDomicilio($this->formEditar['num_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarRefPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['ref_per'] = $this->limpiarTexto($this->formEditar['ref_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarCiuPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['ciu_per'] = $this->normalizarTitulo($this->formEditar['ciu_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarMunPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['mun_per'] = $this->normalizarTitulo($this->formEditar['mun_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarDepPer(): void
    {
        $this->direccionManualEditar = true;
        $this->formEditar['dep_per'] = $this->normalizarTitulo($this->formEditar['dep_per']);
        $this->sincronizarDireccionCompletaEditar();
        $this->analizarFormularioEditar();
    }

    public function updatedFormEditarEstPer(): void
    {
        $this->formEditar['est_per'] = (int) (bool) $this->formEditar['est_per'];
        $this->analizarFormularioEditar();
    }

    public function updatedFotoEditar(): void
    {
        $this->validateOnly('fotoEditar', [
            'fotoEditar' => ['nullable', 'image', 'max:2048'],
        ], $this->messages);
    }

    // ============================================================
    // VALIDACIONES
    // ============================================================

    private function rulesCrear(): array
    {
        return [
            'form.nom_per' => ['required', 'string', 'min:2', 'max:100'],
            'form.ape_pat_per' => ['required', 'string', 'min:2', 'max:100'],
            'form.ape_mat_per' => ['nullable', 'string', 'max:100'],

            'form.ci_per' => [
                'required',
                'string',
                'regex:/^[0-9]{4,12}$/',
                Rule::unique('persona', 'ci_per'),
            ],

            'form.com_per' => ['nullable', 'string', 'max:10', 'regex:/^[A-Z0-9-]*$/'],
            'form.exp_per' => ['required', Rule::in($this->expedicionesPermitidas())],

            'form.fec_nac_per' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subYears(120)->format('Y-m-d'),
            ],

            'form.gen_per' => ['required', Rule::in($this->generosPermitidos())],

            'form.tel_per' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]*$/'],

            'form.ema_per' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('persona', 'ema_per')->whereNotNull('ema_per'),
            ],

            'form.dir_per' => ['nullable', 'string', 'max:255'],
            'form.zona_per' => ['nullable', 'string', 'max:100'],
            'form.ave_per' => ['nullable', 'string', 'max:120'],
            'form.cal_per' => ['nullable', 'string', 'max:120'],
            'form.num_per' => ['nullable', 'string', 'max:30'],
            'form.ref_per' => ['nullable', 'string', 'max:255'],
            'form.ciu_per' => ['nullable', 'string', 'max:100'],
            'form.mun_per' => ['nullable', 'string', 'max:100'],
            'form.dep_per' => ['nullable', 'string', 'max:100'],

            'form.est_per' => ['required', 'boolean'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    private function rulesEditar(): array
    {
        return [
            'formEditar.cod_per' => ['required', 'exists:persona,cod_per'],
            'formEditar.nom_per' => ['required', 'string', 'min:2', 'max:100'],
            'formEditar.ape_pat_per' => ['required', 'string', 'min:2', 'max:100'],
            'formEditar.ape_mat_per' => ['nullable', 'string', 'max:100'],

            'formEditar.ci_per' => [
                'required',
                'string',
                'regex:/^[0-9]{4,12}$/',
                Rule::unique('persona', 'ci_per')->ignore($this->formEditar['cod_per'], 'cod_per'),
            ],

            'formEditar.com_per' => ['nullable', 'string', 'max:10', 'regex:/^[A-Z0-9-]*$/'],
            'formEditar.exp_per' => ['required', Rule::in($this->expedicionesPermitidas())],

            'formEditar.fec_nac_per' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subYears(120)->format('Y-m-d'),
            ],

            'formEditar.gen_per' => ['required', Rule::in($this->generosPermitidos())],

            'formEditar.tel_per' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]*$/'],

            'formEditar.ema_per' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('persona', 'ema_per')->ignore($this->formEditar['cod_per'], 'cod_per'),
            ],

            'formEditar.dir_per' => ['nullable', 'string', 'max:255'],
            'formEditar.zona_per' => ['nullable', 'string', 'max:100'],
            'formEditar.ave_per' => ['nullable', 'string', 'max:120'],
            'formEditar.cal_per' => ['nullable', 'string', 'max:120'],
            'formEditar.num_per' => ['nullable', 'string', 'max:30'],
            'formEditar.ref_per' => ['nullable', 'string', 'max:255'],
            'formEditar.ciu_per' => ['nullable', 'string', 'max:100'],
            'formEditar.mun_per' => ['nullable', 'string', 'max:100'],
            'formEditar.dep_per' => ['nullable', 'string', 'max:100'],

            'formEditar.est_per' => ['required', 'boolean'],
            'fotoEditar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    // ============================================================
    // MODALES
    // ============================================================

    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->resetFormulario();
        $this->analizarFormularioCrear();
        $this->modalCrear = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrear = false;
        $this->resetValidation();
        $this->resetFormulario();
    }

    public function abrirModalVer(string $codPer): void
    {
        $this->personaDetalle = Persona::with('usuario')
            ->where('cod_per', $codPer)
            ->first();

        if (! $this->personaDetalle) {
            $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');
            return;
        }

        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->personaDetalle = null;
    }

    public function abrirModalEditar(string $codPer): void
    {
        $persona = Persona::where('cod_per', $codPer)->first();

        if (! $persona) {
            $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');
            return;
        }

        $this->resetValidation();
        $this->fotoEditar = null;
        $this->direccionManualEditar = false;
        $this->modoDireccionEditar = 'inteligente';

        $this->formEditar = [
            'cod_per' => $persona->cod_per,
            'nom_per' => $persona->nom_per ?? '',
            'ape_pat_per' => $persona->ape_pat_per ?? '',
            'ape_mat_per' => $persona->ape_mat_per ?? '',
            'ci_per' => $persona->ci_per ?? '',
            'com_per' => $persona->com_per ?? '',
            'exp_per' => $persona->exp_per ?? '',
            'fec_nac_per' => $persona->fec_nac_per ?? '',
            'gen_per' => $persona->gen_per ?? '',
            'tel_per' => $persona->tel_per ?? '',
            'ema_per' => $persona->ema_per ?? '',

            'dir_per' => $persona->dir_per ?? '',
            'zona_per' => $persona->zona_per ?? '',
            'ave_per' => $persona->ave_per ?? '',
            'cal_per' => $persona->cal_per ?? '',
            'num_per' => $persona->num_per ?? '',
            'ref_per' => $persona->ref_per ?? '',
            'ciu_per' => $persona->ciu_per ?? '',
            'mun_per' => $persona->mun_per ?? '',
            'dep_per' => $persona->dep_per ?? '',

            'fot_per' => $persona->fot_per,
            'est_per' => (int) (bool) $persona->est_per,
        ];

        $this->analizarFormularioEditar();
        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->resetValidation();
        $this->resetFormularioEditar();
    }

    // ============================================================
    // RESET
    // ============================================================

    public function resetFormulario(): void
    {
        $this->foto = null;
        $this->direccionManualCrear = false;
        $this->modoDireccionCrear = 'inteligente';
        $this->analisisPersona = [];

        $this->form = [
            'nom_per' => '',
            'ape_pat_per' => '',
            'ape_mat_per' => '',
            'ci_per' => '',
            'com_per' => '',
            'exp_per' => '',
            'fec_nac_per' => '',
            'gen_per' => '',
            'tel_per' => '',
            'ema_per' => '',

            'dir_per' => '',
            'zona_per' => '',
            'ave_per' => '',
            'cal_per' => '',
            'num_per' => '',
            'ref_per' => '',
            'ciu_per' => 'La Paz',
            'mun_per' => 'La Paz',
            'dep_per' => 'La Paz',

            'fot_per' => null,
            'est_per' => 1,
        ];
    }

    private function resetFormularioEditar(): void
    {
        $this->fotoEditar = null;
        $this->direccionManualEditar = false;
        $this->modoDireccionEditar = 'inteligente';
        $this->analisisPersonaEditar = [];

        $this->formEditar = [
            'cod_per' => '',
            'nom_per' => '',
            'ape_pat_per' => '',
            'ape_mat_per' => '',
            'ci_per' => '',
            'com_per' => '',
            'exp_per' => '',
            'fec_nac_per' => '',
            'gen_per' => '',
            'tel_per' => '',
            'ema_per' => '',

            'dir_per' => '',
            'zona_per' => '',
            'ave_per' => '',
            'cal_per' => '',
            'num_per' => '',
            'ref_per' => '',
            'ciu_per' => '',
            'mun_per' => '',
            'dep_per' => '',

            'fot_per' => null,
            'est_per' => 1,
        ];
    }

    // ============================================================
    // DIRECCIÓN INTELIGENTE
    // ============================================================

    public function cambiarModoDireccionCrear(string $modo): void
    {
        $this->modoDireccionCrear = in_array($modo, ['inteligente', 'manual'], true) ? $modo : 'inteligente';
        $this->direccionManualCrear = $this->modoDireccionCrear === 'manual';

        if ($this->modoDireccionCrear === 'inteligente') {
            $this->aplicarDireccionInteligenteCrear();
        }

        $this->analizarFormularioCrear();
    }

    public function cambiarModoDireccionEditar(string $modo): void
    {
        $this->modoDireccionEditar = in_array($modo, ['inteligente', 'manual'], true) ? $modo : 'inteligente';
        $this->direccionManualEditar = $this->modoDireccionEditar === 'manual';

        if ($this->modoDireccionEditar === 'inteligente') {
            $this->aplicarDireccionInteligenteEditar();
        }

        $this->analizarFormularioEditar();
    }

    public function aplicarDireccionInteligenteCrear(): void
    {
        $partes = $this->descifrarDireccion($this->form['dir_per'] ?? '');

        foreach ($partes as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $this->form[$campo] = $valor;
            }
        }

        $this->sincronizarDireccionCompletaCrear(false);
    }

    public function aplicarDireccionInteligenteEditar(): void
    {
        $partes = $this->descifrarDireccion($this->formEditar['dir_per'] ?? '');

        foreach ($partes as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $this->formEditar[$campo] = $valor;
            }
        }

        $this->sincronizarDireccionCompletaEditar(false);
    }

    public function sincronizarDireccionCompletaCrear(bool $sobrescribir = true): void
    {
        $generada = $this->generarDireccionVisible($this->form);

        if ($sobrescribir || ! filled($this->form['dir_per'])) {
            $this->form['dir_per'] = $generada;
        }
    }

    public function sincronizarDireccionCompletaEditar(bool $sobrescribir = true): void
    {
        $generada = $this->generarDireccionVisible($this->formEditar);

        if ($sobrescribir || ! filled($this->formEditar['dir_per'])) {
            $this->formEditar['dir_per'] = $generada;
        }
    }

    private function descifrarDireccion(?string $direccion): array
    {
        $textoOriginal = $this->limpiarTexto($direccion) ?? '';
        $texto = mb_strtolower($textoOriginal);

        $resultado = [
            'zona_per' => '',
            'ave_per' => '',
            'cal_per' => '',
            'num_per' => '',
            'ref_per' => '',
            'ciu_per' => '',
            'mun_per' => '',
            'dep_per' => '',
        ];

        if ($texto === '') {
            return $resultado;
        }

        $segmentos = preg_split('/[,;|]+/', $textoOriginal) ?: [];

        foreach ($segmentos as $segmento) {
            $segmento = $this->limpiarTexto($segmento);

            if (! $segmento) {
                continue;
            }

            $segmentoLower = mb_strtolower($segmento);

            if (preg_match('/\b(zona|barrio|urb\.?|urbanizacion|urbanización)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['zona_per'] = $this->normalizarTitulo($m[2]);
                continue;
            }

            if (preg_match('/\b(avenida|av\.?|avda\.?)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['ave_per'] = $this->normalizarTitulo($m[2]);
                continue;
            }

            if (preg_match('/\b(calle|c\/)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['cal_per'] = $this->normalizarTitulo($m[2]);
                continue;
            }

            if (preg_match('/(#|nro\.?|n°|numero|número)\s*([a-zA-Z0-9\-\/]+)/iu', $segmento, $m)) {
                $resultado['num_per'] = $this->normalizarNumeroDomicilio($m[2]);
                continue;
            }

            if (preg_match('/\b(referencia|ref\.?)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['ref_per'] = $this->limpiarTexto($m[2]);
                continue;
            }

            if (preg_match('/\b(ciudad)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['ciu_per'] = $this->normalizarTitulo($m[2]);
                continue;
            }

            if (preg_match('/\b(municipio)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['mun_per'] = $this->normalizarTitulo($m[2]);
                continue;
            }

            if (preg_match('/\b(departamento|depto\.?)\b\s*(.+)/iu', $segmento, $m)) {
                $resultado['dep_per'] = $this->normalizarTitulo($m[2]);
                continue;
            }
        }

        if (! $resultado['num_per'] && preg_match('/(?:#|nro\.?|n°|numero|número)\s*([a-zA-Z0-9\-\/]+)/iu', $textoOriginal, $m)) {
            $resultado['num_per'] = $this->normalizarNumeroDomicilio($m[1]);
        }

        if (! $resultado['zona_per'] && preg_match('/zona\s+([^,#;]+)/iu', $textoOriginal, $m)) {
            $resultado['zona_per'] = $this->normalizarTitulo($m[1]);
        }

        if (! $resultado['ave_per'] && preg_match('/(?:avenida|av\.?|avda\.?)\s+([^,#;]+)/iu', $textoOriginal, $m)) {
            $resultado['ave_per'] = $this->normalizarTitulo($m[1]);
        }

        if (! $resultado['cal_per'] && preg_match('/(?:calle|c\/)\s+([^,#;]+)/iu', $textoOriginal, $m)) {
            $resultado['cal_per'] = $this->normalizarTitulo($m[1]);
        }

        if (! $resultado['ciu_per']) {
            $resultado['ciu_per'] = $this->form['ciu_per'] ?? 'La Paz';
        }

        if (! $resultado['mun_per']) {
            $resultado['mun_per'] = $this->form['mun_per'] ?? 'La Paz';
        }

        if (! $resultado['dep_per']) {
            $resultado['dep_per'] = $this->form['dep_per'] ?? 'La Paz';
        }

        return $resultado;
    }

    private function generarDireccionVisible(array $datos): ?string
    {
        $partes = [];

        if (filled($datos['cal_per'] ?? null)) {
            $partes[] = 'Calle ' . $datos['cal_per'];
        }

        if (filled($datos['ave_per'] ?? null)) {
            $partes[] = 'Av. ' . $datos['ave_per'];
        }

        if (filled($datos['zona_per'] ?? null)) {
            $partes[] = 'Zona ' . $datos['zona_per'];
        }

        if (filled($datos['num_per'] ?? null)) {
            $partes[] = '#' . $datos['num_per'];
        }

        if (filled($datos['ref_per'] ?? null)) {
            $partes[] = 'Ref. ' . $datos['ref_per'];
        }

        if (filled($datos['ciu_per'] ?? null)) {
            $partes[] = 'Ciudad ' . $datos['ciu_per'];
        }

        if (filled($datos['mun_per'] ?? null) && ($datos['mun_per'] ?? null) !== ($datos['ciu_per'] ?? null)) {
            $partes[] = 'Municipio ' . $datos['mun_per'];
        }

        if (filled($datos['dep_per'] ?? null)) {
            $partes[] = 'Departamento ' . $datos['dep_per'];
        }

        return ! empty($partes) ? implode(', ', $partes) : null;
    }

    // ============================================================
    // ANÁLISIS INTELIGENTE
    // ============================================================

    private function analizarFormularioCrear(): void
    {
        $payload = $this->payloadParaPersonaInteligente($this->form);
        $payload['tipo_vinculacion'] = 'SOLO_PERSONA';

        $this->analisisPersona = $this->soportePersona()->reconocerEnTiempoReal($payload);
    }

    private function analizarFormularioEditar(): void
    {
        $payload = $this->payloadParaPersonaInteligente($this->formEditar);
        $payload['tipo_vinculacion'] = 'SOLO_PERSONA';

        $analisis = $this->soportePersona()->reconocerEnTiempoReal($payload);

        $codActual = $this->formEditar['cod_per'] ?? null;
        $personaDetectada = $analisis['coincidencias']['persona_principal']['cod_per'] ?? null;

        if ($codActual && $personaDetectada === $codActual) {
            $analisis['puede_continuar'] = true;
            $analisis['estado_inteligente'] = 'VALIDO';
            $analisis['mensaje'] = 'Editando persona existente. No se detecta duplicidad externa.';
            $analisis['bloqueos'] = [];
        }

        $this->analisisPersonaEditar = $analisis;
    }

    private function validarAnalisisAntesDeGuardar(array $analisis, ?string $codIgnorado = null): bool
    {
        $personaDetectada = $analisis['coincidencias']['persona_principal']['cod_per'] ?? null;

        if ($codIgnorado && $personaDetectada === $codIgnorado) {
            return true;
        }

        if (($analisis['puede_continuar'] ?? false) !== true) {
            $this->dispatch(
                'error-general',
                mensaje: $analisis['mensaje'] ?? 'La persona no puede registrarse porque existen observaciones críticas.'
            );

            return false;
        }

        if (($analisis['coincidencias']['duplicado_ci'] ?? false) || ($analisis['coincidencias']['duplicado_correo'] ?? false)) {
            $this->dispatch(
                'error-general',
                mensaje: 'Ya existe una persona registrada con el CI o correo ingresado.'
            );

            return false;
        }

        return true;
    }

    private function payloadParaPersonaInteligente(array $datos): array
    {
        return [
            'cod_per' => $datos['cod_per'] ?? null,
            'nom_per' => $datos['nom_per'] ?? null,
            'ape_pat_per' => $datos['ape_pat_per'] ?? null,
            'ape_mat_per' => $datos['ape_mat_per'] ?? null,
            'ci_per' => $datos['ci_per'] ?? null,
            'com_per' => $datos['com_per'] ?? null,
            'exp_per' => $datos['exp_per'] ?? null,
            'fec_nac_per' => $datos['fec_nac_per'] ?? null,
            'gen_per' => $datos['gen_per'] ?? null,
            'tel_per' => $datos['tel_per'] ?? null,
            'ema_per' => $datos['ema_per'] ?? null,

            'dir_per' => $datos['dir_per'] ?? null,
            'zona_per' => $datos['zona_per'] ?? null,
            'ave_per' => $datos['ave_per'] ?? null,
            'cal_per' => $datos['cal_per'] ?? null,
            'num_per' => $datos['num_per'] ?? null,
            'ref_per' => $datos['ref_per'] ?? null,
            'ciu_per' => $datos['ciu_per'] ?? null,
            'mun_per' => $datos['mun_per'] ?? null,
            'dep_per' => $datos['dep_per'] ?? null,

            'tipo_vinculacion' => 'SOLO_PERSONA',
        ];
    }

    // ============================================================
    // GUARDAR / ACTUALIZAR
    // ============================================================

    public function guardarPersona(): void
    {
        $this->normalizarFormularioCrearFinal();
        $this->validate($this->rulesCrear(), $this->messages);
        $this->analizarFormularioCrear();

        if (! $this->validarAnalisisAntesDeGuardar($this->analisisPersona)) {
            return;
        }

        DB::transaction(function () {
            $rutaFoto = null;

            if ($this->foto) {
                $rutaFoto = $this->foto->store('personas', 'public');
            }

            $persona = Persona::create($this->payloadGuardarPersona($this->form, $rutaFoto));

            $persona = $persona->fresh();

            $this->registrarBitacora(
                accion: 'CREAR_PERSONA',
                tabla: 'persona',
                registro: $persona->cod_per,
                nombreRegistro: $this->nombreCompleto($persona),
                descripcion: 'Se registró una nueva persona con datos personales e identificación institucional.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: $persona->toArray()
            );

            $this->cerrarModalCrear();

            $this->dispatch('persona-creada');
            $this->dispatch('success-general', mensaje: 'Persona registrada correctamente.');
            $this->actualizarGraficos();
        });
    }

    public function actualizarPersona(): void
    {
        $this->normalizarFormularioEditarFinal();
        $this->validate($this->rulesEditar(), $this->messages);
        $this->analizarFormularioEditar();

        if (! $this->validarAnalisisAntesDeGuardar($this->analisisPersonaEditar, $this->formEditar['cod_per'])) {
            return;
        }

        DB::transaction(function () {
            $persona = Persona::where('cod_per', $this->formEditar['cod_per'])->first();

            if (! $persona) {
                $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');
                return;
            }

            $valoresAnteriores = $persona->toArray();
            $rutaFoto = $persona->fot_per;

            if ($this->fotoEditar) {
                if ($persona->fot_per) {
                    Storage::disk('public')->delete($persona->fot_per);
                }

                $rutaFoto = $this->fotoEditar->store('personas', 'public');
            }

            $persona->update($this->payloadGuardarPersona($this->formEditar, $rutaFoto));

            $personaActualizada = $persona->fresh();

            $this->registrarBitacora(
                accion: 'ACTUALIZAR_PERSONA',
                tabla: 'persona',
                registro: $personaActualizada->cod_per,
                nombreRegistro: $this->nombreCompleto($personaActualizada),
                descripcion: 'Se actualizó la información personal registrada en el sistema.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $personaActualizada->toArray()
            );

            $this->cerrarModalEditar();

            $this->dispatch('persona-actualizada');
            $this->dispatch('success-general', mensaje: 'Persona actualizada correctamente.');
            $this->actualizarGraficos();
        });
    }

    private function payloadGuardarPersona(array $datos, ?string $rutaFoto): array
    {
        $payload = [
            'nom_per' => $this->normalizarNombre($datos['nom_per'] ?? null),
            'ape_pat_per' => $this->normalizarNombre($datos['ape_pat_per'] ?? null),
            'ape_mat_per' => $this->normalizarNombre($datos['ape_mat_per'] ?? null),
            'ci_per' => $this->normalizarCi($datos['ci_per'] ?? null),
            'com_per' => $this->normalizarMayuscula($datos['com_per'] ?? null),
            'exp_per' => $this->normalizarExpedido($datos['exp_per'] ?? null),
            'fec_nac_per' => $datos['fec_nac_per'] ?: null,
            'gen_per' => $this->normalizarGenero($datos['gen_per'] ?? null),
            'tel_per' => $this->normalizarTelefono($datos['tel_per'] ?? null),
            'ema_per' => $this->normalizarCorreo($datos['ema_per'] ?? null),
            'dir_per' => $this->limpiarTexto($datos['dir_per'] ?? null),
            'fot_per' => $rutaFoto,
            'est_per' => (bool) ($datos['est_per'] ?? true),
        ];

        foreach ([
            'zona_per',
            'ave_per',
            'cal_per',
            'num_per',
            'ref_per',
            'ciu_per',
            'mun_per',
            'dep_per',
        ] as $campo) {
            if (Schema::hasColumn('persona', $campo)) {
                $payload[$campo] = $this->limpiarTexto($datos[$campo] ?? null);
            }
        }

        return $payload;
    }

    // ============================================================
    // DESACTIVAR / REACTIVAR / FOTO
    // ============================================================

    public function desactivarPersona(string $codPer): void
    {
        DB::transaction(function () use ($codPer) {
            $persona = Persona::where('cod_per', $codPer)->first();

            if (! $persona) {
                $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');
                return;
            }

            if (! $persona->est_per) {
                $this->dispatch('error-general', mensaje: 'La persona ya se encuentra inactiva.');
                return;
            }

            $valoresAnteriores = $persona->toArray();

            $persona->update(['est_per' => false]);

            $personaActualizada = $persona->fresh();

            $this->registrarBitacora(
                accion: 'DESACTIVAR_PERSONA',
                tabla: 'persona',
                registro: $personaActualizada->cod_per,
                nombreRegistro: $this->nombreCompleto($personaActualizada),
                descripcion: 'Se desactivó el registro de una persona. No se realizó eliminación física.',
                nivel: 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $personaActualizada->toArray()
            );

            $this->dispatch('persona-desactivada');
            $this->dispatch('success-general', mensaje: 'Persona desactivada correctamente.');
            $this->actualizarGraficos();
        });
    }

    public function reactivarPersona(string $codPer): void
    {
        DB::transaction(function () use ($codPer) {
            $persona = Persona::where('cod_per', $codPer)->first();

            if (! $persona) {
                $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');
                return;
            }

            if ($persona->est_per) {
                $this->dispatch('error-general', mensaje: 'La persona ya se encuentra activa.');
                return;
            }

            $valoresAnteriores = $persona->toArray();

            $persona->update(['est_per' => true]);

            $personaActualizada = $persona->fresh();

            $this->registrarBitacora(
                accion: 'REACTIVAR_PERSONA',
                tabla: 'persona',
                registro: $personaActualizada->cod_per,
                nombreRegistro: $this->nombreCompleto($personaActualizada),
                descripcion: 'Se reactivó el registro de una persona en el sistema.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $personaActualizada->toArray()
            );

            $this->dispatch('persona-reactivada');
            $this->dispatch('success-general', mensaje: 'Persona reactivada correctamente.');
            $this->actualizarGraficos();
        });
    }

    public function eliminarFotoEditar(): void
    {
        DB::transaction(function () {
            $persona = Persona::where('cod_per', $this->formEditar['cod_per'] ?? null)->first();

            if (! $persona || ! $persona->fot_per) {
                $this->dispatch('error-general', mensaje: 'No existe una fotografía para eliminar.');
                return;
            }

            $valoresAnteriores = $persona->toArray();

            Storage::disk('public')->delete($persona->fot_per);

            $persona->update(['fot_per' => null]);

            $personaActualizada = $persona->fresh();

            $this->formEditar['fot_per'] = null;
            $this->fotoEditar = null;

            $this->registrarBitacora(
                accion: 'ELIMINAR_FOTO_PERSONA',
                tabla: 'persona',
                registro: $personaActualizada->cod_per,
                nombreRegistro: $this->nombreCompleto($personaActualizada),
                descripcion: 'Se eliminó la fotografía asociada a una persona.',
                nivel: 'INFO',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $personaActualizada->toArray()
            );

            $this->dispatch('persona-actualizada');
            $this->dispatch('success-general', mensaje: 'Fotografía eliminada correctamente.');
            $this->actualizarGraficos();
        });
    }

    // ============================================================
    // QUERY
    // ============================================================

    private function personasQuery()
    {
        return Persona::query()
            ->with('usuario')
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $driver = DB::connection()->getDriverName();
                    $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

                    $q->where('nom_per', $like, "%{$search}%")
                        ->orWhere('ape_pat_per', $like, "%{$search}%")
                        ->orWhere('ape_mat_per', $like, "%{$search}%")
                        ->orWhere('ci_per', $like, "%{$search}%")
                        ->orWhere('tel_per', $like, "%{$search}%")
                        ->orWhere('ema_per', $like, "%{$search}%")
                        ->orWhereRaw(
                            $driver === 'pgsql'
                                ? "CONCAT(nom_per, ' ', ape_pat_per, ' ', COALESCE(ape_mat_per, '')) ILIKE ?"
                                : "CONCAT(nom_per, ' ', ape_pat_per, ' ', COALESCE(ape_mat_per, '')) LIKE ?",
                            ["%{$search}%"]
                        );
                });
            })
            ->when($this->genero, function ($query) {
                $query->where('gen_per', $this->genero);
            })
            ->when($this->estado !== '', function ($query) {
                $query->where('est_per', (bool) $this->estado);
            })
            ->when($this->cuentaUsuario === 'con_usuario', function ($query) {
                $query->whereHas('usuario');
            })
            ->when($this->cuentaUsuario === 'sin_usuario', function ($query) {
                $query->whereDoesntHave('usuario');
            })
            ->when($this->direccion, function ($query) {
                $search = trim($this->direccion);
                $driver = DB::connection()->getDriverName();
                $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

                $query->where(function ($q) use ($search, $like) {
                    $q->where('dir_per', $like, "%{$search}%");

                    foreach (['zona_per', 'ave_per', 'cal_per', 'ciu_per', 'mun_per', 'dep_per'] as $campo) {
                        if (Schema::hasColumn('persona', $campo)) {
                            $q->orWhere($campo, $like, "%{$search}%");
                        }
                    }
                });
            })
            ->orderByDesc('created_at');
    }

    // ============================================================
    // BITÁCORA
    // ============================================================

    private function registrarBitacora(
        string $accion,
        string $tabla,
        ?string $registro = null,
        ?string $modulo = 'Gestión de Personas',
        ?string $nombreRegistro = null,
        ?string $descripcion = null,
        string $nivel = 'INFO',
        string $resultado = 'EXITOSO',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $error = null
    ): void {
        BitacoraService::registrar(
            accion: $accion,
            tabla: $tabla,
            registro: $registro,
            modulo: $modulo,
            nombreRegistro: $nombreRegistro,
            descripcion: $descripcion,
            nivel: $nivel,
            resultado: $resultado,
            valoresAnteriores: $valoresAnteriores,
            valoresNuevos: $valoresNuevos,
            error: $error
        );
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function normalizarFormularioCrearFinal(): void
    {
        $this->form['nom_per'] = $this->normalizarNombre($this->form['nom_per']);
        $this->form['ape_pat_per'] = $this->normalizarNombre($this->form['ape_pat_per']);
        $this->form['ape_mat_per'] = $this->normalizarNombre($this->form['ape_mat_per']);
        $this->form['ci_per'] = $this->normalizarCi($this->form['ci_per']);
        $this->form['com_per'] = $this->normalizarMayuscula($this->form['com_per']);
        $this->form['exp_per'] = $this->normalizarExpedido($this->form['exp_per']);
        $this->form['gen_per'] = $this->normalizarGenero($this->form['gen_per']);
        $this->form['tel_per'] = $this->normalizarTelefono($this->form['tel_per']);
        $this->form['ema_per'] = $this->normalizarCorreo($this->form['ema_per']);

        if (! filled($this->form['dir_per'])) {
            $this->sincronizarDireccionCompletaCrear();
        }
    }

    private function normalizarFormularioEditarFinal(): void
    {
        $this->formEditar['nom_per'] = $this->normalizarNombre($this->formEditar['nom_per']);
        $this->formEditar['ape_pat_per'] = $this->normalizarNombre($this->formEditar['ape_pat_per']);
        $this->formEditar['ape_mat_per'] = $this->normalizarNombre($this->formEditar['ape_mat_per']);
        $this->formEditar['ci_per'] = $this->normalizarCi($this->formEditar['ci_per']);
        $this->formEditar['com_per'] = $this->normalizarMayuscula($this->formEditar['com_per']);
        $this->formEditar['exp_per'] = $this->normalizarExpedido($this->formEditar['exp_per']);
        $this->formEditar['gen_per'] = $this->normalizarGenero($this->formEditar['gen_per']);
        $this->formEditar['tel_per'] = $this->normalizarTelefono($this->formEditar['tel_per']);
        $this->formEditar['ema_per'] = $this->normalizarCorreo($this->formEditar['ema_per']);

        if (! filled($this->formEditar['dir_per'])) {
            $this->sincronizarDireccionCompletaEditar();
        }
    }

    private function limpiarTexto(?string $valor): ?string
    {
        $valor = trim(preg_replace('/\s+/', ' ', (string) $valor));

        return $valor === '' ? null : $valor;
    }

    private function normalizarNombre(?string $valor): ?string
    {
        $valor = $this->limpiarTexto($valor);

        if (! $valor) {
            return null;
        }

        return mb_convert_case(mb_strtolower($valor), MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizarTitulo(?string $valor): ?string
    {
        return $this->normalizarNombre($valor);
    }

    private function normalizarMayuscula(?string $valor): ?string
    {
        $valor = $this->limpiarTexto($valor);

        return $valor ? mb_strtoupper($valor) : null;
    }

    private function normalizarCi(?string $valor): ?string
    {
        $valor = preg_replace('/\D+/', '', (string) $valor);

        return $valor === '' ? null : $valor;
    }

    private function normalizarCorreo(?string $valor): ?string
    {
        $valor = trim(mb_strtolower((string) $valor));

        return $valor === '' ? null : $valor;
    }

    private function normalizarTelefono(?string $valor): ?string
    {
        $valor = trim((string) $valor);
        $valor = preg_replace('/\s+/', ' ', $valor);

        return $valor === '' ? null : $valor;
    }

    private function normalizarNumeroDomicilio(?string $valor): ?string
    {
        $valor = trim((string) $valor);
        $valor = preg_replace('/[^a-zA-Z0-9\-\/]/', '', $valor);

        return $valor === '' ? null : mb_strtoupper($valor);
    }

    private function normalizarExpedido(?string $valor): ?string
    {
        $valor = $this->normalizarMayuscula($valor);

        return match ($valor) {
            'CB' => 'CBBA',
            'SC' => 'SCZ',
            'OR' => 'ORU',
            default => $valor,
        };
    }

    private function normalizarGenero(?string $valor): ?string
    {
        $valor = $this->normalizarMayuscula($valor);

        return match ($valor) {
            'MASCULINO' => 'M',
            'FEMENINO' => 'F',
            default => $valor,
        };
    }

    public function nombreCompleto(?Persona $persona): string
    {
        if (! $persona) {
            return 'Sin persona';
        }

        return trim(collect([
            $persona->nom_per,
            $persona->ape_pat_per,
            $persona->ape_mat_per,
        ])->filter()->implode(' '));
    }

    public function edadPersona(?string $fecha): ?int
    {
        if (! filled($fecha)) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    public function puedeGuardarPersona(): bool
    {
        return filled($this->form['nom_per'] ?? null)
            && filled($this->form['ape_pat_per'] ?? null)
            && filled($this->form['ci_per'] ?? null)
            && filled($this->form['exp_per'] ?? null)
            && filled($this->form['fec_nac_per'] ?? null)
            && filled($this->form['gen_per'] ?? null)
            && (($this->analisisPersona['puede_continuar'] ?? true) === true);
    }

    public function puedeActualizarPersona(): bool
    {
        return filled($this->formEditar['cod_per'] ?? null)
            && filled($this->formEditar['nom_per'] ?? null)
            && filled($this->formEditar['ape_pat_per'] ?? null)
            && filled($this->formEditar['ci_per'] ?? null)
            && filled($this->formEditar['exp_per'] ?? null)
            && filled($this->formEditar['fec_nac_per'] ?? null)
            && filled($this->formEditar['gen_per'] ?? null)
            && (($this->analisisPersonaEditar['puede_continuar'] ?? true) === true);
    }

    private function soportePersona(): PersonaInteligente
    {
        return app(PersonaInteligente::class);
    }

    private function expedicionesPermitidas(): array
    {
        return ['LP', 'CBBA', 'SCZ', 'ORU', 'PT', 'CH', 'TJ', 'BN', 'PD'];
    }

    private function generosPermitidos(): array
    {
        return ['M', 'F'];
    }

    // ============================================================
    // PROPIEDADES COMPUTADAS
    // ============================================================

    public function getTotalPersonasProperty(): int
    {
        return Persona::count();
    }

    public function getTotalActivasProperty(): int
    {
        return Persona::where('est_per', true)->count();
    }

    public function getTotalInactivasProperty(): int
    {
        return Persona::where('est_per', false)->count();
    }

    public function getTotalConUsuarioProperty(): int
    {
        return Persona::whereHas('usuario')->count();
    }

    public function getTotalSinUsuarioProperty(): int
    {
        return Persona::whereDoesntHave('usuario')->count();
    }

    public function getDatosGraficoGeneroProperty(): array
    {
        return [
            'labels' => ['Masculino', 'Femenino', 'No definido'],
            'data' => [
                Persona::where('gen_per', 'M')->count(),
                Persona::where('gen_per', 'F')->count(),
                Persona::whereNull('gen_per')->orWhere('gen_per', '')->count(),
            ],
        ];
    }

    public function getDatosGraficoEstadoProperty(): array
    {
        return [
            'labels' => ['Activas', 'Inactivas'],
            'data' => [
                $this->totalActivas,
                $this->totalInactivas,
            ],
        ];
    }

    public function getDatosGraficoUsuariosProperty(): array
    {
        return [
            'labels' => ['Con usuario', 'Sin usuario'],
            'data' => [
                $this->totalConUsuario,
                $this->totalSinUsuario,
            ],
        ];
    }

    public function getDatosGraficosProperty(): array
    {
        return [
            'genero' => $this->datosGraficoGenero,
            'estado' => $this->datosGraficoEstado,
            'usuarios' => $this->datosGraficoUsuarios,
        ];
    }

    public function actualizarGraficos(): void
    {
        $this->dispatch('actualizar-graficos-personas', data: $this->datosGraficos);
    }
}