<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Parámetros Institucionales del LMS / Aula Virtual SAVP
    | Unidad Educativa de Nivel Secundario Comunitario Productivo (Bolivia)
    |--------------------------------------------------------------------------
    */
    'aula_virtual' => [
        'tareas' => [
            'puntaje_minimo' => 1,
            'puntaje_maximo' => 100,
            'decimales' => 2,
            'descripcion_max_caracteres' => 2000,
            'limite_alerta_sobrecarga_semanal' => 3,
        ],

        'entregas' => [
            'tamano_maximo_mb' => 10,
            'tamano_maximo_kb' => 10240,
            'mimes_permitidos' => ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt', 'zip', 'jpg', 'jpeg', 'png'],
            'texto_max_caracteres' => 5000,
            'disco_almacenamiento' => 'local', // Almacenamiento privado seguro
            'directorio_entregas' => 'entregas_tareas',
        ],

        'calificaciones' => [
            'escala_minima' => 0,
            'escala_maxima' => 100,
            'nota_aprobacion' => 51,
            'comentario_max_caracteres' => 1000,
        ],

        'materiales' => [
            'tamano_maximo_mb' => 25,
            'tamano_maximo_kb' => 25600,
            'mimes_permitidos' => ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt', 'zip', 'mp4', 'mp3', 'jpg', 'jpeg', 'png'],
            'disco_almacenamiento' => 'local', // Almacenamiento privado seguro
            'directorio_materiales' => 'materiales_clase',
            'tipos_permitidos' => ['DOCUMENTO', 'PRESENTACION', 'VIDEO', 'ENLACE', 'EJERCICIO', 'LECTURA'],
            'estados_permitidos' => ['ACTIVO', 'INACTIVO'],
        ],

        'asistencia' => [
            'ausencias_consecutivas_alerta' => 3,
            'porcentaje_minimo_asistencia' => 80.0,
            'dias_edicion_retroactiva_max' => 7,
        ],
    ],
];
