@extends('aula-virtual.layouts.app')

@section('title', 'Aula Virtual | SAVP - TIS 3')
@section('page-title', 'Aula Virtual')

@section('content')
    <section class="mx-auto max-w-3xl py-10">
        <div class="ui-panel text-center">
            <span class="ui-badge-warning">Perfil pendiente</span>
            <h2 class="ui-title mt-5 text-2xl font-black">
                Tu cuenta tiene acceso al Aula Virtual, pero no se detecto un perfil especifico de estudiante o docente.
            </h2>
            <p class="ui-subtitle mt-4 text-sm leading-7">
                Contacta con administracion para revisar los roles y permisos asignados a tu cuenta institucional.
            </p>
        </div>
    </section>
@endsection
