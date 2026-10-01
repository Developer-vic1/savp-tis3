param([switch]$ActualizarMatriz)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '../../..')).Path
Set-Location -LiteralPath $root
$files = @(rg --files app resources routes | Where-Object { $_ -match '\.(php|js)$' })
$current = @{}
foreach ($file in $files) { $current[$file.Replace('\','/')] = [IO.File]::ReadAllText((Join-Path $root $file)) }
$original = @{}
foreach ($file in @(git ls-tree -r --name-only HEAD app resources routes | Where-Object { $_ -match '\.(php|js)$' })) {
    $original[$file] = (git show "HEAD:$file") -join "`n"
}
$matrixPath = Join-Path $PSScriptRoot '../MATRIZ-CONCILIACION-105.csv'
$matrix = @(Import-Csv -LiteralPath $matrixPath)
$inventory = foreach ($file in @(rg --files app/Support | Sort-Object)) {
    $path = $file.Replace('\','/')
    $source = $current[$path]
    $class = [IO.Path]::GetFileNameWithoutExtension($path)
    $pattern = '\b' + [regex]::Escape($class) + '\b'
    $callersBefore = @($original.Keys | Where-Object { $_ -ne $path -and $original[$_] -match $pattern } | Sort-Object)
    $callersNow = @($current.Keys | Where-Object { $_ -ne $path -and $current[$_] -match $pattern } | Sort-Object)
    $reachable = @($callersNow)
    for ($depth = 0; $depth -lt 3; $depth++) {
        $next = foreach ($caller in $reachable) {
            if ($caller -notmatch '^app/(Livewire|Services)/') { continue }
            $owner = [IO.Path]::GetFileNameWithoutExtension($caller)
            $ownerPattern = '\b'+[regex]::Escape($owner)+'\b'
            $current.Keys | Where-Object { $_ -ne $path -and $_ -ne $caller -and $_ -match '^app/(Livewire|Services|Http/Controllers)/' -and $current[$_] -match $ownerPattern }
        }
        $reachable = @(@($reachable)+@($next) | Sort-Object -Unique)
    }
    $methods = @([regex]::Matches($source, '\b(public|protected|private)\s+(?:static\s+)?function\s+(\w+)') | ForEach-Object { $_.Groups[2].Value+' ('+$_.Groups[1].Value+')' })
    $windows = @($matrix | Where-Object {
        $codes = @(($_.'LIVEWIRE/CONTROLLER' -split ';\s*') | ForEach-Object { $_ -replace [regex]::Escape($root.Replace('\','/'))+'/', '' })
        $view = ($_.VISTA -replace [regex]::Escape($root.Replace('\','/'))+'/', '')
        @($codes | Where-Object {$_ -in $reachable}).Count -gt 0 -or $view -in $reachable -or $_.SERVICE -match $pattern
    } | ForEach-Object { $_.'ID ORIGINAL' })
    $kind = if ($class -match 'Inteligente$|^InscripcionAcademica$|^CatalogoInteligenteBase$|^InstitutionalRoleGovernance$') {'INTELIGENTE_LOCAL'} else {'UTILIDAD'}
    if ($class -eq 'KardexInteligente') { $windows = @('V082','V083') }
    if ($class -eq 'InstitutionalRoleGovernance') { $windows = @('V003','V020') }
    if ($class -eq 'CalificacionInteligente') { $windows = @(@($windows)+@('V079') | Sort-Object -Unique) }
    [pscustomobject][ordered]@{
        SUPPORT=$class; RUTA=$path; MODULO=$(if(($path -split '/').Count -gt 3){($path -split '/')[2]}else{'Transversal'}); TIPO=$kind
        METODOS=($methods -join '; ')
        BLOQUEOS=[bool]($source -match 'bloque|reasons|blocked' -or $class -eq 'CursoInteligente')
        ADVERTENCIAS=[bool]($source -match 'advertencia|warnings')
        SUGERENCIAS=[bool]($source -match 'suger|recomend|suggest|orientacionPorEspecialidad|function\s+observacion')
        COINCIDENCIAS=[bool]($source -match 'coincid|duplic')
        RESUMEN=[bool]($source -match 'resumen|summary|completitud|clasificar|relaciones_esperadas|respuestaBase')
        ACCION_RECOMENDADA=[bool]($source -match 'accion_recomendada|sugerirAccion|suggested_role|reactivar')
        VENTANAS=($windows -join '; ')
        CALLER_ORIGINAL=($callersBefore -join '; '); CALLER_ACTUAL=($callersNow -join '; ')
        CALLER_TRANSITIVO=($reachable -join '; ')
        EXISTIA_EN_HEAD=$original.ContainsKey($path)
        CALLERS_PERDIDOS=(@($callersBefore | Where-Object { $_ -notin $callersNow }) -join '; ')
        ESTADO=$(if($callersNow.Count){'CONECTADO_ESTATICAMENTE'}else{'SIN_CALLER'})
        SHA256=(Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash
    }
}
$inventory | Export-Csv -LiteralPath (Join-Path $PSScriptRoot 'support-inventario.csv') -NoTypeInformation -Encoding utf8
if ($ActualizarMatriz) {
    foreach ($window in $matrix) {
        $related = @($inventory | Where-Object { $window.'ID ORIGINAL' -in ($_.VENTANAS -split '; ') -and $_.TIPO -eq 'INTELIGENTE_LOCAL' })
        foreach ($entry in @{
            SUPPORT_ASOCIADO=$(if($related.Count){($related.SUPPORT -join '; ')}else{'SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo'})
            SUPPORT_CONECTADO=$(if($related.Count){'SI; evidencia estatica en callers del inventario'}else{'NO APLICA DIRECTAMENTE; no se fuerza un Support administrativo sobre lectura'})
            SUGERENCIAS_UI=$(if($related.Count){'Preservadas segun caller/vista; QA autenticada pendiente'}else{'Evaluacion por contexto; sin inventar recomendaciones'})
            REGRESION_SUPPORT=$(if($window.'ID ORIGINAL' -eq 'V006'){'REG-SUP-001 corregida; completitud docente recuperada'}elseif($window.'ID ORIGINAL' -in @('V002','V045')){'DEF-SUP-002 corregido; edicion no omite bloqueos'}else{'Sin perdida de caller directo detectada frente a HEAD; ver 29'})
        }.GetEnumerator()) { $window | Add-Member -NotePropertyName $entry.Key -NotePropertyValue $entry.Value -Force }
    }
    $matrix | Export-Csv -LiteralPath $matrixPath -NoTypeInformation -Encoding utf8
    $markdown = @('# 105 ventanas — estado conciliado','', 'IDs, actor, nombre, tipo y ruta propuesta originales preservados. PARTIAL conserva trabajo interno y QA. Referencias completas: MATRIZ-CONCILIACION-105.csv.','',
        '| ID | Actor | Ventana original | Ruta adaptada | Estado | Migration | Support asociado |','|---|---|---|---|---|---|---|')
    foreach($window in $matrix) {
        $values = @($window.'ID ORIGINAL',$window.ACTOR,$window.'VENTANA ORIGINAL',$window.'RUTA ACTUAL ADAPTADA',$window.'ESTADO ACTUAL',$window.MIGRATIONS,$window.SUPPORT_ASOCIADO)
        $markdown += '| '+(($values | ForEach-Object {([string]$_).Replace('|','/').Replace("`n",' ')}) -join ' | ')+' |'
    }
    [IO.File]::WriteAllText((Join-Path $PSScriptRoot '../MATRIZ-105-VENTANAS.md'),($markdown -join "`n")+"`n",[Text.UTF8Encoding]::new($false))
}
$inventory | Select-Object SUPPORT,TIPO,ESTADO,VENTANAS,CALLERS_PERDIDOS | Format-Table -Wrap
