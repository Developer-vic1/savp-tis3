@php
    $estado = $estado ?? 'Pendiente';
    $mapa = [
        'Pendiente' => 'ui-badge-warning',
        'En proceso' => 'ui-badge-info',
        'Entregado' => 'ui-badge-success',
        'Tardío' => 'ui-badge-warning',
        'Revisado' => 'ui-badge-success',
        'Devuelto' => 'ui-badge-danger',
        'Publicado' => 'ui-badge-success',
        'Oculto' => 'ui-badge-muted',
        'Programado' => 'ui-badge-info',
        'Cerrado' => 'ui-badge-muted',
        'Presente' => 'ui-badge-success',
        'Tardanza' => 'ui-badge-warning',
        'Falta' => 'ui-badge-danger',
        'Justificado' => 'ui-badge-info',
        'Requiere seguimiento' => 'ui-badge-danger',
    ];
@endphp

<span class="{{ $mapa[$estado] ?? 'ui-badge-muted' }}">{{ $estado }}</span>
