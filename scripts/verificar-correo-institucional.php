<?php

use Illuminate\Contracts\Console\Kernel;

// Verifica autenticación SMTP sin enviar un mensaje.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
try {
    $transporte = app('mail.manager')->mailer('smtp')->getSymfonyTransport();
    $transporte->start();
    $transporte->stop();
    echo "Autenticación SMTP correcta. No se envió ningún correo.\n";
} catch (Throwable) {
    fwrite(STDERR, "No se pudo autenticar SMTP. Verifica la conexión y la credencial de aplicación.\n");
    exit(1);
}
