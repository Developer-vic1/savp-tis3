<div
    class="usuarios-pagina"
    x-data="gestionUsuariosPage(@js($indicadoresUsuarios))"
    x-on:indicadores-usuarios.window="actualizarIndicadores($event.detail.datos)"
    x-on:usuario-creado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario creado',
            text: 'La cuenta de usuario se registró correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
    x-on:usuarios-sincronizados.window="
        Swal.fire({
            icon: 'success',
            title: 'Datos sincronizados',
            text: `Se sincronizaron ${$event.detail.cantidad} registro(s) faltante(s).`,
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
    x-on:error-sincronizacion.window="
        Swal.fire({
            icon: 'error',
            title: 'Error de sincronización',
            text: 'No se pudieron sincronizar los datos. Tus registros se conservan; vuelve a intentarlo.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-danger)'
        });
    "
    x-on:usuario-actualizado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario actualizado',
            text: 'Los datos de la cuenta fueron actualizados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
    x-on:no-puedes-desactivarte.window="
        Swal.fire({
            icon: 'warning',
            title: 'Acción no permitida',
            text: 'No puedes desactivar tu propia cuenta.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-warning)'
        });
    "
    x-on:usuario-desactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario desactivado',
            text: 'La cuenta fue desactivada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
    x-on:usuario-reactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuario reactivado',
            text: 'La cuenta fue reactivada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
    x-on:usuarios-desactivados.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuarios desactivados',
            text: 'Los usuarios seleccionados fueron desactivados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
    x-on:usuarios-reactivados.window="
        Swal.fire({
            icon: 'success',
            title: 'Usuarios reactivados',
            text: 'Los usuarios seleccionados fueron reactivados correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: 'var(--ui-primary)'
        });
    "
>

    <header class="ui-card personas-cabecera usuarios-cabecera">
        <div><p class="ui-kicker">Acceso institucional</p><h1 class="ui-title mt-2 text-2xl font-extrabold">Gestión de usuarios</h1><p class="ui-muted mt-2 text-sm">Una cuenta por persona. Organiza su rol y entrega el acceso de forma segura.</p></div>
        <div class="personas-acciones"><button type="button" class="ui-btn ui-btn-secondary" :aria-expanded="mostrarIndicadores" aria-controls="usuarios-indicadores" x-on:click="mostrarIndicadores = !mostrarIndicadores"><i class="ph-duotone ph-chart-bar" aria-hidden="true"></i> Indicadores</button>
        <button type="button" wire:click="abrirModalCrear" wire:loading.attr="disabled" wire:target="abrirModalCrear" class="ui-btn ui-btn-primary"><i class="ph-duotone ph-user-plus" aria-hidden="true"></i>Añadir usuario</button></div>
    </header>
    <section class="ui-card personas-cifras usuarios-cifras" aria-label="Resumen general de cuentas">
        @foreach([['Cuentas registradas',$totalUsuarios,'users'],['Activas',$totalActivos,'check-circle'],['Inactivas',$totalInactivos,'pause-circle'],['Personas sin cuenta',$personasSinCuenta,'user-plus']] as [$etiqueta,$valor,$icono])<div><i class="ph-duotone ph-{{ $icono }}" aria-hidden="true"></i><strong>{{ $valor }}</strong><span>{{ $etiqueta }}</span></div>@endforeach
    </section>
    <section class="ui-card usuarios-recorrido" aria-label="Entrega del acceso">
        <div><span class="usuarios-paso">1</span><div><strong>Persona registrada</strong><p>Identidad y contacto en Personas.</p></div></div>
        <div><span class="usuarios-paso">2</span><div><strong>Cuenta y rol</strong><p>Elige la persona y su función.</p></div></div>
        <div><span class="usuarios-paso">3</span><div><strong>Acceso por correo</strong><p>El usuario elige su contraseña.</p></div></div>
    </section>
    <section id="usuarios-indicadores" class="personas-indicadores" x-show="mostrarIndicadores" x-transition.opacity.duration.180ms aria-label="Indicadores de las cuentas filtradas">
        @foreach(['roles'=>['Mosaico de roles','El tamaño de cada bloque muestra la cantidad de asignaciones.','users-three'], 'edades'=>['Puntos por etapa de edad','La posición de cada punto muestra cuántas cuentas hay en esa etapa.','cake'], 'generos'=>['Cuadrícula de la comunidad','Composición por género registrado en Personas.','users']] as $clave=>[$titulo,$descripcion,$icono])
        <article class="ui-card personas-indicador" wire:key="grafico-usuarios-{{ $clave }}"><div class="personas-indicador-titulo"><h2 class="ui-title font-bold">{{ $titulo }}</h2><i class="ph-duotone ph-{{ $icono }}" aria-hidden="true"></i></div><p class="ui-muted mt-2 text-xs leading-5">{{ $descripcion }}</p>
        @if($clave === 'roles')<div class="personas-tipos-vista usuarios-rol-vista mt-3" role="group" aria-label="Alcance del gráfico de roles"><button type="button" :aria-pressed="!incluirEstudiantes" x-on:click="incluirEstudiantes=false; dibujar()">Equipo institucional</button><button type="button" :aria-pressed="incluirEstudiantes" x-on:click="incluirEstudiantes=true; dibujar()">Todos los roles</button></div>@endif
        @if($indicadoresUsuarios['total'] === 0)<p class="usuarios-grafico-vacio ui-muted">Sin cuentas para estos filtros.</p>
        @elseif($clave === 'roles')@include('livewire.admin.usuarios.grafico-mosaico')
        @elseif($clave === 'generos')@include('livewire.admin.usuarios.grafico-cuadricula')
        @else<div class="usuarios-lienzo" wire:ignore><canvas id="usuarios-grafico-edades" role="img" aria-label="Puntos por etapa de edad. Los valores se encuentran debajo del gráfico."></canvas></div>@endif
        <x-plegable-institucional class="personas-valores mt-3" icono="ph-chart-bar" :compacto="true"><x-slot:titulo>Consultar valores</x-slot:titulo><dl class="mt-2 text-xs ui-muted">@foreach($indicadoresUsuarios[$clave]['labels'] as $i=>$etiqueta)<div @if($clave === 'roles' && $etiqueta === 'Estudiante') x-show="incluirEstudiantes" @endif><dt>{{ $etiqueta }}</dt><dd>{{ $indicadoresUsuarios[$clave]['data'][$i] ?? 'Sin información' }}</dd></div>@endforeach</dl></x-plegable-institucional></article>
        @endforeach
        <div class="personas-indicadores-pie"><p class="ui-muted text-xs">Indicadores sobre {{ $indicadoresUsuarios['total'] }} cuentas según los filtros actuales.</p><button type="button" class="personas-enlace" x-on:click="repetirAnimacion()"><i class="ph-duotone ph-arrows-clockwise" aria-hidden="true"></i>Repetir animación</button></div>
    </section>
    <section class="ui-card usuarios-filtros" aria-label="Buscar y filtrar cuentas">
        <div class="usuarios-filtros-grid"><div><label for="usuarios-buscar" class="ui-label">Buscar usuario</label><input id="usuarios-buscar" type="search" class="ui-input w-full mt-2" wire:model.live.debounce.450ms="search" maxlength="150" placeholder="Nombre, código o correo" /><x-input-error for="search" /></div>
        <x-selector-institucional modelo="rol" identificador="usuarios-rol" etiqueta="Rol" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los roles']], $rolesDisponibles->map(fn($item)=>['valor'=>$item->name,'etiqueta'=>$item->name])->all())" />
        <x-selector-institucional modelo="estado" identificador="usuarios-estado" etiqueta="Estado" :opciones="[['valor'=>'','etiqueta'=>'Todos'],['valor'=>'ACTIVO','etiqueta'=>'Activo'],['valor'=>'INACTIVO','etiqueta'=>'Inactivo']]" /></div>
        <div class="usuarios-filtros-pie"><p class="ui-muted text-sm" role="status"><strong class="ui-title">{{ $usuarios->total() }}</strong> cuentas encontradas · {{ count($selected) }} seleccionadas</p><div class="personas-acciones"><button type="button" class="ui-btn ui-btn-secondary" wire:click="limpiarFiltros">Limpiar filtros</button>
        @can('usuarios.sincronizar')<button type="button" class="ui-btn ui-btn-secondary" wire:click="sincronizarDatosUsuarios" wire:loading.attr="disabled" wire:target="sincronizarDatosUsuarios"><i class="ph-duotone ph-arrows-clockwise" aria-hidden="true"></i>Sincronizar datos</button>@endcan</div></div>
        <div class="personas-tipos-vista usuarios-vistas mt-4" role="group" aria-label="Tipo de vista de usuarios"><template x-for="opcion in opcionesVista" :key="opcion.id"><button type="button" :aria-pressed="vista===opcion.id" x-on:click="cambiarVista(opcion.id)"><i class="ph-duotone" :class="opcion.icono" aria-hidden="true"></i><span x-text="opcion.nombre"></span></button></template></div>
        @if(count($selected))<div class="usuarios-seleccion"><x-selector-institucional modelo="accionLote" identificador="usuarios-accion" :etiqueta="'Gestionar '.count($selected).' cuentas seleccionadas'" :opciones="[['valor'=>'','etiqueta'=>'Selecciona una acción'],['valor'=>'activar','etiqueta'=>'Activar seleccionados'],['valor'=>'inactivar','etiqueta'=>'Desactivar seleccionados']]" /><button type="button" class="ui-btn ui-btn-secondary" x-on:click="confirmarLote()" wire:loading.attr="disabled" @disabled(!$accionLote)>Aplicar</button><x-input-error for="accionLote" /></div>@endif
    </section>
    {{-- TABLA --}}
    <x-estado-carga-institucional mensaje="Cargando usuarios…" />
    <section x-ref="resultados" :aria-busy="cargandoPagina">
        <div class="ui-table-wrap" x-show="vista==='tabla'" x-transition.opacity.duration.160ms><div class="overflow-x-auto">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th class="w-12">
                            <input type="checkbox"
                                aria-label="Seleccionar todas las cuentas de esta página" wire:model.live="selectAll"
                                class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        </th>

                        <th>Nombre completo</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($usuarios as $usuario)
                        @php
                            $persona = $usuario->persona;
                            $actores = $usuario->roles->whereIn('name', app(\App\Support\InstitutionalRoleGovernance::class)->rolesInstitucionales())->pluck('name');
                            $rolActual = $actores->count() === 1 ? $actores->first() : 'Revisión requerida';

                            $nombreCompleto = trim(
                                ($persona?->nom_per ?? '') . ' ' .
                                ($persona?->ape_pat_per ?? '') . ' ' .
                                ($persona?->ape_mat_per ?? '')
                            );

                            $estadoUsuario = isset($usuario->est_usu) ? $usuario->est_usu : 'ACTIVO';
                            $esInactivo = $estadoUsuario === 'INACTIVO';
                            $esUsuarioActual = auth()->user()?->cod_usu === $usuario->cod_usu;

                            $referencia = match ($rolActual) {
                                'Estudiante' => 'Estudiante registrado',
                                'Docente' => 'Docente del sistema',
                                'Administrador' => 'Administrador del sistema',
                                'Director' => 'Dirección institucional',
                                'Secretaria' => 'Apoyo administrativo',
                                'Regente' => 'Supervisión académica',
                                default => '—',
                            };

                            $inicial = mb_strtoupper(mb_substr($persona?->nom_per ?? 'U', 0, 1));

                            $badgeRolStyle = match ($rolActual) {
                                'Administrador' => 'background: var(--ui-surface-muted); color: var(--ui-text); --tw-ring-color: var(--ui-border);',
                                'Director' => 'background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);',
                                'Docente' => 'background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);',
                                'Estudiante' => 'background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);',
                                'Secretaria' => 'background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);',
                                'Regente' => 'background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);',
                                default => 'background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);',
                            };
                        @endphp

                        <tr wire:key="row-{{ $usuario->cod_usu }}"
                            class="transition {{ $esInactivo ? 'opacity-70' : '' }}">
                            {{-- CHECKBOX --}}
                            <td>
                                <input type="checkbox"
                                    wire:model.live="selected"
                                    value="{{ $usuario->cod_usu }}"
                                    @if ($esUsuarioActual) disabled @endif
                                    title="{{ $esUsuarioActual ? 'No puedes seleccionarte para cambios de estado' : 'Seleccionar usuario' }}"
                                    class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-40">
                            </td>

                            {{-- NOMBRE --}}
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-bold ring-1"
                                        style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft); --tw-ring-color: var(--ui-border);">
                                        {{ $inicial }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold" style="color: var(--ui-text);">
                                            {{ $nombreCompleto ?: 'Usuario sin nombre' }}
                                        </p>

                                    </div>
                                </div>
                            </td>

                            {{-- CORREO --}}
                            <td>
                                <p class="text-sm font-medium" style="color: var(--ui-text-soft);">
                                    <x-contacto-institucional tipo="correo" :correo="$usuario->email" />
                                </p>
                            </td>

                            {{-- ROL --}}
                            <td>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                    style="{{ $badgeRolStyle }}">
                                    {{ $rolActual }}
                                </span>
                            </td>

                            {{-- REFERENCIA --}}
                            <td>
                                <p class="text-sm" style="color: var(--ui-text-soft);">
                                    {{ $referencia }}
                                </p>
                            </td>

                            {{-- ESTADO --}}
                            <td>
                                <div class="flex flex-col items-start gap-1">
                                    @if ($estadoUsuario === 'ACTIVO')
                                        <span class="ui-badge-success">
                                            <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="ui-badge-danger">
                                            <span class="h-2 w-2 rounded-full" style="background: var(--ui-danger);"></span>
                                            Inactivo
                                        </span>
                                    @endif

                                    @if($activacion = $activacionesPendientes->get($usuario->cod_usu))<span class="ui-muted text-xs">Activación: {{ $activacion->fecha_activacion->timezone('America/La_Paz')->format('d/m/Y') }}</span>@endif
                                    @if ($esInactivo)
                                        <span class="text-[10px] font-medium uppercase tracking-[0.12em]" style="color: var(--ui-muted);">
                                            Sin acceso al sistema
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    @can('usuarios.reset_password')
                                    @if(!$esInactivo)<button type="button" class="ui-icon-btn" wire:click="prepararInvitacion('{{ $usuario->cod_usu }}')" wire:loading.attr="disabled" title="Enviar enlace de acceso" aria-label="Enviar enlace de acceso a <x-contacto-institucional tipo="correo" :correo="$usuario->email" />"><i class="ph-duotone ph-envelope-simple" aria-hidden="true"></i></button>@endif
                                    @endcan
                                    {{-- VER DETALLE --}}
                                    <button type="button"
                                        wire:click="abrirModalVer('{{ $usuario->cod_usu }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalVer('{{ $usuario->cod_usu }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
                                        title="Ver detalle">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    {{-- EDITAR --}}
                                    <button type="button"
                                        wire:click="abrirModalEditar('{{ $usuario->cod_usu }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalEditar('{{ $usuario->cod_usu }}')"
                                        class="ui-icon-btn disabled:cursor-not-allowed disabled:opacity-40"
                                        title="Editar usuario">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                        </svg>
                                    </button>

                                    @if (!$esInactivo)
                                        @if ($esUsuarioActual)
                                            <button type="button"
                                                x-on:click="
                                                    Swal.fire({
                                                        icon: 'warning',
                                                        title: 'Acción no permitida',
                                                        text: 'No puedes desactivar tu propia cuenta.',
                                                        confirmButtonText: 'Entendido',
                                                        confirmButtonColor: 'var(--ui-warning)'
                                                    });
                                                "
                                                class="ui-icon-btn cursor-not-allowed opacity-40"
                                                title="No puedes desactivar tu propia cuenta">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                                    <circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button"
                                                x-on:click="confirmarDesactivacion(@js($usuario->cod_usu))"
                                                class="ui-icon-btn"
                                                style="color: var(--ui-danger);"
                                                title="Desactivar usuario">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                                    <circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </button>
                                        @endif
                                    @else
                                        <button type="button"
                                            x-on:click="
                                                Swal.fire({
                                                    title: '¿Reactivar usuario?',
                                                    text: 'El usuario volverá a tener acceso al sistema.',
                                                    icon: 'question',
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Sí, reactivar',
                                                    cancelButtonText: 'Cancelar',
                                                    confirmButtonColor: 'var(--ui-primary)',
                                                    cancelButtonColor: '#64748b',
                                                    reverseButtons: true
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.reactivarUsuario(@js($usuario->cod_usu));
                                                    }
                                                });
                                            "
                                            class="ui-icon-btn"
                                            style="color: var(--ui-primary);"
                                            title="Reactivar usuario">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                                <circle cx="12" cy="12" r="9" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem]"
                                        style="background: var(--ui-surface-muted); color: var(--ui-muted);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-5 text-lg font-bold" style="color: var(--ui-text);">
                                        No se encontraron usuarios
                                    </h3>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        No existen registros que coincidan con los filtros aplicados.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        </div>
        @include('livewire.admin.usuarios.directorio')
        {{ $usuarios->onEachSide(1)->links('livewire.admin.personas.paginacion', ['cantidad' => $perPage, 'entidad' => 'usuarios', 'singular' => 'usuario']) }}
    </section>

    {{-- MODAL CREAR --}}
    @if($modalCrear)
        @include('livewire.admin.usuarios.crear-cuenta')
    @endif
    @if($destinatarioInvitacion)
        @include('livewire.admin.usuarios.invitar-cuenta')
    @endif
    @if($modalVer && $usuarioDetalle)
        @include('livewire.admin.usuarios.ficha-cuenta')
    @endif

    {{-- MODAL EDITAR USUARIO --}}
    @if ($modalEditar)
        @teleport('body')
        <div wire:key="modal-editar-usuario" x-data="{}" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarModalEditar()" role="dialog" aria-modal="true" class="usuarios-modal fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalEditar"></div>

            <div
                x-data="{
                    showPassword: false,
                    showConfirm: false,

                    email: @entangle('formEditar.email').live,
                    role: @entangle('formEditar.role').live,
                    estado: @entangle('formEditar.est_usu').live,
                    password: @entangle('formEditar.password').live,
                    confirmPassword: @entangle('formEditar.password_confirmation').live,

                    get emailValido() {
                        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email ?? '')
                    },

                    get rolValido() {
                        return this.role !== null && this.role !== ''
                    },

                    get estadoValido() {
                        return this.estado !== null && this.estado !== ''
                    },

                    get passwordVacia() {
                        return !this.password || this.password.length === 0
                    },

                    get tieneMinimo() {
                        return (this.password ?? '').length >= 8
                    },

                    get tieneMayuscula() {
                        return /[A-Z]/.test(this.password ?? '')
                    },

                    get tieneMinuscula() {
                        return /[a-z]/.test(this.password ?? '')
                    },

                    get tieneNumero() {
                        return /[0-9]/.test(this.password ?? '')
                    },

                    get tieneSimbolo() {
                        return /[^A-Za-z0-9]/.test(this.password ?? '')
                    },

                    get passwordSegura() {
                        if (this.passwordVacia) return true

                        return this.tieneMinimo
                            && this.tieneMayuscula
                            && this.tieneMinuscula
                            && this.tieneNumero
                            && this.tieneSimbolo
                    },

                    get passwordsCoinciden() {
                        if (this.passwordVacia) return true

                        return this.password === this.confirmPassword
                    },

                    get formularioValido() {
                        return this.emailValido
                            && this.rolValido
                            && this.estadoValido
                            && this.passwordSegura
                            && this.passwordsCoinciden
                    }
                }"
                class="ui-modal w-full max-w-2xl">

                <div class="usuarios-modal-cabecera px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Edición de cuenta
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Editar usuario
                            </h3>
                            <p class="mt-2 text-sm text-white/90">
                                Actualiza los datos de acceso del usuario seleccionado.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    @error('editar_general')
                        <div class="ui-alert-danger mb-5">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-5 md:grid-cols-2">

                        {{-- CORREO --}}
                        <div>
                            <label class="ui-label">
                                Correo electrónico
                            </label>

                            <input type="email"
                                wire:model.live="formEditar.email" readonly
                                placeholder="usuario@gmail.com"
                                class="ui-input"
                                :style="email && !emailValido
                                    ? 'border-color: var(--ui-danger); box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);'
                                    : emailValido
                                        ? 'border-color: var(--ui-primary);'
                                        : ''">

                            <template x-if="email && !emailValido">
                                <p class="ui-error">
                                    Ingresa un correo válido: gmail.com, hotmail.com, outlook.com o yahoo.com.
                                </p>
                            </template>

                            <template x-if="emailValido">
                                <p class="mt-2 text-sm font-medium" style="color: var(--ui-primary);">
                                    ✓ Correo válido.
                                </p>
                            </template>

                            @error('formEditar.email')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div><x-selector-institucional modelo="formEditar.role" identificador="editar-rol" etiqueta="Rol institucional" :requerido="true" :opciones="array_merge([['valor'=>'','etiqueta'=>'Seleccionar rol']], $rolesDisponibles->map(fn($item)=>['valor'=>$item->name,'etiqueta'=>$item->name])->all())" /></div>
                        <div class="md:col-span-2">@include('livewire.admin.usuarios.estado-acceso', ['prefijo' => 'editar', 'modeloEstado' => 'formEditar.est_usu'])</div>
                        @if(($usuarioDetalle?->est_usu === 'ACTIVO') && ($formEditar['est_usu'] === 'INACTIVO'))
                        <div class="md:col-span-2"><label for="motivo-estado" class="ui-label">Motivo de desactivación *</label><textarea id="motivo-estado" class="ui-input w-full mt-2" wire:model.live.debounce.450ms="motivoEstado" rows="2" minlength="10" maxlength="500" required></textarea><x-input-error for="motivoEstado" /><p class="ui-muted mt-2 text-xs">El motivo quedará en la descripción de bitácora.</p></div>
                        @endif
                        @if(filled($formEditar['password']))
                        <div class="md:col-span-2"><label for="motivo-password" class="ui-label">Motivo del cambio de contraseña *</label><textarea id="motivo-password" class="ui-input w-full mt-2" wire:model.live.debounce.450ms="motivoPassword" rows="2" minlength="10" maxlength="500" required></textarea><x-input-error for="motivoPassword" /><p class="ui-muted mt-2 text-xs">Se registrará el motivo. La contraseña no aparecerá en bitácora.</p></div>
                        @endif

                        {{-- NUEVA CONTRASEÑA --}}
                        <div>
                            <label class="ui-label">
                                Nueva contraseña
                            </label>

                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                    wire:model.live="formEditar.password"
                                    placeholder="Dejar vacío si no deseas cambiarla"
                                    class="ui-input pr-12"
                                    :style="passwordVacia
                                        ? ''
                                        : passwordSegura
                                            ? 'border-color: var(--ui-primary);'
                                            : 'border-color: var(--ui-danger); box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);'">

                                <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-3 flex items-center transition"
                                    style="color: var(--ui-muted);">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>

                                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3l18 18M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58M9.88 5.23A9.8 9.8 0 0 1 12 5c6 0 9.75 7 9.75 7a17.8 17.8 0 0 1-3.23 4.12M6.54 6.54C3.82 8.29 2.25 12 2.25 12s3.75 7 9.75 7c1.33 0 2.56-.32 3.67-.84" />
                                    </svg>
                                </button>
                            </div>

                            @error('formEditar.password')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- CONFIRMAR CONTRASEÑA --}}
                        <div>
                            <label class="ui-label">
                                Confirmar nueva contraseña
                            </label>

                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'"
                                    wire:model.live="formEditar.password_confirmation"
                                    placeholder="Repite la nueva contraseña"
                                    :disabled="passwordVacia"
                                    class="ui-input pr-12 disabled:cursor-not-allowed disabled:opacity-60"
                                    :style="passwordVacia
                                        ? ''
                                        : passwordsCoinciden
                                            ? 'border-color: var(--ui-primary);'
                                            : 'border-color: var(--ui-danger); box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);'">

                                <button type="button"
                                    @click="showConfirm = !showConfirm"
                                    :disabled="passwordVacia"
                                    class="absolute inset-y-0 right-3 flex items-center transition disabled:cursor-not-allowed disabled:opacity-40"
                                    style="color: var(--ui-muted);">
                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>

                                    <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3l18 18M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58M9.88 5.23A9.8 9.8 0 0 1 12 5c6 0 9.75 7 9.75 7a17.8 17.8 0 0 1-3.23 4.12M6.54 6.54C3.82 8.29 2.25 12 2.25 12s3.75 7 9.75 7c1.33 0 2.56-.32 3.67-.84" />
                                    </svg>
                                </button>
                            </div>

                            <template x-if="!passwordVacia && !passwordsCoinciden">
                                <p class="ui-error">
                                    Las contraseñas no coinciden.
                                </p>
                            </template>
                        </div>

                        {{-- VALIDACIONES VISUALES --}}
                        <div class="ui-card-soft md:col-span-2 p-4">
                            <p class="mb-3 text-sm font-semibold" style="color: var(--ui-text-soft);">
                                Validación del formulario
                            </p>

                            <div class="grid gap-2 text-sm sm:grid-cols-2">
                                <p :style="emailValido ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Correo electrónico válido
                                </p>

                                <p :style="rolValido ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Rol seleccionado
                                </p>

                                <p :style="estadoValido ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Estado de cuenta seleccionado
                                </p>

                                <p :style="passwordVacia || tieneMinimo ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Contraseña con mínimo 8 caracteres
                                </p>

                                <p :style="passwordVacia || tieneMayuscula ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Una letra mayúscula
                                </p>

                                <p :style="passwordVacia || tieneMinuscula ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Una letra minúscula
                                </p>

                                <p :style="passwordVacia || tieneNumero ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Un número
                                </p>

                                <p :style="passwordVacia || tieneSimbolo ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Un símbolo especial
                                </p>

                                <p :style="passwordsCoinciden ? 'color: var(--ui-primary)' : 'color: var(--ui-muted)'">
                                    ✓ Confirmación correcta
                                </p>
                            </div>

                            <p x-show="passwordVacia" class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">
                                La contraseña es opcional al editar. Si no deseas cambiarla, deja ambos campos vacíos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!formularioValido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Completa correctamente los datos para guardar los cambios.
                    </p>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button"
                        x-on:click="confirmarEdicion(@js($usuarioDetalle?->est_usu))" wire:loading.attr="disabled" wire:target="guardarEdicionUsuario,formEditar"
                        :disabled="!formularioValido || $wire.programarActivacion"
                        :class="formularioValido
                            ? 'ui-btn-primary'
                            : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>
