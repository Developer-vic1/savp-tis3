Set-StrictMode -Version Latest

function Actualizar-RutaIniciador {
    $rutas = @($env:Path, [Environment]::GetEnvironmentVariable('Path', 'User'), [Environment]::GetEnvironmentVariable('Path', 'Machine'))
    $rutas += @("$env:LOCALAPPDATA\Microsoft\WinGet\Links", "$env:ProgramFiles\nodejs", "$env:LOCALAPPDATA\Programs\Python\Python314")
    $env:Path = ($rutas | Where-Object { $_ }) -join ';'
}

function Ejecutar-ComandoSavp {
    param([string]$Programa, [string[]]$Argumentos)
    & $Programa @Argumentos
    if ($LASTEXITCODE -ne 0) { throw "Falló $([IO.Path]::GetFileName($Programa)). Revisa el diagnóstico anterior; el iniciador se detuvo." }
}

function Instalar-PaqueteSavp {
    param([string]$Id, [switch]$NoInstalar)
    if ($NoInstalar) { throw "Falta $Id. El modo actual no permite instalar dependencias." }
    if (-not (Get-Command winget.exe -ErrorAction SilentlyContinue)) {
        Write-Host 'Preparando WinGet desde el módulo oficial de Microsoft...'
        Install-PackageProvider -Name NuGet -Scope CurrentUser -Force | Out-Null
        Install-Module -Name Microsoft.WinGet.Client -Repository PSGallery -Scope CurrentUser -Force
        Import-Module Microsoft.WinGet.Client
        Repair-WinGetPackageManager
        Actualizar-RutaIniciador
    }
    Write-Host "Instalando $Id desde WinGet. Atiende las ventanas del instalador y de Windows."
    Ejecutar-ComandoSavp 'winget.exe' @('install', '--id', $Id, '--exact', '--source', 'winget', '--interactive')
    Actualizar-RutaIniciador
}

function Buscar-PHPIniciador {
    $candidatos = @()
    $actual = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($actual) { $candidatos += $actual.Source }
    $candidatos += @(Get-ChildItem -Path "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.4*" -Directory -ErrorAction SilentlyContinue | Get-ChildItem -Recurse -Filter php.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName)
    foreach ($ruta in $candidatos) {
        $archivo = Get-Item -LiteralPath $ruta -ErrorAction SilentlyContinue
        if ($archivo -and $archivo.PSObject.Properties['Target'] -and $archivo.Target) {
            $ruta = @($archivo.Target)[0]
            if (-not [IO.Path]::IsPathRooted($ruta)) { $ruta = Join-Path $archivo.DirectoryName $ruta }
        }
        $version = & $ruta -r 'echo PHP_VERSION_ID;'
        if ($LASTEXITCODE -eq 0 -and [int]$version -ge 80400) { return $ruta }
    }
    return $null
}

function Leer-EstadoIniciador {
    param([string]$Archivo)
    $estado = @{}
    if (Test-Path -LiteralPath $Archivo) {
        try { (Get-Content -LiteralPath $Archivo -Raw | ConvertFrom-Json).PSObject.Properties | ForEach-Object { $estado[$_.Name] = [string]$_.Value } }
        catch { return @{} }
    }
    return $estado
}

function Guardar-EstadoIniciador {
    param([hashtable]$Estado, [string]$Archivo)
    [IO.File]::WriteAllText($Archivo, ($Estado | ConvertTo-Json), (New-Object Text.UTF8Encoding($false)))
}

function Buscar-UvIniciador {
    $actual = Get-Command uv.exe -ErrorAction SilentlyContinue
    if ($actual) { return $actual.Source }
    $portatil = Get-ChildItem -Path "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\astral-sh.uv*" -Directory -ErrorAction SilentlyContinue | Get-ChildItem -Recurse -Filter uv.exe -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($portatil) { return $portatil.FullName }
    return $null
}

function Preparar-PHPIniciador {
    param([string]$Php, [string]$Directorio)
    $ini = Join-Path $Directorio 'php.ini'
    $base = Split-Path -Parent $Php
    $cargado = & $Php -r 'echo php_ini_loaded_file();'
    $contenido = if ($cargado -and (Test-Path -LiteralPath $cargado)) { [IO.File]::ReadAllText($cargado) } else { '' }
    $contenido += "`r`n; Configuración privada del iniciador; no modifica php.ini global.`r`n"
    $contenido += "extension_dir=`"$($base.Replace('\','/'))/ext`"`r`nmemory_limit=512M`r`n"
    $modulos = @(& $Php -m)
    foreach ($extension in @('curl','fileinfo','gd','intl','mbstring','openssl','pdo_pgsql','pgsql','zip')) {
        if ($modulos -notcontains $extension) {
            if (-not (Test-Path -LiteralPath (Join-Path $base "ext/php_$extension.dll"))) { throw "El PHP seleccionado no contiene la extensión $extension. Instala una distribución completa de PHP." }
            $contenido += "extension=$extension`r`n"
        }
    }
    [IO.File]::WriteAllText($ini, $contenido, (New-Object Text.UTF8Encoding($false)))
    return $ini
}

function Leer-DatoSavp {
    param([string]$Pregunta, [string]$Predeterminado)
    $valor = Read-Host "$Pregunta [$Predeterminado]"
    if ([string]::IsNullOrWhiteSpace($valor)) { return $Predeterminado }
    return $valor.Trim()
}

function Leer-SecretoSavp {
    param([string]$Pregunta)
    $seguro = Read-Host $Pregunta -AsSecureString
    $puntero = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($seguro)
    try { return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($puntero) }
    finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($puntero) }
}

function Obtener-PuertoLibreSavp {
    param([int]$Desde)
    for ($puerto = $Desde; $puerto -lt ($Desde + 100); $puerto++) {
        $oyente = New-Object Net.Sockets.TcpListener([Net.IPAddress]::Loopback, $puerto)
        try { $oyente.Start(); return $puerto } catch [Net.Sockets.SocketException] { } finally { $oyente.Stop() }
    }
    throw 'No hay un puerto local libre disponible.'
}

function Esperar-ServicioSavp {
    param([string]$Url, [System.Diagnostics.Process]$Proceso, [int]$Segundos = 60)
    $limite = [DateTime]::UtcNow.AddSeconds($Segundos)
    do {
        $Proceso.Refresh()
        if ($Proceso.HasExited) { throw 'Un servicio terminó antes de estar disponible. Revisa storage/logs/iniciador.' }
        try {
            $respuesta = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 4
            if ($respuesta.StatusCode -eq 200) { return }
        } catch { }
        Start-Sleep -Milliseconds 500
    } while ([DateTime]::UtcNow -lt $limite)
    throw "El servicio no respondió a tiempo: $Url. Revisa storage/logs/iniciador."
}
