param([ValidateRange(1024,65535)][int]$Puerto = 8000)
$ErrorActionPreference = 'Stop'
$raizSavp = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$routerSavp = (Join-Path $raizSavp 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php').Replace('\','/')
$escuchasSavp = @(Get-NetTCPConnection -LocalPort $Puerto -State Listen -ErrorAction SilentlyContinue)
if (!$escuchasSavp.Count) { Write-Host "No hay un servidor escuchando en $Puerto."; return }
foreach ($escuchaSavp in $escuchasSavp) {
    $procesoSavp = Get-CimInstance Win32_Process -Filter "ProcessId=$($escuchaSavp.OwningProcess)"
    $comandoSavp = if ($procesoSavp) { $procesoSavp.CommandLine.Replace('\','/') } else { '' }
    if (!$procesoSavp -or $procesoSavp.Name -ne 'php.exe' -or !$comandoSavp.Contains($routerSavp) -or !$comandoSavp.Contains("127.0.0.1:$Puerto") -or !$comandoSavp.Contains('opcache.enable_cli=1')) {
        throw 'El puerto no corresponde al servidor optimizado de este proyecto. No se detuvo ningún proceso ajeno.'
    }
}
foreach ($escuchaSavp in $escuchasSavp) { Stop-Process -Id $escuchaSavp.OwningProcess -Force; Wait-Process -Id $escuchaSavp.OwningProcess -Timeout 5 -ErrorAction SilentlyContinue }
Write-Host "Servidor optimizado de SAVP detenido en $Puerto."
