@extends('aula-virtual.layouts.app')

@section('title', 'Mi plan | SAVP-TIS3')
@section('page-title', 'Mi plan')

@section('content')
@php
    $opcionesPlan = [['valor' => '', 'etiqueta' => 'Elige un programa']];
    $opcionesCambio = [['valor' => '', 'etiqueta' => 'Elige otro programa']];
    foreach ($carrerasPlan as $carrera) {
        $opcion = ['valor' => $carrera['career_id'], 'etiqueta' => $carrera['career_name'].' · '.$carrera['university']];
        $opcionesPlan[] = $opcion;
        if ($carrera['career_id'] !== ($planPersonal['career_id'] ?? null)) $opcionesCambio[] = $opcion;
    }
    $carreraInicial = old('career_id', $planPersonal['cambio_pendiente']['career_id'] ?? request('career', ''));
    if (!is_string($carreraInicial) || !array_key_exists($carreraInicial, $carrerasPlan)) $carreraInicial = '';
    $metaInicial = is_string(old('meta', '')) ? old('meta', '') : '';
    $accionInicial = is_string(old('accion_personal', '')) ? old('accion_personal', '') : '';
    $motivoInicial = is_string(old('motivo', '')) ? old('motivo', '') : '';
@endphp
<div class="mx-auto max-w-6xl space-y-6">
    <section class="ui-panel">
        <p class="ui-kicker">Planificación personal</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Define metas claras y acciones alcanzables</h1>
        <p class="ui-subtitle mt-3 max-w-3xl leading-7">
            Tú eliges qué carrera quieres explorar. Convertimos sus materias documentadas y tus refuerzos disponibles en acciones personales; el avance depende de lo que declares haber realizado.
        </p>
    </section>

    <section class="grid gap-4 md:grid-cols-3" aria-label="Pasos para construir un plan académico">
        @foreach ([
            ['number' => '1', 'title' => 'Define una meta', 'text' => 'Escribe un resultado concreto que quieras alcanzar.'],
            ['number' => '2', 'title' => 'Elige una acción', 'text' => 'Indica el primer paso que depende de ti.'],
            ['number' => '3', 'title' => 'Revisa tu avance', 'text' => 'Actualiza el estado y explica cada cambio importante.'],
        ] as $step)
            <article class="ui-panel">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl font-black text-white" style="background: var(--ui-primary);">{{ $step['number'] }}</span>
                <h2 class="ui-title mt-4 text-lg font-black">{{ $step['title'] }}</h2>
                <p class="ui-subtitle mt-2 text-sm leading-6">{{ $step['text'] }}</p>
            </article>
        @endforeach
    </section>

    @if($errors->any())
        <section class="ui-panel" role="alert"><h2 class="ui-title font-bold">Revisa lo que falta</h2><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></section>
    @endif
    @if($planPersonal)
        <section class="ui-panel space-y-4"><p class="ui-kicker">Mi enfoque actual</p><h2 class="ui-title text-2xl font-bold">{{ $planPersonal['carrera'] }}</h2><p class="ui-subtitle">{{ $planPersonal['universidad'] }}</p><p>{{ $planPersonal['meta'] }}</p><label class="ui-label">{{ $progresoPlan }}% de acciones declaradas completas<progress class="w-full" max="100" value="{{ $progresoPlan }}"></progress></label><p class="ui-subtitle">Este porcentaje describe tus acciones, no tu preparación ni la probabilidad de ingresar a una universidad.</p>
            @foreach($planPersonal['acciones'] as $accion)
                <form method="POST" action="{{ route('estudiante.plan.progreso') }}" class="ui-card-soft flex flex-wrap items-center justify-between gap-3 p-4">@csrf<input type="hidden" name="plan_id" value="{{ $planPersonal['id'] }}"><input type="hidden" name="accion_id" value="{{ $accion['id'] }}"><input type="hidden" name="completada" value="{{ $accion['completada'] ? '0' : '1' }}"><div><h3 class="ui-title font-bold">{{ $accion['titulo'] }}</h3><p class="ui-subtitle">{{ $accion['completada'] ? 'Declarada completa' : 'Pendiente' }}</p>@if(isset($accion['motivo']))<p class="ui-subtitle">{{ $accion['motivo'] }}</p>@endif</div><button class="ui-btn-secondary" type="submit">{{ $accion['completada'] ? 'Volver a pendiente' : 'Marcar realizada' }}</button></form>
            @endforeach
            @if($planPersonal['cambio_pendiente'])
                <div class="ui-alert-warning"><strong>Cambio pendiente de confirmar</strong><p>{{ $planPersonal['cambio_pendiente']['motivo'] }}</p><p>Responde otra vez el cuestionario, genera un estudio y confirma abajo tu nueva meta. Tu plan actual conserva su avance hasta ese momento.</p><a class="ui-btn-secondary mt-3" href="{{ route('estudiante.intereses') }}">Actualizar mis respuestas</a></div>
                <form method="POST" action="{{ route('estudiante.plan.conservar') }}">@csrf<input type="hidden" name="plan_id" value="{{ $planPersonal['id'] }}"><button class="ui-btn-secondary" type="submit">Conservar mi plan actual</button><p class="ui-muted mt-2">Cancela la solicitud de cambio y conserva tus acciones y el avance actual.</p></form>
            @else
                <x-plegable-institucional etiqueta="Quiero cambiar de enfoque" descripcion="El cambio necesita un motivo, un cuestionario nuevo y tu confirmación." icono="ph-path">
                    <form method="POST" action="{{ route('estudiante.plan.cambiar') }}" class="space-y-4" x-data="{{ \Illuminate\Support\Js::from(['carrera' => $carreraInicial === $planPersonal['career_id'] ? '' : $carreraInicial, 'motivo' => $motivoInicial, 'confirmar' => false, 'enviando' => false]) }}" @submit="enviando = true">
                        @csrf
                        <x-selector-institucional identificador="plan-cambio-carrera" etiqueta="Otra carrera" enlace="carrera" nombre="career_id" :opciones="$opcionesCambio" valorInicial="" :requerido="true" />
                        @error('career_id')<p class="ui-error">{{ $message }}</p>@enderror
                        <label class="ui-label">¿Por qué quieres cambiar?<textarea class="ui-textarea" name="motivo" x-model="motivo" minlength="15" maxlength="1000" required>{{ $motivoInicial }}</textarea></label>
                        <p class="ui-muted" x-show="motivo.length > 0 && motivo.trim().length < 15">Describe tu motivo con al menos 15 caracteres.</p>
                        @error('motivo')<p class="ui-error">{{ $message }}</p>@enderror
                        <div class="ui-alert-warning" x-show="carrera" x-cloak>Tu enfoque actual conserva {{ $progresoPlan }}% de acciones declaradas completas mientras actualizas tus respuestas. Al confirmar el nuevo plan, su avance empieza en 0%; las acciones anteriores quedarán en el historial.</div>
                        <label class="ui-card-soft flex items-start gap-3 p-4"><input class="mt-1 h-5 w-5 accent-[var(--ui-primary)]" type="checkbox" name="confirmar" value="1" x-model="confirmar" required><span>Entiendo que debo responder RIASEC otra vez y que conservaré el historial de mis acciones.</span></label>
                        <button class="ui-btn-secondary" type="submit" :disabled="enviando || !carrera || motivo.trim().length < 15 || !confirmar">Guardar motivo y volver al cuestionario</button>
                        <p role="status" class="ui-muted" x-show="enviando" x-cloak>Guardando tu motivo…</p>
                    </form>
                </x-plegable-institucional>
            @endif
        </section>
    @endif
    @if(!$planPersonal || $planPersonal['cambio_pendiente'])
        <section class="ui-panel space-y-4"><h2 class="ui-title text-xl font-bold">{{ $planPersonal ? 'Confirmar mi nuevo enfoque' : 'Elegir una carrera para mi plan' }}</h2><p class="ui-subtitle">Puedes elegir una opción de tu estudio aunque no sea la más cercana a tus intereses. Revisa sus materias y fuentes antes de comprometerte.</p>
            @if(count($carrerasPlan))
                <form method="POST" action="{{ route('estudiante.plan.elegir') }}" class="space-y-4" x-data="{{ \Illuminate\Support\Js::from(['carrera' => $carreraInicial, 'meta' => $metaInicial, 'accion' => $accionInicial, 'compromiso' => false, 'enviando' => false]) }}" @submit="enviando = true">
                    @csrf
                    <x-selector-institucional identificador="plan-elegir-carrera" etiqueta="Carrera para mi enfoque" enlace="carrera" nombre="career_id" :opciones="$opcionesPlan" :valorInicial="$carreraInicial" :requerido="true" />
                    @error('career_id')<p class="ui-error">{{ $message }}</p>@enderror
                    <div x-show="!carrera" class="ui-alert-info">Elige un programa para revisar sus materias y las acciones de tu plan.</div>
                    @foreach($carrerasPlan as $carrera)
                        <article class="ui-card-soft space-y-3 p-4" x-show="carrera === @js($carrera['career_id'])" x-cloak>
                            <h3 class="ui-title font-bold">{{ $carrera['career_name'] }} · {{ $carrera['university'] }}</h3><p class="ui-subtitle">{{ data_get($carrera, 'academic_program.curriculum_note') }}</p>
                            <x-plegable-institucional etiqueta="Materias y refuerzos de esta opción" descripcion="Referencias del estudio para comenzar tu exploración; no representan una malla completa." icono="ph-books" :abierto="true"><ul class="space-y-3">@foreach(data_get($carrera, 'academic_program.documented_subjects', []) as $materia)<li>Materia documentada: {{ \Illuminate\Support\Str::ucfirst($materia) }}</li>@endforeach @foreach(data_get($carrera, 'preparation.reinforcement_areas', []) as $refuerzo)<li><strong>Contenido para revisar: {{ \Illuminate\Support\Str::ucfirst($refuerzo['competency']) }}</strong><x-plegable-institucional class="mt-2" etiqueta="Por qué conviene revisar este contenido" descripcion="Consulta la información que respalda esta sugerencia." icono="ph-lightbulb" :compacto="true"><p>{{ $refuerzo['rationale'] }}</p></x-plegable-institucional></li>@endforeach</ul></x-plegable-institucional>
                        </article>
                    @endforeach
                    <label class="ui-label">Mi meta personal<textarea class="ui-textarea" name="meta" x-model="meta" minlength="10" maxlength="1000" required>{{ $metaInicial }}</textarea></label>
                    <p class="ui-muted" x-show="meta.length > 0 && meta.trim().length < 10">Escribe una meta concreta con al menos 10 caracteres.</p>@error('meta')<p class="ui-error">{{ $message }}</p>@enderror
                    <label class="ui-label">El primer paso que haré esta semana<textarea class="ui-textarea" name="accion_personal" x-model="accion" minlength="10" maxlength="500" required>{{ $accionInicial }}</textarea></label>
                    <p class="ui-muted" x-show="accion.length > 0 && accion.trim().length < 10">Describe una acción que dependa de ti con al menos 10 caracteres.</p>@error('accion_personal')<p class="ui-error">{{ $message }}</p>@enderror
                    <label class="ui-card-soft flex items-start gap-3 p-4"><input class="mt-1 h-5 w-5 accent-[var(--ui-primary)]" type="checkbox" name="compromiso" value="1" x-model="compromiso" required><span>Elijo este enfoque personalmente. Revisaré las fuentes y registraré solo las acciones que realice.</span></label>
                    <button class="ui-btn-primary" type="submit" :disabled="enviando || !carrera || meta.trim().length < 10 || accion.trim().length < 10 || !compromiso">Confirmar mi plan y comenzar</button>
                    <p class="ui-muted" role="status" x-show="enviando" x-cloak>Guardando tu enfoque y tu primera acción…</p>
                </form>
            @else
                <p class="ui-alert-info">Necesitas un estudio con carreras documentadas para vincular un plan.</p><a class="ui-btn-secondary" href="{{ route('estudiante.analisis') }}">Ir a Mi análisis</a>
            @endif
        </section>
    @endif
    @if(count($historialPlanes))
        <x-plegable-institucional etiqueta="Mis enfoques anteriores" descripcion="Conservamos sus metas y acciones; no trasladamos ese progreso al enfoque nuevo." icono="ph-clock-counter-clockwise">@foreach($historialPlanes as $anterior)<article class="ui-card-soft p-4"><h3 class="ui-title font-bold">{{ $anterior['carrera'] }}</h3><p>{{ $anterior['meta'] }}</p><ul>@foreach($anterior['acciones'] as $accion)<li>{{ $accion['titulo'] }} · {{ $accion['completada'] ? 'Declarada completa' : 'Pendiente' }}</li>@endforeach</ul></article>@endforeach</x-plegable-institucional>
    @endif

    <section class="ui-panel">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-title text-xl font-black">¿Necesitas revisar tus pendientes?</h2>
                <p class="ui-subtitle mt-2">
                    Tienes {{ $academic['metricas']['actividades_pendientes'] }} actividad(es) pendiente(s) registrada(s) en tus materias.
                </p>
            </div>
            <a class="ui-btn-secondary inline-flex shrink-0" href="{{ route('estudiante.preparacion') }}">
                <i class="ph ph-brain" aria-hidden="true"></i>Ver Mi preparación
            </a>
        </div>
    </section>
</div>
@endsection
