@extends('aula-virtual.layouts.app')

@section('title', 'Registrar Asistencia | SAVP')
@section('page-title', 'Control de Asistencia')

@section('content')
    <livewire:aula-virtual.asistencia.registrar-asistencia :codCla="$curso->cod_cla" />
@endsection
