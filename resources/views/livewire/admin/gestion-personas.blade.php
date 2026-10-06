<div class="personas-pagina" x-data="gestionPersonasPage(@js($datosGraficos))"
    x-on:actualizar-graficos-personas.window="actualizarIndicadores($event.detail.data)"
    x-on:error-general.window="avisar('error', $event.detail.mensaje)"
    x-on:persona-creada.window="avisar('success', 'La persona se registró correctamente.')"
    x-on:persona-actualizada.window="avisar('success', 'Los cambios se guardaron correctamente.')"
>
    <header class="ui-card personas-cabecera">
        <div><p class="ui-kicker">Registro institucional</p><h1 class="ui-title mt-2 text-2xl font-extrabold">Gestión de Personas</h1><p class="ui-muted mt-2 text-sm">Identidad, contacto y dirección de nuestra comunidad educativa.</p></div>
        <div class="personas-acciones"><button type="button" class="ui-btn ui-btn-secondary" x-on:click="mostrarIndicadores = !mostrarIndicadores" :aria-expanded="mostrarIndicadores" aria-controls="personas-indicadores"><i class="ph-duotone ph-chart-bar" aria-hidden="true"></i> Indicadores</button><button type="button" class="ui-btn ui-btn-primary" wire:click="abrirModalCrear" wire:loading.attr="disabled" wire:target="abrirModalCrear"><i class="ph-duotone ph-user-plus" aria-hidden="true"></i> Registrar persona</button></div>
    </header>
    <div class="personas-cifras ui-card" aria-label="Resumen general del registro">
        <div><i class="ph-duotone ph-users" aria-hidden="true"></i><strong>{{ $totalPersonas }}</strong><span>Personas registradas</span></div>
        <div><strong>{{ $totalActivas }}</strong><span>Personas con registro vigente</span></div>
        <div><strong>{{ $totalSinUsuario }}</strong><span>Sin cuenta de acceso</span></div>
        <div><strong>{{ $totalInactivas }}</strong><span>Registros inactivos</span></div>
    </div>
    <p class="ui-muted text-xs">{{ $totalConUsuario }} personas tienen una cuenta de acceso. El registro de persona conserva sus datos; su vigencia es independiente del estado de esa cuenta.</p>
    @if($totalSinUsuario > 0)
    <aside class="personas-alerta-cuentas" role="status"><i class="ph-duotone ph-warning-circle" aria-hidden="true"></i><div><strong>{{ $totalSinUsuario }} {{ $totalSinUsuario === 1 ? 'persona sin cuenta vinculada' : 'personas sin cuenta vinculada' }}</strong><p>Revisa los registros y gestiona sus accesos desde Usuarios.</p></div><button type="button" class="ui-btn ui-btn-secondary" wire:click="$set('cuentaUsuario', 'sin_usuario')">Revisar registros</button></aside>
    @endif
    <section id="personas-indicadores" x-show="mostrarIndicadores" class="personas-indicadores" aria-label="Indicadores de las personas filtradas">
        @foreach(['contacto' => ['Datos para contactar', 'Presencia de teléfono, correo y dirección. Los grupos pueden coincidir.', 'address-book'], 'edades' => ['Etapas de edad', 'Edades calculadas desde la fecha de nacimiento registrada.', 'users-three'], 'generos' => ['Distribución por género', 'Información registrada para conocer la composición de la comunidad.', 'users-three']] as $grafico => [$titulo, $ayuda, $icono])
            <article class="ui-card personas-indicador">
                <div class="personas-indicador-titulo"><h2 class="ui-title font-bold">{{ $titulo }}</h2><i class="ph-duotone ph-{{ $icono }}" aria-hidden="true"></i></div><p class="ui-muted mt-2 text-xs leading-5">{{ $ayuda }}</p>
                <div class="personas-lienzo" wire:ignore><canvas id="personas-grafico-{{ $grafico }}" role="img" aria-label="{{ $titulo }}. Consulta los valores en el detalle debajo."></canvas></div>
                <x-plegable-institucional class="personas-valores" icono="ph-chart-bar" :compacto="true"><x-slot:titulo>Consultar valores</x-slot:titulo><dl class="mt-2 text-xs">@foreach($datosGraficos[$grafico]['labels'] as $indice => $etiqueta)<div><dt>{{ $etiqueta }}</dt><dd class="font-semibold">{{ $datosGraficos[$grafico]['data'][$indice] }}</dd></div>@endforeach</dl></x-plegable-institucional>
            </article>
        @endforeach
        <div class="personas-indicadores-pie"><p class="ui-muted text-xs">Indicadores sobre {{ $datosGraficos['total'] }} registros según los filtros actuales.</p><button type="button" class="personas-enlace" x-on:click="repetirAnimacion()"><i class="ph-duotone ph-play-circle" aria-hidden="true"></i> Repetir animación</button></div>
    </section>
    <section class="ui-card personas-filtros" aria-label="Buscar y filtrar personas">
        <div class="personas-busqueda">
            <div><label for="personas-buscar" class="ui-label">Buscar persona</label><div class="relative mt-2"><i class="ph-duotone ph-magnifying-glass personas-buscar-icono" aria-hidden="true"></i><input id="personas-buscar" class="ui-input w-full pl-10" type="search" wire:model.live.debounce.350ms="search" maxlength="150" placeholder="Nombre, CI, correo o teléfono" /></div><x-input-error for="search" /></div>
            <div><label for="personas-direccion" class="ui-label">Dirección</label><input id="personas-direccion" class="ui-input mt-2 w-full" type="search" wire:model.live.debounce.350ms="direccion" maxlength="255" placeholder="Zona, calle o ciudad" /></div>
            <div><label for="personas-genero" class="ui-label">Género</label><select id="personas-genero" class="ui-select mt-2 w-full" wire:model.live="genero"><option value="">Todos</option><option value="M">Masculino</option><option value="F">Femenino</option></select></div>
            <div><label for="personas-estado" class="ui-label">Registros</label><select id="personas-estado" class="ui-select mt-2 w-full" wire:model.live="estado"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
            <div><label for="personas-cuenta" class="ui-label">Cuenta de acceso</label><select id="personas-cuenta" class="ui-select mt-2 w-full" wire:model.live="cuentaUsuario"><option value="">Todas</option><option value="con_usuario">Con cuenta</option><option value="sin_usuario">Sin cuenta</option></select></div>
        </div>
        <fieldset class="personas-revisiones"><legend class="ui-muted text-xs">Revisar datos pendientes · puedes combinar varios criterios</legend>
            @foreach(['telefono'=>'Sin teléfono', 'correo'=>'Sin correo', 'direccion'=>'Sin dirección', 'nacimiento'=>'Sin fecha de nacimiento', 'foto'=>'Sin foto'] as $criterio => $etiqueta)
                <label class="personas-filtro-chip"><input type="checkbox" class="ui-checkbox" wire:model.live="pendientes" value="{{ $criterio }}" /><span>{{ $etiqueta }}</span></label>
            @endforeach
        </fieldset>
        <div class="personas-filtros-pie">
            <p class="ui-muted text-sm" role="status"><strong class="ui-title">{{ $personas->total() }}</strong> {{ $personas->total() === 1 ? 'persona encontrada' : 'personas encontradas' }} <span wire:loading.delay wire:target="search,direccion,genero,estado,cuentaUsuario,pendientes" class="ml-2">Actualizando…</span></p>
            <div class="personas-tipos-vista" role="group" aria-label="Tipo de vista">
                <template x-for="opcion in opcionesVista" :key="opcion.id"><button type="button" :aria-pressed="vista === opcion.id" x-on:click="cambiarVista(opcion.id)"><i class="ph-duotone" :class="opcion.icono" aria-hidden="true"></i><span x-text="opcion.nombre"></span></button></template>
            </div>
            <button type="button" class="ui-btn ui-btn-secondary" wire:click="limpiarFiltros"><i class="ph-duotone ph-x" aria-hidden="true"></i> Limpiar filtros</button>
        </div>
    </section>
    <x-estado-carga-institucional mensaje="Cargando personas…" />
    <div class="personas-resultados" x-ref="resultados" :aria-busy="cargandoPagina" :class="cargandoPagina ? 'personas-cargando' : ''" wire:loading.class="personas-cargando" wire:target="search,direccion,genero,estado,cuentaUsuario,pendientes,gotoPage,perPage">
        @if($personas->isEmpty())
            <div class="ui-card personas-vacio"><span class="personas-icono"><i class="ph-duotone ph-user-magnifying-glass" aria-hidden="true"></i></span><h2 class="ui-title mt-4 font-bold">No encontramos personas con esos criterios</h2><p class="ui-muted mt-2 text-sm">Prueba otra búsqueda o limpia los filtros para ver el registro completo.</p><button type="button" class="ui-btn ui-btn-secondary mt-4" wire:click="limpiarFiltros">Ver todos los registros</button></div>
        @else
            <section x-show="vista === 'tabla'" x-cloak class="ui-table-wrap personas-tabla" aria-label="Listado de personas">
                <table class="ui-table"><thead><tr><th>Persona e identificación</th><th>Edad y género</th><th>Contacto</th><th>Dirección</th><th>Cuenta</th><th class="text-right">Acciones</th></tr></thead><tbody>
                @foreach($personas as $persona)
                    <tr wire:key="persona-tabla-{{ $persona->cod_per }}"><td><div class="personas-identidad">@include('livewire.admin.personas.avatar', ['persona'=>$persona])<div><strong class="ui-title">{{ $this->nombreCompleto($persona) }}</strong><p class="ui-muted mt-1 text-xs">CI {{ $persona->ci_per }}{{ $persona->com_per ? '-'.$persona->com_per : '' }} · {{ $persona->exp_per }}</p>@if(!$persona->est_per)<span class="ui-badge-warning mt-1">Registro inactivo</span>@endif</div></div></td><td>{{ $this->edadPersona($persona->fec_nac_per) !== null ? $this->edadPersona($persona->fec_nac_per).' años' : 'Sin fecha' }}<p class="ui-muted mt-1 text-xs">{{ $persona->gen_per === 'M' ? 'Masculino' : ($persona->gen_per === 'F' ? 'Femenino' : 'Sin registrar') }}</p></td><td><span><x-contacto-institucional :telefono="$persona->tel_per" /></span><p class="ui-muted mt-1 text-xs personas-texto-largo"><x-contacto-institucional tipo="correo" :correo="$persona->ema_per" /></p></td><td class="personas-direccion-celda">{{ $persona->dir_per ?: 'Sin dirección' }}</td><td><span class="{{ $persona->usuario ? 'ui-badge' : 'ui-badge-warning' }}">{{ $persona->usuario ? 'Vinculada' : 'Sin cuenta' }}</span></td><td>@include('livewire.admin.personas.acciones', ['persona'=>$persona])</td></tr>
                @endforeach
                </tbody></table>
            </section>
            <section x-show="vista !== 'tabla'" class="personas-directorio" :class="vista === 'galeria' ? 'personas-galeria' : ''" aria-label="Directorio de personas">
                @foreach($personas as $persona)
                    <article class="ui-card personas-ficha" wire:key="persona-ficha-{{ $persona->cod_per }}">
                        <div class="personas-ficha-identidad">@include('livewire.admin.personas.avatar', ['persona'=>$persona])<div class="min-w-0"><h2 class="ui-title font-bold personas-texto-largo">{{ $this->nombreCompleto($persona) }}</h2><p class="ui-muted mt-1 text-xs">CI {{ $persona->ci_per }}{{ $persona->com_per ? '-'.$persona->com_per : '' }} · {{ $persona->exp_per }}</p><p class="ui-muted mt-1 text-xs">{{ $this->edadPersona($persona->fec_nac_per) !== null ? $this->edadPersona($persona->fec_nac_per).' años' : 'Sin fecha de nacimiento' }} · {{ $persona->gen_per === 'M' ? 'Masculino' : ($persona->gen_per === 'F' ? 'Femenino' : 'Sin registrar') }}</p></div></div>
                        <dl class="personas-ficha-contacto"><div><dt><i class="ph-duotone ph-phone" aria-hidden="true"></i> Teléfono</dt><dd><x-contacto-institucional :telefono="$persona->tel_per" /></dd></div><div><dt><i class="ph-duotone ph-envelope" aria-hidden="true"></i> Correo</dt><dd><x-contacto-institucional tipo="correo" :correo="$persona->ema_per" /></dd></div><div><dt><i class="ph-duotone ph-map-pin" aria-hidden="true"></i> Dirección</dt><dd>{{ $persona->dir_per ?: 'Sin registrar' }}</dd></div></dl>
                        <div class="personas-ficha-pie"><div><span class="{{ $persona->usuario ? 'ui-badge' : 'ui-badge-warning' }}">{{ $persona->usuario ? 'Cuenta vinculada' : 'Sin cuenta de acceso' }}</span>@if(!$persona->est_per)<span class="ui-badge-warning">Inactiva</span>@endif</div>@include('livewire.admin.personas.acciones', ['persona'=>$persona])</div>
                    </article>
                @endforeach
            </section>
            {{ $personas->onEachSide(1)->links('vendor.livewire.paginacion-institucional', ['cantidad' => $perPage]) }}
        @endif
    </div>
    @if($modalCrear || $modalEditar)
        @include('livewire.admin.personas.formulario', ['editar'=>$modalEditar])
    @endif
    @if($modalVer && $personaDetalle)
        @include('livewire.admin.personas.detalle', ['persona'=>$personaDetalle])
    @endif
</div>
