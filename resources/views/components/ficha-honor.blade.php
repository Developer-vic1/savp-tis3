@props(['estudiante', 'accion' => true, 'compacto' => false, 'contexto' => 'honor'])
@php
    $nombre = trim($estudiante->nom_per.' '.$estudiante->ape_pat_per.' '.$estudiante->ape_mat_per);
    $puesto = (int) ($estudiante->puesto ?? 1);
    $iniciales = mb_substr(trim($estudiante->nom_per), 0, 1).mb_substr(trim($estudiante->ape_pat_per), 0, 1);
@endphp
<article {{ $attributes->class(['honor-ficha', 'honor-ficha-compacta' => $compacto, 'honor-ficha-primera' => $puesto === 1, 'honor-ficha-tercera' => $puesto === 3]) }} wire:key="{{ $contexto }}-{{ $estudiante->cod_ins }}">
    <div class="honor-ficha-cabecera">
        <span class="honor-puesto"><i class="ph-duotone {{ $puesto === 1 ? 'ph-trophy' : 'ph-medal' }}" aria-hidden="true"></i> Puesto {{ $puesto }}</span>
        @if($estudiante->empatado ?? false)<span class="ui-badge-warning">Empate</span>@endif
    </div>
    <div class="honor-persona">
        <span class="honor-iniciales" aria-hidden="true">{{ $iniciales }}</span>
        <div class="honor-persona-texto"><h4>{{ $nombre }}</h4><p>{{ $estudiante->nom_cur }} · {{ $estudiante->nom_par ?? 'Sin paralelo' }}</p></div>
    </div>
    <div class="honor-promedio"><strong>{{ number_format((float) $estudiante->promedio, 2) }}</strong><span>/ 100<br>promedio observado</span></div>
    <div class="honor-ficha-evidencia">
        <span><i class="ph-duotone ph-notebook" aria-hidden="true"></i> {{ (int) $estudiante->notas }} notas registradas</span>
        <span class="honor-estado"><i class="ph-duotone ph-info" aria-hidden="true"></i> {{ $estudiante->estado_honor }}</span>
    </div>
    @if(!empty($estudiante->carreras))
        <div class="honor-carreras">
            <p class="ui-kicker">Opciones de carrera analizadas</p>
            @foreach($estudiante->carreras as $carrera)<p><strong>{{ $carrera['career_name'] }}</strong><span>{{ $carrera['university'] }}</span></p>@endforeach
            <p class="ui-help">{{ $estudiante->fecha ? 'Análisis guardado el '.$estudiante->fecha.'. ' : '' }}Orientación documentada; no determina una carrera ideal única.</p>
        </div>
    @endif
    @if($accion)
        <button type="button" class="ui-btn-secondary honor-accion" wire:click="verHonor('{{ $estudiante->cod_ins }}')" wire:loading.attr="disabled" wire:target="verHonor" aria-label="Revisar trayectoria de {{ $nombre }}">
            <i class="ph-duotone ph-path" aria-hidden="true"></i><span>Revisar trayectoria</span><i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i>
        </button>
    @endif
</article>
