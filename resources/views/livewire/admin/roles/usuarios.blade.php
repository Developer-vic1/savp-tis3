<div class="roles-zona">
    <aside class="ui-card roles-lista" aria-label="Buscar una cuenta">
        <h2>¿A quién asignamos?</h2>
        <label class="ui-label">Buscar por nombre<input type="search" class="ui-input mt-2" wire:model.live.debounce.300ms="buscarCuenta" maxlength="100" placeholder="Nombre o apellido…"></label>
        <span wire:loading wire:target="buscarCuenta" class="ui-muted text-xs" role="status">Buscando cuentas…</span>
        @forelse($coincidencias as $u)
            @php $nombre=trim(implode(' ',array_filter([$u->persona?->nom_per,$u->persona?->ape_pat_per,$u->persona?->ape_mat_per]))); @endphp
            <button type="button" class="roles-elegir" x-on:click="elegirCuenta(@js($u->getKey()))" :disabled="!!proceso||$wire.edicionUsuario" aria-pressed="{{ $cuentaSeleccionada===$u->getKey()?'true':'false' }}">
                <i class="ph-duotone ph-user-circle" aria-hidden="true"></i><span><strong>{{ $nombre }}</strong><small>{{ $u->roles->whereIn('name',\App\Models\Oficial\Sistema\Role::INSTITUTIONAL)->pluck('name')->implode(' · ') ?: 'Actor pendiente' }}</small></span>
            </button>
        @empty <p class="ui-muted text-xs">{{ mb_strlen(trim($buscarCuenta))<2?'Escribe al menos dos letras para encontrar una cuenta.':'No encontramos cuentas vigentes. Prueba otro nombre.' }}</p>
        @endforelse
        @if($coincidencias->count()===20)<p class="ui-muted text-xs">Hasta 20 coincidencias. Afina el nombre para encontrar a la persona.</p>@endif
    </aside>
    <section class="ui-card roles-trabajo" :aria-busy="proceso==='cuenta'">
    @if($usuarioElegido&&$resumenUsuario)
        @php
            $nombreUsuario=trim(implode(' ',array_filter([$usuarioElegido->persona?->nom_per,$usuarioElegido->persona?->ape_pat_per,$usuarioElegido->persona?->ape_mat_per])))?:'Cuenta institucional';
            $puedeCuenta=!$usuarioElegido->is(auth()->user())&&app(\App\Services\RoleDashboardResolver::class)->roleFor($usuarioElegido);
        @endphp
        <div class="roles-titulo"><i class="ph-duotone ph-user-circle" aria-hidden="true"></i><div><p class="roles-kicker">Acceso de esta persona</p><h2>{{ $nombreUsuario }}</h2><p class="ui-muted">{{ $usuarioElegido->roles->whereIn('name',\App\Models\Oficial\Sistema\Role::INSTITUTIONAL)->pluck('name')->implode(' · ') }} · {{ $resumenUsuario['total'] }} permisos totales</p></div>
        @if($puedeCuenta)<div class="roles-modo"><button type="button" class="roles-boton-edicion" :aria-pressed="$wire.edicionUsuario" :class="{'en-edicion':$wire.edicionUsuario}" x-on:click="actuar('editar-cuenta',()=>$wire.edicionUsuario?$wire.descartarCuenta():$wire.editarCuenta())" :disabled="!!proceso"><i class="ph-duotone" :class="proceso==='editar-cuenta'?'ph-spinner-gap roles-giro':$wire.edicionUsuario?'ph-lock-simple-open':'ph-pencil-simple'" aria-hidden="true"></i><span x-text="$wire.edicionUsuario?'Salir':'Edición'"></span></button></div>@endif</div>
        <div class="roles-usuario-conteos" aria-label="Origen de los permisos"><span><strong>{{ $resumenUsuario['heredados'] }}</strong> Del rol</span><span><strong>{{ $resumenUsuario['personales'] }}</strong> Personales adicionales</span><span><strong>{{ $resumenUsuario['temporales'] }}</strong> Temporales adicionales</span></div>
        <div class="roles-herramientas mt-3"><nav class="roles-espacios" aria-label="Tareas de la persona"><button type="button" x-on:click="espacioUsuario='administrativo'" :aria-pressed="espacioUsuario==='administrativo'">Administración</button><button type="button" x-on:click="espacioUsuario='aula'" :aria-pressed="espacioUsuario==='aula'">Aula virtual</button></nav>
        @if($puedeCuenta)<button type="button" class="ui-btn ui-btn-secondary" x-on:click="accesoPersonal()" :disabled="!!proceso||$wire.edicionUsuario"><i class="ph-duotone ph-calendar-plus" aria-hidden="true"></i>Dar acceso con vigencia</button>@endif</div>
        <div class="roles-pie-cambios" x-show="$wire.edicionUsuario&&!proceso&&JSON.stringify([...$wire.permisosUsuario].sort())!==JSON.stringify(@js(collect($resumenUsuario['directos'])->sort()->values()->all()))" x-cloak>
            <strong>Guardar aplica estos permisos solo a {{ $nombreUsuario }}.</strong><p class="text-xs"><span x-text="$wire.permisosUsuario.filter(n=>!@js($resumenUsuario['directos']).includes(n)).length"></span> por agregar · <span x-text="@js($resumenUsuario['directos']).filter(n=>!$wire.permisosUsuario.includes(n)).length"></span> por retirar</p>
            <x-selector-institucional modelo="tipoMotivoUsuario" identificador="roles-motivo-personal" etiqueta="Motivo de la asignación" :opciones="collect(\App\Support\SupportRolesInstitucionales::MOTIVOS['personal'])->map(fn($texto,$valor)=>['valor'=>$valor,'etiqueta'=>$texto])->prepend(['valor'=>'','etiqueta'=>'Selecciona el motivo'])->values()->all()" />
            <label class="ui-label" x-show="$wire.tipoMotivoUsuario==='OTRO'">Explica el otro motivo<textarea x-model="$wire.motivoUsuario" class="ui-textarea w-full" maxlength="2000"></textarea></label>
            <p role="status" class="roles-error" x-show="$wire.tipoMotivoUsuario" x-text="errorMotivo('personal',$wire.tipoMotivoUsuario,$wire.motivoUsuario)"></p>
            <div class="roles-acciones"><button type="button" class="ui-btn ui-btn-secondary" x-on:click="actuar('descartar-cuenta',()=>$wire.descartarCuenta())" :disabled="!!proceso">Descartar</button><button type="button" class="ui-btn ui-btn-primary" x-on:click="actuar('guardar-cuenta',()=>$wire.guardarCuenta())" :disabled="!!proceso||!$wire.edicionUsuario||!!errorMotivo('personal',$wire.tipoMotivoUsuario,$wire.motivoUsuario)||JSON.stringify([...$wire.permisosUsuario].sort())===JSON.stringify(@js(collect($resumenUsuario['directos'])->sort()->values()->all()))"><i class="ph-duotone ph-floppy-disk" aria-hidden="true"></i>Guardar para esta persona</button></div>
        </div>
        <label class="ui-label block my-3">Buscar una tarea personal<input type="search" class="ui-input mt-2" x-model.debounce.150ms="buscarTareaUsuario" placeholder="Consultar, reportes, aula…"></label>
        <p class="ui-muted text-xs mb-3">Las tareas del rol se conservan. Edición permite agregar o retirar permisos personales; los accesos protegidos requieren su proceso institucional.</p>
        @foreach($tareasUsuario->groupBy('domain') as $area=>$tareas)
        <x-plegable-institucional class="mb-3" :etiqueta="$area" icono="ph-list-checks" :data-espacios="$tareas->pluck('espacio')->unique()->implode(',')" x-show="$el.dataset.espacios.includes(espacioUsuario)">
            <x-slot:contador><span x-text="espacioUsuario==='aula'?{{ $tareas->where('espacio','aula')->filter(fn($p)=>$p['heredado']||$p['directo']||$p['temporal'])->count() }}:{{ $tareas->where('espacio','administrativo')->filter(fn($p)=>$p['heredado']||$p['directo']||$p['temporal'])->count() }}"></span> vigentes · <span x-text="espacioUsuario==='aula'?{{ $tareas->where('espacio','aula')->count() }}:{{ $tareas->where('espacio','administrativo')->count() }}"></span> tareas</x-slot:contador>
            <div class="roles-permisos">@foreach($tareas as $p)
            <div class="roles-permiso" x-show="espacioUsuario===@js($p['espacio'])&&(!buscarTareaUsuario||@js(mb_strtolower($p['label'].' '.$p['scope_label'])).includes(buscarTareaUsuario.toLocaleLowerCase('es')))">
                @if($p['delegable']&&$puedeCuenta&&(!$p['heredado']||$p['directo']))
                    <input x-show="$wire.edicionUsuario" x-cloak type="checkbox" aria-label="Asignar {{ $p['label'] }} · {{ $p['scope_label'] }}" x-model="$wire.permisosUsuario" value="{{ $p['name'] }}" :disabled="!$wire.edicionUsuario||!!proceso">
                    <i x-show="!$wire.edicionUsuario" class="ph-duotone {{ $p['heredado']||$p['directo']||$p['temporal']?'ph-check-circle':'ph-minus-circle' }}" aria-hidden="true"></i>
                @else<i class="ph-duotone ph-lock-simple" aria-hidden="true"></i>@endif
                <span><button type="button" class="roles-detalle-enlace" x-on:click="permisoDetalle=@js($p)"><strong>{{ $p['label'] }}</strong><i class="ph-duotone ph-info" aria-hidden="true"></i><span class="sr-only">Detalles del permiso</span></button><small>{{ $p['scope_label'] }}</small><small>{{ $p['heredado']?'Del rol':($p['directo']?'Personal':($p['temporal']?'Temporal vigente':'Sin permiso asignado')) }}{{ !$p['delegable']?' · Protegido':'' }}</small>
                @if($p['name']==='cursos.gestionar.global'&&$puedeCuenta&&$usuarioElegido->hasRole('Docente'))<button type="button" class="roles-ir mt-2" x-on:click="accesoPersonal('cursos.gestionar.global')" :disabled="!!proceso||$wire.edicionUsuario"><i class="ph-duotone ph-clock" aria-hidden="true"></i>Autorizar por un día</button><small>Requiere vigencia; no se concede permanentemente.</small>@endif</span>
            </div>@endforeach</div>
        </x-plegable-institucional>
        @endforeach
        <x-input-error for="permisosUsuario" /><x-input-error for="motivo" />
    @else <div class="roles-vacio"><i class="ph-duotone ph-user-check" aria-hidden="true"></i><h2>Permisos de una persona</h2><p>Busca una cuenta para consultar su total y asignar las tareas que necesita.</p></div> @endif
    </section>
</div>
