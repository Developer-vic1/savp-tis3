<?php
// Importación acotada de las dos referencias proporcionadas y autorizadas por el usuario.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.'.config('database.default').'.database') !== 'SAVPTIS3-OFICIAL') throw new RuntimeException('Destino inesperado.');
$usuarios = \App\Models\Oficial\Sistema\User::role('Administrador')->where('est_usu','ACTIVO')->get();
if ($usuarios->count() !== 1 || $usuarios->first()->cod_usu !== 'USU_0001') throw new RuntimeException('Administrador de la importación no identificado inequívocamente.');
\Illuminate\Support\Facades\Auth::login($usuarios->first());
$servicio = app(\App\Services\ReferenciaDocumentalService::class);
$autoridad = $servicio->autorizar();
if (\Illuminate\Support\Str::upper(\Illuminate\Support\Str::ascii($autoridad['name'] ?? '')) !== 'GERMAN CAREAGA CANTUTA') throw new RuntimeException('La firma recibida no corresponde al Director vigente.');
$imagenes = ['SELLO'=>'C:/Users/LOQ/AppData/Local/Temp/codex-clipboard-73645b71-4da3-4a98-a2c4-04ca5feaf703.png',
    'FIRMA'=>'C:/Users/LOQ/AppData/Local/Temp/codex-clipboard-5f69480f-3977-4820-9da1-55294217643f.png'];
foreach ($imagenes as $tipo=>$ruta) {
    if (!is_file($ruta)) throw new RuntimeException('Imagen recibida no disponible.');
    $actual = $servicio->actuales()[strtolower($tipo)] ?? null;
    $referencia = $actual && hash_equals($actual->sha256, hash_file('sha256',$ruta)) ? $actual : $servicio->guardar($tipo,$ruta);
    if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($referencia->ruta) || !hash_equals($referencia->sha256, hash_file('sha256', \Illuminate\Support\Facades\Storage::disk('local')->path($referencia->ruta)))) throw new RuntimeException('No se verificó la conservación de la referencia.');
    echo $tipo.': registro y archivo privado verificados.'.PHP_EOL;
}
