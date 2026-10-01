<div>
    @if($detail)
    <div class="fixed inset-0 z-50 flex justify-end" x-data="{previous: document.activeElement, destroy() { this.previous?.focus(); }}" x-init="$nextTick(() => $refs.close.focus())" @keydown.escape.window="$wire.close()">
        <button type="button" class="absolute inset-0 bg-black/50" wire:click="close" aria-label="Cerrar ficha"></button>
        <section class="ui-panel relative m-0 flex h-full w-full max-w-xl flex-col overflow-y-auto rounded-none p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="student-drawer-title" @keydown.tab.prevent="const items=[...$el.querySelectorAll('button,a[href],input,select,textarea,[tabindex]')].filter(el=>!el.disabled); const i=items.indexOf(document.activeElement); items[(i+($event.shiftKey?-1:1)+items.length)%items.length]?.focus()">
            <header class="flex items-start justify-between gap-4"><div><p class="ui-kicker">Ficha del estudiante</p><h2 id="student-drawer-title" class="ui-title text-xl font-bold">{{ $detail['student']->persona?->nom_per }} {{ $detail['student']->persona?->ape_pat_per }}</h2></div><button type="button" x-ref="close" wire:click="close" class="ui-btn-secondary">Cerrar</button></header>
            <p class="ui-muted mt-3">{{ $detail['student']->cod_est }} · {{ $detail['student']->est_est }}</p>
            <h3 class="ui-title mt-6 font-bold">Inscripciones autorizadas</h3>
            @forelse($detail['enrollments'] as $item)<article class="ui-card-soft mt-2 p-3">{{ $item->curso?->nom_cur }} · {{ $item->paralelo?->nom_par }} · {{ $item->gestionAcademica?->ani_gea }} · {{ $item->est_ins }}</article>@empty<p class="ui-muted mt-2">No hay inscripciones en este alcance.</p>@endforelse
            @if($curso)
            <h3 class="ui-title mt-6 font-bold">Últimas entregas del curso</h3>
            @forelse($detail['deliveries'] as $item)<article class="ui-card-soft mt-2 p-3"><p>{{ $item->tarea?->tit_tar }} · {{ $item->est_ent }}</p>@if(auth()->user()->can('Calificaciones_Aula'))<p class="ui-muted">Nota LMS: {{ $item->calificacion?->pun_obt ?? 'Sin calificar' }}</p>@endif</article>@empty<p class="ui-muted mt-2">No hay entregas disponibles para tu permiso y curso.</p>@endforelse
            <h3 class="ui-title mt-6 font-bold">Asistencia reciente</h3>
            @forelse($detail['attendance'] as $item)<p class="ui-card-soft mt-2 p-3">{{ $item->asistenciaClase?->fec_asi_cla?->format('d/m/Y') }} · {{ $item->estadoAsistencia?->nom_est_asi }}</p>@empty<p class="ui-muted mt-2">No hay asistencia disponible para tu permiso y curso.</p>@endforelse
            @endif
        </section>
    </div>
    @endif
</div>
