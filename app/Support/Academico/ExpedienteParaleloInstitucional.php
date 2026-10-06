<?php

namespace App\Support\Academico;

use Illuminate\Support\Str;

/** Detecta omisiones; no valida firmas ni reemplaza a la autoridad educativa. */
class ExpedienteParaleloInstitucional
{
    /** Extrae referencias explícitas; una omisión o varias coincidencias exige revisión manual. */
    public static function detectar(string $texto): array
    {
        $t = Str::lower(Str::ascii($texto));
        $unico = static function (string $patron) use ($t): string {
            preg_match_all($patron, $t, $coincidencias);
            $valores = array_values(array_unique(array_map('trim', $coincidencias[1] ?? [])));
            return count($valores) === 1 ? $valores[0] : '';
        };
        $numero = rtrim($unico('/resolucion\s+administrativa\s*(?:n(?:ro|umero|o)?[.°º\s]*|#\s*)?([a-z0-9-]*\d[a-z0-9\/.-]*)/'), '.-/');
        $gestion = $unico('/gestion\s*(?:escolar|educativa|autorizada)?\s*[:\-]?\s*(20\d{2})\b/');
        $fecha = $unico('/\b(\d{4}-\d{2}-\d{2}|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}|\d{1,2}\s+de\s+(?:enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+de\s+\d{4})\b/');
        $fechaNormalizada = '';
        if ($fecha !== '') {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $m)) {
                [$anio, $mes, $dia] = [(int)$m[1], (int)$m[2], (int)$m[3]];
            } elseif (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $fecha, $m)) {
                [$dia, $mes, $anio] = [(int)$m[1], (int)$m[2], (int)$m[3]];
            } else {
                preg_match('/^(\d{1,2})\s+de\s+(\w+)\s+de\s+(\d{4})$/', $fecha, $m);
                $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                [$dia, $mes, $anio] = [(int)$m[1], array_search($m[2], $meses, true) + 1, (int)$m[3]];
            }
            if (checkdate($mes, $dia, $anio)) $fechaNormalizada = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
        }
        preg_match_all('/Direcci[oó]n\s+Departamental\s+de\s+Educaci[oó]n(?:\s+(?:de|del)\s+[^\r\n.,;]{2,60})?/iu', $texto, $emisores);
        $autoridades = array_values(array_unique(array_map('trim', $emisores[0] ?? [])));
        return ['numero'=>$numero, 'fecha'=>$fechaNormalizada, 'gestion'=>$gestion,
            'autoridad'=>count($autoridades) === 1 ? $autoridades[0] : '',
            'tipo'=>preg_match('/resolucion\s+administrativa/', $t) ? 'resolucion' : ''];
    }

    public static function justificacionComprensible(string $texto): bool
    {
        preg_match_all('/[\p{L}\p{N}]{2,}/u', Str::lower($texto), $palabras);
        return count($palabras[0]) >= 5 && count(array_unique($palabras[0])) >= 4;
    }

    public static function revisar(string $texto, string $numero, string $paralelo, string $tipo, string $fecha = '', string $gestion = ''): array
    {
        $t = Str::lower(Str::ascii($texto));
        $referencia = preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($numero)));
        $compacto = preg_replace('/[^a-z0-9]/', '', $t);
        $nombre = preg_quote(Str::lower(Str::ascii($paralelo)), '/');
        $dia = $fecha ? \Carbon\Carbon::parse($fecha) : null;
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $fechas = $dia ? [$dia->format('d/m/Y'), $dia->format('j/n/Y'), $dia->format('d-m-Y'), $dia->format('j-n-Y'), $dia->format('Y-m-d'), $dia->day.' de '.$meses[$dia->month-1].' de '.$dia->year] : [];
        $reglas = [
            'Documento legible' => mb_strlen(trim($t)) >= 150,
            'Número de documento coincide' => strlen($referencia) >= 3 && str_contains($compacto, $referencia),
            'Institución identificada' => preg_match('/franz\s+tamayo\s*(?:n(?:ro|umero)?[.°º\s]*|no[.\s]*)?(?:3|iii|tres)\b/', $t) === 1,
            'Paralelo identificado' => preg_match('/paralelo\s*["\x27:«]?\s*'.$nombre.'\b/', $t) === 1,
            'Gestión solicitada coincide' => $gestion !== '' && RespaldoCursoInstitucional::gestionMencionada($t,(int)$gestion),
            'Fecha de emisión coincide' => collect($fechas)->contains(fn ($f) => str_contains($t, $f)),
            'Documento de decisión' => $tipo === 'resolucion'
                ? preg_match('/resolucion\s+administrativa/', $t) === 1 && preg_match('/resuelve|resolutiv/', $t) === 1 && preg_match('/direccion\s+departamental/', $t) === 1
                : preg_match('/acta|informe|rectificacion/', $t) === 1,
            'Sin denegación explícita' => preg_match('/(?:no\s+(?:se\s+)?autoriza|deniega|rechaza\s+la\s+(?:apertura|creacion))/', $t) !== 1,
        ];
        return ['reglas' => $reglas, 'coherente' => !in_array(false, $reglas, true)];
    }
}
