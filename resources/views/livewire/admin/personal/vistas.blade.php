<section class="personal-lista" x-show="vista==='lista'" x-cloak aria-label="Lista de personal institucional">
    @forelse($personal as $registro)
        @php
            $persona = $registro->persona;
            $nombre = mb_strtoupper(trim(($persona?->nom_per??'').' '.($persona?->ape_pat_per??'').' '.($persona?->ape_mat_per??'')) ?: 'Identidad por revisar');
            $horas = (int)$registro->docente?->horas_materias_vista + (int)$registro->docente?->horas_tecnicas_vista;
        @endphp
        <article class="ui-card personal-lista-fila" wire:key="personal-lista-{{ $registro->getKey() }}">
            <div class="personal-vista-identidad"><x-avatar-institucional :user="$persona?->usuario" :nombre="$nombre" /><div><h2 class="ui-title font-bold">{{ $nombre }}</h2><p class="ui-muted text-xs mt-2">{{ ucfirst(mb_strtolower($registro->car_pin??'Cargo por revisar')) }}</p></div></div>
            <div class="personal-lista-contexto"><p class="ui-muted text-xs personal-correo">{{ $persona?->ema_per ?: 'Sin correo registrado' }}</p>@if($registro->docente)<p class="ui-title text-sm font-bold mt-2">{{ $horas }} h académicas asignadas</p>@else<p class="ui-muted text-xs mt-2">Vinculación institucional</p>@endif</div>
            @include('livewire.admin.personal.accesos')
        </article>
    @empty<div class="ui-card personas-vacio"><i class="ph-duotone ph-users-three" aria-hidden="true"></i><h2 class="ui-title font-bold">Sin integrantes para estos filtros</h2><p class="ui-muted mt-2">Limpia la búsqueda o cambia los filtros.</p></div>@endforelse
</section>
<section x-show="vista==='cargas'" x-cloak aria-label="Carga horaria del personal">
    <div class="personal-vista-ayuda ui-card-soft"><i class="ph-duotone ph-clock" aria-hidden="true"></i><p class="ui-muted text-sm">Consulta las horas de materias y especialidades activas. Abre «Horario y carga» para ver los bloques, comprimirlos y consultar cada asignación.</p></div>
    <div class="personal-cargas-grid mt-4">
        @forelse($personal as $registro)
            @php
                $persona = $registro->persona;
                $nombre = mb_strtoupper(trim(($persona?->nom_per??'').' '.($persona?->ape_pat_per??'').' '.($persona?->ape_mat_per??'')) ?: 'Identidad por revisar');
                $docente = $registro->docente;
                $materias = (int)$docente?->horas_materias_vista;
                $tecnicas = (int)$docente?->horas_tecnicas_vista;
                $horas = $materias + $tecnicas;
            @endphp
            <article class="ui-card personal-carga-tarjeta" wire:key="personal-carga-{{ $registro->getKey() }}">
                <header class="personal-vista-identidad"><x-avatar-institucional :user="$persona?->usuario" :nombre="$nombre" /><div><h2 class="ui-title font-bold">{{ $nombre }}</h2><p class="ui-muted text-xs mt-2">{{ ucfirst(mb_strtolower($registro->car_pin??'Cargo por revisar')) }}</p></div></header>
                @if($docente)
                    <div class="personal-carga-total"><strong class="ui-title">{{ $horas }}<small>h</small></strong><span class="ui-muted text-xs">académicas asignadas</span>@if($horas>$maxHorasDocente)<span class="ui-badge-warning">Revisar distribución</span>@elseif(!$horas)<span class="ui-badge-info">Sin asignaciones activas</span>@endif</div>
                    <dl class="personal-carga-desglose"><div><dt>Materias</dt><dd>{{ $materias }} h<small>{{ (int)$docente->materias_vista }} asignaciones</small></dd></div><div><dt>Especialidades</dt><dd>{{ $tecnicas }} h<small>{{ (int)$docente->tecnicas_vista }} asignaciones</small></dd></div></dl>
                    <div class="personal-carga-proporcion" role="img" aria-label="{{ $materias }} horas de materias y {{ $tecnicas }} horas de especialidades"><span style="width:{{ $horas ? $materias/$horas*100 : 0 }}%"></span><span style="width:{{ $horas ? $tecnicas/$horas*100 : 0 }}%"></span></div>
                    @if($horas>$maxHorasDocente)<p class="ui-muted text-xs">{{ $horas-$maxHorasDocente }} h por encima del límite actual de {{ $maxHorasDocente }} h.</p>@endif
                @else<div class="personal-carga-no-academica"><i class="ph-duotone ph-identification-card" aria-hidden="true"></i><strong class="ui-title text-sm">Función institucional</strong><p class="ui-muted text-xs">Sin perfil docente vinculado. Consulta su cargo, cuenta y documentos en la ficha.</p></div>@endif
                <footer>@include('livewire.admin.personal.accesos')</footer>
            </article>
        @empty<div class="ui-card personas-vacio"><i class="ph-duotone ph-clock" aria-hidden="true"></i><h2 class="ui-title font-bold">Sin integrantes para estos filtros</h2><p class="ui-muted mt-2">Limpia la búsqueda o cambia los filtros.</p></div>@endforelse
    </div>
</section>
