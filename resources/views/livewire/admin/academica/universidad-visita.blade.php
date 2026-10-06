<section class="ga-campo-ancho ga-universidad" x-data="{enlaceVigente:true,mostrarPagina:false}">
    <p class="ui-kicker">Destino de la visita</p>
    <h4 class="ui-title mt-2">¿Qué universidad conocerán?</h4>
    <p class="ui-muted text-xs mt-2">Elige una institución del catálogo de orientación o indica otra. Este destino es opcional y no cambia el cálculo de las clases.</p>
    <div class="mt-3"><x-selector-institucional modelo="casoImpacto.universidad" identificador="caso-universidad" etiqueta="Universidad a visitar · opcional" :opciones="array_merge([['valor'=>'','etiqueta'=>'Por definir']],collect($universidadesCaso)->map(fn($u)=>['valor'=>$u['valor'],'etiqueta'=>$u['etiqueta']])->all(),[['valor'=>'OTRA','etiqueta'=>'Otra universidad']])" /></div>
    @php($universidadElegida=collect($universidadesCaso)->firstWhere('valor',$casoImpacto['universidad']))
    @if($universidadElegida)
        <p class="ui-muted text-sm mt-3">{{ $universidadElegida['sede'] }}</p>
        <p class="ui-muted text-xs mt-2">Carreras presentes en nuestro estudio; no representan toda su oferta.</p>
        <div class="ga-carreras-tarjetas">@foreach($universidadElegida['carreras'] as $carrera)<x-carrera-orientacion :nombre="$carrera" :registrada="true" />@endforeach</div>
        <x-plegable-institucional icono="ph-file-text"><x-slot:titulo>Consultar las fuentes de estas carreras</x-slot:titulo><div class="grid gap-2 mt-3">@foreach($universidadElegida['fuentes'] as $fuente)<a class="ui-muted text-xs" href="{{ $fuente['url'] }}" target="_blank" rel="noopener noreferrer">{{ $fuente['nombre'] }} <i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endforeach</div></x-plegable-institucional>
    @elseif($casoImpacto['universidad']==='OTRA')
        <div class="mt-3"><label for="caso-universidad-nombre" class="ui-label">Nombre de la universidad · opcional</label><input id="caso-universidad-nombre" class="ui-input mt-2" wire:model="casoImpacto.universidad_nombre" maxlength="240" /><x-input-error for="casoImpacto.universidad_nombre" /></div>
    @endif
    @can('conocimiento.proponer')
        <div class="mt-4"><x-selector-institucional modelo="casoImpacto.estudiar_universidad" identificador="caso-estudiar-universidad" etiqueta="¿Quieres ayudarnos a conocer sus carreras?" :opciones="[['valor'=>'NO','etiqueta'=>'Ahora no'],['valor'=>'SI','etiqueta'=>'Sí, revisar su página oficial']]" /></div>
        @if($casoImpacto['estudiar_universidad']==='SI')
            <p class="ui-muted text-sm mt-3">Comparte su página oficial para revisar la universidad y las carreras que ofrece. Podrás proponer esa información para ampliar la orientación que brindamos a nuestros estudiantes, después de comprobar sus fuentes.</p>
            <div class="mt-3"><label for="caso-universidad-url" class="ui-label">Página oficial de la universidad</label><input id="caso-universidad-url" type="url" class="ui-input mt-2" wire:model="casoImpacto.universidad_url" x-on:input="enlaceVigente=false" maxlength="2000" placeholder="https://…" /><p class="ui-muted text-xs mt-2">Comparte el portal institucional. Si pegas una página de carrera, la agruparemos bajo la misma universidad; un buscador o una red social no sirven como página institucional.</p><x-input-error for="casoImpacto.universidad_url" /></div>
            <button type="button" class="ui-btn ui-btn-secondary mt-3" wire:click="comprobarUniversidad" x-on:click="enlaceVigente=true" wire:loading.attr="disabled" wire:target="comprobarUniversidad"><span class="ga-carga-boton" wire:loading wire:target="comprobarUniversidad" aria-hidden="true"></span><span wire:loading.remove wire:target="comprobarUniversidad">Revisar página</span><span wire:loading wire:target="comprobarUniversidad">Revisando la página…</span></button>
            @if($revisionUniversidad)<div class="ga-nota-fuente mt-3" x-show="enlaceVigente" role="status"><div>@if($revisionUniversidad['titulo'] ?? null)<h5 class="ui-title">{{ $revisionUniversidad['titulo'] }}</h5>@endif<p class="text-sm">{{ $revisionUniversidad['mensaje'] }}</p>@foreach($revisionUniversidad['avisos'] as $aviso)<p class="ui-muted text-xs mt-2">{{ $aviso }}</p>@endforeach</div></div>@endif
            @if($revisionUniversidad && !($revisionUniversidad['bloqueada'] ?? false) && (($revisionUniversidad['conocida'] ?? false) || ($revisionUniversidad['verificada'] ?? false)))<div x-show="enlaceVigente">@include('livewire.admin.academica.vista-previa-universidad')</div>@endif
            <a href="{{ route('conocimiento.fuentes.index') }}" class="ui-btn ui-btn-secondary mt-3" target="_blank" rel="noopener noreferrer">Ir al estudio de fuentes <i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
            <p class="ui-muted text-xs mt-2">La comprobación del enlace no incorpora automáticamente carreras ni aprueba una visita.</p>
        @endif
    @endcan
</section>
