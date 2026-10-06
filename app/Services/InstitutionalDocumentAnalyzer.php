<?php

namespace App\Services;


class InstitutionalDocumentAnalyzer
{
    public function leerAutorizacion(string $ruta,string $nombre,string $director,int $anio): array
    {
        $lectura=app(\App\Support\Academico\RespaldoCursoInstitucional::class)->leer($ruta,'document');
        $normal=fn($v)=>preg_replace('/\s+/u',' ',\Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($v)));
        $texto=$normal($lectura['texto']);$errores=[];
        foreach(['rol propuesto'=>$nombre,'director vigente'=>$director] as $dato=>$valor){
            if(!$valor||!str_contains($texto,$normal($valor)))$errores[]='No encontramos en el texto el '.$dato.' de la solicitud.';
        }
        if(!\App\Support\Academico\RespaldoCursoInstitucional::gestionMencionada($texto,$anio))$errores[]='La carta debe identificar la gestión autorizada.';
        if(!preg_match('/\b(?:director|directora|direccion)\b/u',$texto)||!preg_match('/\b(?:autoriza|autorizo|autorizamos|aprueba|aprobamos)\b/u',$texto))$errores[]='La carta debe indicar la autoridad y una autorización expresa.';
        if(preg_match('/\b(?:no\s+(?:se\s+)?(?:autoriza\w*|autorizo|aprueba\w*|aprobamos)|rechazad[ao]|denegad[ao])\b/u',$texto))$errores[]='El texto contiene una denegación.';
        return ['coherente'=>!$errores,'errores'=>$errores,'sha256'=>$lectura['sha256'],'paginas'=>$lectura['paginas'],
            'advertencias'=>['Un segundo administrador debe comprobar personalmente la firma y la autenticidad por el canal institucional.']];
    }
    public function analyze(object $request): array
    {
        // Contrato reservado para el aporte ingenieril. No se inventa evidencia si falta el analizador.
        return ['version' => '1', 'status' => 'REQUIERE_REVISION_MANUAL', 'document_readable' => null,
            'director_name_detected' => null, 'director_role_detected' => null,
            'requested_role_detected' => null, 'authorization_language_detected' => null,
            'signature_detected' => null, 'seal_detected' => null, 'document_date' => null,
            'warnings' => ['Análisis automático no disponible. Se requiere revisión documental por otro administrador.']];
    }
}
