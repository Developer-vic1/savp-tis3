<?php

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\InspectorConexionAporte;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('aporte:comprobar', function () {
    $result = app(InspectorConexionAporte::class)->inspeccionar();
    foreach ($result['checks'] as $check) {
        $this->line(($check['available'] ? 'OK: ' : 'PENDIENTE: ').$check['label']);
    }
    $this->line('Fuentes: '.($result['source_count'] ?? 'No disponible'));
    $this->line($result['message']);

    return $result['connected'] ? 0 : 1;
})->purpose('Comprueba Laravel, autenticación interna, corpus y tutor sin escribir datos.');

Artisan::command('aporte:preparar', function () {
    $client = app(AporteIngenierilClient::class);
    if (! $client->health()->available || ! $client->warmUp()->available) {
        $this->error('El motor de análisis no está disponible.');

        return 1;
    }
    $this->info('Corpus documental preparado. No se persistió actividad de estudiante.');

    return 0;
});

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('accesos:avisar', function () {
    $this->info('Avisos procesados: '.app(\App\Services\AccesoProgramadoService::class)->procesarAvisos());
})->purpose('Publica avisos personales de inicio y fin sin cambiar permisos permanentes.');
\Illuminate\Support\Facades\Schedule::command('accesos:avisar')->everyMinute()->withoutOverlapping();
Artisan::command('documentacion:avisar-firma', function () {
    $this->info('Revisión de firma pendiente: '.app(\App\Services\ReferenciaDocumentalService::class)->avisarFirmaPendiente());
})->purpose('Avisa al Director vigente cuando falta su referencia de firma.');
\Illuminate\Support\Facades\Schedule::command('documentacion:avisar-firma')->everyFiveMinutes()->withoutOverlapping();
\Illuminate\Support\Facades\Schedule::command('regencia:rotar --aplicar')->dailyAt('00:10')->timezone('America/La_Paz')->withoutOverlapping();
