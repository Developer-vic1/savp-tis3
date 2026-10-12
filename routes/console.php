<?php

use App\Services\AccesoProgramadoService;
use App\Services\AporteIngenieril\ActualizacionesFuentes;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\InspectorConexionAporte;
use App\Services\ReferenciaDocumentalService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

Artisan::command('conocimiento:revisar-actualizaciones', function () {
    $this->info('Publicaciones revisadas: '.app(ActualizacionesFuentes::class)->procesarPendientes());
})->purpose('Detecta cambios en fuentes universitarias programadas y avisa sin publicarlos.');
Schedule::command('conocimiento:revisar-actualizaciones')->hourly()->timezone('America/La_Paz')->withoutOverlapping(5);

Artisan::command('accesos:avisar', function () {
    $this->info('Avisos procesados: '.app(AccesoProgramadoService::class)->procesarAvisos());
})->purpose('Publica avisos personales de inicio y fin sin cambiar permisos permanentes.');
Schedule::command('accesos:avisar')->everyMinute()->withoutOverlapping();
Artisan::command('documentacion:avisar-firma', function () {
    $this->info('Revisión de firma pendiente: '.app(ReferenciaDocumentalService::class)->avisarFirmaPendiente());
})->purpose('Avisa al Director vigente cuando falta su referencia de firma.');
Schedule::command('documentacion:avisar-firma')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('regencia:rotar --aplicar')->dailyAt('00:10')->timezone('America/La_Paz')->withoutOverlapping();
