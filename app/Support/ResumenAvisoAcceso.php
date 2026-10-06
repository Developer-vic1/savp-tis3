<?php

namespace App\Support;

final class ResumenAvisoAcceso
{
    public static function tareas(iterable $nombres): string
    {
        $tareas = collect($nombres)->unique()->map(fn ($nombre) => PermissionLabel::describe($nombre)['label']);
        $resumen = $tareas->take(3)->implode(', ');
        if ($tareas->count() > 3) $resumen .= ' y '.($tareas->count() - 3).' tareas más';
        return mb_strimwidth($resumen, 0, 280, '…');
    }

    public static function plazo(int $segundos): string
    {
        if ($segundos === 86400) return '24 horas';
        if ($segundos % 86400 === 0) return (int) ($segundos / 86400).' días';
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        return implode(' y ', array_filter([$horas ? $horas.' '.($horas === 1 ? 'hora' : 'horas') : null,
            $minutos ? $minutos.' '.($minutos === 1 ? 'minuto' : 'minutos') : null]));
    }
}
