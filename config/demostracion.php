<?php

return [
    'habilitada' => env('SAVP_DEMO_ENABLED', false),
    'base_autorizada' => env('SAVP_DEMO_DATABASE'),
    'password' => env('SAVP_DEMO_PASSWORD'),
    'ultimo_anio' => (int) env('SAVP_DEMO_YEAR', (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->format('Y')),
];
