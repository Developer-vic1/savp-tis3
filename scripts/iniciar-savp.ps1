[CmdletBinding()]
param(
    [ValidateSet('Existente','Demostracion')][string]$Modo,
    [string]$NombreBase,
    [switch]$NoInstalar,
    [switch]$NoAbrir,
    [switch]$SoloDiagnostico,
    [switch]$SoloPreparar,
    [switch]$NoInteractivo,
    [ValidateRange(0,600)][int]$SegundosVerificacion = 0
)
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'
$OutputEncoding = New-Object Text.UTF8Encoding($false)
[Console]::OutputEncoding = $OutputEncoding
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
. (Join-Path $PSScriptRoot 'iniciador/funciones.ps1')
$raizSavp = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$raizAnteriorSavp = Get-Location
$procesosSavp = New-Object 'System.Collections.Generic.List[System.Diagnostics.Process]'
$hashRaizSavp = [BitConverter]::ToString([Security.Cryptography.SHA256]::Create().ComputeHash([Text.Encoding]::UTF8.GetBytes($raizSavp))).Replace('-','')
$mutexSavp = New-Object Threading.Mutex($false, "Local\SAVP_$hashRaizSavp")
$bloqueoSavp = $false
$variablesGuardadasSavp = @{}
try {
    $bloqueoSavp = $mutexSavp.WaitOne(0)
    if (-not $bloqueoSavp) { throw 'El iniciador ya está abierto para esta carpeta.' }
    Set-Location -LiteralPath $raizSavp
    Write-Host "`nSAVP · Preparación y acceso local" -ForegroundColor Green
    Write-Host "Carpeta: $raizSavp"
    Actualizar-RutaIniciador
    $phpSavp = Buscar-PHPIniciador
    if (-not $phpSavp) {
        Instalar-PaqueteSavp 'PHP.PHP.8.4' -NoInstalar:($NoInstalar -or $SoloDiagnostico)
        $phpSavp = Buscar-PHPIniciador
        if (-not $phpSavp) { throw 'PHP fue instalado, pero aún no está disponible. Vuelve a abrir ABRIR-SAVP.cmd.' }
    }
    if ($SoloDiagnostico) {
        if (-not (Test-Path vendor/autoload.php)) { throw 'Faltan dependencias Composer; ejecuta el iniciador en modo normal.' }
        $iniSavp = $null
    } else {
        New-Item -ItemType Directory -Force -Path 'storage/app/private/iniciador','storage/logs/iniciador' | Out-Null
        $iniSavp = Preparar-PHPIniciador $phpSavp (Join-Path $raizSavp 'storage/app/private/iniciador')
    }
    $phpArgsSavp = if ($iniSavp) { @('-c',$iniSavp) } else { @() }
    $archivoEstadoSavp = Join-Path $raizSavp 'storage/app/private/iniciador/estado.json'
    $estadoSavp = Leer-EstadoIniciador $archivoEstadoSavp
    $huellaComposerSavp = (Get-FileHash composer.lock).Hash + (Get-FileHash composer.json).Hash
    if (-not $SoloDiagnostico) {
        $composerSavp = Get-Command composer.bat,composer.exe -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($composerSavp) {
            # Composer usa el mismo PHP/ini local que Laravel.
            $composerPharSavp = Join-Path (Split-Path -Parent $composerSavp.Source) 'composer.phar'
        } else { $composerPharSavp = Join-Path $raizSavp 'storage/app/private/iniciador/composer.phar' }
        if (-not (Test-Path -LiteralPath $composerPharSavp)) {
            if ($NoInstalar) { throw 'Falta Composer y se deshabilitaron las instalaciones.' }
            $instaladorSavp = Join-Path $raizSavp 'storage/app/private/iniciador/composer-setup.php'
            [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
            Invoke-WebRequest 'https://getcomposer.org/installer' -UseBasicParsing -OutFile $instaladorSavp
            $firmaSavp = (Invoke-WebRequest 'https://composer.github.io/installer.sig' -UseBasicParsing).Content.Trim()
            if ((Get-FileHash -LiteralPath $instaladorSavp -Algorithm SHA384).Hash.ToLowerInvariant() -ne $firmaSavp.ToLowerInvariant()) { throw 'La firma del instalador de Composer no coincide. No se ejecutó.' }
            Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @($instaladorSavp,'--install-dir='+(Split-Path -Parent $composerPharSavp),'--filename=composer.phar'))
        }
        if (-not (Test-Path vendor/autoload.php) -or $estadoSavp['composer'] -ne $huellaComposerSavp) {
            Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @($composerPharSavp,'install','--no-interaction','--prefer-dist','--no-scripts'))
            $estadoSavp['composer'] = $huellaComposerSavp
            Guardar-EstadoIniciador $estadoSavp $archivoEstadoSavp
        }
    }
    $jsonEntornoSavp = & $phpSavp @phpArgsSavp 'scripts/iniciador/entorno.php' leer
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo leer la configuración local.' }
    $entornoSavp = @{}
    ($jsonEntornoSavp | ConvertFrom-Json).PSObject.Properties | ForEach-Object { $entornoSavp[$_.Name] = [string]$_.Value }
    if (-not $SoloDiagnostico -and $entornoSavp['APP_ENV'] -eq 'production') { throw 'Esta configuración es de producción. El iniciador requiere una copia local separada; no se cambió .env.' }
    if (-not $Modo) {
        if ($NoInteractivo) { throw 'Indica -Modo Existente o -Modo Demostracion.' }
        Write-Host "`n1. Abrir base existente (conservar datos; no migrar ni cargar registros)."
        Write-Host '2. Crear demostración nueva con seis gestiones y horarios completos.'
        $eleccionSavp = Read-Host 'Opción [1]'
        $Modo = if ($eleccionSavp -eq '2') { 'Demostracion' } elseif ($eleccionSavp -in @('','1')) { 'Existente' } else { throw 'Opción no válida.' }
    }
    $predeterminadaSavp = if ($Modo -eq 'Demostracion') { 'savp_demo_local' } elseif ($entornoSavp.ContainsKey('DB_DATABASE') -and $entornoSavp['DB_CONNECTION'] -eq 'pgsql') { $entornoSavp['DB_DATABASE'] } else { '' }
    if (-not $NombreBase) {
        if ($NoInteractivo) { throw 'Indica -NombreBase para verificar el destino.' }
        $NombreBase = Leer-DatoSavp 'Nombre exacto de la base PostgreSQL' $predeterminadaSavp
    }
    if ($NombreBase -notmatch '\A[A-Za-z0-9_-]{1,63}\z') { throw 'Usa un nombre de base de 1 a 63 letras, números, guiones o guiones bajos.' }
    if ($Modo -eq 'Demostracion' -and $NombreBase -notmatch '\Asavp_demo_[a-z0-9_]+\z') { throw 'La demostración requiere un nombre savp_demo_. La base oficial nunca se usa como demostración.' }
    $conexionSavp = @{}
    foreach ($parSavp in @(@('DB_HOST','127.0.0.1'),@('DB_PORT','5432'),@('DB_USERNAME','postgres'))) {
        $valorSavp = if ($entornoSavp.ContainsKey($parSavp[0])) { $entornoSavp[$parSavp[0]] } else { $parSavp[1] }
        $conexionSavp[$parSavp[0]] = if ($NoInteractivo) { $valorSavp } else { Leer-DatoSavp $parSavp[0] $valorSavp }
    }
    $claveDbSavp = if ($entornoSavp.ContainsKey('DB_PASSWORD')) { $entornoSavp['DB_PASSWORD'] } else { '' }
    if (-not $NoInteractivo) {
        $entradaClaveSavp = Leer-SecretoSavp 'Contraseña PostgreSQL (Enter conserva la configurada, si existe)'
        if ($entradaClaveSavp -ne '') { $claveDbSavp = $entradaClaveSavp }
    }
    $conexionSavp['DB_PASSWORD'] = $claveDbSavp
    $conexionSavp['DB_DATABASE'] = $NombreBase
    $conexionSavp['DB_CONNECTION'] = 'pgsql'
    $conexionSavp['DB_URL'] = ''
    $conexionSavp['DATABASE_URL'] = ''
    foreach ($claveSavp in $conexionSavp.Keys) {
        $variablesGuardadasSavp[$claveSavp] = [Environment]::GetEnvironmentVariable($claveSavp,'Process')
        [Environment]::SetEnvironmentVariable($claveSavp,$conexionSavp[$claveSavp],'Process')
    }
    Write-Host "`nVerificando destino: $($conexionSavp['DB_HOST']):$($conexionSavp['DB_PORT']) / $NombreBase"
    $inspeccionTextoSavp = & $phpSavp @phpArgsSavp 'scripts/iniciador/base-datos.php' inspeccionar
    if ($LASTEXITCODE -ne 0) {
        if ($NoInteractivo -or $NoInstalar -or $SoloDiagnostico) { throw 'No se pudo verificar PostgreSQL; no se modificó .env.' }
        $servicioPgSavp = Get-Service -Name 'postgresql*' -ErrorAction SilentlyContinue | Where-Object { $_.Status -eq 'Stopped' } | Select-Object -First 1
        if ($servicioPgSavp -and $conexionSavp['DB_HOST'] -in @('127.0.0.1','localhost','::1')) {
            if ($servicioPgSavp.Name -notmatch '\A[A-Za-z0-9_-]+\z') { throw 'Nombre de servicio PostgreSQL no válido.' }
            Write-Host "Iniciando el servicio PostgreSQL $($servicioPgSavp.Name)..."
            try { Start-Service -Name $servicioPgSavp.Name }
            catch {
                Start-Process powershell.exe -Verb RunAs -WindowStyle Hidden -Wait -ArgumentList @('-NoProfile','-Command',"Start-Service -Name '$($servicioPgSavp.Name)'")
            }
            $inspeccionTextoSavp = & $phpSavp @phpArgsSavp 'scripts/iniciador/base-datos.php' inspeccionar
            if ($LASTEXITCODE -ne 0) { throw 'PostgreSQL no pudo verificarse. Revisa las credenciales y el puerto; no se modificó .env.' }
        } else {
        $instalarPgSavp = Read-Host 'Si PostgreSQL no está instalado, escribe INSTALAR. Para corregir credenciales, pulsa Enter y vuelve a ejecutar'
        if ($instalarPgSavp -ne 'INSTALAR') { throw 'Conexión no verificada. No se modificó .env.' }
        Instalar-PaqueteSavp 'PostgreSQL.PostgreSQL.17'
        $conexionSavp['DB_PORT'] = Leer-DatoSavp 'Puerto elegido al instalar PostgreSQL' $conexionSavp['DB_PORT']
        $conexionSavp['DB_USERNAME'] = Leer-DatoSavp 'Usuario PostgreSQL' $conexionSavp['DB_USERNAME']
        $conexionSavp['DB_PASSWORD'] = Leer-SecretoSavp 'Contraseña que elegiste al instalar PostgreSQL'
        foreach ($claveSavp in @('DB_PORT','DB_USERNAME','DB_PASSWORD')) { [Environment]::SetEnvironmentVariable($claveSavp,$conexionSavp[$claveSavp],'Process') }
        $inspeccionTextoSavp = & $phpSavp @phpArgsSavp 'scripts/iniciador/base-datos.php' inspeccionar
        if ($LASTEXITCODE -ne 0) { throw 'PostgreSQL fue instalado, pero la conexión sigue sin verificarse. No se modificó .env.' }
        }
    }
    $inspeccionSavp = $inspeccionTextoSavp | ConvertFrom-Json
    if ($SoloDiagnostico) { $inspeccionSavp | ConvertTo-Json -Depth 4; return }
    if (-not $inspeccionSavp.existe) {
        if ($Modo -ne 'Demostracion') { throw 'La base no existe. Selecciona una base existente o crea una demostración savp_demo_.' }
        if ($NoInteractivo) { throw 'La creación de una base nueva exige confirmar su nombre en modo interactivo.' }
        if ((Read-Host "Para CREAR la base nueva, escribe exactamente $NombreBase") -cne $NombreBase) { throw 'Nombre no confirmado. No se creó la base.' }
        $inspeccionTextoSavp = & $phpSavp @phpArgsSavp 'scripts/iniciador/base-datos.php' crear
        if ($LASTEXITCODE -ne 0) { throw 'No se creó la base solicitada.' }
        $inspeccionSavp = $inspeccionTextoSavp | ConvertFrom-Json
    }
    $migrarDemoSavp = $Modo -eq 'Demostracion' -and $inspeccionSavp.vacia
    $prepararDemoSavp = $Modo -eq 'Demostracion' -and ($inspeccionSavp.vacia -or $inspeccionSavp.puede_inicializar_demo)
    if ($inspeccionSavp.vacia -and -not $prepararDemoSavp) { throw 'La base está vacía. El modo Existente no ejecuta migraciones ni seeders.' }
    if (-not $inspeccionSavp.vacia -and $inspeccionSavp.faltantes.Count -gt 0) { throw "La base existente está incompleta o no es SAVP. Faltan: $($inspeccionSavp.faltantes -join ', '). No se alteró su esquema." }
    $passwordDemoSavp = ''
    if ($prepararDemoSavp) {
        if ($NoInteractivo) { throw 'La preparación de una demostración vacía requiere una contraseña elegida en modo interactivo.' }
        Write-Host 'Se cargarán datos sintéticos, sin fuentes privadas ni registros institucionales.'
        $passwordDemoSavp = Leer-SecretoSavp 'Contraseña para las cuentas de demostración (mínimo 12 caracteres)'
        $longitudDemoSavp = (New-Object Globalization.StringInfo($passwordDemoSavp)).LengthInTextElements
        if ($longitudDemoSavp -lt 12 -or [Text.Encoding]::UTF8.GetByteCount($passwordDemoSavp) -gt 72 -or $passwordDemoSavp -cne (Leer-SecretoSavp 'Repite la contraseña de demostración')) { throw 'Usa al menos 12 caracteres, máximo 72 bytes, y repite la misma contraseña.' }
    }
    $nodeSavp = Get-Command node.exe -ErrorAction SilentlyContinue
    if (-not $nodeSavp -or [version](& $nodeSavp.Source -p 'process.versions.node') -lt [version]'22.12.0') {
        Instalar-PaqueteSavp 'OpenJS.NodeJS.LTS' -NoInstalar:$NoInstalar
        $nodeSavp = Get-Command node.exe -ErrorAction Stop
    }
    $npmSavp = (Get-Command npm.cmd -ErrorAction Stop).Source
    $huellaNpmSavp = (Get-FileHash package-lock.json).Hash + (& $nodeSavp.Source -p 'process.versions.node')
    $npmActualizadoSavp = -not (Test-Path node_modules/.package-lock.json) -or $estadoSavp['npm'] -ne $huellaNpmSavp
    if ($npmActualizadoSavp) {
        Ejecutar-ComandoSavp $npmSavp @('ci','--ignore-scripts')
        $estadoSavp['npm'] = $huellaNpmSavp
        Guardar-EstadoIniciador $estadoSavp $archivoEstadoSavp
    }
    $pythonSavp = Get-Command python.exe -ErrorAction SilentlyContinue
    $pythonValidoSavp = if ($pythonSavp) { (& $pythonSavp.Source -c 'import sys; print(int((3,12) <= sys.version_info[:2] < (3,15)))' 2>$null) -eq '1' } else { $false }
    if (-not $pythonValidoSavp) {
        Instalar-PaqueteSavp 'Python.Python.3.14' -NoInstalar:$NoInstalar
        $pythonSavp = Get-Command python.exe -ErrorAction Stop
    }
    $pythonAporteSavp = Join-Path $raizSavp 'ai-service/.venv/Scripts/python.exe'
    $huellaPythonSavp = (Get-FileHash ai-service/uv.lock).Hash
    if (-not (Test-Path -LiteralPath $pythonAporteSavp) -or $estadoSavp['python'] -ne $huellaPythonSavp) {
        $uvSavp = Buscar-UvIniciador
        if (-not $uvSavp) {
            Instalar-PaqueteSavp 'astral-sh.uv' -NoInstalar:$NoInstalar
            $uvSavp = Buscar-UvIniciador
            if (-not $uvSavp) { throw 'uv fue instalado, pero aún no está disponible. Abre de nuevo el iniciador.' }
        }
        $variablesGuardadasSavp['UV_PROJECT_ENVIRONMENT'] = [Environment]::GetEnvironmentVariable('UV_PROJECT_ENVIRONMENT','Process')
        $env:UV_PROJECT_ENVIRONMENT = Join-Path $raizSavp 'ai-service/.venv'
        $opcionTlsSavp = if ((& $uvSavp --help | Out-String) -match '\-\-system-certs') { '--system-certs' } else { '--native-tls' }
        Ejecutar-ComandoSavp $uvSavp @('sync',$opcionTlsSavp,'--frozen','--no-dev','--project',(Join-Path $raizSavp 'ai-service'),'--python',$pythonSavp.Source)
        $estadoSavp['python'] = $huellaPythonSavp
        Guardar-EstadoIniciador $estadoSavp $archivoEstadoSavp
    }
    $puertoWebSavp = Obtener-PuertoLibreSavp 8000
    $puertoAporteSavp = Obtener-PuertoLibreSavp ([Math]::Max(8001,$puertoWebSavp + 1))
    $urlSavp = "http://127.0.0.1:$puertoWebSavp"
    $cambiosSavp = $conexionSavp.Clone()
    $cambiosSavp['APP_NAME'] = 'SAVP TIS 3'
    $cambiosSavp['APP_URL'] = $urlSavp
    $cambiosSavp['APP_ENV'] = 'local'
    $cambiosSavp['APP_DEBUG'] = 'false'
    $cambiosSavp['APP_LOCALE'] = 'es'
    $cambiosSavp['APP_FALLBACK_LOCALE'] = 'es'
    $cambiosSavp['SAVP_RECURSOS_COMPILADOS'] = 'true'
    $cambiosSavp['APORTE_INGENIERIL_ENABLED'] = 'true'
    $cambiosSavp['APORTE_INGENIERIL_BASE_URL'] = "http://127.0.0.1:$puertoAporteSavp"
    $cambiosSavp['APORTE_INGENIERIL_ANALYSIS_PATH'] = '/api/v1/analysis'
    $cambiosSavp['APORTE_INGENIERIL_KNOWLEDGE_PATH'] = '/api/v1/knowledge/search'
    $cambiosSavp['APORTE_INGENIERIL_KNOWLEDGE_GOVERNANCE_PATH'] = '/api/v1/knowledge/governance'
    $cambiosSavp['APORTE_INGENIERIL_TUTOR_PATH'] = '/api/v1/tutor/query'
    $cambiosSavp['SAVP_URL_ACCESO_PUBLICO'] = $urlSavp
    $cambiosSavp['CALENDARIO_ESTUDIO_PYTHON'] = $pythonAporteSavp.Replace('\','/')
    $cambiosSavp['SESSION_SECURE_COOKIE'] = 'false'
    $cambiosSavp['SESSION_DOMAIN'] = 'null'
    if (-not $entornoSavp.ContainsKey('APP_KEY') -or -not $entornoSavp['APP_KEY']) { $cambiosSavp['APP_KEY'] = 'base64:'+(& $phpSavp -r 'echo base64_encode(random_bytes(32));') }
    if ($Modo -eq 'Demostracion') { $cambiosSavp['SAVP_DEMO_ENABLED'] = 'true'; $cambiosSavp['SAVP_DEMO_DATABASE'] = $NombreBase }
    $apiKeySavp = if ($entornoSavp.ContainsKey('APORTE_INGENIERIL_API_KEY') -and $entornoSavp['APORTE_INGENIERIL_API_KEY'].Length -ge 32) { $entornoSavp['APORTE_INGENIERIL_API_KEY'] } else { & $phpSavp -r 'echo bin2hex(random_bytes(32));' }
    $cambiosSavp['APORTE_INGENIERIL_API_KEY'] = $apiKeySavp
    $cambiosSavp | ConvertTo-Json -Compress | & $phpSavp @phpArgsSavp 'scripts/iniciador/entorno.php' escribir
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo guardar la configuración verificada.' }
    foreach ($claveSavp in $cambiosSavp.Keys) {
        if (-not $variablesGuardadasSavp.ContainsKey($claveSavp)) { $variablesGuardadasSavp[$claveSavp] = [Environment]::GetEnvironmentVariable($claveSavp,'Process') }
        [Environment]::SetEnvironmentVariable($claveSavp,[string]$cambiosSavp[$claveSavp],'Process')
    }
    Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @('artisan','config:clear'))
    Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @('artisan','package:discover'))
    if ($prepararDemoSavp) {
        if ($migrarDemoSavp) { Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @('artisan','migrate','--force')) }
        $env:SAVP_DEMO_PASSWORD = $passwordDemoSavp
        try { Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @('artisan','db:seed','--class=Database\Seeders\Demostracion\HistorialSeisGestionesSeeder','--force')) }
        finally { Remove-Item Env:SAVP_DEMO_PASSWORD -ErrorAction SilentlyContinue; $passwordDemoSavp = '' }
    }
    Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @('artisan','view:clear'))
    $fuentesSavp = @(Get-ChildItem resources -Recurse -File; Get-Item tailwind.config.js,vite.config.js,package-lock.json)
    $ultimaFuenteSavp = ($fuentesSavp | Sort-Object LastWriteTimeUtc -Descending | Select-Object -First 1).LastWriteTimeUtc
    $manifiestoSavp = Get-Item public/build/manifest.json -ErrorAction SilentlyContinue
    if (-not $manifiestoSavp -or $manifiestoSavp.LastWriteTimeUtc -lt $ultimaFuenteSavp -or $npmActualizadoSavp) { Ejecutar-ComandoSavp $npmSavp @('run','build') }
    Ejecutar-ComandoSavp $phpSavp ($phpArgsSavp + @('artisan','view:cache'))
    @{ composer=$huellaComposerSavp; npm=$huellaNpmSavp; python=$huellaPythonSavp } | ConvertTo-Json | Set-Content -LiteralPath $archivoEstadoSavp -Encoding UTF8
    Write-Host "`nPreparación completada. Base verificada: $NombreBase" -ForegroundColor Green
    if ($Modo -eq 'Demostracion') {
        Write-Host 'Acceso estudiante: estudiante6.m@savp.test (mañana) o estudiante6.t@savp.test (tarde).'
        Write-Host 'Administración: administrador@savp.test. Usa la contraseña elegida al crear esta demostración.'
        Write-Host 'El ingreso mantiene el formulario y la autorización normal del sistema.'
    }
    if ($SoloPreparar) { return }
    # Claves solo en el entorno heredado del servicio; no en argumentos ni logs.
    $variablesAporteSavp = @{ SAVP_AI_API_KEY=$apiKeySavp; SAVP_AI_HOST='127.0.0.1'; SAVP_AI_PORT=[string]$puertoAporteSavp }
    foreach ($claveSavp in $variablesAporteSavp.Keys) { $variablesGuardadasSavp[$claveSavp] = [Environment]::GetEnvironmentVariable($claveSavp,'Process'); [Environment]::SetEnvironmentVariable($claveSavp,$variablesAporteSavp[$claveSavp],'Process') }
    $argsWebSavp = $phpArgsSavp + @('-S',"127.0.0.1:$puertoWebSavp",(Join-Path $raizSavp 'public/enrutador-desarrollo.php'))
    $opcacheSavp = Join-Path (Split-Path -Parent $phpSavp) 'ext/php_opcache.dll'
    if (Test-Path -LiteralPath $opcacheSavp) {
        $argsOpcacheSavp = @('-d','opcache.enable_cli=1','-d','opcache.validate_timestamps=1','-d','opcache.revalidate_freq=0')
        if (@(& $phpSavp @phpArgsSavp -m) -notcontains 'Zend OPcache') { $argsOpcacheSavp += @('-d',"zend_extension=$opcacheSavp") }
        $argsWebSavp = $phpArgsSavp + $argsOpcacheSavp + @('-S',"127.0.0.1:$puertoWebSavp",(Join-Path $raizSavp 'public/enrutador-desarrollo.php'))
    }
    # Start-Process recibe una línea Windows; citar rutas con espacios sin interpolar comandos.
    $argsWebTextoSavp = ($argsWebSavp | ForEach-Object { '"'+$_.Replace('"','\"')+'"' }) -join ' '
    $webSavp = Start-Process -FilePath $phpSavp -ArgumentList $argsWebTextoSavp -WorkingDirectory (Join-Path $raizSavp 'public') -WindowStyle Hidden -RedirectStandardOutput (Join-Path $raizSavp 'storage/logs/iniciador/web.stdout.log') -RedirectStandardError (Join-Path $raizSavp 'storage/logs/iniciador/web.stderr.log') -PassThru
    $procesosSavp.Add($webSavp)
    $aporteSavp = Start-Process -FilePath $pythonAporteSavp -ArgumentList @('-m','uvicorn','app.main:app','--host','127.0.0.1','--port',[string]$puertoAporteSavp) -WorkingDirectory (Join-Path $raizSavp 'ai-service') -WindowStyle Hidden -RedirectStandardOutput (Join-Path $raizSavp 'storage/logs/iniciador/aporte.stdout.log') -RedirectStandardError (Join-Path $raizSavp 'storage/logs/iniciador/aporte.stderr.log') -PassThru
    $procesosSavp.Add($aporteSavp)
    Esperar-ServicioSavp "http://127.0.0.1:$puertoAporteSavp/health" $aporteSavp
    Esperar-ServicioSavp "$urlSavp/login" $webSavp
    if (-not $NoAbrir) { Start-Process "$urlSavp/login" }
    Write-Host "`nSAVP abierto en $urlSavp/login"
    if ($SegundosVerificacion -gt 0) { Start-Sleep -Seconds $SegundosVerificacion }
    else { Read-Host 'Pulsa Enter aquí para detener los servicios iniciados por este programa' | Out-Null }
} catch {
    Write-Host "`n$($_.Exception.Message)" -ForegroundColor Red
    exit 1
} finally {
    foreach ($procesoSavp in $procesosSavp) { $procesoSavp.Refresh(); if (-not $procesoSavp.HasExited) { $procesoSavp.Kill(); $procesoSavp.WaitForExit(5000) | Out-Null } }
    foreach ($claveSavp in $variablesGuardadasSavp.Keys) { [Environment]::SetEnvironmentVariable($claveSavp,$variablesGuardadasSavp[$claveSavp],'Process') }
    if ($bloqueoSavp) { $mutexSavp.ReleaseMutex() }
    $mutexSavp.Dispose()
    Set-Location $raizAnteriorSavp
}
