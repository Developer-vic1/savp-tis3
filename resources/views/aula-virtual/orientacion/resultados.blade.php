@extends('aula-virtual.layouts.app')

@section('title', 'Resultados de orientación | SAVP-TIS3')
@section('page-title', 'Resultados de orientación')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <h2 class="ui-title text-2xl font-black">Orientación académica-profesional</h2>
            <p class="ui-subtitle mt-3 text-sm leading-7">{{ $resumen['mensaje'] }}</p>
        </section>

        @if ($resumen['resultado'] ?? null)
            <section class="ui-panel">
                <h3 class="ui-title text-xl font-black">Resultados por áreas</h3>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    @foreach (app(\App\Services\AulaVirtual\OrientacionService::class)->dimensiones() as $codigo => $nombre)
                        @include('aula-virtual.componentes.progress-bar', ['value' => (int) round((float) $resumen['resultado']->{$codigo}), 'label' => $nombre])
                    @endforeach
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                @foreach ($resumen['carreras'] as $carrera)
                    <article class="ui-panel">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="ui-title text-lg font-black">{{ $carrera->carrera }}</h3>
                                <p class="ui-muted mt-1 text-sm">{{ $carrera->area_profesional }}</p>
                            </div>
                            <span class="ui-badge-success">{{ $carrera->compatibilidad }}%</span>
                        </div>
                        <p class="ui-subtitle mt-3 text-sm leading-7">{{ $carrera->razon }}</p>
                    </article>
                @endforeach
            </section>
        @else
            @include('aula-virtual.componentes.empty-state', [
                'titulo' => 'Carreras sugeridas.',
                'descripcion' => 'Las sugerencias aparecerán al finalizar el explorador y se complementarán con rendimiento académico y acompañamiento docente.',
            ])
        @endif
    </div>
@endsection
