$ErrorActionPreference = 'Stop'
$OutputEncoding = New-Object Text.UTF8Encoding($false)
[Console]::OutputEncoding = $OutputEncoding
$raizPruebasSavp = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
. (Join-Path $raizPruebasSavp 'scripts/iniciador/funciones.ps1')
$carpetaPruebasSavp = Join-Path $raizPruebasSavp ('output/iniciador/funciones-'+[Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Force -Path (Join-Path $carpetaPruebasSavp 'scripts/iniciador') | Out-Null
Copy-Item (Join-Path $raizPruebasSavp 'scripts/iniciador/entorno.php') (Join-Path $carpetaPruebasSavp 'scripts/iniciador/entorno.php')
New-Item -ItemType Junction -Path (Join-Path $carpetaPruebasSavp 'vendor') -Target (Join-Path $raizPruebasSavp 'vendor') | Out-Null
[IO.File]::WriteAllText((Join-Path $carpetaPruebasSavp '.env.example'), "APP_NAME=SAVP`nDB_PASSWORD=`n", (New-Object Text.UTF8Encoding($false)))
$phpPruebasSavp = Buscar-PHPIniciador
if (-not $phpPruebasSavp) { throw 'La prueba requiere PHP instalado.' }
$antesEntornoSavp = (Get-FileHash (Join-Path $raizPruebasSavp '.env')).Hash
$contadorPruebasSavp = 0
foreach ($valorPruebaSavp in @('espacios y # comentarios','comillas " y '' simples','literal ${VARIABLE} y $(comando)','ruta C:\prueba\archivo','texto con á y ñ')) {
    @{ DB_PASSWORD=$valorPruebaSavp } | ConvertTo-Json -Compress | & $phpPruebasSavp (Join-Path $carpetaPruebasSavp 'scripts/iniciador/entorno.php') escribir | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'Falló la escritura del entorno aislado.' }
    $leidoPruebaSavp = (& $phpPruebasSavp (Join-Path $carpetaPruebasSavp 'scripts/iniciador/entorno.php') leer | ConvertFrom-Json).DB_PASSWORD
    if ($leidoPruebaSavp -cne $valorPruebaSavp) { throw 'Una contraseña no se conservó literalmente.' }
    $contadorPruebasSavp++
}
$puertoPruebaSavp = Obtener-PuertoLibreSavp 19000
$oyentePruebaSavp = New-Object Net.Sockets.TcpListener([Net.IPAddress]::Loopback,$puertoPruebaSavp)
try {
    $oyentePruebaSavp.Start()
    $otroPuertoSavp = Obtener-PuertoLibreSavp $puertoPruebaSavp
    if ($otroPuertoSavp -eq $puertoPruebaSavp) { throw 'Se seleccionó un puerto ocupado.' }
    $clientePruebaSavp = New-Object Net.Sockets.TcpClient('127.0.0.1',$puertoPruebaSavp)
    $clientePruebaSavp.Dispose()
    $contadorPruebasSavp++
} finally { $oyentePruebaSavp.Stop() }
$iniPruebaSavp = Preparar-PHPIniciador $phpPruebasSavp $carpetaPruebasSavp
$modulosPruebaSavp = @(& $phpPruebasSavp -c $iniPruebaSavp -m)
foreach ($extensionPruebaSavp in @('pdo_pgsql','mbstring','gd','zip')) { if ($modulosPruebaSavp -notcontains $extensionPruebaSavp) { throw 'No se preparó una extensión necesaria.' } }
$contadorPruebasSavp++
$erroresParserSavp = $null
[Management.Automation.Language.Parser]::ParseFile((Join-Path $raizPruebasSavp 'scripts/iniciar-savp.ps1'),[ref]$null,[ref]$erroresParserSavp) | Out-Null
if ($erroresParserSavp.Count -gt 0) { throw 'El iniciador no se puede interpretar en PowerShell.' }
$contadorPruebasSavp++
& powershell.exe -NoProfile -ExecutionPolicy Bypass -File (Join-Path $raizPruebasSavp 'scripts/iniciar-savp.ps1') -SoloDiagnostico -NoInstalar -NoInteractivo -Modo Demostracion -NombreBase SAVPTIS3-OFICIAL | Out-Null
if ($LASTEXITCODE -ne 1) { throw 'No se bloqueó la base institucional en modo Demostracion.' }
if ((Get-FileHash (Join-Path $raizPruebasSavp '.env')).Hash -ne $antesEntornoSavp) { throw 'La prueba cambió el .env institucional.' }
$contadorPruebasSavp++
@{ resultado='correcto'; pruebas=$contadorPruebasSavp; entorno_original_conservado=$true; puerto_ajeno_conservado=$true } | ConvertTo-Json
