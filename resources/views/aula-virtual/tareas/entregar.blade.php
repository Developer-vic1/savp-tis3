@extends('aula-virtual.layouts.app')

@section('title', 'Entregar tarea | SAVP-TIS3')
@section('page-title', 'Entregar tarea')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1fr_.8fr]">
        <section class="ui-panel">
            <p class="ui-kicker">{{ $tarea->claseVirtual?->planAsignatura?->asignatura?->nom_asi }}</p>
            <h2 class="ui-title mt-2 text-2xl font-black">{{ $tarea->tit_tar }}</h2>
            <p class="ui-subtitle mt-4 whitespace-pre-line text-sm leading-7">{{ $tarea->des_tar ?: 'Instrucciones registradas por el docente.' }}</p>
            <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                <div><dt class="ui-muted text-sm">Fecha límite</dt><dd class="font-bold">{{ optional($tarea->fec_lim_tar)->format('d/m/Y H:i') ?: 'Fecha definida por el docente' }}</dd></div>
                <div><dt class="ui-muted text-sm">Puntaje máximo</dt><dd class="font-bold">{{ $tarea->pun_max_tar }}</dd></div>
            </dl>
        </section>

        <section class="ui-panel">
            <h3 class="ui-title text-xl font-black">Mi entrega</h3>
            <form method="POST" action="{{ route('aula-virtual.estudiante.tareas.entregas.store', $tarea->cod_tar) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <textarea name="tex_ent" rows="6" class="ui-textarea" placeholder="Respuesta o comentario"></textarea>
                @include('aula-virtual.componentes.file-upload-box', ['name' => 'archivo'])
                <div class="flex flex-wrap gap-2">
                    <button name="accion" value="guardar" class="ui-btn-secondary" type="submit">Guardar borrador</button>
                    <button name="accion" value="enviar" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--savp-green)] px-4 py-2.5 text-sm font-bold text-white transition hover:brightness-105" type="submit">Enviar</button>
                </div>
            </form>
        </section>
    </div>
@endsection
