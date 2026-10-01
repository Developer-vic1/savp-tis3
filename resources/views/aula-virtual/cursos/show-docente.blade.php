@extends('aula-virtual.layouts.app')
@section('title', 'Curso docente | SAVP-TIS3')
@section('content')
    <livewire:aula-virtual.cursos.curso-detalle-docente :curso="$curso->cod_cla" />
@endsection
