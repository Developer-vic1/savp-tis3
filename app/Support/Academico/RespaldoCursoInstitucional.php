<?php

namespace App\Support\Academico;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class RespaldoCursoInstitucional
{
    public const FUENTE = 'https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/1_RM_0001_EDUCACIN_REGULAR.pdf';

    public static function gestionMencionada(string $texto,int $anio): bool
    {
        // El año del número de resolución o de emisión no demuestra la gestión autorizada.
        return preg_match('/\bgestion\s+(?:(?:educativa|academica|escolar)\s+)?(?:(?:del|para\s+el)\s+)?(?:ano\s+)?'.preg_quote((string)$anio,'/').'\b/',Str::lower(Str::ascii($texto)))===1;
    }

    public function leer(string $ruta, string $campo = 'respaldoPdf'): array
    {
        $python = config('calendario-estudio.python');
        if ($python === 'python' && is_file(base_path('ai-service/.venv/Scripts/python.exe'))) {
            $python = base_path('ai-service/.venv/Scripts/python.exe');
        }
        $proceso = new Process([$python, base_path('scripts/academico/leer_respaldo_curso.py'), $ruta], base_path());
        $proceso->setTimeout(15)->run();
        $lectura = json_decode($proceso->getOutput(), true);
        if (!$proceso->isSuccessful() || !isset($lectura['texto'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([$campo => $lectura['error'] ?? 'No pudimos leer el respaldo. Conservamos los datos; vuelve a cargar un PDF legible.']);
        }
        return ['texto'=>$lectura['texto'], 'paginas'=>$lectura['paginas'], 'sha256'=>hash_file('sha256', $ruta)];
    }

    public function analizar(array $lectura, string $numero, string $fecha, int $orden, string $operacion, int $anio, string $normaAdicional='', array $grupos=[]): array
    {
        $texto = Str::lower(Str::ascii($lectura['texto']));
        $compacto = preg_replace('/[^a-z0-9]/', '', $texto);
        $referencia = preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($numero)));
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $dia = \Carbon\Carbon::parse($fecha);
        $fechas = [$dia->format('d/m/Y'),$dia->format('d-m-Y'),$dia->format('Y-m-d'),$dia->day.' de '.$meses[$dia->month-1].' de '.$dia->year];
        $ordinales = [1=>'primer',2=>'segundo',3=>'tercer',4=>'cuarto',5=>'quinto',6=>'sexto',7=>'septimo',8=>'octavo',9=>'noveno',10=>'decimo',11=>'undecimo',12=>'duodecimo'];
        $reglas = [
            'PDF con texto legible' => mb_strlen(trim($texto)) >= 150,
            'Autoridad educativa identificada' => preg_match('/ministerio de educacion|direccion (departamental|distrital).*educacion/s', $texto) === 1,
            'Resolución e identificación del respaldo' => preg_match('/resolucion\s+administrativa/', $texto) === 1 && strlen($referencia) >= 3 && str_contains($compacto, $referencia),
            'Parte resolutiva identificada' => preg_match('/resuelve|resolutiv/', $texto) === 1,
            'Sin denegación explícita' => preg_match('/no\s+(?:se\s+)?autoriza|deniega|rechaza\s+la\s+(?:apertura|creacion)/', $texto) !== 1,
            'Fecha de emisión coincide' => collect($fechas)->contains(fn ($f) => str_contains($texto, $f)),
            'Institución identificada' => preg_match('/franz\s+tamayo\s*(?:n(?:ro|umero)?[.°º\s]*|no[.\s]*)?(?:3|iii|tres)\b/', $texto) === 1,
            'Curso y nivel mencionados' => str_contains($texto, 'secundaria') && (preg_match('/\b'.($ordinales[$orden]??'grado-no-expresado').'\w*\b/u',$texto)===1 || preg_match('/\b'.$orden.'\s*(?:ro|do|to|mo|vo|no|°|º|año|ano)/u', $texto) === 1),
            'Gestión indicada' => self::gestionMencionada($texto,$anio),
            'Decisión corresponde al cambio' => preg_match(match ($operacion) {
                'desactivar' => '/cierre|supresion|desactivacion/', 'reactivar' => '/reapertura|reactivacion|habilitacion/',
                'crear' => '/apertura|creacion|habilitacion/', default => '/modificacion|rectificacion|actualizacion/',
            }, $texto) === 1,
        ];
        if($orden>6){
            $norma=preg_replace('/[^a-z0-9]/','',Str::lower(Str::ascii($normaAdicional)));
            $reglas['Norma de ampliación identificada en el respaldo']=strlen($norma)>=5&&str_contains($compacto,$norma);
            $reglas['Fundamento de cambio de estructura educativa']=str_contains($texto,'ministerio de educacion')&&preg_match('/ampliacion\s+(?:del\s+)?(?:nivel|grado|estructura)|reforma\s+(?:curricular|educativa)|modificacion\s+(?:de\s+la\s+)?estructura\s+(?:educativa|curricular)/',$texto)===1;
            $reglas['Grado adicional expresamente autorizado']=preg_match('/(?:autoriza\w*|aprueba\w*|dispone)\b[^.;]{0,250}\b(?:'.($ordinales[$orden]??'grado-no-expresado').'\w*|'.$orden.'\s*(?:mo|vo|no|ro|do|to|°|º|ano))(?=\W|$)/u',$texto)===1;
        }
        if($operacion==='crear'&&$grupos){
            preg_match_all('/\bparalelos?\s*[:«"\x27]?\s*((?:unico|[a-z])(?:\s*(?:,|y|e)\s*(?:unico|[a-z]))*)\b/u',$texto,$listas);
            $letras=collect($listas[1])->flatMap(fn($lista)=>preg_split('/\s*,\s*|\s+(?:y|e)\s+/u',trim($lista)))->all();
            $reglas['Paralelos solicitados identificados']=collect($grupos['paralelos']??[])->isNotEmpty()&&collect($grupos['paralelos'])->every(fn($p)=>in_array(CursoInteligente::normalizar($p),$letras,true));
            $turno=CursoInteligente::normalizar($grupos['turno']??'');
            $reglas['Turno de los grupos indicado']=$turno!==''&&preg_match('/\b(?:turno|jornada)\s+(?:de\s+la\s+)?'.preg_quote($turno,'/').'\b/u',$texto)===1;
        }
        return ['reglas'=>$reglas, 'coherente'=>!in_array(false,$reglas,true), 'paginas'=>$lectura['paginas'], 'sha256'=>$lectura['sha256'],
            'aviso'=>'La lectura revisa contenido y coincidencias. La autenticidad y la autorización deben comprobarse con la autoridad emisora antes de aplicar el cambio.'];
    }
}
