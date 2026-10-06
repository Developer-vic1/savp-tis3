@props(['telefono'=>null, 'correo'=>null, 'tipo'=>'telefono'])
@php
    $valor = trim((string) ($tipo === 'correo' ? $correo : $telefono));
    $numero = preg_replace('/\D/', '', (string) $telefono);
    if (str_starts_with($numero, '00')) $numero = substr($numero, 2);
    if (preg_match('/^[67]\d{7}$/', $numero)) $numero = '591'.$numero;
    $telefonoValido = preg_match('/^[1-9]\d{7,14}$/', $numero) === 1;
    $correoValido = filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
@endphp
@if($tipo === 'correo')
    @if($correoValido)
        <a href="mailto:{{ $valor }}" class="contacto-institucional-enlace" aria-label="Escribir correo a {{ $valor }}"><i class="ph-duotone ph-envelope" aria-hidden="true"></i><span>{{ $valor }}</span></a>
    @else
        <span>{{ $valor ?: 'Sin correo registrado' }}</span>
    @endif
@else
    @if($telefonoValido)
        <span class="contacto-institucional-telefono"><a href="https://wa.me/{{ $numero }}" target="_blank" rel="noopener noreferrer" class="contacto-institucional-enlace" aria-label="Abrir WhatsApp de {{ $valor }}"><i class="ph-duotone ph-whatsapp-logo" aria-hidden="true"></i><span>{{ $valor }}</span></a><a href="tel:+{{ $numero }}" class="contacto-institucional-llamada" aria-label="Llamar a {{ $valor }}" title="Llamar"><i class="ph-duotone ph-phone" aria-hidden="true"></i><span>Llamar</span></a></span>
    @else
        <span>{{ $valor ?: 'Sin teléfono registrado' }}</span>
    @endif
@endif
