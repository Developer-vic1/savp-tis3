<?php

namespace App\Livewire\Admin;

use App\Models\Administrador;
use App\Models\Bitacora;
use App\Models\Director;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\Regente;
use App\Models\SecretariaGeneral;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class GestionUsuarios extends Component
{
    use WithPagination;

    private const ROLES_ADMINISTRATIVOS = [
        'Administrador',
        'Director',
        'Secretaria',
        'Secretaria Académica',
        'Regente',
    ];

    private const ROLES_PERSONAL_INSTITUCIONAL = [
        'Administrador',
        'Director',
        'Docente',
        'Secretaria',
        'Secretaria Académica',
        'Regente',
    ];

    protected $paginationTheme = 'tailwind';

    /*
    |--------------------------------------------------------------------------
    | FILTROS Y TABLA
    |--------------------------------------------------------------------------
    */
    public string $search = '';

    public string $rol = '';

    public string $estado = '';

    public int $perPage = 10;

    public array $selected = [];

    public bool $selectAll = false;

    public string $accionLote = '';

    /*
    |--------------------------------------------------------------------------
    | MODAL CREAR USUARIO
    |--------------------------------------------------------------------------
    */
    public bool $modalCrear = false;

    public array $form = [
        'cod_per' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
        'est_usu' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | MODAL EDITAR USUARIO
    |--------------------------------------------------------------------------
    */
    public bool $modalEditar = false;

    public ?User $usuarioDetalle = null;

    public array $formEditar = [
        'cod_usu' => '',
        'email' => '',
        'role' => '',
        'est_usu' => 'ACTIVO',
        'password' => '',
        'password_confirmation' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | MODAL VER USUARIO
    |--------------------------------------------------------------------------
    */
    public bool $modalVer = false;

    public array $formVer = [
        'cod_usu' => '',
        'email' => '',
        'role' => '',
        'est_usu' => 'ACTIVO',
    ];

    /*
    |--------------------------------------------------------------------------
    | REGLAS
    |--------------------------------------------------------------------------
    */
    protected function rules(): array
    {
        $rules = [
            'form.cod_per' => ['required', 'exists:persona,cod_per', 'unique:users,cod_per'],
            'form.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'form.password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Za-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'form.role' => ['required', 'exists:roles,name'],
        ];

        if (Schema::hasColumn('users', 'est_usu')) {
            $rules['form.est_usu'] = ['required', 'in:ACTIVO,INACTIVO'];
        }

        return $rules;
    }

    protected array $messages = [
        'form.cod_per.required' => 'Debes seleccionar una persona.',
        'form.cod_per.exists' => 'La persona seleccionada no existe.',
        'form.cod_per.unique' => 'La persona seleccionada ya tiene una cuenta de usuario.',
        'form.email.required' => 'El correo electrónico es obligatorio.',
        'form.email.email' => 'Debes ingresar un correo electrónico válido.',
        'form.email.unique' => 'Ese correo electrónico ya está registrado.',
        'form.password.required' => 'La contraseña es obligatoria.',
        'form.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'form.password.confirmed' => 'La confirmación de contraseña no coincide.',
        'form.password.regex' => 'La contraseña debe incluir letras, números y al menos un carácter especial.',
        'form.role.required' => 'Debes seleccionar un rol.',
        'form.role.exists' => 'El rol seleccionado no existe.',
        'form.est_usu.required' => 'Debes seleccionar un estado.',
        'form.est_usu.in' => 'El estado seleccionado no es válido.',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZACIONES DE FILTROS
    |--------------------------------------------------------------------------
    */
    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingRol(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL
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

    public function resetFormulario(): void
    {
        $this->form = [
            'cod_per' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'role' => '',
            'est_usu' => 'ACTIVO',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR USUARIO
    |--------------------------------------------------------------------------
    */
    public function guardarUsuario(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'cod_usu' => $this->generarCodigoUsuario(),
                'cod_per' => $this->form['cod_per'],
                'email' => $this->form['email'],
                'password' => Hash::make($this->form['password']),
            ];

            if (Schema::hasColumn('users', 'est_usu')) {
                $data['est_usu'] = $this->form['est_usu'];
            }

            $user = User::create($data);

            $user->syncRoles([$this->form['role']]);

            $this->sincronizarPerfilUsuario($user);

            DB::commit();

            $this->cerrarModalCrear();
            $this->dispatch('usuario-creado');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->addError('general', 'Ocurrió un error al crear el usuario. Intenta nuevamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VER USUARIO
    |--------------------------------------------------------------------------
    */
    public function abrirModalVer(string $codUsu): void
    {
        $this->usuarioDetalle = User::with(['persona', 'roles'])
            ->where('cod_usu', $codUsu)
            ->first();

        if (! $this->usuarioDetalle) {
            return;
        }

        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->usuarioDetalle = null;
    }

    public function abrirModalEditar(string $codUsu): void
    {
        $usuario = User::with('roles')
            ->where('cod_usu', $codUsu)
            ->first();

        if (! $usuario) {
            return;
        }

        if ($usuario->est_usu === 'INACTIVO') {
            return;
        }

        $this->resetValidation();

        $this->formEditar = [
            'cod_usu' => $usuario->cod_usu,
            'email' => $usuario->email,
            'role' => $usuario->roles->first()?->name ?? '',
            'est_usu' => $usuario->est_usu ?? 'ACTIVO',
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->modalEditar = true;
    }

    public function cerrarModalEditar(): void
    {
        $this->modalEditar = false;
        $this->resetValidation();

        $this->formEditar = [
            'cod_usu' => '',
            'email' => '',
            'role' => '',
            'est_usu' => 'ACTIVO',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function guardarEdicionUsuario(): void
    {
        $this->validate([
            'formEditar.cod_usu' => ['required', 'exists:users,cod_usu'],
            'formEditar.email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $this->formEditar['cod_usu'] . ',cod_usu',
            ],
            'formEditar.role' => ['required', 'exists:roles,name'],
            'formEditar.est_usu' => ['required', 'in:ACTIVO,INACTIVO'],
            'formEditar.password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Za-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'formEditar.email.required' => 'El correo electrónico es obligatorio.',
            'formEditar.email.email' => 'Debes ingresar un correo electrónico válido.',
            'formEditar.email.unique' => 'Ese correo ya está registrado por otro usuario.',
            'formEditar.role.required' => 'Debes seleccionar un rol.',
            'formEditar.role.exists' => 'El rol seleccionado no existe.',
            'formEditar.est_usu.required' => 'Debes seleccionar un estado.',
            'formEditar.est_usu.in' => 'El estado seleccionado no es válido.',
            'formEditar.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'formEditar.password.confirmed' => 'La confirmación de contraseña no coincide.',
            'formEditar.password.regex' => 'La contraseña debe incluir letras, números y al menos un carácter especial.',
        ]);

        DB::beginTransaction();

        try {
            $usuario = User::with('roles')
                ->where('cod_usu', $this->formEditar['cod_usu'])
                ->first();

            if (! $usuario) {
                DB::rollBack();
                return;
            }

            $rolAnterior = $usuario->roles->first()?->name;
            $rolNuevo = $this->formEditar['role'];

            $data = [
                'email' => $this->formEditar['email'],
                'est_usu' => $this->formEditar['est_usu'],
            ];

            if (! empty($this->formEditar['password'])) {
                $data['password'] = Hash::make($this->formEditar['password']);
            }

            $usuario->update($data);

            if ($rolAnterior && $rolAnterior !== $rolNuevo) {
                $this->desactivarPerfilAnterior($usuario, $rolAnterior);
            }

            $usuario->syncRoles([$rolNuevo]);

            $usuario->load('roles');

            $this->sincronizarPerfilUsuario($usuario);

            DB::commit();

            $this->cerrarModalEditar();
            $this->dispatch('usuario-actualizado');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->addError('editar_general', 'Ocurrió un error al actualizar el usuario.');
        }
    }

    private function desactivarPerfilAnterior(User $usuario, string $rolAnterior): void
    {
        if (! $usuario->cod_per) {
            return;
        }

        if ($rolAnterior === 'Estudiante') {
            Estudiante::where('cod_per', $usuario->cod_per)->update(['est_est' => 'INACTIVO']);

            return;
        }

        $personal = PersonalInstitucional::where('cod_per', $usuario->cod_per)->first();

        if ($personal) {
            match ($rolAnterior) {
                'Administrador' => Administrador::where('cod_pin', $personal->cod_pin)->update(['est_adm' => 'INACTIVO']),
                'Director' => Director::where('cod_pin', $personal->cod_pin)->update(['est_dir' => 'INACTIVO']),
                'Docente' => Docente::where('cod_pin', $personal->cod_pin)->update(['est_doc' => 'INACTIVO']),
                'Secretaria', 'Secretaria Académica' => SecretariaGeneral::where('cod_pin', $personal->cod_pin)->update(['est_sge' => 'INACTIVO']),
                'Regente' => Regente::where('cod_pin', $personal->cod_pin)->update(['est_reg' => 'INACTIVO']),
                default => null,
            };
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SELECCIÓN MÚLTIPLE
    |--------------------------------------------------------------------------
    */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $usuarioActual = Auth::user()?->cod_usu;

            $this->selected = $this->usuariosQuery()
                ->where('cod_usu', '!=', $usuarioActual)
                ->pluck('cod_usu')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'rol', 'estado', 'accionLote']);
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCIONES MASIVAS
    |--------------------------------------------------------------------------
    */
    public function aplicarAccionLote(): void
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            return;
        }

        if (empty($this->selected) || empty($this->accionLote)) {
            return;
        }

        $usuarios = User::query()
            ->whereIn('cod_usu', $this->selected)
            ->get();

        $usuarioActual = Auth::user()?->cod_usu;

        if ($this->accionLote === 'inactivar') {
            foreach ($usuarios as $usuario) {
                if ($usuario->cod_usu !== $usuarioActual) {
                    $usuario->update(['est_usu' => 'INACTIVO']);
                }
            }

            $this->dispatch('usuarios-desactivados');
        }

        if ($this->accionLote === 'activar') {
            foreach ($usuarios as $usuario) {
                $usuario->update(['est_usu' => 'ACTIVO']);
            }

            $this->dispatch('usuarios-reactivados');
        }

        $this->registrarBitacora(
            'ACCION_REGISTROS_USUARIOS',
            'users',
            'Acción de lote aplicada: ' . $this->accionLote
        );

        $this->selected = [];
        $this->selectAll = false;
        $this->accionLote = '';
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY BASE
    |--------------------------------------------------------------------------
    */
    private function usuariosQuery()
    {
        return User::query()
            ->with(['persona', 'roles'])
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('cod_usu', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhereHas('persona', function ($qp) use ($search) {
                            $qp->where('nom_per', 'ilike', "%{$search}%")
                                ->orWhere('ape_pat_per', 'ilike', "%{$search}%")
                                ->orWhere('ape_mat_per', 'ilike', "%{$search}%")
                                ->orWhereRaw(
                                    "CONCAT(nom_per, ' ', ape_pat_per, ' ', ape_mat_per) ILIKE ?",
                                    ["%{$search}%"]
                                );
                        });
                });
            })
            ->when($this->rol, function ($query) {
                $query->role($this->rol);
            })
            ->when($this->estado && Schema::hasColumn('users', 'est_usu'), function ($query) {
                $query->where('est_usu', $this->estado);
            })
            ->orderByDesc('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS AUXILIARES
    |--------------------------------------------------------------------------
    */
    public function getRolesDisponiblesProperty()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();
    }

    public function getPersonasDisponiblesProperty()
    {
        return Persona::query()
            ->whereNotIn('cod_per', User::query()->select('cod_per'))
            ->orderBy('nom_per')
            ->orderBy('ape_pat_per')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTRICAS
    |--------------------------------------------------------------------------
    */
    public function getTotalUsuariosProperty(): int
    {
        return User::count();
    }

    public function getTotalEstudiantesProperty(): int
    {
        return User::role('Estudiante')->count();
    }

    public function getTotalDocentesProperty(): int
    {
        return User::role('Docente')->count();
    }

    public function getTotalAdministrativosProperty(): int
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', self::ROLES_ADMINISTRATIVOS);
        })->count();
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAR CÓDIGO
    |--------------------------------------------------------------------------
    */
    private function generarCodigoUsuario(): string
    {
        $ultimo = User::query()
            ->select('cod_usu')
            ->where('cod_usu', 'like', 'USU_%')
            ->orderByDesc('cod_usu')
            ->value('cod_usu');

        if (! $ultimo) {
            return 'USU_0001';
        }

        $numero = (int) str_replace('USU_', '', $ultimo);
        $nuevo = $numero + 1;

        return 'USU_' . str_pad((string) $nuevo, 4, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCIONES INDIVIDUALES
    |--------------------------------------------------------------------------
    */
    public function desactivarUsuario(string $codUsu): void
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            return;
        }

        if (Auth::user()?->cod_usu === $codUsu) {
            $this->dispatch('no-puedes-desactivarte');

            return;
        }

        $usuario = User::where('cod_usu', $codUsu)->first();

        if (! $usuario) {
            return;
        }

        if ($usuario->est_usu === 'INACTIVO') {
            return;
        }

        $usuario->update([
            'est_usu' => 'INACTIVO',
        ]);

        $this->registrarBitacora(
            'DESACTIVAR_USUARIO',
            'users',
            'Usuario desactivado: ' . $usuario->cod_usu
        );

        $this->dispatch('usuario-desactivado');
    }

    public function reactivarUsuario(string $codUsu): void
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            return;
        }

        $usuario = User::where('cod_usu', $codUsu)->first();

        if (! $usuario) {
            return;
        }

        if ($usuario->est_usu === 'ACTIVO') {
            return;
        }

        $usuario->update([
            'est_usu' => 'ACTIVO',
        ]);

        $this->registrarBitacora(
            'REACTIVAR_USUARIO',
            'users',
            'Usuario reactivado: ' . $usuario->cod_usu
        );

        $this->dispatch('usuario-reactivado');
    }

    public function getHayUsuariosParaSincronizarProperty(): bool
    {
        $usuarios = User::with('roles')->get();

        foreach ($usuarios as $usuario) {
            if ($this->usuarioTienePerfilFaltante($usuario)) {
                logger('Usuario con perfil faltante', [
                    'cod_usu' => $usuario->cod_usu,
                    'cod_per' => $usuario->cod_per,
                    'rol' => $usuario->roles->first()?->name,
                ]);

                return true;
            }
        }

        return false;
    }

    private function usuarioTienePerfilFaltante(User $usuario): bool
    {
        $rol = $usuario->roles->first()?->name;

        if (! $rol || ! $usuario->cod_per) {
            return false;
        }

        if ($rol === 'Estudiante') {
            return ! Estudiante::where('cod_per', $usuario->cod_per)
                ->where('est_est', 'ACTIVO')
                ->exists();
        }

        if (in_array($rol, self::ROLES_PERSONAL_INSTITUCIONAL, true)) {
            $personal = PersonalInstitucional::where('cod_per', $usuario->cod_per)->first();

            if (! $personal) {
                return true;
            }

            if ($personal->car_pin !== $rol || $personal->est_pin !== 'ACTIVO') {
                return true;
            }

            return match ($rol) {
                'Administrador' => ! Administrador::where('cod_pin', $personal->cod_pin)->where('est_adm', 'ACTIVO')->exists(),
                'Director' => ! Director::where('cod_pin', $personal->cod_pin)->where('est_dir', 'ACTIVO')->exists(),
                'Docente' => ! Docente::where('cod_pin', $personal->cod_pin)->where('est_doc', 'ACTIVO')->exists(),
                'Secretaria', 'Secretaria Académica' => ! SecretariaGeneral::where('cod_pin', $personal->cod_pin)->where('est_sge', 'ACTIVO')->exists(),
                'Regente' => ! Regente::where('cod_pin', $personal->cod_pin)->where('est_reg', 'ACTIVO')->exists(),
                default => false,
            };
        }

        return false;
    }

    public function sincronizarDatosUsuarios(): void
    {
        DB::beginTransaction();

        try {
            $usuarios = User::with('roles')->get();
            $sincronizados = 0;

            foreach ($usuarios as $usuario) {
                if ($this->usuarioTienePerfilFaltante($usuario)) {
                    $this->sincronizarPerfilUsuario($usuario);
                    $sincronizados++;
                }
            }

            $this->registrarBitacora(
                'SINCRONIZAR_DATOS_USUARIOS',
                'users',
                'Registros sincronizados: ' . $sincronizados
            );

            DB::commit();

            $this->dispatch('usuarios-sincronizados', cantidad: $sincronizados);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->dispatch('error-sincronizacion');
        }
    }

    private function sincronizarPerfilUsuario(User $usuario): void
    {
        $rol = $usuario->roles->first()?->name;

        if (! $rol || ! $usuario->cod_per) {
            return;
        }

        if ($rol === 'Estudiante') {
            Estudiante::updateOrCreate(
                ['cod_per' => $usuario->cod_per],
                [
                    'rud_est' => 'AUTO-' . $usuario->cod_per,
                    'cod_tve' => 'TVE_0002',
                    'cod_ipe' => 'IPE_0001',
                    'cod_esp' => 'ESP_0001',
                    'est_est' => 'ACTIVO',
                ]
            );

            return;
        }

        if (! in_array($rol, self::ROLES_PERSONAL_INSTITUCIONAL, true)) {
            return;
        }

        $personal = PersonalInstitucional::firstOrCreate(
            ['cod_per' => $usuario->cod_per],
            [
                'car_pin' => $rol,
                'est_pin' => 'ACTIVO',
            ]
        );

        $personal->update([
            'car_pin' => $rol,
            'est_pin' => 'ACTIVO',
        ]);

        match ($rol) {
            'Administrador' => Administrador::updateOrCreate(
                ['cod_pin' => $personal->cod_pin],
                ['est_adm' => 'ACTIVO']
            ),

            'Director' => Director::updateOrCreate(
                ['cod_pin' => $personal->cod_pin],
                ['est_dir' => 'ACTIVO']
            ),

            'Docente' => Docente::updateOrCreate(
                ['cod_pin' => $personal->cod_pin],
                [
                    'esp_doc' => null,
                    'est_doc' => 'ACTIVO',
                ]
            ),

            'Secretaria', 'Secretaria Académica' => SecretariaGeneral::updateOrCreate(
                ['cod_pin' => $personal->cod_pin],
                ['est_sge' => 'ACTIVO']
            ),

            'Regente' => Regente::updateOrCreate(
                ['cod_pin' => $personal->cod_pin],
                ['est_reg' => 'ACTIVO']
            ),

            default => null,
        };
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

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $usuarios = $this->usuariosQuery()->paginate($this->perPage);

        return view('livewire.admin.gestion-usuarios', [
            'usuarios' => $usuarios,
            'rolesDisponibles' => $this->rolesDisponibles,
            'personasDisponibles' => $this->personasDisponibles,
            'totalUsuarios' => $this->totalUsuarios,
            'totalEstudiantes' => $this->totalEstudiantes,
            'totalDocentes' => $this->totalDocentes,
            'totalAdministrativos' => $this->totalAdministrativos,
            'hayUsuariosParaSincronizar' => $this->hayUsuariosParaSincronizar,
        ]);
    }
}
