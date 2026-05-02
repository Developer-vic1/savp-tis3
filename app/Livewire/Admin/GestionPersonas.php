<?php

namespace App\Livewire\Admin;

use App\Models\Persona;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GestionPersonas extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | Filtros principales
    |--------------------------------------------------------------------------
    */
    public string $search = '';
    public string $genero = '';
    public string $estado = '';
    public string $cuentaUsuario = '';
    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | Control de modales
    |--------------------------------------------------------------------------
    */
    public bool $modalCrear = false;
    public bool $modalVer = false;
    public bool $modalEditar = false;

    public ?Persona $personaDetalle = null;

    /*
    |--------------------------------------------------------------------------
    | Carga de imágenes
    |--------------------------------------------------------------------------
    */
    public $foto = null;
    public $fotoEditar = null;

    /*
    |--------------------------------------------------------------------------
    | Formularios
    |--------------------------------------------------------------------------
    */
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
        'fot_per' => null,
        'est_per' => 1,
    ];

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación
    |--------------------------------------------------------------------------
    */
    protected array $messages = [
        'form.nom_per.required' => 'El nombre es obligatorio.',
        'form.nom_per.max' => 'El nombre no debe superar los 100 caracteres.',
        'form.ape_pat_per.required' => 'El apellido paterno es obligatorio.',
        'form.ape_pat_per.max' => 'El apellido paterno no debe superar los 100 caracteres.',
        'form.ape_mat_per.max' => 'El apellido materno no debe superar los 100 caracteres.',
        'form.ci_per.required' => 'El CI es obligatorio.',
        'form.ci_per.unique' => 'Ese CI ya está registrado.',
        'form.ci_per.max' => 'El CI no debe superar los 30 caracteres.',
        'form.com_per.max' => 'El complemento no debe superar los 10 caracteres.',
        'form.exp_per.required' => 'Debes seleccionar el lugar de expedición.',
        'form.exp_per.max' => 'La expedición no debe superar los 5 caracteres.',
        'form.fec_nac_per.required' => 'La fecha de nacimiento es obligatoria.',
        'form.fec_nac_per.date' => 'La fecha de nacimiento no es válida.',
        'form.fec_nac_per.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'form.gen_per.required' => 'Debes seleccionar el género.',
        'form.gen_per.in' => 'El género seleccionado no es válido.',
        'form.tel_per.max' => 'El teléfono no debe superar los 30 caracteres.',
        'form.ema_per.email' => 'Debes ingresar un correo válido.',
        'form.ema_per.max' => 'El correo no debe superar los 150 caracteres.',
        'form.ema_per.unique' => 'Ese correo ya está registrado.',
        'form.dir_per.max' => 'La dirección no debe superar los 255 caracteres.',
        'form.est_per.required' => 'Debes seleccionar el estado.',
        'form.est_per.boolean' => 'El estado seleccionado no es válido.',
        'foto.image' => 'El archivo debe ser una imagen.',
        'foto.max' => 'La imagen no debe superar los 2 MB.',

        'formEditar.cod_per.required' => 'No se pudo identificar a la persona.',
        'formEditar.cod_per.exists' => 'La persona seleccionada no existe.',
        'formEditar.nom_per.required' => 'El nombre es obligatorio.',
        'formEditar.nom_per.max' => 'El nombre no debe superar los 100 caracteres.',
        'formEditar.ape_pat_per.required' => 'El apellido paterno es obligatorio.',
        'formEditar.ape_pat_per.max' => 'El apellido paterno no debe superar los 100 caracteres.',
        'formEditar.ape_mat_per.max' => 'El apellido materno no debe superar los 100 caracteres.',
        'formEditar.ci_per.required' => 'El CI es obligatorio.',
        'formEditar.ci_per.unique' => 'Ese CI ya está registrado por otra persona.',
        'formEditar.ci_per.max' => 'El CI no debe superar los 30 caracteres.',
        'formEditar.com_per.max' => 'El complemento no debe superar los 10 caracteres.',
        'formEditar.exp_per.required' => 'Debes seleccionar el lugar de expedición.',
        'formEditar.exp_per.max' => 'La expedición no debe superar los 5 caracteres.',
        'formEditar.fec_nac_per.required' => 'La fecha de nacimiento es obligatoria.',
        'formEditar.fec_nac_per.date' => 'La fecha de nacimiento no es válida.',
        'formEditar.fec_nac_per.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'formEditar.gen_per.required' => 'Debes seleccionar el género.',
        'formEditar.gen_per.in' => 'El género seleccionado no es válido.',
        'formEditar.tel_per.max' => 'El teléfono no debe superar los 30 caracteres.',
        'formEditar.ema_per.email' => 'Debes ingresar un correo válido.',
        'formEditar.ema_per.max' => 'El correo no debe superar los 150 caracteres.',
        'formEditar.ema_per.unique' => 'Ese correo ya está registrado por otra persona.',
        'formEditar.dir_per.max' => 'La dirección no debe superar los 255 caracteres.',
        'formEditar.est_per.required' => 'Debes seleccionar el estado.',
        'formEditar.est_per.boolean' => 'El estado seleccionado no es válido.',
        'fotoEditar.image' => 'El archivo debe ser una imagen.',
        'fotoEditar.max' => 'La imagen no debe superar los 2 MB.',
    ];

    /*
    |--------------------------------------------------------------------------
    | Reactividad de filtros
    |--------------------------------------------------------------------------
    */
    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->actualizarGraficos();
    }

    public function updatingGenero(): void
    {
        $this->resetPage();
        $this->actualizarGraficos();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
        $this->actualizarGraficos();
    }

    public function updatingCuentaUsuario(): void
    {
        $this->resetPage();
        $this->actualizarGraficos();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */
    private function rulesCrear(): array
    {
        return [
            'form.nom_per' => ['required', 'string', 'max:100'],
            'form.ape_pat_per' => ['required', 'string', 'max:100'],
            'form.ape_mat_per' => ['nullable', 'string', 'max:100'],
            'form.ci_per' => ['required', 'string', 'max:30', 'unique:persona,ci_per'],
            'form.com_per' => ['nullable', 'string', 'max:10'],
            'form.exp_per' => ['required', 'string', 'max:5'],
            'form.fec_nac_per' => ['required', 'date', 'before_or_equal:today'],
            'form.gen_per' => ['required', Rule::in(['M', 'F'])],
            'form.tel_per' => ['nullable', 'string', 'max:30'],
            'form.ema_per' => ['nullable', 'email', 'max:150', 'unique:persona,ema_per'],
            'form.dir_per' => ['nullable', 'string', 'max:255'],
            'form.est_per' => ['required', 'boolean'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    private function rulesEditar(): array
    {
        return [
            'formEditar.cod_per' => ['required', 'exists:persona,cod_per'],
            'formEditar.nom_per' => ['required', 'string', 'max:100'],
            'formEditar.ape_pat_per' => ['required', 'string', 'max:100'],
            'formEditar.ape_mat_per' => ['nullable', 'string', 'max:100'],
            'formEditar.ci_per' => [
                'required',
                'string',
                'max:30',
                Rule::unique('persona', 'ci_per')->ignore($this->formEditar['cod_per'], 'cod_per'),
            ],
            'formEditar.com_per' => ['nullable', 'string', 'max:10'],
            'formEditar.exp_per' => ['required', 'string', 'max:5'],
            'formEditar.fec_nac_per' => ['required', 'date', 'before_or_equal:today'],
            'formEditar.gen_per' => ['required', Rule::in(['M', 'F'])],
            'formEditar.tel_per' => ['nullable', 'string', 'max:30'],
            'formEditar.ema_per' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('persona', 'ema_per')->ignore($this->formEditar['cod_per'], 'cod_per'),
            ],
            'formEditar.dir_per' => ['nullable', 'string', 'max:255'],
            'formEditar.est_per' => ['required', 'boolean'],
            'fotoEditar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Modales
    |--------------------------------------------------------------------------
    */
    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->resetFormulario();
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
            'fot_per' => $persona->fot_per,
            'est_per' => (int) $persona->est_per,
        ];

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->resetValidation();
        $this->resetFormularioEditar();
    }

    /*
    |--------------------------------------------------------------------------
    | Reset de formularios
    |--------------------------------------------------------------------------
    */
    public function resetFormulario(): void
    {
        $this->foto = null;

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
            'fot_per' => null,
            'est_per' => 1,
        ];
    }

    private function resetFormularioEditar(): void
    {
        $this->fotoEditar = null;

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
            'fot_per' => null,
            'est_per' => 1,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones CRUD controladas
    |--------------------------------------------------------------------------
    */
    public function guardarPersona(): void
    {
        $this->validate($this->rulesCrear(), $this->messages);

        DB::transaction(function () {
            $rutaFoto = null;

            if ($this->foto) {
                $rutaFoto = $this->foto->store('personas', 'public');
            }

            $persona = Persona::create([
                'nom_per' => $this->limpiarTexto($this->form['nom_per']),
                'ape_pat_per' => $this->limpiarTexto($this->form['ape_pat_per']),
                'ape_mat_per' => $this->limpiarTexto($this->form['ape_mat_per']),
                'ci_per' => $this->limpiarTexto($this->form['ci_per']),
                'com_per' => $this->limpiarTexto($this->form['com_per']),
                'exp_per' => $this->limpiarTexto($this->form['exp_per']),
                'fec_nac_per' => $this->form['fec_nac_per'],
                'gen_per' => $this->form['gen_per'],
                'tel_per' => $this->limpiarTexto($this->form['tel_per']),
                'ema_per' => $this->limpiarCorreo($this->form['ema_per']),
                'dir_per' => $this->limpiarTexto($this->form['dir_per']),
                'fot_per' => $rutaFoto,
                'est_per' => (bool) $this->form['est_per'],
            ]);

            $persona = $persona->fresh();

            $this->registrarBitacora(
                accion: 'CREAR_PERSONA',
                tabla: 'persona',
                registro: $persona->cod_per,
                nombreRegistro: $this->nombreCompleto($persona),
                descripcion: 'Se registró una nueva persona en el sistema institucional.',
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
        $this->validate($this->rulesEditar(), $this->messages);

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

            $persona->update([
                'nom_per' => $this->limpiarTexto($this->formEditar['nom_per']),
                'ape_pat_per' => $this->limpiarTexto($this->formEditar['ape_pat_per']),
                'ape_mat_per' => $this->limpiarTexto($this->formEditar['ape_mat_per']),
                'ci_per' => $this->limpiarTexto($this->formEditar['ci_per']),
                'com_per' => $this->limpiarTexto($this->formEditar['com_per']),
                'exp_per' => $this->limpiarTexto($this->formEditar['exp_per']),
                'fec_nac_per' => $this->formEditar['fec_nac_per'],
                'gen_per' => $this->formEditar['gen_per'],
                'tel_per' => $this->limpiarTexto($this->formEditar['tel_per']),
                'ema_per' => $this->limpiarCorreo($this->formEditar['ema_per']),
                'dir_per' => $this->limpiarTexto($this->formEditar['dir_per']),
                'fot_per' => $rutaFoto,
                'est_per' => (bool) $this->formEditar['est_per'],
            ]);

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

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */
    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'genero',
            'estado',
            'cuentaUsuario',
        ]);

        $this->resetPage();
        $this->actualizarGraficos();
    }

    private function personasQuery()
    {
        return Persona::query()
            ->with('usuario')
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('nom_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ape_pat_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ape_mat_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ci_per', 'ILIKE', "%{$search}%")
                        ->orWhere('tel_per', 'ILIKE', "%{$search}%")
                        ->orWhere('ema_per', 'ILIKE', "%{$search}%")
                        ->orWhereRaw(
                            "CONCAT(nom_per, ' ', ape_pat_per, ' ', COALESCE(ape_mat_per, '')) ILIKE ?",
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
            ->orderByDesc('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Bitácora
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function limpiarTexto(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    private function limpiarCorreo(?string $valor): ?string
    {
        $valor = trim(mb_strtolower((string) $valor));

        return $valor === '' ? null : $valor;
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

    public function puedeGuardarPersona(): bool
    {
        return filled($this->form['nom_per'] ?? null)
            && filled($this->form['ape_pat_per'] ?? null)
            && filled($this->form['ci_per'] ?? null)
            && filled($this->form['exp_per'] ?? null)
            && filled($this->form['fec_nac_per'] ?? null)
            && filled($this->form['gen_per'] ?? null);
    }

    public function puedeActualizarPersona(): bool
    {
        return filled($this->formEditar['cod_per'] ?? null)
            && filled($this->formEditar['nom_per'] ?? null)
            && filled($this->formEditar['ape_pat_per'] ?? null)
            && filled($this->formEditar['ci_per'] ?? null)
            && filled($this->formEditar['exp_per'] ?? null)
            && filled($this->formEditar['fec_nac_per'] ?? null)
            && filled($this->formEditar['gen_per'] ?? null);
    }

    /*
    |--------------------------------------------------------------------------
    | Propiedades computadas
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
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
        ]);
    }
}
