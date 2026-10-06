<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>{{ $titulo }} · SAVP</title>
    <x-icono-institucional />
    <script>try{if(localStorage.getItem('savp-theme')==='dark'){document.documentElement.classList.add('dark');document.documentElement.dataset.theme='dark';}}catch{}</script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="error-institucional" data-error-institucional="{{ $estado }}">
    @php($datosSoporte=['nombre'=>$nombre,'rol'=>$rol,'pagina'=>$pagina,'estado'=>$estado,'tipo'=>$tipo,'volver'=>$volver])
    <script id="error-datos" type="application/json">@json($datosSoporte)</script>
    <main class="error-contenedor">
        <a href="{{ $inicio }}" class="error-marca ui-title" aria-label="SAVP, volver a mi espacio"><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i><span>SAVP<small>Unidad Educativa Franz Tamayo III</small></span></a>
        <section class="ui-card error-panel" aria-labelledby="error-titulo">
            <div class="error-ilustracion" aria-hidden="true"><span class="error-orbita"></span><i class="ph-duotone ph-{{ $icono }}"></i><small>{{ $estado }}</small></div>
            <p class="ui-kicker">Estamos aquí para ayudarte</p><h1 id="error-titulo" class="ui-title">{{ $titulo }}</h1><p class="ui-muted error-mensaje">{{ $mensaje }}</p>
            <div class="error-acciones"><a href="{{ $volver }}" id="error-regreso" class="ui-btn ui-btn-primary"><i class="ph-duotone ph-arrow-left" aria-hidden="true"></i>Volver a mi página anterior</a>@if(in_array($estado,[401,419]))<a href="{{ url('/login') }}" class="ui-btn ui-btn-secondary">Iniciar sesión</a>@else<a href="{{ $inicio }}" class="ui-btn ui-btn-secondary">{{ $conSesion ? 'Ir a mi inicio' : 'Ir al inicio de sesión' }}</a>@endif</div>
            <div class="error-soporte ui-card-soft"><div><i class="ph-duotone ph-whatsapp-logo" aria-hidden="true"></i><div><strong class="ui-title">¿Necesitas una mano?</strong><p class="ui-muted">Soporte SAVP · +591 75836807</p></div></div><a href="https://wa.me/59175836807" id="error-whatsapp" target="_blank" rel="noopener noreferrer" class="ui-btn ui-btn-secondary">Contactar por WhatsApp<i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></a></div>
            <x-plegable-institucional class="error-detalle" icono="ph-chat-circle-text"><x-slot:titulo>Personalizar mensaje para soporte</x-slot:titulo><label for="error-mensaje-soporte" class="ui-label block mt-3">Tu mensaje para soporte</label><textarea id="error-mensaje-soporte" class="ui-textarea mt-2" rows="5" maxlength="3000" aria-describedby="error-mensaje-ayuda"></textarea><p id="error-mensaje-ayuda" class="ui-error text-xs mt-2" hidden>Escribe un mensaje antes de abrir WhatsApp.</p><p class="ui-muted text-xs mt-3">Personaliza el texto a tu manera. WhatsApp se abrirá con este mensaje; tú decides cuándo enviarlo.</p></x-plegable-institucional>
        </section><p class="ui-muted error-pie">Error {{ $estado }} · {{ $tipo }}</p>
    </main>
</body>
</html>
