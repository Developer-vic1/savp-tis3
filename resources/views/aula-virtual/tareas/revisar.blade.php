@extends('aula-virtual.layouts.app')

@section('title', 'Revisar entregas | SAVP-TIS3')
@section('page-title', 'Revisión y Calificación de Entregas')

@section('content')
    <div class="space-y-6">
        <section class="ui-panel">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <p class="ui-kicker">{{ $tarea->claseVirtual?->planAsignatura?->asignatura?->nom_asi ?? 'Curso' }}</p>
                    <h2 class="ui-title mt-1 text-2xl font-black">{{ $tarea->tit_tar }}</h2>
                    <p class="ui-subtitle mt-1 text-sm">
                        Límite: {{ optional($tarea->fec_lim_tar)->format('d/m/Y H:i') ?: 'Sin límite' }} — 
                        Puntaje Máximo: <strong>{{ (int)$tarea->pun_max_tar }} pts</strong>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                        Total Entregas: {{ $tarea->entregas->count() }}
                    </span>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="space-y-4">
            @forelse ($tarea->entregas as $entrega)
                @php
                    $persona = $entrega->estudiante?->persona;
                    $nombre = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
                    $estado = match ($entrega->est_ent) {
                        'ENTREGADO' => 'Entregado a tiempo',
                        'ENTREGADO_TARDE' => 'Entregado tarde',
                        'CALIFICADO' => 'Calificado',
                        'DEVUELTO' => 'Devuelto para corrección',
                        'ANULADO' => 'Anulado',
                        default => 'Pendiente',
                    };
                @endphp
                <article class="ui-panel space-y-4 border {{ $entrega->est_ent === 'ENTREGADO_TARDE' ? 'border-amber-300' : '' }}">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="ui-title text-lg font-black text-gray-900 dark:text-white">{{ $nombre ?: 'Estudiante registrado' }}</p>
                            <p class="ui-muted text-xs mt-0.5">RUDE: {{ $entrega->estudiante?->rud_est }} — Fecha entrega: {{ optional($entrega->fec_ent)->format('d/m/Y H:i') ?: 'En borrador' }}</p>
                            <div class="mt-2">
                                @include('aula-virtual.componentes.status-badge', ['estado' => $estado])
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($entrega->archivos as $archivo)
                                <a href="{{ route('aula-virtual.entregas.archivos.descargar', $archivo->cod_ent_arc) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    {{ $archivo->nom_arc }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if($entrega->tex_ent)
                        <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg text-sm text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700">
                            <strong>Respuesta del estudiante:</strong>
                            <p class="mt-1 whitespace-pre-line text-xs sm:text-sm">{{ $entrega->tex_ent }}</p>
                        </div>
                    @endif

                    <!-- Formulario de Calificación -->
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 grid gap-4 lg:grid-cols-2">
                        <form method="POST" action="{{ route('aula-virtual.docente.entregas.calificar', $entrega->cod_ent) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            @csrf
                            <div class="w-32 flex-shrink-0">
                                <input 
                                    name="pun_obt" 
                                    type="number" 
                                    min="0" 
                                    max="{{ $tarea->pun_max_tar }}" 
                                    step="0.5" 
                                    value="{{ $entrega->calificacion?->pun_obt !== null ? $entrega->calificacion->pun_obt : '' }}" 
                                    class="ui-input font-bold" 
                                    placeholder="Nota (0-{{ (int)$tarea->pun_max_tar }})" 
                                    required
                                />
                            </div>
                            <input 
                                name="com_cal" 
                                class="ui-input text-xs sm:text-sm flex-grow" 
                                value="{{ $entrega->calificacion?->com_cal }}" 
                                placeholder="Retroalimentación formativa..."
                                maxlength="1000"
                            />
                            @include('aula-virtual.componentes.icon-action-button', ['type' => 'submit', 'icon' => 'calificar', 'label' => $entrega->calificacion ? 'Actualizar Nota' : 'Calificar'])
                        </form>

                        <!-- Devolución -->
                        <form method="POST" action="{{ route('aula-virtual.docente.entregas.devolver', $entrega->cod_ent) }}" class="flex items-center gap-2">
                            @csrf
                            <input 
                                name="obs_ent" 
                                class="ui-input text-xs flex-grow" 
                                placeholder="Motivo de devolución (si requiere corrección)..." 
                                required
                            />
                            <button type="submit" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 rounded-lg text-xs font-semibold border border-amber-200 transition">
                                Devolver
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                @include('aula-virtual.componentes.empty-state', ['titulo' => 'Entregas por revisar.', 'descripcion' => 'Las entregas enviadas por estudiantes aparecerán en esta bandeja.'])
            @endforelse
        </section>
    </div>
@endsection
