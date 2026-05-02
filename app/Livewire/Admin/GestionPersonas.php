<?php

namespace App\Livewire\Admin;

use App\Models\Persona;
use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
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

    public string $search = '';
    public string $genero = '';
    public string $estado = '';
    public string $cuentaUsuario = '';
    public int $perPage = 10;

    public bool $modalCrear = false;
    public bool $modalVer = false;
    public bool $modalEditar = false;

    public ?Persona $personaDetalle = null;

    public $foto = null;
    public $fotoEditar = null;

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

    protected array $messages = [
        'form.nom_per.required' => 'El nombre es obligatorio.',
        'form.ape_pat_per.required' => 'El apellido paterno es obligatorio.',
        'form.ci_per.required' => 'El CI es obligatorio.',
        'form.ci_per.unique' => 'Ese CI ya está registrado.',
        'form.exp_per.required' => 'Debes seleccionar el lugar de expedición.',
        'form.fec_nac_per.required' => 'La fecha de nacimiento es obligatoria.',
        'form.gen_per.required' => 'Debes seleccionar el género.',
        'form.ema_per.email' => 'Debes ingresar un correo válido.',
        'foto.image' => 'El archivo debe ser una imagen.',
        'foto.max' => 'La imagen no debe superar los 2 MB.',

        'formEditar.nom_per.required' => 'El nombre es obligatorio.',
        'formEditar.ape_pat_per.required' => 'El apellido paterno es obligatorio.',
        'formEditar.ci_per.required' => 'El CI es obligatorio.',
        'formEditar.ci_per.unique' => 'Ese CI ya está registrado por otra persona.',
        'formEditar.exp_per.required' => 'Debes seleccionar el lugar de expedición.',
        'formEditar.fec_nac_per.required' => 'La fecha de nacimiento es obligatoria.',
        'formEditar.gen_per.required' => 'Debes seleccionar el género.',
        'formEditar.ema_per.email' => 'Debes ingresar un correo válido.',
        'fotoEditar.image' => 'El archivo debe ser una imagen.',
        'fotoEditar.max' => 'La imagen no debe superar los 2 MB.',
    ];

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

    public function guardarPersona(): void
    {
        $this->validate([
            'form.nom_per' => ['required', 'string', 'max:100'],
            'form.ape_pat_per' => ['required', 'string', 'max:100'],
            'form.ape_mat_per' => ['nullable', 'string', 'max:100'],
            'form.ci_per' => ['required', 'string', 'max:30', 'unique:persona,ci_per'],
            'form.com_per' => ['nullable', 'string', 'max:10'],
            'form.exp_per' => ['required', 'string', 'max:5'],
            'form.fec_nac_per' => ['required', 'date'],
            'form.gen_per' => ['required', 'in:M,F'],
            'form.tel_per' => ['nullable', 'string', 'max:30'],
            'form.ema_per' => ['nullable', 'email', 'max:150'],
            'form.dir_per' => ['nullable', 'string', 'max:255'],
            'form.est_per' => ['required', 'boolean'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $rutaFoto = null;

        if ($this->foto) {
            $rutaFoto = $this->foto->store('personas', 'public');
        }

        $persona = Persona::create([
            'nom_per' => $this->form['nom_per'],
            'ape_pat_per' => $this->form['ape_pat_per'],
            'ape_mat_per' => $this->form['ape_mat_per'],
            'ci_per' => $this->form['ci_per'],
            'com_per' => $this->form['com_per'],
            'exp_per' => $this->form['exp_per'],
            'fec_nac_per' => $this->form['fec_nac_per'],
            'gen_per' => $this->form['gen_per'],
            'tel_per' => $this->form['tel_per'],
            'ema_per' => $this->form['ema_per'],
            'dir_per' => $this->form['dir_per'],
            'fot_per' => $rutaFoto,
            'est_per' => (bool) $this->form['est_per'],
        ]);

        $this->registrarBitacora(
            'CREAR_PERSONA',
            'persona',
            $persona->cod_per
        );

        $this->cerrarModalCrear();
        $this->dispatch('persona-creada');
        $this->actualizarGraficos();
    }

    public function abrirModalVer(string $codPer): void
    {
        $this->personaDetalle = Persona::with('usuario')
            ->where('cod_per', $codPer)
            ->first();

        if (! $this->personaDetalle) {
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
            return;
        }

        $this->resetValidation();
        $this->fotoEditar = null;

        $this->formEditar = [
            'cod_per' => $persona->cod_per,
            'nom_per' => $persona->nom_per,
            'ape_pat_per' => $persona->ape_pat_per,
            'ape_mat_per' => $persona->ape_mat_per,
            'ci_per' => $persona->ci_per,
            'com_per' => $persona->com_per,
            'exp_per' => $persona->exp_per,
            'fec_nac_per' => $persona->fec_nac_per,
            'gen_per' => $persona->gen_per,
            'tel_per' => $persona->tel_per,
            'ema_per' => $persona->ema_per,
            'dir_per' => $persona->dir_per,
            'fot_per' => $persona->fot_per,
            'est_per' => (int) $persona->est_per,
        ];

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->resetValidation();
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

    public function actualizarPersona(): void
    {
        $this->validate([
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
            'formEditar.fec_nac_per' => ['required', 'date'],
            'formEditar.gen_per' => ['required', 'in:M,F'],
            'formEditar.tel_per' => ['nullable', 'string', 'max:30'],
            'formEditar.ema_per' => ['nullable', 'email', 'max:150'],
            'formEditar.dir_per' => ['nullable', 'string', 'max:255'],
            'formEditar.est_per' => ['required', 'boolean'],
            'fotoEditar' => ['nullable', 'image', 'max:2048'],
        ]);

        $persona = Persona::where('cod_per', $this->formEditar['cod_per'])->first();

        if (! $persona) {
            return;
        }

        $rutaFoto = $persona->fot_per;

        if ($this->fotoEditar) {
            if ($persona->fot_per) {
                Storage::disk('public')->delete($persona->fot_per);
            }

            $rutaFoto = $this->fotoEditar->store('personas', 'public');
        }

        $persona->update([
            'nom_per' => $this->formEditar['nom_per'],
            'ape_pat_per' => $this->formEditar['ape_pat_per'],
            'ape_mat_per' => $this->formEditar['ape_mat_per'],
            'ci_per' => $this->formEditar['ci_per'],
            'com_per' => $this->formEditar['com_per'],
            'exp_per' => $this->formEditar['exp_per'],
            'fec_nac_per' => $this->formEditar['fec_nac_per'],
            'gen_per' => $this->formEditar['gen_per'],
            'tel_per' => $this->formEditar['tel_per'],
            'ema_per' => $this->formEditar['ema_per'],
            'dir_per' => $this->formEditar['dir_per'],
            'fot_per' => $rutaFoto,
            'est_per' => (bool) $this->formEditar['est_per'],
        ]);

        $this->registrarBitacora(
            'ACTUALIZAR_PERSONA',
            'persona',
            $persona->cod_per
        );

        $this->cerrarModalEditar();
        $this->dispatch('persona-actualizada');
        $this->actualizarGraficos();
    }

    public function desactivarPersona(string $codPer): void
    {
        $persona = Persona::where('cod_per', $codPer)->first();

        if (! $persona) {
            return;
        }

        $persona->update(['est_per' => false]);

        $this->registrarBitacora(
            'DESACTIVAR_PERSONA',
            'persona',
            $persona->cod_per
        );

        $this->dispatch('persona-desactivada');
        $this->actualizarGraficos();
    }

    public function reactivarPersona(string $codPer): void
    {
        $persona = Persona::where('cod_per', $codPer)->first();

        if (! $persona) {
            return;
        }

        $persona->update(['est_per' => true]);

        $this->registrarBitacora(
            'REACTIVAR_PERSONA',
            'persona',
            $persona->cod_per
        );

        $this->dispatch('persona-reactivada');
        $this->actualizarGraficos();
    }

    public function eliminarFotoEditar(): void
    {
        $persona = Persona::where('cod_per', $this->formEditar['cod_per'] ?? null)->first();

        if (! $persona || ! $persona->fot_per) {
            return;
        }

        Storage::disk('public')->delete($persona->fot_per);

        $persona->update(['fot_per' => null]);

        $this->formEditar['fot_per'] = null;
        $this->fotoEditar = null;

        $this->dispatch('persona-actualizada');
    }

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
                    $q->where('nom_per', 'ilike', "%{$search}%")
                        ->orWhere('ape_pat_per', 'ilike', "%{$search}%")
                        ->orWhere('ape_mat_per', 'ilike', "%{$search}%")
                        ->orWhere('ci_per', 'ilike', "%{$search}%")
                        ->orWhere('tel_per', 'ilike', "%{$search}%")
                        ->orWhere('ema_per', 'ilike', "%{$search}%")
                        ->orWhereRaw(
                            "CONCAT(nom_per, ' ', ape_pat_per, ' ', ape_mat_per) ILIKE ?",
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

    private function registrarBitacora(string $accion, string $tabla, ?string $registro = null): void
    {
        Bitacora::create([
            'acc_bit' => $accion,
            'tab_bit' => $tabla,
            'reg_bit' => $registro,
            'cod_usu' => Auth::user()?->cod_usu,
            'fec_bit' => now(),
            'est_bit' => 'ACTIVO',
        ]);
    }

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
