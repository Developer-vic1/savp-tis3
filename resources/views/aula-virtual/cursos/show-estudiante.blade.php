@extends('aula-virtual.layouts.app')
@section('title', 'Mi materia | SAVP-TIS3')
@section('content')
    <livewire:aula-virtual.cursos.curso-detalle-estudiante :curso="$curso->cod_cla" />
@endsection
