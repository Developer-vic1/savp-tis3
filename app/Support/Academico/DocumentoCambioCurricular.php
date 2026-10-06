<?php

namespace App\Support\Academico;

use Illuminate\Support\Str;

/** Revisa el contenido; las firmas y sellos requieren comprobación por canal institucional. */
class DocumentoCambioCurricular
{
    public const FUENTE = 'https://www.minedu.gob.bo/files/publicaciones/curricula/Curriculo-Final.pdf';

    public static function detectar(string $texto): array
    {
        $campo = static function (string $nombre) use ($texto): string {
            preg_match_all('/^\s*'.$nombre.'\s*:\s*([^\r\n]+)$/imu', $texto, $m);
            $valores = array_unique(array_map('trim', $m[1] ?? []));
            return count($valores) === 1 ? reset($valores) : '';
        };
        return ['nombre'=>$campo('(?:Asignatura|Materia|Especialidad)'), 'sigla'=>mb_strtoupper($campo('Sigla')),
            'horas'=>$campo('Horas acad[eé]micas'), 'gestion'=>$campo('Gesti[oó]n'),
            'referencia'=>$campo('(?:Referencia|Resoluci[oó]n|Carta)'), 'fecha'=>$campo('Fecha'),
            'fundamento'=>$campo('(?:Fundamento|Justificaci[oó]n)')];
    }

    public static function revisar(string $texto, string $director, int $gestion, string $operacion, string $anterior = '', string $dominio = ''): array
    {
        $datos = self::detectar($texto);
        $normal = static fn ($v) => Str::lower(Str::ascii(Str::squish($v)));
        $t = $normal($texto); $nombre = $normal($datos['nombre']);
        $fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha']) ? explode('-', $datos['fecha']) : [];
        $decision = $operacion === 'crear' ? '(?:incorporaci[oó]n|apertura|creaci[oó]n|implementaci[oó]n)' : '(?:modificaci[oó]n|rectificaci[oó]n|actualizaci[oó]n|cambio)';
        $reglas = [
            'Contenido suficiente y fundamento comprensible'=>mb_strlen(trim($texto)) >= 250 && ExpedienteParaleloInstitucional::justificacionComprensible($datos['fundamento']),
            'Nombre de la materia o especialidad expresado sin ambigüedad'=>$nombre !== '' && mb_strlen($nombre) >= 3,
            'Unidad educativa Franz Tamayo N° 3 identificada'=>preg_match('/franz\s+tamayo\s*(?:n(?:ro|umero)?[.°º\s]*|no[.\s]*)?(?:3|iii|tres)\b/u', $t) === 1,
            'Nombre del Director vigente y cargo identificados'=>$director !== '' && str_contains($t,$normal($director)) && preg_match('/\b(?:director|directora)\b/', $t) === 1,
            'Referencia documental identificable'=>preg_match('/^[\p{L}\d.\/-]*\d[\p{L}\d.\/-]*$/u', $datos['referencia']) === 1,
            'Fecha de emisión válida y no futura'=>count($fecha) === 3 && checkdate((int)$fecha[1],(int)$fecha[2],(int)$fecha[0]) && $datos['fecha'] <= now()->toDateString(),
            'Gestión de aplicación coincide'=>(string)$gestion === $datos['gestion'],
            'Autorización expresa del cambio solicitado'=>preg_match('/\b(?:autoriza\w*|aprueba\w*)\s+(?:la\s+|el\s+)?'.$decision.'[^.;\r\n]{0,200}'.preg_quote($datos['nombre'],'/').'/iu', $texto) === 1,
            'Sin rechazo o denegación'=>preg_match('/\b(?:no\s+(?:se\s+)?(?:autoriza\w*|aprueba\w*)|deniega\w*|rechaza\w*)\b/', $t) !== 1,
        ];
        if ($operacion === 'editar') $reglas['Identidad anterior y cambio concreto documentados']=$anterior !== '' && str_contains($t,$normal($anterior)) && preg_match('/(?:nombre|sigla|horas)\s+anterior(?:es)?\s*:/iu',$texto) === 1;
        if ($dominio === 'especialidad') {
            $reglas['El documento autoriza una especialidad, no una asignatura'] = preg_match('/^\s*Especialidad\s*:/imu', $texto) === 1 && preg_match('/^\s*(?:Asignatura|Materia)\s*:/imu', $texto) !== 1;
            $reglas['Denominación técnica diferenciada de las materias'] = (new EspecialidadTecnicaInteligente)->orientacion($datos['nombre'])['reconocida'];
        }
        return ['coherente'=>!in_array(false,$reglas,true),'reglas'=>$reglas,'datos'=>$datos,
            'firma'=>'REQUIERE_COMPROBACION','sello'=>'REQUIERE_COMPROBACION'];
    }
}
