@extends('diseno-local.base')
@section('contenido')
<main class="ui-card diseno-acceso">
    <div class="diseno-identidad"><img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Franz Tamayo N°3"><div><p class="ui-kicker">SAVP · Espacio de diseño</p><h1 class="ui-title text-2xl mt-2">Revisemos las ventanas</h1></div></div>
    <p class="ui-muted mt-4">Explora las referencias visuales de cada rol y del Aula Virtual, sin modificar registros.</p>
    <form method="POST" action="{{ route('diseno.entrar') }}">@csrf
        <label for="clave-diseno" class="ui-label">Clave del modo de diseño</label>
        <input id="clave-diseno" name="clave" type="password" class="ui-input" required maxlength="200" autocomplete="off" autofocus aria-describedby="error-clave-diseno">
        <div id="error-clave-diseno">@error('clave')<p class="diseno-error" role="alert">{{ $message }}</p>@enderror</div>
        <button class="ui-btn ui-btn-primary w-full mt-5" type="submit"><i class="ph-duotone ph-palette" aria-hidden="true"></i>Entrar al modo de diseño</button>
    </form>
    <p class="ui-muted text-xs mt-4">El acceso de diseño dura 30 minutos. Tu sesión institucional se gestiona por separado.</p>
</main>
@endsection
