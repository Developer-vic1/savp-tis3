<div class="relative" wire:poll.60s.visible x-data="{open:$wire.entangle('abierta').live,avisos:[],registrar(detalle){const mensaje=detalle.message||detalle.mensaje||detalle.title;if(typeof mensaje!=='string'||!mensaje.trim())return;const ultimo=this.avisos[0];if(ultimo?.mensaje===mensaje&&Date.now()-ultimo.id<1500)return;this.avisos.unshift({id:Date.now(),mensaje:mensaje.slice(0,500),hora:new Date().toLocaleTimeString('es-BO',{timeZone:'America/La_Paz',hour:'2-digit',minute:'2-digit'})});this.avisos=this.avisos.slice(0,10);}}" @keydown.escape.window="open=false" @click.outside="open=false" @toast.window="registrar($event.detail)" @success-general.window="registrar($event.detail)" @warning-general.window="registrar($event.detail)" x-on:error-general.window="registrar($event.detail)">
    <button type="button" class="savp-topbar-action relative" @click="open=!open" :aria-expanded="open" aria-controls="notification-panel" aria-label="Notificaciones"><i class="ph-duotone ph-bell" aria-hidden="true"></i> @if($available && $unread)<span class="savp-notification-count">{{ $unread > 99 ? '99+' : $unread }}</span>@else<span x-show="avisos.length" x-cloak class="savp-notification-count" x-text="avisos.length"></span>@endif</button>
    <section id="notification-panel" x-show="open" x-cloak x-transition.opacity.duration.150ms class="ui-panel fixed inset-x-3 top-20 z-40 mt-2 max-h-[75vh] w-auto overflow-y-auto md:absolute md:left-auto md:right-0 md:top-auto md:w-96 md:max-w-[88vw]" aria-label="Notificaciones">
        <div class="flex items-center justify-between gap-3"><div><h2 class="ui-title font-bold">Tus notificaciones</h2><p class="ui-muted text-xs mt-1">Avisos y pendientes para ti.</p></div><button type="button" class="ui-btn-secondary" @click="open=false" aria-label="Cerrar notificaciones"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></div>
        <p wire:loading.delay wire:target="abierta" class="ui-muted mt-3" role="status"><i class="ph-duotone ph-circle-notch animate-spin" aria-hidden="true"></i> Consultando avisos…</p>
        @if(!$available)<p class="ui-muted mt-3 text-sm" role="status">Aquí verás los avisos de las acciones que realices en esta pantalla.</p>
        @else
        <nav class="flex flex-wrap gap-1 mt-4" aria-label="Filtrar notificaciones">
            @foreach(['TODAS'=>'Bandeja','NO_LEIDAS'=>'No leídas','LEIDAS'=>'Leídas','ARCHIVADAS'=>'Archivadas'] as $valor=>$etiqueta)
            <button type="button" class="ui-btn {{ $filtro === $valor ? 'ui-btn-primary' : 'ui-btn-secondary' }} text-xs" wire:click="$set('filtro', '{{ $valor }}')" aria-pressed="{{ $filtro === $valor ? 'true' : 'false' }}" wire:loading.attr="disabled">{{ $etiqueta }}</button>
            @endforeach
        </nav>
        <div class="mt-3"><x-selector-institucional modelo="origen" identificador="origen-notificaciones" etiqueta="Área del aviso" :opciones="[['valor'=>'TODOS','etiqueta'=>'Todas las áreas'],['valor'=>'ADMINISTRATIVO','etiqueta'=>'Administración'],['valor'=>'AULA_VIRTUAL','etiqueta'=>'Aula virtual'],['valor'=>'APORTE','etiqueta'=>'Orientación y asistente'],['valor'=>'SISTEMA','etiqueta'=>'Sistema']]" /></div>
        @forelse($rows as $item)
            @php($aviso = $item->notificacion)
            <article class="ui-card-soft mt-3 p-3" wire:key="notification-{{ $item->getKey() }}">
                <div class="flex items-start gap-2"><i class="ph-duotone {{ ['INFORMACION'=>'ph-info','ADVERTENCIA'=>'ph-warning','ACCION'=>'ph-check-circle','RECORDATORIO'=>'ph-clock-countdown'][$aviso->tipo] }} text-lg" aria-hidden="true"></i><h3 class="ui-title font-semibold text-sm">{{ $aviso->titulo }}</h3></div>
                <p class="ui-muted text-sm mt-2 break-words">{{ $aviso->mensaje }}</p>
                @if($vigencia=$vigencias[$item->getKey()]??null)
                    <div class="ui-card-soft mt-2 p-2 text-xs" x-data="typeof vigenciaAviso==='function' ? vigenciaAviso(@js($vigencia)) : {etiqueta:'Actualiza la página para ver el contador',restante:'',porcentaje:0}" wire:key="vigencia-{{ $item->getKey() }}-{{ $vigencia['estado'] }}-{{ $vigencia['fin'] }}">
                        <div class="flex flex-wrap items-center gap-1"><i class="ph-duotone ph-clock-countdown" aria-hidden="true"></i><strong x-text="etiqueta"></strong><span class="tabular-nums" x-text="restante" aria-live="off"></span></div>
                        <div class="h-1 rounded-full overflow-hidden mt-2" style="background:var(--ui-border)" x-show="porcentaje>0" aria-hidden="true"><div class="h-full rounded-full" style="background:var(--ui-primary)" :style="{width:porcentaje+'%'}"></div></div>
                        <p class="ui-muted mt-1">Fin: {{ \Carbon\CarbonImmutable::parse($vigencia['fin'])->timezone('America/La_Paz')->locale('es')->isoFormat('D MMM, HH:mm') }} · Bolivia</p>
                    </div>
                @endif
                <p class="ui-muted text-xs mt-2">{{ $aviso->publicada_en?->timezone('America/La_Paz')->format('d/m/Y H:i') }} · {{ $item->leida_en ? 'Leída' : 'No leída' }}{{ $item->archivada_en ? ' · Archivada' : '' }}</p>
                @if($link=$service->link(auth()->user(),$aviso))<a class="underline text-sm" href="{{ $link }}">Consultar</a>@endif
                <div class="flex flex-wrap gap-2 mt-3"><button type="button" class="ui-btn-secondary text-xs" wire:click="mark(@js($item->getKey()), {{ $item->leida_en ? 'false' : 'true' }})" wire:loading.attr="disabled">{{ $item->leida_en ? 'Marcar no leída' : 'Marcar leída' }}</button><button type="button" class="ui-btn-secondary text-xs" wire:click="archive(@js($item->getKey()), {{ $item->archivada_en ? 'false' : 'true' }})" wire:loading.attr="disabled"><i class="ph-duotone {{ $item->archivada_en ? 'ph-arrow-u-up-left' : 'ph-archive' }}" aria-hidden="true"></i>{{ $item->archivada_en ? 'Restaurar' : 'Archivar' }}</button></div>
            </article>
        @empty<div class="ui-card-soft p-5 mt-4 text-center"><i class="ph-duotone ph-bell-simple text-2xl" aria-hidden="true"></i><p class="ui-title text-sm mt-2">No hay avisos en esta vista.</p><p class="ui-muted text-xs mt-1">Los nuevos avisos aparecerán aquí cuando haya una actividad que te corresponda.</p></div>@endforelse
        {{ $rows->links() }}
        @endif
        <section x-show="avisos.length" x-cloak class="mt-4" aria-label="Avisos de esta pantalla"><h3 class="ui-title font-semibold text-sm">Actividad de esta pantalla</h3><template x-for="aviso in avisos" :key="aviso.id"><article class="ui-card-soft mt-2 p-3"><p class="ui-muted text-sm" x-text="aviso.mensaje"></p><time class="ui-muted text-xs" x-text="aviso.hora"></time></article></template><button type="button" class="ui-btn-secondary mt-3" @click="avisos=[]">Limpiar avisos de pantalla</button></section>
        <p wire:loading.delay wire:target="mark,archive,filtro,origen,gotoPage,nextPage,previousPage" class="ui-muted mt-3" role="status"><i class="ph-duotone ph-circle-notch animate-spin" aria-hidden="true"></i> Actualizando tus avisos…</p>
    </section>
</div>
