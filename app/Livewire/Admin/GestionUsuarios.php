<?php

namespace App\Livewire\Admin;

use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\PersonalInstitucional;
use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\User;
use App\Services\BitacoraService;
use App\Services\EnvioAccesoUsuario;
use App\Services\RoleDashboardResolver;
use App\Services\RolePermissionService;
use App\Support\InstitutionalRoleGovernance;
use App\Support\PermissionLabel;
use App\Support\Usuarios\CorreoInstitucional;
use App\Support\Usuarios\IndicadoresUsuarios;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class GestionUsuarios extends Component
{
    use WithPagination;

    private const ROLES_ADMINISTRATIVOS = [
        'Administrador',
        'Director',
        'Secretaria',
        'Regente',
    ];

    private const ROLES_PERSONAL_INSTITUCIONAL = [
        'Administrador',
        'Director',
        'Docente',
        'Secretaria',
        'Regente',
    ];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasRole('Administrador'), 403);
    }

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

    public bool $accesoPorCorreo = true;

    public ?string $cuentaInvitar = null;

    public string $correoEntrega = '';

    public string $motivoCorreo = '';

    public bool $entregaAutorizada = false;

    public bool $cambiarCorreoEntrega = false;

    public bool $programarActivacion = false;

    public string $fechaActivacion = '';

    public string $motivoPassword = '';

    public string $motivoEstado = '';

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

    public array $actividadUsuario = [];

    public array $permisosUsuario = [];

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
                $this->accesoPorCorreo ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Za-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'form.role' => ['required', Rule::in(app(InstitutionalRoleGovernance::class)->rolesInstitucionales()), Rule::exists('roles', 'name')->where('guard_name', 'web')],
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

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, [10, 20, 50], true)) {
            $this->perPage = 10;
            $this->addError('perPage', 'Elige 10, 20 o 50 usuarios por página.');
        }
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
        $this->correoEntrega = '';
        $this->motivoCorreo = '';
        $this->entregaAutorizada = false;
        $this->cambiarCorreoEntrega = false;
        $this->motivoPassword = '';
        $this->programarActivacion = false;
        $this->fechaActivacion = '';
        $this->accesoPorCorreo = true;
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
    public function updatedFormCodPer(): void
    {
        $persona = Persona::where('cod_per', $this->form['cod_per'])->whereDoesntHave('usuario')->first();
        $this->form['email'] = $persona ? app(CorreoInstitucional::class)->sugerir($persona) : '';
        $this->correoEntrega = $persona?->ema_per ?? '';
        $this->motivoCorreo = '';
        $this->entregaAutorizada = false;
        $this->cambiarCorreoEntrega = false;
    }

    public function guardarUsuario(): void
    {
        $this->authorizeUserAction('usuarios.crear');
        $this->authorizeUserAction('usuarios.asignar_roles');
        $this->accesoPorCorreo = true;
        $personaSeleccionada = Persona::find($this->form['cod_per']);
        $this->form['email'] = $personaSeleccionada ? app(CorreoInstitucional::class)->sugerir($personaSeleccionada) : '';
        $this->validate();
        $this->validarProgramacion();
        if ($this->programarActivacion) {
            $this->form['est_usu'] = 'INACTIVO';
        }
        $this->validarCorreosAcceso($personaSeleccionada, $this->form['email']);
        if ($this->form['est_usu'] === 'ACTIVO' && ($bloqueo = app(EnvioAccesoUsuario::class)->bloqueoConfiguracion())) {
            $this->addError('general', $bloqueo);

            return;
        }

        $enviarAcceso = $this->form['est_usu'] === 'ACTIVO';
        $entrega = $this->correoEntrega;
        $motivo = trim($this->motivoCorreo);
        DB::beginTransaction();

        try {
            app(RolePermissionService::class)->lockRoles();
            $persona = Persona::where('cod_per', $this->form['cod_per'])->lockForUpdate()->first();

            if (! $persona) {
                DB::rollBack();
                $this->dispatch('error-general', mensaje: 'No se encontró la persona seleccionada.');

                return;
            }

            $data = [
                'cod_per' => $this->form['cod_per'],
                'email' => $this->limpiarCorreo($this->form['email']),
                'password' => Hash::make($this->accesoPorCorreo ? Str::random(64) : $this->form['password']),
            ];

            if (Schema::hasColumn('users', 'est_usu')) {
                $data['est_usu'] = $this->form['est_usu'];
            }

            $user = User::create($data);
            app(RolePermissionService::class)->assignActor($user, $this->form['role'], Auth::user());
            $user->load(['persona', 'roles']);

            $this->sincronizarPerfilUsuario($user);

            $this->registrarBitacora(
                accion: 'CREAR_USUARIO',
                tabla: 'users',
                registro: $user->cod_usu,
                nombreRegistro: $this->nombreVisibleUsuario($user),
                descripcion: 'Se creó una cuenta de usuario y se asignó el rol correspondiente.'.($motivo ? ' Motivo del cambio de correo: '.$motivo : ''),
                nivel: 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: [
                    'cod_usu' => $user->cod_usu,
                    'cod_per' => $user->cod_per,
                    'email' => $user->email,
                    'rol' => $this->actorName($user),
                    'est_usu' => $user->est_usu ?? null,
                    'correo_entrega' => $entrega,
                    'motivo_correo' => $motivo,
                ]
            );

            DB::commit();

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

            return;
        }
        $this->cerrarModalCrear();
        $this->dispatch('usuario-creado');
        $this->dispatch('success-general', mensaje: 'Usuario creado correctamente.');
        if ($enviarAcceso) {
            $resultado = app(EnvioAccesoUsuario::class)->enviar($user, $entrega);
            $this->dispatch('toast', type: $resultado['enviado'] ? 'success' : 'warning', message: $resultado['mensaje']);
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
            'role' => $this->actorName($this->usuarioDetalle) ?? '',
            'est_usu' => $this->usuarioDetalle->est_usu ?? 'ACTIVO',
        ];

        $this->permisosUsuario = $this->usuarioDetalle->getAllPermissions()->sortBy('name')->map(fn ($permiso) => PermissionLabel::describe($permiso->name))->values()->all();
        $this->actividadUsuario = Schema::hasTable('bitacora')
            ? Bitacora::where('reg_bit', $this->usuarioDetalle->cod_usu)->where('tab_bit', 'users')
                ->orderByDesc('fec_bit')->limit(8)->get(['des_bit', 'fec_bit', 'niv_bit', 'res_bit'])
                ->map(fn ($registro) => ['descripcion' => $registro->des_bit, 'fecha' => $registro->fec_bit?->timezone('America/La_Paz')->format('d/m/Y H:i'), 'resultado' => $registro->res_bit])->all()
            : [];

        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->usuarioDetalle = null;
        $this->actividadUsuario = [];
        $this->permisosUsuario = [];

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

        $this->resetValidation();

        $this->usuarioDetalle = $usuario;
        $this->motivoPassword = '';
        $this->motivoEstado = '';
        $this->programarActivacion = false;
        $this->fechaActivacion = '';

        $this->formEditar = [
            'cod_usu' => $usuario->cod_usu,
            'email' => $usuario->email,
            'role' => $this->actorName($usuario) ?? '',
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
            'formEditar.role' => ['required', Rule::in(app(InstitutionalRoleGovernance::class)->rolesInstitucionales()), Rule::exists('roles', 'name')->where('guard_name', 'web')],
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
        $this->authorizeUserAction('usuarios.editar');
        $this->authorizeUserAction('usuarios.asignar_roles');
        if (! empty($this->formEditar['password'])) {
            $this->authorizeUserAction('usuarios.reset_password');
        }
        $this->authorizeUserAction(($this->formEditar['est_usu'] ?? '') === 'ACTIVO' ? 'usuarios.activar' : 'usuarios.desactivar');
        $actual = User::findOrFail($this->formEditar['cod_usu']);
        if ($this->limpiarCorreo($this->formEditar['email']) !== $actual->email) {
            $this->addError('formEditar.email', 'El correo de acceso es fijo y no se puede modificar.');

            return;
        }
        $this->validate($this->rulesEditarUsuario(), $this->messages);
        $this->validarProgramacion();
        if ($this->programarActivacion) {
            $this->formEditar['est_usu'] = 'INACTIVO';
        }
        if (! empty($this->formEditar['password'])) {
            $this->motivoPassword = $this->validarMotivo($this->motivoPassword, 'motivoPassword');
        }
        if ($actual->est_usu === 'ACTIVO' && $this->formEditar['est_usu'] === 'INACTIVO') {
            if ($actual->cod_usu === Auth::user()?->cod_usu) {
                $this->addError('formEditar.est_usu', 'No puedes desactivar tu propia cuenta.');

                return;
            }
            $this->motivoEstado = $this->validarMotivo($this->motivoEstado, 'motivoEstado');
        }

        DB::beginTransaction();

        try {
            app(RolePermissionService::class)->lockRoles();
            $usuario = User::with(['persona', 'roles'])
                ->where('cod_usu', $this->formEditar['cod_usu'])
                ->lockForUpdate()
                ->first();

            if (! $usuario) {
                DB::rollBack();
                $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');

                return;
            }

            $valoresAnteriores = $this->resumenUsuario($usuario);

            $rolAnterior = $this->actorName($usuario);
            $rolNuevo = $this->formEditar['role'];

            if ($usuario->hasRole('Administrador')
                && ($rolNuevo !== 'Administrador' || $this->formEditar['est_usu'] !== 'ACTIVO')
                && $this->esUltimoAdministrador($usuario)) {
                DB::rollBack();
                $this->addError('formEditar.role', 'No puedes retirar el rol al último Administrador activo.');

                return;
            }

            $data = [
                'email' => $usuario->email,
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

            app(RolePermissionService::class)->assignActor($usuario, $rolNuevo, Auth::user());
            $usuario->load(['persona', 'roles']);

            $this->sincronizarPerfilUsuario($usuario);

            $usuarioActualizado = $usuario->fresh(['persona', 'roles']);

            $this->registrarBitacora(
                accion: 'ACTUALIZAR_USUARIO',
                tabla: 'users',
                registro: $usuarioActualizado->cod_usu,
                nombreRegistro: $this->nombreVisibleUsuario($usuarioActualizado),
                descripcion: ($passwordCambiada
                    ? 'Se actualizó la cuenta de usuario, incluyendo cambio de contraseña.'
                    : 'Se actualizó la cuenta de usuario.').($passwordCambiada ? ' Motivo del cambio de contraseña: '.$this->motivoPassword : '')
                    .($valoresAnteriores['est_usu'] === 'ACTIVO' && $usuarioActualizado->est_usu === 'INACTIVO' ? ' Motivo de desactivación: '.$this->motivoEstado : ''),
                nivel: $rolAnterior !== $rolNuevo || $passwordCambiada ? 'WARNING' : 'SUCCESS',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenUsuario($usuarioActualizado) + [
                    'password_cambiada' => $passwordCambiada,
                    'motivo_password' => $passwordCambiada ? $this->motivoPassword : null,
                    'motivo_estado' => $this->motivoEstado ?: null,
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
        // Los perfiles y sus hechos históricos se conservan; el acceso depende de RBAC y est_usu.
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
                ->map(fn ($id) => (string) $id)
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
    public function aplicarAccionLote(string $motivo = ''): void
    {
        $this->authorizeUserAction($this->accionLote === 'activar' ? 'usuarios.activar' : 'usuarios.desactivar');
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

        if ($this->accionLote === 'inactivar') {
            $motivo = $this->validarMotivo($motivo, 'motivoEstado');
        }
        DB::transaction(function () use ($motivo) {
            $usuarioActual = Auth::user()?->cod_usu;

            $usuarios = User::query()
                ->with(['persona', 'roles'])
                ->whereIn('cod_usu', $this->selected)
                ->get();

            $afectados = [];
            $omitidos = [];

            foreach ($usuarios as $usuario) {
                if ($this->accionLote === 'inactivar' && ($usuario->cod_usu === $usuarioActual || $this->esUltimoAdministrador($usuario))) {
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
                    'nombre' => $this->nombreVisibleUsuario($usuario),
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
                descripcion: $this->accionLote === 'inactivar'
                    ? $this->descripcionDesactivacion($afectados, $motivo)
                    : 'Se activaron '.count($afectados).' cuentas de usuario.',
                nivel: $this->accionLote === 'inactivar' ? 'WARNING' : 'SUCCESS',
                resultado: 'EXITOSO',
                valoresNuevos: [
                    'accion_lote' => $this->accionLote,
                    'afectados' => $afectados,
                    'omitidos' => $omitidos,
                    'total_afectados' => count($afectados),
                    'total_omitidos' => count($omitidos),
                    'motivo' => $motivo ?: null,
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
        $operador = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        $nombreCompleto = DB::connection()->getDriverName() === 'sqlite'
            ? "nom_per || ' ' || ape_pat_per || ' ' || COALESCE(ape_mat_per, '')"
            : "CONCAT(nom_per, ' ', ape_pat_per, ' ', COALESCE(ape_mat_per, ''))";

        return User::query()
            ->with(['persona', 'roles'])
            ->when($this->search, function ($query) use ($operador, $nombreCompleto) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search, $operador, $nombreCompleto) {
                    $q->where('cod_usu', $operador, "%{$search}%")
                        ->orWhere('email', $operador, "%{$search}%")
                        ->orWhereHas('persona', function ($qp) use ($search, $operador, $nombreCompleto) {
                            $qp->where('nom_per', $operador, "%{$search}%")
                                ->orWhere('ape_pat_per', $operador, "%{$search}%")
                                ->orWhere('ape_mat_per', $operador, "%{$search}%")
                                ->orWhereRaw(
                                    $nombreCompleto.' '.$operador.' ?',
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
            ->orderByDesc('created_at')->orderBy('cod_usu');
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
            ->whereIn('name', app(InstitutionalRoleGovernance::class)->rolesInstitucionales())
            ->orderBy('name')
            ->get();
    }

    public function getPersonasDisponiblesProperty()
    {
        return Persona::query()
            ->whereDoesntHave('usuario')
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
        return \App\Support\Modelos\FormatoCodigoInstitucional::siguiente(DB::connection(), 'users');
    }

    private function esUltimoAdministrador(User $usuario): bool
    {
        // Todas las bajas de administradores comparten este bloqueo transaccional.
        Role::query()->where('name', 'Administrador')->where('guard_name', 'web')->lockForUpdate()->first();
        if (! $usuario->hasRole('Administrador')) {
            return false;
        }

        return User::query()
            ->where('cod_usu', '!=', $usuario->cod_usu)
            ->where('est_usu', 'ACTIVO')
            ->whereHas('roles', fn ($query) => $query->where('name', 'Administrador'))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', array_diff(app(InstitutionalRoleGovernance::class)->rolesInstitucionales(), ['Administrador'])))
            ->doesntExist();
    }

    private function authorizeUserAction(string $permission): void
    {
        abort_unless(Auth::user() && app(RoleDashboardResolver::class)->roleFor(Auth::user()) === 'Administrador' && Auth::user()->can($permission), 403, 'No tienes autorización para realizar esta acción.');
    }

    private function actorName(User $user): ?string
    {
        $names = $user->roles->where('guard_name', 'web')->pluck('name')
            ->intersect(app(InstitutionalRoleGovernance::class)->rolesInstitucionales())->values();

        return $names->count() === 1 ? $names->first() : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones individuales
    |--------------------------------------------------------------------------
    */
    public function desactivarUsuario(string $codUsu, string $motivo = ''): void
    {
        $this->authorizeUserAction('usuarios.desactivar');
        if (! Schema::hasColumn('users', 'est_usu')) {
            $this->dispatch('error-general', mensaje: 'La tabla de usuarios no tiene campo de estado.');

            return;
        }

        if (Auth::user()?->cod_usu === $codUsu) {
            $this->dispatch('no-puedes-desactivarte');
            $this->dispatch('error-general', mensaje: 'No puedes desactivar tu propia cuenta.');

            return;
        }

        $motivo = $this->validarMotivo($motivo, 'motivoEstado');
        DB::transaction(function () use ($codUsu, $motivo) {
            app(RolePermissionService::class)->lockRoles();
            $usuario = User::with(['persona', 'roles'])
                ->where('cod_usu', $codUsu)
                ->lockForUpdate()
                ->first();

            if (! $usuario) {
                $this->dispatch('error-general', mensaje: 'No se encontró el usuario seleccionado.');

                return;
            }

            if (($usuario->est_usu ?? 'ACTIVO') === 'INACTIVO') {
                $this->dispatch('error-general', mensaje: 'El usuario ya se encuentra inactivo.');

                return;
            }

            if ($this->esUltimoAdministrador($usuario)) {
                $this->dispatch('error-general', mensaje: 'No puedes desactivar al último Administrador activo.');

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
                descripcion: 'Se desactivó a '.$this->nombreVisibleUsuario($usuarioActualizado).'. Motivo: '.$motivo,
                nivel: 'WARNING',
                resultado: 'EXITOSO',
                valoresAnteriores: $valoresAnteriores,
                valoresNuevos: $this->resumenUsuario($usuarioActualizado) + ['motivo' => $motivo]
            );

            $this->dispatch('usuario-desactivado');
            $this->dispatch('success-general', mensaje: 'Usuario desactivado correctamente.');
        });
    }

    public function reactivarUsuario(string $codUsu): void
    {
        $this->authorizeUserAction('usuarios.activar');
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
                    'rol' => $this->actorName($usuario),
                ]);

                return true;
            }
        }

        return false;
    }

    private function usuarioTienePerfilFaltante(User $usuario): bool
    {
        $rol = $this->actorName($usuario);
        if (! $rol || ! $usuario->cod_per) {
            return false;
        }
        if ($rol === 'Estudiante') {
            return ! Estudiante::where('cod_per', $usuario->cod_per)->where('est_est', 'ACTIVO')->exists();
        }
        if (! in_array($rol, self::ROLES_PERSONAL_INSTITUCIONAL, true)) {
            return false;
        }
        $personal = PersonalInstitucional::where('cod_per', $usuario->cod_per)->where('est_pin', 'ACTIVO')->first();
        return ! $personal || ($rol === 'Docente' && ! Docente::where('cod_pin', $personal->cod_pin)->where('est_doc', 'ACTIVO')->exists());
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
                        'rol' => $this->actorName($usuario),
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
                    ? 'Se sincronizaron '.$sincronizados.' perfiles institucionales pendientes. El sistema actualizó la relación entre usuarios, personas y roles académicos.'
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
            $this->dispatch('success-general', mensaje: 'Sincronización completada. Registros sincronizados: '.$sincronizados);
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
        if ($this->usuarioTienePerfilFaltante($usuario)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'form.role' => 'La persona debe tener su perfil institucional registrado y activo antes de asignar este rol. No se generarán RUDE, procedencias ni perfiles con datos incompletos.',
            ]);
        }
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

    private function validarMotivo(string $motivo, string $campo): string
    {
        Validator::make([$campo => trim($motivo)], [$campo => ['required', 'string', 'min:10', 'max:500']], [
            $campo.'.required' => 'Describe el motivo de esta acción. Se guardará en bitácora.',
            $campo.'.min' => 'El motivo debe tener al menos 10 caracteres.',
        ])->validate();

        return $motivo;
    }

    private function descripcionDesactivacion(array $afectados, string $motivo): string
    {
        $cantidad = count($afectados);
        $detalle = $cantidad > 3 ? $cantidad.' cuentas'
            : ($cantidad ? implode(', ', array_column($afectados, 'nombre')) : 'ninguna cuenta');

        return ($cantidad > 3 ? 'Se desactivaron '.$detalle : ($cantidad ? 'Se desactivaron las cuentas de: '.$detalle : 'No se desactivaron cuentas')).'. Motivo: '.$motivo;
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
                return $nombre.' · '.$usuario->email;
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
            'rol' => $this->actorName($usuario),
            'est_usu' => $usuario->est_usu ?? null,
        ];
    }

    public function puedeGuardarUsuario(): bool
    {
        return filled($this->form['cod_per'] ?? null)
            && filter_var($this->form['email'] ?? '', FILTER_VALIDATE_EMAIL)
            && filled($this->form['role'] ?? null)
            && filter_var($this->correoEntrega, FILTER_VALIDATE_EMAIL)
            && $this->entregaAutorizada
            && (! $this->requiereMotivoCorreo() || mb_strlen(trim($this->motivoCorreo)) >= 10)
            && (($this->form['est_usu'] ?? 'ACTIVO') === 'INACTIVO' || ($this->accesoPorCorreo ? app(EnvioAccesoUsuario::class)->bloqueoConfiguracion() === null
                : ($this->form['password'] === $this->form['password_confirmation'] && mb_strlen((string) $this->form['password']) >= 8)))
            && ! $this->programarActivacion;
    }

    public function updatedProgramarActivacion(): void
    {
        if ($this->programarActivacion) {
            if ($this->modalCrear) {
                $this->form['est_usu'] = 'INACTIVO';
            } else {
                $this->formEditar['est_usu'] = 'INACTIVO';
            }
        } else {
            $this->fechaActivacion = '';
        }
    }

    private function validarProgramacion(): void
    {
        if (! $this->programarActivacion) {
            return;
        }
        $this->authorizeUserAction('usuarios.activar');
        $this->authorizeUserAction('usuarios.desactivar');
        $this->validate(['fechaActivacion' => ['required', 'date_format:Y-m-d', 'after:'.now('America/La_Paz')->toDateString()]]);
        throw ValidationException::withMessages(['fechaActivacion' => 'La programación de incorporaciones todavía no está disponible para confirmar.']);
    }

    public function habilitarCambioCorreoEntrega(): void
    {
        $this->cambiarCorreoEntrega = true;
        $this->entregaAutorizada = false;
    }

    public function restaurarCorreoEntrega(): void
    {
        $this->cambiarCorreoEntrega = false;
        $this->correoEntrega = $this->correoPersonalSugerido();
        $this->motivoCorreo = '';
        $this->entregaAutorizada = false;
        $this->resetValidation(['correoEntrega', 'motivoCorreo']);
    }

    public function updatedCorreoEntrega(): void
    {
        $this->entregaAutorizada = false;
    }

    public function correoPersonalSugerido(): string
    {
        $codPer = $this->cuentaInvitar
            ? User::find($this->cuentaInvitar)?->cod_per : ($this->form['cod_per'] ?? null);

        return (string) Persona::find($codPer)?->ema_per;
    }

    public function requiereMotivoCorreo(): bool
    {
        return $this->limpiarCorreo($this->correoEntrega) !== $this->limpiarCorreo($this->correoPersonalSugerido());
    }

    private function validarCorreosAcceso(?Persona $persona, ?string $correoAcceso = null): void
    {
        $this->correoEntrega = (string) $this->limpiarCorreo($this->correoEntrega);
        $personal = $this->limpiarCorreo($persona?->ema_per);
        $cambio = $this->correoEntrega !== $personal;
        $this->validate([
            'correoEntrega' => ['required', 'email', 'max:255'],
            'entregaAutorizada' => ['accepted'],
            'motivoCorreo' => [$cambio ? 'required' : 'nullable', 'string', 'min:10', 'max:500'],
        ], [
            'correoEntrega.required' => 'Indica el correo donde la persona recibirá su acceso.',
            'entregaAutorizada.accepted' => 'Confirma que la persona autorizó este destinatario.',
            'motivoCorreo.required' => 'Explica por qué cambias el correo sugerido. El motivo quedará en bitácora.',
            'motivoCorreo.min' => 'Describe el motivo con al menos 10 caracteres.',
        ]);
        if ($correoAcceso && app(CorreoInstitucional::class)->ocupado($correoAcceso)) {
            throw ValidationException::withMessages(['form.email' => 'Ese correo o una variante equivalente de Gmail ya está registrado. Revisa si la persona ya tiene una cuenta. No se modificaron los datos.']);
        }
    }

    public function prepararInvitacion(string $codUsu): void
    {
        $this->authorizeUserAction('usuarios.reset_password');
        $this->resetValidation();
        $usuario = User::with('persona')->where('cod_usu', $codUsu)->where('est_usu', 'ACTIVO')->firstOrFail();
        $this->cuentaInvitar = $usuario->cod_usu;
        $this->correoEntrega = $usuario->persona?->ema_per ?? '';
        $this->motivoCorreo = '';
        $this->motivoPassword = '';
        $this->entregaAutorizada = false;
        $this->cambiarCorreoEntrega = false;
    }

    public function cancelarInvitacion(): void
    {
        $this->cuentaInvitar = null;
        $this->correoEntrega = '';
        $this->motivoCorreo = '';
        $this->motivoPassword = '';
        $this->entregaAutorizada = false;
        $this->resetValidation();
    }

    public function enviarInvitacion(): void
    {
        $this->authorizeUserAction('usuarios.reset_password');
        $usuario = User::with('persona')->where('cod_usu', $this->cuentaInvitar)->firstOrFail();
        $this->validarCorreosAcceso($usuario->persona);
        $this->motivoPassword = $this->validarMotivo($this->motivoPassword, 'motivoPassword');
        $this->registrarBitacora(accion: 'SOLICITAR_ENLACE_ACCESO', tabla: 'users', registro: $usuario->cod_usu,
            nombreRegistro: $this->nombreVisibleUsuario($usuario), descripcion: 'Se confirmó el destinatario autorizado del acceso. Motivo de la solicitud de contraseña: '.$this->motivoPassword.(trim($this->motivoCorreo) ? ' Motivo del cambio de correo: '.trim($this->motivoCorreo) : ''),
            valoresNuevos: ['correo_entrega' => $this->correoEntrega, 'motivo_correo' => trim($this->motivoCorreo), 'motivo_password' => $this->motivoPassword]);
        $resultado = app(EnvioAccesoUsuario::class)->enviar($usuario, $this->correoEntrega);
        if (! $resultado['enviado']) {
            $this->addError('invitacion', $resultado['mensaje']);

            return;
        }
        $this->registrarBitacora(accion: 'ENVIAR_ENLACE_ACCESO', tabla: 'users', registro: $usuario->cod_usu,
            nombreRegistro: $this->nombreVisibleUsuario($usuario), descripcion: 'El servidor de correo aceptó el enlace temporal de acceso.',
            nivel: 'INFO', resultado: 'EXITOSO');
        $this->cancelarInvitacion();
        $this->dispatch('toast', type: 'success', message: $resultado['mensaje']);
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
        abort_unless(Auth::user()?->hasRole('Administrador'), 403);
        $filtros = ['search' => '', 'rol' => '', 'estado' => '', 'perPage' => 10];
        $validacion = Validator::make(array_intersect_key(get_object_vars($this), $filtros), [
            'search' => ['string', 'max:150'],
            'rol' => ['nullable', Rule::in(app(InstitutionalRoleGovernance::class)->rolesInstitucionales())],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
            'perPage' => ['integer', Rule::in([10, 20, 50])],
        ]);
        if ($validacion->fails()) {
            foreach ($validacion->errors()->messages() as $campo => $mensajes) {
                $this->{$campo} = $filtros[$campo];
                $this->addError($campo, $mensajes[0]);
            }
        }
        $consulta = $this->usuariosQuery();
        $usuarios = (clone $consulta)->paginate($this->perPage);
        $indicadoresUsuarios = app(IndicadoresUsuarios::class)->analizar($consulta);
        $this->dispatch('indicadores-usuarios', datos: $indicadoresUsuarios);

        return view('livewire.admin.gestion-usuarios', [
            'usuarios' => $usuarios,
            'rolesDisponibles' => $this->rolesDisponibles,
            'personasDisponibles' => $this->modalCrear ? $this->personasDisponibles : collect(),
            'indicadoresUsuarios' => $indicadoresUsuarios,
            'activacionesPendientes' => collect(), // Pendiente de conectar al contrato oficial de incorporaciones.
            'personasSinCuenta' => Persona::whereDoesntHave('usuario')->count(),
            'bloqueoCorreo' => app(EnvioAccesoUsuario::class)->bloqueoConfiguracion(),
            'destinatarioInvitacion' => $this->cuentaInvitar ? User::find($this->cuentaInvitar) : null,
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
