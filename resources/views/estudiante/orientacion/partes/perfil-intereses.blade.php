<section id="mi-perfil" class="intereses-panel" aria-labelledby="perfil-titulo">
    <div class="intereses-cabecera"><div><p class="intereses-etiqueta">Resultado guardado</p><h2 id="perfil-titulo" class="mt-2">Mi perfil de intereses</h2><p class="intereses-texto mt-2">Es una fotografía de lo que disfrutas hoy. Puedes descubrir nuevos intereses con el tiempo.</p></div>@if($experiencia['fecha'])<span class="intereses-chip"><i class="ph-duotone ph-check-circle" aria-hidden="true"></i>{{ $experiencia['fecha']->format('d/m/Y') }}</span>@endif</div>
    <div class="intereses-perfil">
        <div role="list" aria-label="Puntajes por área de interés">
            @foreach($experiencia['areas'] as $codigo => $area)
                <div class="intereses-area" role="listitem" @if($area['destacada']) data-destacada @endif><i class="ph-duotone ph-{{ $area['icono'] }}" aria-hidden="true"></i><div><strong>{{ $area['nombre'] }}</strong><div class="intereses-area-barra" aria-hidden="true"><span style="width: {{ $area['puntaje'] * 5 }}%"></span></div></div><strong aria-label="{{ $area['nombre'] }}: {{ $area['puntaje'] }} de 20">{{ $area['puntaje'] }}<small>/20</small></strong></div>
            @endforeach
        </div>
        <div class="intereses-lectura"><p class="intereses-etiqueta">Código Holland</p><p class="intereses-codigo">{{ $experiencia['codigo'] }}</p>
            @if($experiencia['plano'])<h3>Hoy no destaca una sola área</h3><p class="intereses-texto mt-2">Las seis áreas tienen el mismo puntaje. El orden de las letras resuelve el empate; no significa que unas te interesen más.</p>
            @elseif($experiencia['empate'])<h3>Hay intereses con igual fuerza</h3><p class="intereses-texto mt-2">{{ collect($experiencia['destacados'])->map(fn ($c) => $experiencia['areas'][$c]['nombre'])->join(', ', ' y ') }} comparten tu mayor puntaje. Explora esas perspectivas.</p>
            @else<h3>Destaca {{ $experiencia['areas'][$experiencia['destacados'][0]]['nombre'] }}</h3><p class="intereses-texto mt-2">{{ $experiencia['areas'][$experiencia['destacados'][0]]['actividad'] }}</p>@endif
            <p class="intereses-texto mt-3">No mide inteligencia ni aptitud. Tampoco predice éxito o admisión.</p>
        </div>
    </div>
    <x-plegable-institucional etiqueta="Entender el porqué de mi resultado" descripcion="Revisa cómo se suman tus respuestas y qué expresa cada área." icono="ph-chart-bar" class="mt-5"><p class="intereses-texto mt-2">Cada área reúne cinco actividades. La escala de respuesta de 1 a 5 se convierte a 0 a 4 y se suma: el máximo por área es 20. Un puntaje mayor indica más interés declarado en esas actividades, no más capacidad.</p>
        <div class="intereses-periodos">@foreach($experiencia['areas'] as $codigo => $area)<article class="intereses-periodo"><strong>{{ $codigo }} · {{ $area['nombre'] }}</strong><p>{{ $area['ejemplo'] }}</p><p>{{ $area['puntaje'] }} de 20 en tus respuestas.</p></article>@endforeach</div>
        <p class="intereses-texto mt-3">En un empate no hay una preferencia superior entre esas áreas. El orden estable de las letras solo permite presentar el código.</p><a class="ui-btn-secondary mt-3" href="https://www.onetcenter.org/reports/IP_Manual.html" target="_blank" rel="noopener noreferrer">Consultar el manual O*NET<span class="sr-only">(abre otra pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>
    </x-plegable-institucional>
</section>
