param([switch]$Consultar)
$ErrorActionPreference = 'Stop'
$raizFuentes = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$artisanFuentes = Join-Path $raizFuentes 'artisan'
if (-not (Test-Path -LiteralPath $artisanFuentes -PathType Leaf)) { throw 'No se encontró el proyecto SAVP.' }
$phpFuentes = (Get-Command php -ErrorAction Stop).Source
$nombreTareaFuentes = 'SAVP-Conocimiento-Actualizaciones'
$tareaFuentes = Get-ScheduledTask -TaskName $nombreTareaFuentes -ErrorAction SilentlyContinue
if ($Consultar) {
    if ($tareaFuentes) { $tareaFuentes | Select-Object TaskName, State; Get-ScheduledTaskInfo -TaskName $nombreTareaFuentes | Select-Object LastRunTime, NextRunTime, LastTaskResult }
    else { Write-Output 'La tarea de revisión no está registrada en este ordenador.' }
    return
}
$argumentosFuentes = '"' + $artisanFuentes + '" conocimiento:revisar-actualizaciones --no-interaction'
if ($tareaFuentes) {
    if (@($tareaFuentes.Actions).Count -ne 1 -or $tareaFuentes.Actions[0].Arguments -ne $argumentosFuentes -or $tareaFuentes.Actions[0].Execute -ne $phpFuentes) {
        throw 'Existe una tarea con el mismo nombre y otra acción. No se modificó.'
    }
    Write-Output 'La revisión del proyecto ya está programada.'
    return
}
$accionFuentes = New-ScheduledTaskAction -Execute $phpFuentes -Argument $argumentosFuentes -WorkingDirectory $raizFuentes
$disparadorFuentes = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Hours 1)
$usuarioFuentes = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$principalFuentes = New-ScheduledTaskPrincipal -UserId $usuarioFuentes -LogonType Interactive -RunLevel Limited
$ajustesFuentes = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 4)
Register-ScheduledTask -TaskName $nombreTareaFuentes -Action $accionFuentes -Trigger $disparadorFuentes -Principal $principalFuentes -Settings $ajustesFuentes -Description 'Revisa fuentes universitarias programadas de SAVP y avisa de cambios sin publicarlos.' | Select-Object TaskName, State
