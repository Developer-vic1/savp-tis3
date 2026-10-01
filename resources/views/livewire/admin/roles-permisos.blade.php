<div class="space-y-6">
    <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">Seguridad</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Roles y permisos</h1>
        <p class="ui-muted mt-3 max-w-3xl">Gobernanza de acceso institucional. Toda solicitud de rol requiere evidencia y revisión independiente.</p>
        @if(auth()->user()->can('roles.solicitudes.crear'))
            <button type="button" wire:click="openRequest" class="ui-btn-primary mt-5">+ Solicitar nuevo rol</button>
        @endif
    </section>

    <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-7" aria-label="Jerarquía de acceso">
        <h2 class="text-xl font-bold">Jerarquía de acceso</h2>
        <p class="ui-muted mt-2">Para entrar en un workspace, la cuenta activa debe tener un único actor institucional. El rol reúne permisos; cada permiso habilita módulos o acciones. Los seis actores son funciones distintas, no una cadena de herencia de permisos.</p>
        <ol class="mt-5 grid gap-3 md:grid-cols-4">
            @foreach (['1. Cuenta activa', '2. Rol institucional', '3. Permisos asignados', '4. Módulos y acciones'] as $step)
                <li class="ui-card-soft rounded-2xl p-4 font-semibold">{{ $step }}</li>
            @endforeach
        </ol>
        @unless(auth()->user()->can('roles-permisos.gestionar') && auth()->user()->can('roles.permisos.asignar'))
            <p class="ui-alert-warning mt-5" role="status">Consulta disponible con el permiso histórico. La edición de permisos requiere la autorización nueva y permanece deshabilitada.</p>
        @endunless
    </section>

    <div class="grid gap-6 xl:grid-cols-[320px_1fr]">
        <aside class="ui-card card-shadow rounded-[2rem] p-5" aria-label="Roles disponibles">
            <h2 class="font-bold" style="color: var(--ui-text);">Roles</h2>
            <div class="mt-4 space-y-2">
                @foreach ($roles as $role)
                    <button type="button" wire:click="$set('selectedRoleId', {{ $role->id }})"
                        class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition"
                        style="border-color: var(--ui-border); {{ $selectedRoleId === $role->id ? 'background: var(--ui-primary-soft); color: var(--ui-primary);' : 'color: var(--ui-text-soft);' }}">
                        <span class="font-semibold">{{ $role->name }}</span>
                        <span class="rounded-full px-2 py-1 text-xs" style="background: var(--ui-surface-muted);">{{ $role->users_count }}</span>
                    </button>
                @endforeach
            </div>
        </aside>

        <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-7">
            <div class="ui-card-soft mb-6 rounded-2xl p-4 sm:p-5" aria-label="Ventanas originales del rol">
                <h2 class="text-xl font-bold">Ventanas originales · {{ $selectedRole?->name }}</h2>
                <p class="ui-muted mt-2 text-sm">{{ $roleWindows->count() }} ventanas del catálogo aprobado V001–V105. Rutas y estados tomados de la matriz de conciliación; PARCIAL o BLOQUEADA no significan certificación funcional ni autorizan accesos nuevos.</p>
                <div class="mt-4 max-h-80 overflow-auto rounded-xl border" style="border-color: var(--ui-border);">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead style="background: var(--ui-surface-muted);"><tr><th class="px-3 py-2">ID</th><th class="px-3 py-2">Ventana original</th><th class="px-3 py-2">Estado</th><th class="px-3 py-2">Ruta actual</th><th class="px-3 py-2">Objetivo</th></tr></thead>
                        <tbody>
                            @foreach($roleWindows as $window)
                                <tr class="border-t" style="border-color: var(--ui-border);">
                                    <td class="px-3 py-2 font-bold">{{ $window['id'] }}</td>
                                    <td class="px-3 py-2">{{ $window['name'] }}<span class="ui-muted block text-xs">{{ $window['system'] }} · histórica {{ $window['current_path'] }}</span></td>
                                    <td class="px-3 py-2">{{ match($window['status']) { 'PARTIAL' => 'PARCIAL', 'BLOCKED_EXTERNALLY_DB' => 'BLOQUEADA POR BD', 'BLOCKED_EXTERNALLY_INSTITUTIONAL' => 'BLOQUEADA POR DECISIÓN', default => $window['status'] } }}</td>
                                    <td class="px-3 py-2">{{ $window['runtime_path'] }}</td>
                                    <td class="px-3 py-2">{{ $window['target_path'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($legacyReadGrants->isNotEmpty())
                    <h3 class="mt-5 font-bold">Lecturas habilitadas por permisos históricos</h3>
                    <p class="ui-muted mt-1 text-sm">Compatibilidad de consulta calculada con los permisos que este rol tiene realmente en la base de datos. Las escrituras siguen sujetas a permisos propios.</p>
                    <ul class="mt-3 grid gap-2 md:grid-cols-2">
                        @foreach($legacyReadGrants as $grant)
                            <li class="rounded-xl border px-3 py-2 text-sm" style="border-color: var(--ui-border);">
                                <span class="font-semibold">{{ $grant['permission'] }}</span>
                                <span class="ui-muted block">por {{ $grant['legacy'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="block w-full max-w-xl">
                    <span class="sr-only">Buscar permisos</span>
                    <input wire:model.live.debounce.250ms="search" type="search" placeholder="Buscar permiso..."
                        class="w-full rounded-2xl border px-4 py-3" style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
                </label>
                <button type="button" wire:click="save" wire:confirm="¿Guardar estos cambios de permisos para todas las cuentas del rol? Los cambios sensibles quedarán registrados en Bitácora." wire:loading.attr="disabled" @disabled(!auth()->user()->can('roles-permisos.gestionar') || !auth()->user()->can('roles.permisos.asignar'))
                    class="rounded-2xl px-5 py-3 font-semibold text-white disabled:opacity-60" style="background: var(--ui-primary);">
                    Guardar cambios
                </button>
            </div>

            @if (session('status')) <p role="status" class="ui-alert-success mt-4">{{ session('status') }}</p> @endif
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (['domain' => ['Dominio', $domains], 'action' => ['Acción', $actions], 'scope' => ['Alcance', $scopes]] as $filter => [$label, $options])
                    <label class="ui-label">{{ $label }}
                        <select class="ui-select" wire:model.live="{{ $filter }}"><option value="">Todos</option>
                            @foreach ($options as $option)<option value="{{ $option }}">{{ str_replace('_', ' ', $option) }}</option>@endforeach
                        </select>
                    </label>
                @endforeach
                <label class="ui-label">Estado<select class="ui-select" wire:model.live="selection"><option value="">Todos</option><option value="selected">Seleccionados</option><option value="unselected">Sin seleccionar</option></select></label>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="ui-btn-secondary" wire:click="selectVisible(true)" wire:loading.attr="disabled" @disabled(!auth()->user()->can('roles-permisos.gestionar') || !auth()->user()->can('roles.permisos.asignar'))>Seleccionar visibles</button>
                <button type="button" class="ui-btn-secondary" wire:click="selectVisible(false)" wire:loading.attr="disabled" @disabled(!auth()->user()->can('roles-permisos.gestionar') || !auth()->user()->can('roles.permisos.asignar'))>Deseleccionar visibles</button>
            </div>
            <details class="ui-card-soft mt-4 p-4" @if(count($added) + count($removed)) open @endif>
                <summary>Cambios pendientes: {{ count($added) }} agregados · {{ count($removed) }} retirados</summary>
                @foreach (['Agregar' => $added, 'Retirar' => $removed] as $change => $names)
                    @foreach ($names as $name)<p class="ui-muted mt-2 text-sm">{{ $change }}: {{ \App\Support\PermissionLabel::describe($name)['label'] }} <span class="break-all">({{ $name }})</span></p>@endforeach
                @endforeach
            </details>

            @error('permissions')
                <p class="mt-4 rounded-2xl px-4 py-3 text-sm" style="background: var(--ui-danger-soft); color: var(--ui-danger);">{{ $message }}</p>
            @enderror

            <div class="mt-6 space-y-6">
                @forelse ($permissionGroups as $group => $permissions)
                    <fieldset>
                        <legend class="text-sm font-black tracking-wide" style="color: var(--ui-text);">{{ $group }}</legend>
                        <div class="mt-3 grid gap-2 md:grid-cols-2 2xl:grid-cols-3">
                            @foreach ($permissions as $permission)
                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-3" style="border-color: var(--ui-border);">
                                    <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $permission->name }}" @disabled(!auth()->user()->can('roles-permisos.gestionar') || !auth()->user()->can('roles.permisos.asignar'))
                                        class="mt-1 rounded" style="color: var(--ui-primary);">
                                    @php($description = \App\Support\PermissionLabel::describe($permission->name))
                                    <span class="min-w-0 text-sm" style="color: var(--ui-text-soft);">
                                        <strong class="block">{{ $description['label'] }}</strong>
                                        <span class="ui-muted block">Alcance: {{ $description['scope_label'] }}</span>
                                        @if($description['critical'])<span class="ui-badge-warning">Permiso sensible</span>@endif
                                        <details class="ui-muted text-xs"><summary>Identificador técnico</summary><span class="break-all">{{ $permission->name }}</span></details>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @empty
                    <p class="ui-muted py-10 text-center">No hay permisos que coincidan con la búsqueda.</p>
                @endforelse
            </div>
        </section>
    </div>

    @if(auth()->user()->can('roles.solicitudes.ver'))
        <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-7" aria-label="Solicitudes de rol">
            <h2 class="text-xl font-bold">Solicitudes recientes</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse($requests as $request)
                    <button type="button" wire:click="viewRequest({{ $request->id }})" class="ui-card-soft rounded-2xl p-4 text-left">
                        <strong class="block">#{{ $request->id }} · {{ $request->requested_name }}</strong>
                        <span class="ui-muted text-sm">{{ str_replace('_', ' ', $request->status) }} · {{ $request->created_at?->format('d/m/Y H:i') }}</span>
                    </button>
                @empty
                    <p class="ui-muted">Aún no hay solicitudes.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if($showRequestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-3 sm:p-6" style="background: rgba(0,0,0,.65);" role="presentation"
            x-data x-init="$nextTick(() => $el.querySelector('input,button')?.focus())" x-on:keydown.escape.window="$wire.closeRequest()"
            x-on:keydown.tab="let f=[...$el.querySelectorAll('button:not([disabled]),input:not([disabled]),textarea:not([disabled]),select:not([disabled])')].filter(e=>e.offsetParent!==null); if(!f.length)return; if($event.shiftKey && document.activeElement===f[0]){$event.preventDefault();f[f.length-1].focus()} else if(!$event.shiftKey && document.activeElement===f[f.length-1]){$event.preventDefault();f[0].focus()}">
            <section class="ui-card card-shadow my-auto flex max-h-[92vh] w-full max-w-3xl flex-col rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="request-title">
                <header class="flex items-center justify-between border-b p-5" style="border-color: var(--ui-border);">
                    <div><h2 id="request-title" class="text-xl font-black">Nueva solicitud de rol</h2><p class="ui-muted text-sm">Etapa {{ $requestStep }} de 5</p></div>
                    <button type="button" wire:click="closeRequest" aria-label="Cerrar solicitud" class="ui-btn-secondary">Cerrar</button>
                </header>
                <div class="overflow-y-auto p-5 sm:p-7">
                    <ol class="ui-muted mb-6 flex flex-wrap gap-2 text-xs" aria-label="Etapas">
                        @foreach(['Autorización','Rol','Funciones','Permisos','Análisis'] as $i => $label)
                            <li @if($requestStep === $i + 1) aria-current="step" @endif class="rounded-full px-3 py-1" style="background: var(--ui-surface-muted);">{{ $i + 1 }}. {{ $label }}</li>
                        @endforeach
                    </ol>
                    @if($requestStep === 1)
                        <h3 class="font-bold">Autorización institucional</h3>
                        @if($authority['status'] === 'ACTIVO')
                            <p class="ui-muted mt-2">Director actual: <strong>{{ $authority['name'] }}</strong> · Cargo: Director · Estado: Activo</p>
                        @else
                            <p role="alert" class="ui-alert-warning mt-2">{{ $authority['message'] }}</p>
                        @endif
                        <label for="role-document" class="ui-label mt-5 block">Documento autorizado (PDF, JPG o PNG; máximo 10 MB)</label>
                        <input id="role-document" type="file" wire:model="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="ui-input w-full">
                        @error('document')<p role="alert" class="text-sm" style="color: var(--ui-danger);">{{ $message }}</p>@enderror
                        <p class="ui-muted mt-3 text-sm">El documento se guarda de forma privada. El análisis automático no está disponible; otro administrador deberá revisar la evidencia.</p>
                    @elseif($requestStep === 2)
                        <div class="space-y-4">
                            <label class="ui-label block">Nombre propuesto <input type="text" wire:model="requestedName" class="ui-input w-full" maxlength="80"></label>@error('requestedName')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            <label class="ui-label block">Justificación <textarea wire:model="justification" class="ui-input w-full" rows="3"></textarea></label>@error('justification')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            <label class="ui-label block">Motivo institucional <textarea wire:model="institutionalReason" class="ui-input w-full" rows="3"></textarea></label>@error('institutionalReason')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            <label class="ui-label block">Alcance requerido <input type="text" wire:model="requestedScope" class="ui-input w-full"></label>@error('requestedScope')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @elseif($requestStep === 3)
                        <label class="ui-label block">Funciones principales <textarea wire:model="functions" class="ui-input w-full" rows="6" placeholder="Describa tareas concretas y su relación con SAVP."></textarea></label>
                        @error('functions')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <label class="ui-label mt-4 block">Observaciones <textarea wire:model="observations" class="ui-input w-full" rows="3"></textarea></label>
                    @elseif($requestStep === 4)
                        <p class="ui-muted mb-4">Seleccione únicamente los permisos imprescindibles. Los permisos críticos y globales se bloquean para roles nuevos.</p>
                        @foreach($permissionGroups as $group => $permissions)
                            <fieldset class="mb-5"><legend class="font-bold">{{ $group }}</legend><div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach($permissions as $permission)
                                    @php($label = \App\Support\PermissionLabel::describe($permission->name))
                                    <label class="ui-card-soft flex gap-2 rounded-xl p-3 text-sm"><input type="checkbox" wire:model="requestedPermissions" value="{{ $permission->name }}"><span><strong>{{ $label['label'] }}</strong><span class="ui-muted block">Alcance: {{ $label['scope_label'] }}</span>@if($label['critical'])<span class="ui-badge-warning">Crítico</span>@endif</span></label>
                                @endforeach
                            </div></fieldset>
                        @endforeach
                        @error('requestedPermissions')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                    @else
                        <h3 class="font-bold">Análisis institucional</h3>
                        @if($governanceResult)
                            <div class="ui-card-soft mt-3 rounded-2xl p-4" role="status"><strong>{{ $governanceResult['title'] }} · {{ str_replace('_', ' ', $governanceResult['status']) }}</strong><p class="mt-2">{{ $governanceResult['summary'] }}</p>
                                @if($governanceResult['suggested_role'])<p class="mt-2">Rol existente sugerido: <strong>{{ $governanceResult['suggested_role'] }}</strong></p>@endif
                                @foreach($governanceResult['reasons'] as $reason)<p class="mt-2 text-sm">{{ $reason }}</p>@endforeach
                                @foreach($governanceResult['warnings'] as $warning)<p class="mt-2 text-sm">{{ $warning }}</p>@endforeach
                            </div>
                        @endif
                        <p class="ui-muted mt-4 text-sm">El documento requerirá revisión independiente antes de que se pueda crear el rol. El análisis se repetirá al guardar y al crear.</p>
                    @endif
                </div>
                <footer class="flex flex-wrap justify-end gap-2 border-t p-5" style="border-color: var(--ui-border);">
                    @if($requestStep > 1)<button type="button" wire:click="$set('requestStep', {{ $requestStep - 1 }})" class="ui-btn-secondary">Anterior</button>@endif
                    @if($requestStep < 5)<button type="button" wire:click="nextRequestStep" class="ui-btn-primary" @disabled($requestStep === 1 && $authority['status'] !== 'ACTIVO')>Continuar</button>
                    @else
                        <button type="button" wire:click="analyzeRequest" class="ui-btn-secondary">Reanalizar</button>
                        <button type="button" wire:click="submitRequest" wire:loading.attr="disabled" class="ui-btn-primary" @disabled(($governanceResult['status'] ?? null) !== 'APTO')>Registrar solicitud</button>
                    @endif
                </footer>
            </section>
        </div>
    @endif

    @if($activeRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-3 sm:p-6" style="background: rgba(0,0,0,.65);" role="presentation"
            x-data x-init="$nextTick(() => $el.querySelector('button')?.focus())" x-on:keydown.escape.window="$wire.set('activeRequestId', null)"
            x-on:keydown.tab="let f=[...$el.querySelectorAll('button:not([disabled]),input:not([disabled]),textarea:not([disabled]),a')].filter(e=>e.offsetParent!==null); if(!f.length)return; if($event.shiftKey && document.activeElement===f[0]){$event.preventDefault();f[f.length-1].focus()} else if(!$event.shiftKey && document.activeElement===f[f.length-1]){$event.preventDefault();f[0].focus()}">
            <section class="ui-card card-shadow my-auto max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-[2rem] p-5 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="detail-title">
                <div class="flex justify-between gap-3"><h2 id="detail-title" class="text-xl font-black">Solicitud #{{ $activeRequest->id }} · {{ $activeRequest->requested_name }}</h2><button type="button" wire:click="$set('activeRequestId', null)" class="ui-btn-secondary" aria-label="Cerrar detalle">Cerrar</button></div>
                <p class="ui-muted mt-2">Estado: {{ str_replace('_', ' ', $activeRequest->status) }}</p>
                <dl class="mt-5 space-y-2 text-sm"><div><dt class="font-bold">Justificación</dt><dd>{{ $activeRequest->justification }}</dd></div><div><dt class="font-bold">Funciones</dt><dd>{{ $activeRequest->functions }}</dd></div><div><dt class="font-bold">Alcance</dt><dd>{{ $activeRequest->scope }}</dd></div><div><dt class="font-bold">Resultado</dt><dd>{{ $activeRequest->analysis_result['summary'] ?? 'Pendiente' }}</dd></div><div><dt class="font-bold">Documento</dt><dd>{{ $activeRequest->document_original_name }} · SHA-256 {{ $activeRequest->document_hash }}</dd></div></dl>
                @if(auth()->user()->can('roles.documentos.ver'))<a href="{{ route('admin.roles-permisos.documento', $activeRequest) }}" class="ui-btn-secondary mt-4 inline-block">Descargar documento autorizado</a>@endif
                @if(in_array($activeRequest->status, ['PENDIENTE_REVISION','REQUIERE_REVISION_REUSO']) && $activeRequest->requested_by === auth()->id() && auth()->user()->can('roles.solicitudes.cancelar'))
                    <button type="button" wire:click="cancelRequest" wire:confirm="¿Cancelar esta solicitud?" class="ui-btn-secondary mt-4">Cancelar solicitud</button>
                @endif
                @if(in_array($activeRequest->status, ['PENDIENTE_REVISION','REQUIERE_REVISION_REUSO']) && auth()->user()->can('roles.solicitudes.analizar') && $activeRequest->requested_by !== auth()->id())
                    @if($activeRequest->status === 'REQUIERE_REVISION_REUSO')<p role="alert" class="ui-alert-warning mt-4">Este documento ya se utilizó. Explique expresamente si su reutilización está autorizada.</p>@endif
                    <div class="mt-5 space-y-2"><h3 class="font-bold">Revisión documental independiente</h3>
                        <p class="ui-muted text-sm">Compruebe el documento original. La presencia visual de firma y sello no prueba su autenticidad.</p>
                        <label class="block"><input type="checkbox" wire:model="documentReadable"> Documento legible</label>
                        <label class="block"><input type="checkbox" wire:model="directorMatches"> Identidad del Director coincide con el actual</label>
                        <label class="block"><input type="checkbox" wire:model="signaturePresent"> Presencia aparente de firma</label>
                        <label class="block"><input type="checkbox" wire:model="sealPresent"> Presencia aparente de sello</label>
                        <label class="ui-label block">Fundamento de la revisión<textarea wire:model="reviewNote" rows="3" class="ui-input w-full"></textarea></label>
                        @error('review_note')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <div class="flex flex-wrap gap-2"><button type="button" wire:click="reviewRequest(false)" wire:confirm="¿Rechazar esta solicitud?" class="ui-btn-secondary">Rechazar</button><button type="button" wire:click="reviewRequest(true)" wire:confirm="¿Confirma que revisó personalmente la evidencia?" class="ui-btn-primary">Aprobar revisión</button></div>
                    </div>
                @endif
                @if($activeRequest->status === 'REVISADA' && auth()->user()->can('roles.crear'))
                    <div class="mt-5"><button type="button" wire:click="createRole" wire:confirm="¿Crear el rol tras revalidar documento, autoridad, duplicidad y permisos?" class="ui-btn-primary">Crear rol institucional</button>@error('role_request')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
                @endif
            </section>
        </div>
    @endif
</div>
