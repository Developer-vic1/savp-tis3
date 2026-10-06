<?php

return [
    // Persistencia compartida entre peticiones; Redis en despliegues con varias instancias.
    'cache' => env('ACCESO_CACHE_STORE', 'file'),
    'intentos' => 3,
    'pausa_inicial' => 15,
    'pausa_maxima' => 300,
    'vigencia' => 3600,
    'peticiones_por_minuto' => 60,
];
