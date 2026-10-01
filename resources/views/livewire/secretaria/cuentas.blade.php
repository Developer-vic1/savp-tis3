<div class="space-y-6">
    <section class="ui-panel"><a href="{{ route('secretaria.dashboard') }}" class="ui-muted underline">Inicio
            Secretaría</a>
        <h1 class="ui-title mt-3 text-3xl font-black">Cuentas operativas</h1>
        <p class="ui-muted">Cuentas de estudiantes y docentes con perfil institucional activo.</p>
    </section>
    @if(session('status'))
    <p class="ui-alert-success" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())
        <div class="ui-alert-danger" role="alert">@foreach($errors->all() as $error)
        <p>{{ $error }}</p>@endforeach
    </div>@endif
    <div class="ui-panel flex flex-wrap gap-4"><label class="ui-label">Buscar correo<input class="ui-input"
                wire:model.live.debounce.300ms="search" maxlength="100"></label>@can('usuarios.crear')<button
                class="ui-btn-primary" wire:click="nuevo">Crear cuenta</button>@endcan</div>
    @if($editing)
        <form wire:submit="guardar" class="ui-panel grid gap-4 md:grid-cols-2">
            @if(!$selected)<label class="ui-label">Código de persona registrada<input class="ui-input"
                wire:model="form.cod_per" required></label><label class="ui-label">Tipo de cuenta<select
                class="ui-select" wire:model="form.role" required>
                <option value="">Seleccionar</option>
                <option>Estudiante</option>
                <option>Docente</option>
            </select></label>@endif
            <label class="ui-label">Correo<input type="email" class="ui-input" wire:model="form.email" required
                    autocomplete="off"></label>
            @if(!$selected || auth()->user()->can('usuarios.reset_password'))
                <label class="ui-label">{{ $selected ? 'Nueva contraseña (opcional)' : 'Contraseña' }}<input type="password"
                        class="ui-input" wire:model="form.password" autocomplete="new-password" minlength="8" maxlength="128"
                        @required(!$selected)></label>
                <label class="ui-label">Confirmar contraseña<input type="password" class="ui-input"
                        wire:model="form.password_confirmation" autocomplete="new-password" @required(!$selected)></label>
            @endif
            <div class="flex gap-3"><button class="ui-btn-primary" wire:loading.attr="disabled">Guardar</button><button
                    type="button" class="ui-btn-secondary" wire:click="$set('editing', false)">Cancelar</button></div>
        </form>
    @endif
    <div class="ui-card overflow-x-auto">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Persona</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $account)
                    <tr>
                        <td>{{ $account->persona?->nom_per }} {{ $account->persona?->ape_pat_per }}</td>
                        <td>{{ $account->email }}</td>
                        <td>{{ $account->roles->pluck('name')->join(', ') }}</td>
                        <td>{{ $account->est_usu }}</td>
                        <td>
                            @can('usuarios.editar')<button class="ui-btn-secondary"
                            wire:click="editar('{{ $account->cod_usu }}')">Editar</button>@endcan
                            @can($account->est_usu === 'ACTIVO' ? 'usuarios.desactivar' : 'usuarios.activar')<button
                                class="ui-btn-secondary"
                                wire:click="estado('{{ $account->cod_usu }}', {{ $account->est_usu === 'ACTIVO' ? 'false' : 'true' }})"
                                wire:confirm="¿Actualizar el estado de esta cuenta?"
                            wire:loading.attr="disabled">{{ $account->est_usu === 'ACTIVO' ? 'Desactivar' : 'Activar' }}</button>@endcan
                        </td>
                </tr>@empty<tr>
                    <td colspan="5">No hay cuentas operativas para esta búsqueda.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    {{ $accounts->links() }}
</div>