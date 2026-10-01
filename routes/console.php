<?php

Illuminate\Support\Facades\Artisan::command('peter3:warmup', function () {
    $client = app(App\Services\AporteIngenieril\AporteIngenierilClient::class);
    if (! $client->health()->available || ! $client->warmUp()->available) {
        $this->error('El motor de análisis no está disponible.');
        return 1;
    }
    $this->info('Motor E5 preparado. No se persistió actividad de estudiante.');
    return 0;
});

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
