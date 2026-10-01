<?php

return [
    // Requiere verificar tabla notifications, identidad cod_usu y productores institucionales.
    'notifications' => env('SAVP_NOTIFICATIONS_ENABLED', false),
    'kardex' => env('SAVP_KARDEX_ENABLED', false),
    'curricular_units' => env('SAVP_CURRICULAR_UNITS_ENABLED', false),
    'academic_goals' => env('SAVP_ACADEMIC_GOALS_ENABLED', false),
    'institutional_calendar' => env('SAVP_INSTITUTIONAL_CALENDAR_ENABLED', false),
];
