param([ValidateRange(1024,65535)][int]$Puerto = 8000)
$ErrorActionPreference = 'Stop'
$raizSavp = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$phpSavp = (Get-Command php -ErrorAction Stop).Source
$opcacheSavp = Join-Path (Split-Path -Parent $phpSavp) 'ext/php_opcache.dll'
if (-not (Test-Path -LiteralPath $opcacheSavp)) { throw 'Este PHP no tiene la extensión OPcache. Usa php artisan serve con el runtime habitual.' }
if (Get-NetTCPConnection -LocalPort $Puerto -State Listen -ErrorAction SilentlyContinue) { throw "El puerto $Puerto está ocupado. Este script no detiene procesos existentes." }
$routerSavp = Join-Path $raizSavp 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'
if (-not (Test-Path -LiteralPath $routerSavp)) { throw 'Falta el router del framework. Verifica vendor y el proyecto seleccionado.' }
Push-Location (Join-Path $raizSavp 'public')
try {
    Write-Host "SAVP local en http://127.0.0.1:$Puerto. Ctrl+C detiene este servidor."
    # Validar archivos en cada petición conserva las correcciones de PHP sin reiniciar.
    & $phpSavp '-d' "zend_extension=$opcacheSavp" '-d' 'opcache.enable_cli=1' '-d' 'opcache.validate_timestamps=1' '-d' 'opcache.revalidate_freq=0' '-d' 'opcache.memory_consumption=128' '-S' "127.0.0.1:$Puerto" $routerSavp
} finally { Pop-Location }
