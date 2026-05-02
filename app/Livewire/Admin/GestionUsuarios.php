<?php

namespace App\Livewire\Admin;

use App\Models\Administrador;
use App\Models\Director;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\Regente;
use App\Models\SecretariaGeneral;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
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
    | Filtros y tabla
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
    | Modal crear usuario
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
    | Modal editar usuario
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
    | Modal ver usuario
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
    | Reglas y mensajes
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
            $rules['form.est_usu'] = ['required', Rule::in(['ACTIVO', 'INACTIVO'])];
        }

        return $rules;
    }

    protected array $messages = [
        'form.cod_per.required' => 'Debes seleccionar una persona.',
        'form.cod_per.exists' => 'La persona seleccionada no existe.',
        'form.cod_per.unique' => 'La persona seleccionada ya tiene una cuenta de usuario.',
        'form.email.required' => 'El correo electrónico es obligatorio.',
        'form.email.email' => 'Debes ingresar un correo electrónico válido.',
        'form.email.max' => 'El correo electrónico no debe superar los 255 caracteres.',
        'form.email.unique' => 'Ese correo electrónico ya está registrado.',
        'form.password.required' => 'La contraseña es obligatoria.',
        'form.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'form.password.confirmed' => 'La confirmación de contraseña no coincide.',
        'form.password.regex' => 'La contraseña debe incluir letras, números y al menos un carácter especial.',
        'form.role.required' => 'Debes seleccionar un rol.',
        'form.role.exists' => 'El rol seleccionado no existe.',
        'form.est_usu.required' => 'Debes seleccionar un estado.',
        'form.est_usu.in' => 'El estado seleccionado no es válido.',

        'formEditar.cod_usu.required' => 'No se pudo identificar al usuario.',
        'formEditar.cod_usu.exists' => 'El usuario seleccionado no existe.',
        'formEditar.email.required' => 'El correo electrónico es obligatorio.',
        'formEditar.email.email' => 'Debes ingresar un correo electrónico válido.',
        'formEditar.email.max' => 'El correo electrónico no debe superar los 255 caracteres.',
        'formEditar.email.unique' => 'Ese correo ya está registrado por otro usuario.',
        'formEditar.role.required' => 'Debes seleccionar un rol.',
        'formEditar.role.exists' => 'El rol seleccionado no existe.',
        'formEditar.est_usu.required' => 'Debes seleccionar un estado.',
        'formEditar.est_usu.in' => 'El estado seleccionado no es válido.',
        'formEditar.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'formEditar.password.confirmed' => 'La confirmación de contraseña no coincide.',
        'formEditar.password.regex' => 'La contraseña debe incluir letras, números y al menos un carácter especial.',
    ];

    /*
    |--------------------------------------------------------------------------
    | Reactividad de filtros
    |--------------------------------------------------------------------------
    */
    public function updatingSearch(): void
    {
        $this->limpiarSeleccionTabla();
        $this->resetPage();
    }

    public function updatingRol(): void
    {
        $this->limpiarSeleccionTabla();
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->limpiarSeleccionTabla();
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->limpiarSeleccionTabla();
        $this->resetPage();
    }

    private function limpiarSeleccionTabla(): void
    {
        $this->selected = [];
        $this->selectAll = false;
        $this->accionLote = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Modal crear
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
    | Crear usuario
    |--------------------------------------------------------------------------
    */
    public function guardarUsuario(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $persona = Persona::where('cod_per', $this->form['cod_per'])->first();

            if (! $persona) {
                DB::rollBack();
                $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');
                return;
            }

            $data = [
                'cod_usu' => $this->generarCodigoUsuario(),
                'cod_per' => $this->form['cod_per'],
                'email' => $this->limpiarCorreo($this->form['email']),
                'password' => Hash::make($this->form['password']),
            ];

            if (Schema::hasColumn('users', 'est_usu')) {
                $data['est_usu'] = $this->form['est_usu'];
            }

            $user = User::create($data);
            $user->syncRoles([$this->form['role']]);
            $user->load(['persona', 'roles']);

            $this->sincronizarPerfilUsuario($user);

            $this->registrarBitacora(
                accion: 'CREAR_USUARIO',
                tabla: 'users',
                registro: $user->cod_usu,
                nombreRegistro: $this->nombreVisibleUsuario($user),
                descripcion: 'Se creó una cuenta de usuario y se asignó el rol correspondiente.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: [
                    'cod_usu' => $user->cod_usu,
                    'cod_per' => $user->cod_per,
                    'email' => $user->email,
                    'rol' => $user->roles->first()?->name,
                    'est_usu' => $user->est_usu ?? null,
                ]
            );

            DB::commit();

            $this->cerrarModalCrear();
            $this->dispatch('usuario-creado');
            $this->dispatch('success-general', mensaje: 'Usuario creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->registrarBitacora(
                accion: 'ERROR_CREAR_USUARIO',
                tabla: 'users',
                registro: null,
                nombreRegistro: $this->form['email'] ?? null,
                descripcion: 'Ocurrió un error al intentar crear una cuenta de usuario.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: [
                    'cod_per' => $this->form['cod_per'] ?? null,
                    'email' => $this->form['email'] ?? null,
                    'role' => $this->form['role'] ?? null,
                    'est_usu' => $this->form['est_usu'] ?? null,
                ],
                error: $e->getMessage()
            );

            $this->addError('general', 'Ocurrió un error al crear el usuario. Intenta nuevamente.');
            $this->dispatch('error-general', mensaje: 'No se pudo crear el usuario. Revisa los datos e intenta nuevamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ver usuario
    |--------------------------------------------------------------------------
    */
    public function abrirModalVer(string $codUsu): void
    {
        $this->usuarioDetalle = User::with(['persona', 'roles'])
            ->where('cod_usu', $codUsu)
            ->first();

        if (! $this->usuarioDetalle) {
            $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');
            return;
        }

        $this->formVer = [
            'cod_usu' => $this->usuarioDetalle->cod_usu,
            'email' => $this->usuarioDetalle->email,
            'role' => $this->usuarioDetalle->roles->first()?->name ?? '',
            'est_usu' => $this->usuarioDetalle->est_usu ?? 'ACTIVO',
        ];

        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->usuarioDetalle = null;

        $this->formVer = [
            'cod_usu' => '',
            'email' => '',
            'role' => '',
            'est_usu' => 'ACTIVO',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Editar usuario
    |--------------------------------------------------------------------------
    */
    public function abrirModalEditar(string $codUsu): void
    {
        $usuario = User::with(['persona', 'roles'])
            ->where('cod_usu', $codUsu)
            ->first();

        if (! $usuario) {
            $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');
            return;
        }

        if (($usuario->est_usu ?? 'ACTIVO') === 'INACTIVO') {
            $this->dispatch('error-general', mensaje: 'No puedes editar un usuario inactivo. Primero debes reactivarlo.');
            return;
        }

        $this->resetValidation();

        $this->usuarioDetalle = $usuario;

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
        $this->usuarioDetalle = null;

        $this->formEditar = [
            'cod_usu' => '',
            'email' => '',
            'role' => '',
            'est_usu' => 'ACTIVO',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    private function rulesEditarUsuario(): array
    {
        return [
            'formEditar.cod_usu' => ['required', 'exists:users,cod_usu'],
            'formEditar.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->formEditar['cod_usu'], 'cod_usu'),
            ],
            'formEditar.role' => ['required', 'exists:roles,name'],
            'formEditar.est_usu' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
            'formEditar.password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Za-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ];
    }

    public function guardarEdicionUsuario(): void
    {
        $this->validate($this->rulesEditarUsuario(), $this->messages);

        DB::beginTransaction();

        try {
            $usuario = User::with(['persona', 'roles'])
                ->where('cod_usu', $this->formEditar['cod_usu'])
                ->first();

            if (! $usuario) {
                DB::rollBack();
                $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');
                return;
            }

            $valoresAnteriores = $this->resumenUsuario($usuario);

            $rolAnterior = $usuario->roles->first()?->name;
            $rolNuevo = $this->formEditar['role'];

            $data = [
                'email' => $this->limpiarCorreo($this->formEditar['email']),
                'est_usu' => $this->formEditar['est_usu'],
            ];

            $passwordCambiada = false;

            if (! empty($this->formEditar['password'])) {
                $data['password'] = Hash::make($this->formEditar['password']);
                $passwordCambiada = true;
            }

            $usuario->update($data);

            if ($rolAnterior && $rolAnterior !== $rolNuevo) {
                $this->desactivarPerfilAnterior($usuario, $rolAnterior);
            }

            $usuario->syncRoles([$rolNuevo]);
            $usuario->load(['persona', 'roles']);

            $this->sincronizarPerfilUsuario($usuario);

            $usuarioActualizado = $usuario->fresh(['persona', 'roles']);

            $this->registrarBitacora(
                accion: 'ACTUALIZAR_USUARIO',
                tabla: 'users',
                registro: $usuarioActualizado->cod_usu,
                nombreRegistro: $this->nombreVisibleUsuario($usuarioActualizado),
                descripcion: $passwordCambiada
                    ? 'Se actualizó la cuenta de usuario, incluyendo cambio de contraseña.'
                    : 'Se actualizó la cuenta de usuario.',
                nivel: $rolAnterior !== $rolNuevo || $passwordCambiada ? 'WARNING' : 'SUCCESS',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenUsuario($usuarioActualizado) + [
                    'password_cambiada' => $passwordCambiada,
                ]
            );

            DB::commit();

            $this->cerrarModalEditar();
            $this->dispatch('usuario-actualizado');
            $this->dispatch('success-general', mensaje: 'Usuario actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->registrarBitacora(
                accion: 'ERROR_ACTUALIZAR_USUARIO',
                tabla: 'users',
                registro: $this->formEditar['cod_usu'] ?? null,
                nombreRegistro: $this->formEditar['email'] ?? null,
                descripcion: 'Ocurrió un error al intentar actualizar una cuenta de usuario.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                valoresNuevos: [
                    'cod_usu' => $this->formEditar['cod_usu'] ?? null,
                    'email' => $this->formEditar['email'] ?? null,
                    'role' => $this->formEditar['role'] ?? null,
                    'est_usu' => $this->formEditar['est_usu'] ?? null,
                ],
                error: $e->getMessage()
            );

            $this->addError('editar_general', 'Ocurrió un error al actualizar el usuario.');
            $this->dispatch('error-general', mensaje: 'No se pudo actualizar el usuario.');
        }
    }

    private function desactivarPerfilAnterior(User $usuario, string $rolAnterior): void
    {
        if (! $usuario->cod_per) {
            return;
        }

        if ($rolAnterior === 'Estudiante') {
            Estudiante::where('cod_per', $usuario->cod_per)
                ->update(['est_est' => 'INACTIVO']);

            return;
        }

        $personal = PersonalInstitucional::where('cod_per', $usuario->cod_per)->first();

        if (! $personal) {
            return;
        }

        match ($rolAnterior) {
            'Administrador' => Administrador::where('cod_pin', $personal->cod_pin)->update(['est_adm' => 'INACTIVO']),
            'Director' => Director::where('cod_pin', $personal->cod_pin)->update(['est_dir' => 'INACTIVO']),
            'Docente' => Docente::where('cod_pin', $personal->cod_pin)->update(['est_doc' => 'INACTIVO']),
            'Secretaria', 'Secretaria Académica' => SecretariaGeneral::where('cod_pin', $personal->cod_pin)->update(['est_sge' => 'INACTIVO']),
            'Regente' => Regente::where('cod_pin', $personal->cod_pin)->update(['est_reg' => 'INACTIVO']),
            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Selección múltiple
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
        $this->limpiarSeleccionTabla();
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones masivas
    |--------------------------------------------------------------------------
    */
    public function aplicarAccionLote(): void
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            $this->dispatch('error-general', mensaje: 'La tabla de usuarios no tiene campo de estado.');
            return;
        }

        if (empty($this->selected) || empty($this->accionLote)) {
            $this->dispatch('error-general', mensaje: 'Selecciona usuarios y una acción para continuar.');
            return;
        }

        if (! in_array($this->accionLote, ['activar', 'inactivar'], true)) {
            $this->dispatch('error-general', mensaje: 'Acción de lote no permitida.');
            return;
        }

        DB::transaction(function () {
            $usuarioActual = Auth::user()?->cod_usu;

            $usuarios = User::query()
                ->with(['persona', 'roles'])
                ->whereIn('cod_usu', $this->selected)
                ->get();

            $afectados = [];
            $omitidos = [];

            foreach ($usuarios as $usuario) {
                if ($this->accionLote === 'inactivar' && $usuario->cod_usu === $usuarioActual) {
                    $omitidos[] = $usuario->cod_usu;
                    continue;
                }

                $estadoNuevo = $this->accionLote === 'activar' ? 'ACTIVO' : 'INACTIVO';

                if (($usuario->est_usu ?? null) === $estadoNuevo) {
                    continue;
                }

                $usuario->update(['est_usu' => $estadoNuevo]);

                $afectados[] = [
                    'cod_usu' => $usuario->cod_usu,
                    'email' => $usuario->email,
                    'estado_nuevo' => $estadoNuevo,
                ];
            }

            $accion = $this->accionLote === 'activar'
                ? 'ACTIVAR_USUARIOS_LOTE'
                : 'DESACTIVAR_USUARIOS_LOTE';

            $this->registrarBitacora(
                accion: $accion,
                tabla: 'users',
                registro: 'LOTE',
                nombreRegistro: 'Acción masiva de usuarios',
                descripcion: 'Se aplicó una acción masiva sobre cuentas de usuario.',
                nivel: $this->accionLote === 'inactivar' ? 'WARNING' : 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: [
                    'accion_lote' => $this->accionLote,
                    'afectados' => $afectados,
                    'omitidos' => $omitidos,
                    'total_afectados' => count($afectados),
                    'total_omitidos' => count($omitidos),
                ]
            );

            $mensaje = $this->accionLote === 'activar'
                ? 'Usuarios reactivados correctamente.'
                : 'Usuarios desactivados correctamente.';

            $this->dispatch($this->accionLote === 'activar' ? 'usuarios-reactivados' : 'usuarios-desactivados');
            $this->dispatch('success-general', mensaje: $mensaje);

            $this->limpiarSeleccionTabla();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Query base
    |--------------------------------------------------------------------------
    */
    private function usuariosQuery()
    {
        return User::query()
            ->with(['persona', 'roles'])
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('cod_usu', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%")
                        ->orWhereHas('persona', function ($qp) use ($search) {
                            $qp->where('nom_per', 'ILIKE', "%{$search}%")
                                ->orWhere('ape_pat_per', 'ILIKE', "%{$search}%")
                                ->orWhere('ape_mat_per', 'ILIKE', "%{$search}%")
                                ->orWhereRaw(
                                    "CONCAT(nom_per, ' ', ape_pat_per, ' ', COALESCE(ape_mat_per, '')) ILIKE ?",
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
    | Datos auxiliares
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
    | Métricas
    |--------------------------------------------------------------------------
    */
    public function getTotalUsuariosProperty(): int
    {
        return User::count();
    }

    public function getTotalActivosProperty(): int
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            return User::count();
        }

        return User::where('est_usu', 'ACTIVO')->count();
    }

    public function getTotalInactivosProperty(): int
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            return 0;
        }

        return User::where('est_usu', 'INACTIVO')->count();
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
    | Generar código
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
    | Acciones individuales
    |--------------------------------------------------------------------------
    */
    public function desactivarUsuario(string $codUsu): void
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            $this->dispatch('error-general', mensaje: 'La tabla de usuarios no tiene campo de estado.');
            return;
        }

        if (Auth::user()?->cod_usu === $codUsu) {
            $this->dispatch('no-puedes-desactivarte');
            $this->dispatch('error-general', mensaje: 'No puedes desactivar tu propia cuenta.');
            return;
        }

        DB::transaction(function () use ($codUsu) {
            $usuario = User::with(['persona', 'roles'])
                ->where('cod_usu', $codUsu)
                ->first();

            if (! $usuario) {
                $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');
                return;
            }

            if (($usuario->est_usu ?? 'ACTIVO') === 'INACTIVO') {
                $this->dispatch('error-general', mensaje: 'El usuario ya se encuentra inactivo.');
                return;
            }

            $valoresAnteriores = $this->resumenUsuario($usuario);

            $usuario->update([
                'est_usu' => 'INACTIVO',
            ]);

            $usuarioActualizado = $usuario->fresh(['persona', 'roles']);

            $this->registrarBitacora(
                accion: 'DESACTIVAR_USUARIO',
                tabla: 'users',
                registro: $usuarioActualizado->cod_usu,
                nombreRegistro: $this->nombreVisibleUsuario($usuarioActualizado),
                descripcion: 'Se desactivó una cuenta de usuario. No se realizó eliminación física.',
                nivel: 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenUsuario($usuarioActualizado)
            );

            $this->dispatch('usuario-desactivado');
            $this->dispatch('success-general', mensaje: 'Usuario desactivado correctamente.');
        });
    }

    public function reactivarUsuario(string $codUsu): void
    {
        if (! Schema::hasColumn('users', 'est_usu')) {
            $this->dispatch('error-general', mensaje: 'La tabla de usuarios no tiene campo de estado.');
            return;
        }

        DB::transaction(function () use ($codUsu) {
            $usuario = User::with(['persona', 'roles'])
                ->where('cod_usu', $codUsu)
                ->first();

            if (! $usuario) {
                $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');
                return;
            }

            if (($usuario->est_usu ?? 'ACTIVO') === 'ACTIVO') {
                $this->dispatch('error-general', mensaje: 'El usuario ya se encuentra activo.');
                return;
            }

            $valoresAnteriores = $this->resumenUsuario($usuario);

            $usuario->update([
                'est_usu' => 'ACTIVO',
            ]);

            $usuarioActualizado = $usuario->fresh(['persona', 'roles']);

            $this->registrarBitacora(
                accion: 'REACTIVAR_USUARIO',
                tabla: 'users',
                registro: $usuarioActualizado->cod_usu,
                nombreRegistro: $this->nombreVisibleUsuario($usuarioActualizado),
                descripcion: 'Se reactivó una cuenta de usuario en el sistema.',
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenUsuario($usuarioActualizado)
            );

            $this->dispatch('usuario-reactivado');
            $this->dispatch('success-general', mensaje: 'Usuario reactivado correctamente.');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Sincronización de perfiles
    |--------------------------------------------------------------------------
    */
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
            $usuarios = User::with(['persona', 'roles'])->get();
            $sincronizados = 0;
            $detalle = [];

            foreach ($usuarios as $usuario) {
                if ($this->usuarioTienePerfilFaltante($usuario)) {
                    $this->sincronizarPerfilUsuario($usuario);
                    $sincronizados++;

                    $detalle[] = [
                        'cod_usu' => $usuario->cod_usu,
                        'email' => $usuario->email,
                        'rol' => $usuario->roles->first()?->name,
                    ];
                }
            }

            $this->registrarBitacora(
                accion: $sincronizados > 0
                    ? 'SINCRONIZAR_PERFILES_USUARIO'
                    : 'REVISAR_SINCRONIZACION_USUARIOS',
                tabla: 'users',
                registro: 'SINCRONIZACION',
                nombreRegistro: $sincronizados > 0
                    ? 'Sincronización de perfiles completada'
                    : 'Sincronización revisada sin cambios',
                descripcion: $sincronizados > 0
                    ? 'Se sincronizaron ' . $sincronizados . ' perfiles institucionales pendientes. El sistema actualizó la relación entre usuarios, personas y roles académicos.'
                    : 'Se ejecutó la revisión de sincronización de usuarios. No se encontraron perfiles institucionales pendientes de actualización.',
                nivel: $sincronizados > 0 ? 'SUCCESS' : 'INFO',
                resultado: 'EXITOSO',
                valoresNuevos: [
                    'total_sincronizados' => $sincronizados,
                    'detalle' => $detalle,
                ]
            );

            DB::commit();

            $this->dispatch('usuarios-sincronizados', cantidad: $sincronizados);
            $this->dispatch('success-general', mensaje: 'Sincronización completada. Registros sincronizados: ' . $sincronizados);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->registrarBitacora(
                accion: 'ERROR_SINCRONIZAR_DATOS_USUARIOS',
                tabla: 'users',
                registro: 'SINCRONIZACION',
                nombreRegistro: 'Sincronización de perfiles de usuario',
                descripcion: 'Ocurrió un error durante la sincronización de perfiles asociados a usuarios.',
                nivel: 'ERROR',
                resultado: 'FALLIDO',
                error: $e->getMessage()
            );

            $this->dispatch('error-sincronizacion');
            $this->dispatch('error-general', mensaje: 'No se pudieron sincronizar los datos. Revisa la consola o el log del sistema.');
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

    /*
    |--------------------------------------------------------------------------
    | Bitácora
    |--------------------------------------------------------------------------
    */
    private function registrarBitacora(
        string $accion,
        string $tabla,
        ?string $registro = null,
        ?string $modulo = 'Gestión de Usuarios',
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
    private function limpiarCorreo(?string $valor): ?string
    {
        $valor = trim(mb_strtolower((string) $valor));

        return $valor === '' ? null : $valor;
    }

    private function nombreVisibleUsuario(?User $usuario): string
    {
        if (! $usuario) {
            return 'Usuario no identificado';
        }

        $persona = $usuario->persona;

        if ($persona) {
            $nombre = trim(collect([
                $persona->nom_per,
                $persona->ape_pat_per,
                $persona->ape_mat_per,
            ])->filter()->implode(' '));

            if ($nombre !== '') {
                return $nombre . ' · ' . $usuario->email;
            }
        }

        return $usuario->email ?? $usuario->cod_usu ?? 'Usuario no identificado';
    }

    private function resumenUsuario(User $usuario): array
    {
        return [
            'cod_usu' => $usuario->cod_usu,
            'cod_per' => $usuario->cod_per,
            'email' => $usuario->email,
            'rol' => $usuario->roles->first()?->name,
            'est_usu' => $usuario->est_usu ?? null,
        ];
    }

    public function puedeGuardarUsuario(): bool
    {
        return filled($this->form['cod_per'] ?? null)
            && filled($this->form['email'] ?? null)
            && filled($this->form['password'] ?? null)
            && filled($this->form['password_confirmation'] ?? null)
            && filled($this->form['role'] ?? null)
            && $this->form['password'] === $this->form['password_confirmation']
            && mb_strlen((string) $this->form['password']) >= 8;
    }

    public function puedeActualizarUsuario(): bool
    {
        $password = (string) ($this->formEditar['password'] ?? '');
        $passwordConfirmation = (string) ($this->formEditar['password_confirmation'] ?? '');

        $passwordValida = $password === ''
            || (
                $password === $passwordConfirmation
                && mb_strlen($password) >= 8
            );

        return filled($this->formEditar['cod_usu'] ?? null)
            && filled($this->formEditar['email'] ?? null)
            && filled($this->formEditar['role'] ?? null)
            && filled($this->formEditar['est_usu'] ?? null)
            && $passwordValida;
    }

    /*
    |--------------------------------------------------------------------------
    | Render
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
            'totalActivos' => $this->totalActivos,
            'totalInactivos' => $this->totalInactivos,
            'totalEstudiantes' => $this->totalEstudiantes,
            'totalDocentes' => $this->totalDocentes,
            'totalAdministrativos' => $this->totalAdministrativos,
            'hayUsuariosParaSincronizar' => $this->hayUsuariosParaSincronizar,
        ]);
    }
}
