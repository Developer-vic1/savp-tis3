@php
    $resumen = $resumen ?? [];
    $href = $href ?? '#';
    $docente = $docente ?? false;
    $plan = $curso->planAsignatura;
    $asignatura = $plan?->asignatura?->nom_asi ?? $curso->nom_cla;
    $cursoTexto = trim(($plan?->curso?->nom_cur ?? '') . ' ' . ($plan?->paralelo?->nom_par ?? ''));
    $turno = $plan?->turno?->nom_tur;
    $nombreDocente = $plan?->docente?->personalInstitucional?->persona
        ? trim(($plan->docente->personalInstitucional->persona->nom_per ?? '') . ' ' . ($plan->docente->personalInstitucional->persona->ape_pat_per ?? ''))
        : 'Docente asignado';
@endphp

<article class="ui-card p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="ui-muted text-xs font-black uppercase">{{ $cursoTexto ?: 'Cursos asignados' }}</p>
            <h3 class="ui-title mt-2 text-xl font-black">{{ $asignatura }}</h3>
        </div>
        @include('aula-virtual.componentes.status-badge', ['estado' => 'Publicado'])
    </div>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div><dt class="ui-muted">Turno</dt><dd class="ui-title font-bold">{{ $turno ?: 'Información académica disponible según inscripción.' }}</dd></div>
        <div><dt class="ui-muted">Docente</dt><dd class="ui-title font-bold">{{ $nombreDocente }}</dd></div>
        <div><dt class="ui-muted">Materiales</dt><dd class="ui-title font-bold">{{ $resumen['materiales'] ?? 0 }}</dd></div>
        <div><dt class="ui-muted">{{ $docente ? 'Entregas pendientes' : 'Tareas pendientes' }}</dt><dd class="ui-title font-bold">{{ $docente ? ($resumen['entregas_pendientes'] ?? 0) : ($resumen['tareas_pendientes'] ?? 0) }}</dd></div>
    </dl>
    <div class="mt-5">
        @include('aula-virtual.componentes.progress-bar', ['value' => $resumen['progreso'] ?? 0, 'label' => 'Seguimiento'])
    </div>
    <div class="mt-5">
        @include('aula-virtual.componentes.icon-action-button', ['href' => $href, 'icon' => 'entrar', 'label' => 'Entrar'])
    </div>
</article>
