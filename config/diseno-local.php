<?php

return [
    'habilitado' => (bool) env('SAVP_DISENO_ENABLED', false),
    'clave_hash' => env('SAVP_DISENO_PASSWORD_HASH'),
    'minutos' => 30,
];
