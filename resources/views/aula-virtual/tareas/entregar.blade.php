@extends('aula-virtual.layouts.app')

@section('title', 'Entregar tarea | SAVP-TIS3')
@section('page-title', 'Entregar tarea')

@section('content')
    @php
        $estado = $entrega ? $entrega->est_ent : 'PENDIENTE';
        $bloqueado = in_array($estado, ['ENTREGADO', 'ENTREGADO_TARDE', 'CALIFICADO', 'ANULADO'], true);
        $textoEstado = match($estado) {
            'ENTREGADO' => 'Enviado',
            'ENTREGADO_TARDE' => 'Enviado tarde',
            'CALIFICADO' => 'Calificado',
            'DEVUELTO' => 'Devuelto',
            'ANULADO' => 'Anulado',
            default => 'Pendiente',
        };
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1fr_.8fr]">
        <section class="ui-panel">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-[var(--savp-green)] p-4 text-sm text-white">
                    {{ session('status') == 'Entrega guardada.' ? 'Tarea enviada correctamente' : session('status') }}
                </div>
            @endif
            <p class="ui-kicker">{{ $tarea->claseVirtual?->planAsignatura?->asignatura?->nom_asi }}</p>
            <h2 class="ui-title mt-2 text-2xl font-black">{{ $tarea->tit_tar }}</h2>
            <p class="ui-subtitle mt-4 whitespace-pre-line text-sm leading-7">{{ $tarea->des_tar ?: 'Instrucciones registradas por el docente.' }}</p>
            <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                <div><dt class="ui-muted text-sm">Fecha límite</dt><dd class="font-bold">{{ optional($tarea->fec_lim_tar)->format('d/m/Y H:i') ?: 'Fecha definida por el docente' }}</dd></div>
                <div><dt class="ui-muted text-sm">Puntaje máximo</dt><dd class="font-bold">{{ $tarea->pun_max_tar }}</dd></div>
            </dl>
        </section>

        <section class="ui-panel">
            <div class="flex items-center justify-between">
                <h3 class="ui-title text-xl font-black">Mi entrega</h3>
                @include('aula-virtual.componentes.status-badge', ['estado' => $textoEstado])
            </div>

            @if($estado === 'DEVUELTO' && $entrega->obs_ent)
                <div class="mt-4 rounded-lg bg-amber-50 p-4 border border-amber-200">
                    <h4 class="font-bold text-amber-800">Observación del docente:</h4>
                    <p class="text-sm text-amber-900 mt-1 whitespace-pre-line">{{ $entrega->obs_ent }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('aula-virtual.estudiante.tareas.entregas.store', $tarea->cod_tar) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <textarea name="tex_ent" rows="6" class="ui-textarea" placeholder="Respuesta o comentario" {{ $bloqueado ? 'disabled' : '' }}>{{ old('tex_ent', $entrega->tex_ent ?? '') }}</textarea>
                @if(!$bloqueado)
                    @include('aula-virtual.componentes.file-upload-box', ['name' => 'archivo'])
                @endif
                
                @if($entrega && $entrega->archivos->count() > 0)
                    <div class="mt-2 space-y-2">
                        <p class="font-bold text-sm">Archivos adjuntos:</p>
                        @foreach($entrega->archivos as $arc)
                            <a href="{{ route('aula-virtual.entregas.archivos.descargar', $arc->cod_ent_arc) }}" class="inline-flex items-center gap-2 text-sm text-[var(--savp-green)] hover:underline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                {{ $arc->nom_arc }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @if(!$bloqueado)
                    <div class="flex flex-wrap gap-2">
                        <button name="accion" value="guardar" class="ui-btn-secondary" type="submit">Guardar borrador</button>
                        <button name="accion" value="enviar" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--savp-green)] px-4 py-2.5 text-sm font-bold text-white transition hover:brightness-105" type="submit">Enviar</button>
                    </div>
                @endif
            </form>
        </section>
    </div>
@endsection
