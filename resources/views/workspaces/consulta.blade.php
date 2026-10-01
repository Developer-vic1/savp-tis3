@extends('layouts.app')
@section('title', $title.' | SAVP')
@section('content')
<livewire:shared.institutional-query :area="$area" :workspace="$workspace" />
@endsection
