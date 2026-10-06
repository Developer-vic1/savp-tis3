<?php

namespace App\Support\Academico;

/** Orientaciones para preparar casos; no equivalen a una disposición aprobada. */
final class CasosCalendarioInstitucional
{
    public static function tipos(): array
    {
        return [
            'FERIADO' => ['nombre' => 'Feriado nacional o local', 'icono' => 'flag', 'sugerencia' => 'Contrasta la fecha y su ámbito con la disposición oficial. Un feriado local no afecta automáticamente a todo el país.', 'efecto' => 'SUSPENSION'],
            'BLOQUEO_PARO' => ['nombre' => 'Bloqueo o paro', 'icono' => 'road-horizon', 'sugerencia' => 'Delimita la zona y los grupos afectados. Puedes explorar una suspensión o continuidad virtual sin modificar asistencias.', 'efecto' => 'SUSPENSION'],
            'ANIVERSARIO' => ['nombre' => 'Aniversario del colegio', 'icono' => 'confetti', 'sugerencia' => 'Si dura de tres a cinco días, indica todo el rango y distingue actividades escolares de jornadas sin clases.', 'efecto' => 'ACTIVIDAD'],
            'FESTEJO_TRASLADADO' => ['nombre' => 'Celebración en otra fecha', 'icono' => 'calendar-heart', 'sugerencia' => 'Conserva la fecha conmemorativa y elige cuándo se celebrará. El viernes o lunes se propone; nunca se traslada automáticamente.', 'efecto' => 'ACTIVIDAD'],
            'ENTREGA_NOTAS' => ['nombre' => 'Entrega de notas', 'icono' => 'file-text', 'sugerencia' => 'Si las clases terminan después del recreo, elige el turno y usa su hora de cierre real; no descontamos la jornada completa.', 'efecto' => 'PARCIAL'],
            'EMERGENCIA_SALUD' => ['nombre' => 'Emergencia sanitaria o pandemia', 'icono' => 'first-aid-kit', 'sugerencia' => 'Analiza un rango dentro de esta gestión, incluso prolongado. Revisa acceso virtual, evaluaciones y disposiciones antes de decidir.', 'efecto' => 'VIRTUAL'],
            'VISITA_UNIVERSIDAD' => ['nombre' => 'Visita a universidades', 'icono' => 'graduation-cap', 'sugerencia' => 'Selecciona el curso o grupo de la promoción y las horas de salida. La actividad no suspende al resto del colegio.', 'efecto' => 'SALIDA'],
            'VIAJE' => ['nombre' => 'Viaje o salida pedagógica', 'icono' => 'bus', 'sugerencia' => 'Delimita los grupos participantes, el acompañante y las horas de salida. Revisa quién cubre las demás clases.', 'efecto' => 'SALIDA'],
            'AMPLIACION_DESCANSO' => ['nombre' => 'Ampliación del descanso pedagógico', 'icono' => 'snowflake', 'sugerencia' => 'Indica solo los días adicionales y revisa su disposición. El análisis muestra las clases que necesitarían recuperación; no extiende el cierre automáticamente.', 'efecto' => 'SUSPENSION'],
            'OTRO' => ['nombre' => 'Otro caso', 'icono' => 'dots-three-circle', 'sugerencia' => 'Describe el caso, su alcance y el cambio previsto. La revisión conserva la decisión para revisión institucional.', 'efecto' => 'INFORMATIVO'],
        ];
    }

    public static function efectos(): array
    {
        return ['SUSPENSION' => 'Sin clases durante la jornada', 'PARCIAL' => 'Interrupción parcial de clases',
            'VIRTUAL' => 'Continuar con clases virtuales', 'SALIDA' => 'Salida pedagógica del grupo',
            'ACTIVIDAD' => 'Actividad institucional', 'INFORMATIVO' => 'Solo informar, sin interrumpir clases'];
    }

    public static function vacio(): array
    {
        return ['tipo' => 'BLOQUEO_PARO', 'efecto' => 'SUSPENSION', 'inicio' => '', 'fin' => '',
            'periodo' => 'DIA', 'revision' => 'NO', 'grupos' => [], 'turnos' => [],
            'universidad' => '', 'universidad_nombre' => '', 'universidad_url' => '', 'estudiar_universidad' => 'NO',
            'fecha_original' => '', 'alcance' => 'INSTITUCIONAL', 'grupo' => '', 'turno' => '',
            'jornada' => 'COMPLETA', 'hora_inicio' => '', 'hora_fin' => '', 'motivo' => '',
            'documento' => '', 'url_documento' => '', 'zona' => '', 'responsable' => '',
            'observacion_recuperacion' => '', 'dia_recuperacion' => 'POR_DEFINIR',
            'modalidad_recuperacion' => 'POR_DEFINIR', 'plan_recuperacion' => '',
            'medio_recuperacion' => 'AULA_INSTITUCIONAL', 'enlace_recuperacion' => '',
            'recuperacion' => 'PENDIENTE', 'fecha_recuperacion' => '', 'hora_recuperacion_inicio' => '', 'hora_recuperacion_fin' => '', 'anual' => 'NO'];
    }
}
