<div class="space-y-6">
    <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">Seguridad</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Roles y permisos</h1>
        <p class="ui-muted mt-3 max-w-3xl">Administra los permisos de cada rol. Los permisos críticos del rol Administrador no pueden retirarse.</p>
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="block w-full max-w-xl">
                    <span class="sr-only">Buscar permisos</span>
                    <input wire:model.live.debounce.250ms="search" type="search" placeholder="Buscar permiso..."
                        class="w-full rounded-2xl border px-4 py-3" style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
                </label>
                <button type="button" wire:click="save" wire:confirm="¿Guardar estos cambios de permisos para todas las cuentas del rol?" wire:loading.attr="disabled"
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
                <button type="button" class="ui-btn-secondary" wire:click="selectVisible(true)" wire:loading.attr="disabled">Seleccionar visibles</button>
                <button type="button" class="ui-btn-secondary" wire:click="selectVisible(false)" wire:loading.attr="disabled">Deseleccionar visibles</button>
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
                                    <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $permission->name }}"
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
</div>
