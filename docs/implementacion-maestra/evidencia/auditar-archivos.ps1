$ErrorActionPreference='Stop'
$root='C:/laragon/www/savp-reestructuracion'
Set-Location -LiteralPath $root
$modified=@(git diff --name-only 2>$null)
$files=@($modified,(git ls-files --others --exclude-standard)) | ForEach-Object { $_ } | Where-Object {$_ -ne 'docs/implementacion-maestra/evidencia/cierre-auditoria-archivos.csv'} | Sort-Object -Unique
$result=foreach($file in $files){
    $status='USED'; $note='Entrada/configuración o código referenciado; revisión funcional pendiente'; $refs=@()
    if($file -like 'docs/*'){$status='DOCUMENTATION';$note='Documento o evidencia del checkout actual; no modifica el catálogo fuente'}
    elseif($file -like 'tests/*'){$status='TEST';$note='Prueba/guardia de entorno seguro; ninguna suite autoriza PostgreSQL institucional'}
    elseif($file -eq 'PETER2_REESTRUCTURACION.patch'){$status='REMOVED_NOT_ALLOWED';$note='Patch preexistente conservado; no aplicado ni eliminado'}
    elseif($file -like 'database/migrations/2026_09_30_*'){$status='BLOCKED';$note='Propuesta física para Peter 1, sintaxis validada; NUNCA EJECUTADA'}
    elseif($file -match 'MetaAcademica|AcademicGoal|AcademicPlan|academic-plan|UnidadClase|UnitContentService|SeguimientoAcademico|Services/Kardex|Contracts/Kardex|KardexPolicy|CalendarioEvento|Services/AporteIngenieril/AnalysisService'){$status='BLOCKED';$note='Preparación detrás de flag false/aprobación/contrato; no acredita persistencia o integración científica terminada'}
    elseif($file -match '^app/.*\.php$'){
        $text=Get-Content -LiteralPath $file -Raw
        $symbol=if($text -match '(?:class|interface|trait)\s+(\w+)'){$Matches[1]}else{[IO.Path]::GetFileNameWithoutExtension($file)}
        $tokens=@($symbol)
        if($file -match '^app/Livewire/(.+)\.php$'){
            $tokens+= (($Matches[1] -split '/' | ForEach-Object {($_ -creplace '([a-z0-9])([A-Z])','$1-$2').ToLowerInvariant()}) -join '.')
        }
        $args=@('-l','-F'); foreach($token in $tokens){$args+=@('-e',$token)}
        $refs=@(& rg @args app bootstrap config routes resources 2>$null | Where-Object {($_ -replace '\\','/') -ne $file})
        if(!$refs.Count){$status='REMOVED_NOT_ALLOWED';$note='No hallada referencia literal de producción; conservar y revisar registro/autoload/herencia antes de proponer retiro'}else{$note='Referencias estáticas de símbolo; no equivalen a ejecución de todas sus ramas'}
    }
    elseif($file -match '^resources/views/(.+)\.blade\.php$'){
        $alias=$Matches[1] -replace '/','.'
        $stem=[IO.Path]::GetFileNameWithoutExtension([IO.Path]::GetFileNameWithoutExtension($file))
        $refs=@(rg -l -F -e $alias -e $stem app routes resources 2>$null | Where-Object {($_ -replace '\\','/') -ne $file})
        $note='Vista Blade con referencias por alias/componente/layout; QA visual pendiente'
    }
    [pscustomobject]@{Archivo=$file;Estado=$status;Origen=if($file -in $modified){'TRACKED_MODIFIED'}else{'UNTRACKED'};Referencias=($refs -join ';');Observaciones=$note;SHA256=(Get-FileHash -LiteralPath $file).Hash}
}
$result | Export-Csv -NoTypeInformation -Encoding utf8 docs/implementacion-maestra/evidencia/cierre-auditoria-archivos.csv
$result | Group-Object Estado | Select-Object Name,Count | Format-Table
