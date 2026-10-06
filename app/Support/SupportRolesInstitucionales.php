<?php
namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SupportRolesInstitucionales
{
    public const MOTIVOS = [
        'solicitud'=>['FUNCION_COMPLEMENTARIA'=>'Incorporar una función complementaria al trabajo institucional.','PLAN_INSTITUCIONAL'=>'Atender una actividad prevista en el plan institucional.','CAMBIO_NORMATIVO'=>'Adecuar responsabilidades a una disposición institucional vigente.','OTRO'=>'Otro motivo'],
        'cambio'=>['ADECUACION'=>'Adecuar permisos a las responsabilidades aprobadas del rol.','CORRECCION'=>'Corregir una asignación de permisos registrada por error.','MINIMO_ACCESO'=>'Reducir permisos a las tareas necesarias para el rol.','OTRO'=>'Otro motivo'],
        'personal'=>['ADECUACION'=>'Asignar tareas a las responsabilidades aprobadas de esta persona.','CORRECCION'=>'Corregir un permiso personal registrado por error.','MINIMO_ACCESO'=>'Conservar únicamente las tareas personales que necesita esta cuenta.','OTRO'=>'Otro motivo'],
        'acceso'=>['APOYO_TEMPORAL'=>'Apoyo temporal para una actividad institucional autorizada.','SUPLENCIA'=>'Cubrir temporalmente responsabilidades durante una suplencia autorizada.','PROYECTO'=>'Participar en un proyecto institucional con vigencia definida.','CAPACITACION'=>'Realizar una capacitación institucional con acceso limitado.','OTRO'=>'Otro motivo'],
        'revocacion'=>['FIN_ACTIVIDAD'=>'La actividad autorizada concluyó antes de la fecha prevista.','CAMBIO_RESPONSABILIDAD'=>'Las responsabilidades autorizadas cambiaron y requieren revisar el acceso.','CORRECCION'=>'Corregir una autorización de acceso registrada por error.','OTRO'=>'Otro motivo'],
    ];
    public const RESPONSABILIDADES = [
        'REPORTES'=>'Preparar y organizar reportes e informes institucionales.',
        'PLANIFICACION'=>'Apoyar la planificación de cursos y asignaturas institucionales.',
        'RECURSOS'=>'Organizar materiales y recursos del aula virtual.',
        'ORIENTACION'=>'Apoyar la orientación vocacional y sus actividades autorizadas.',
        'SEGUIMIENTO'=>'Consultar asistencia y preparar reportes de seguimiento.',
        'EXPEDIENTES'=>'Apoyar la actualización de personas y expedientes institucionales.',
        'INTEGRACIONES'=>'Revisar integraciones y preparar informes técnicos autorizados.',
    ];
    public const AMBITOS = ['Reportes institucionales','Organización académica','Aula virtual','Orientación vocacional','Seguimiento académico','Expedientes institucionales','Integraciones institucionales'];

    public static function errorTexto(string $valor,int $minimo=20,bool $nombre=false): ?string
    {
        $valor=trim(preg_replace('/\s+/u',' ',$valor));
        if(mb_strlen($valor)<$minimo)return $nombre?'Escribe un nombre claro de al menos 4 letras.':'Explica el motivo con al menos 20 caracteres y cuatro palabras.';
        if($nombre&&!preg_match('/^[\pL\pM ]{4,80}$/u',$valor))return 'Utiliza solo letras y espacios para el nombre del rol.';
        $palabras=preg_split('/\s+/u',Str::lower(Str::ascii($valor)));
        if(!$nombre&&count(array_unique($palabras))<4)return 'Usa al menos cuatro palabras diferentes para explicar la necesidad.';
        foreach($palabras as $palabra){
            $letras=preg_replace('/[^a-z]/','',$palabra);$n=strlen($letras);
            if($n>=6&&(preg_match('/(.)\1{3,}/',$letras)||preg_match('/[bcdfghjklmnpqrstvwxz]{6,}/',$letras)||$n>24))return 'El texto parece una secuencia de letras. Escribe palabras completas y comprensibles.';
            if($n>=8){$v=preg_match_all('/[aeiou]/',$letras);if($v/$n<($n>=12?.2:.15)||$v/$n>.85)return 'Revisa el texto: no parece una palabra comprensible.';}
        }
        if(preg_match('/<[^>]*>|https?:\/\/|[\x00-\x08\x0b\x0c\x0e-\x1f]/iu',$valor))return 'Escribe texto sencillo, sin enlaces ni etiquetas.';
        return null;
    }

    public static function comprobarTexto(string $valor,string $campo,int $minimo=20,bool $nombre=false):void
    {
        if($error=self::errorTexto($valor,$minimo,$nombre))throw ValidationException::withMessages([$campo=>$error]);
    }

    public static function motivo(string $contexto,string $tipo,string $detalle,string $campo='motivo'):string
    {
        if(!isset(self::MOTIVOS[$contexto][$tipo]))throw ValidationException::withMessages([$campo=>'Selecciona un motivo del catálogo institucional.']);
        if($tipo==='OTRO'){self::comprobarTexto($detalle,$campo);return trim($detalle);}
        return self::MOTIVOS[$contexto][$tipo];
    }

    public static function funciones(mixed $tareas):string
    {
        if(!is_array($tareas)||!$tareas||count($tareas)>7||array_filter($tareas,fn($t)=>!is_string($t)))throw ValidationException::withMessages(['responsabilidades'=>'Selecciona responsabilidades válidas del catálogo.']);
        if(count($tareas)!==count(array_unique($tareas))||array_diff($tareas,array_keys(self::RESPONSABILIDADES)))throw ValidationException::withMessages(['responsabilidades'=>'Selecciona responsabilidades válidas del catálogo.']);
        return implode(' ',array_map(fn($t)=>self::RESPONSABILIDADES[$t],$tareas));
    }
}
