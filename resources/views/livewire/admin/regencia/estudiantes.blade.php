<section class="ui-card p-5 regencia-acompanamiento" aria-label="Estudiantes a cargo y observaciones">
    <div class="regencia-herramientas"><div><p class="ui-kicker">Acompañamiento estudiantil</p><h2 class="ui-title font-bold mt-2">Estudiantes bajo responsabilidad vigente</h2><p class="ui-muted text-sm mt-2">Gestión consultada, al {{ \Carbon\Carbon::parse($hoy)->format('d/m/Y') }}. Cada estudiante se cuenta una vez por regente.</p></div><span class="ui-badge-info">{{ $cobertura['estudiantes'] }} estudiantes únicos en los grados asignados</span></div>
    <div class="regencia-resumen-estudiantes mt-4">
        <div><strong>{{ $cobertura['estudiantes'] }}</strong><span>A cargo</span></div>
        <div><strong>{{ $cobertura['observados'] }}</strong><span>Con notas de inscripción</span></div>
        <div><strong>{{ $cobertura['sin_observaciones'] }}</strong><span>Sin notas de inscripción</span></div>
    </div>
    <div class="regencia-leyenda mt-4"><span><i class="regencia-marca" style="background:var(--ui-warning)"></i>Con notas de inscripción</span><span><i class="regencia-marca" style="background:var(--ui-primary)"></i>Sin notas de inscripción</span></div>
    <div class="regencia-comparativa mt-4">
        @forelse($panorama as $fila)
            <button type="button" class="regencia-comparativa-fila" wire:click="$set('regente','{{ $fila['vinculo']->cod_vpe }}')" aria-label="Filtrar asignaciones de {{ $nombre($fila['vinculo']->personalInstitucional?->persona) }}: {{ $fila['estudiantes'] }} estudiantes, {{ $fila['observados'] }} con notas de inscripción">
                <span class="regencia-comparativa-nombre">{{ $nombre($fila['vinculo']->personalInstitucional?->persona) }}<small>{{ $fila['grados']->pluck('curso.nom_cur')->join(' · ') ?: 'Sin grados vigentes' }}</small></span>
                <span class="regencia-comparativa-barras"><span class="regencia-pista"><i style="--proporcion:{{ $fila['observados']/max(1,$panorama->max('estudiantes')) }};--barra-color:var(--ui-warning)"></i></span><span class="regencia-pista"><i style="--proporcion:{{ $fila['sin_observaciones']/max(1,$panorama->max('estudiantes')) }};--barra-color:var(--ui-primary)"></i></span></span>
                <span class="regencia-comparativa-cifras"><strong>{{ $fila['estudiantes'] }} estudiantes</strong><small>{{ $fila['observados'] }} con notas · {{ $fila['sin_observaciones'] }} sin notas</small></span>
            </button>
        @empty<p class="ui-muted">No hay regentes habilitados para comparar.</p>@endforelse
    </div>
    <p class="ui-muted text-xs mt-4">Fuente: inscripción y ubicación académica vigentes en los grados asignados. Las notas administrativas no acreditan incidentes disciplinarios. No tener notas no garantiza ausencia de problemas.</p>
    @if(!$cobertura['estudiantes'])<p class="ui-alert-info mt-3">No hay estudiantes con inscripción y ubicación vigentes en los grados bajo responsabilidad para esta consulta.</p>@endif
</section>
