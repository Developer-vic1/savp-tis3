<?php

namespace App\Support\Comunidad;

use Illuminate\Support\Str;

class DocenteInteligente
{
    /** Coincidencia orientativa con el catálogo; no certifica habilitación profesional. */
    public function correspondencia(string $especialidad, string $materia, bool $tecnica = false): array
    {
        $normalizar = fn ($texto) => Str::lower(Str::ascii(Str::squish($texto)));
        $perfil = $normalizar($especialidad);
        $nombre = $normalizar($materia);
        if ($nombre === 'fisica') {
            // Educación Física no demuestra especialidad en la ciencia Física.
            $perfil = preg_replace('/\beducacion\s+fisica\b/u', '', $perfil);
        }
        $referencias = [$nombre];
        if (!$tecnica) {
            foreach (\App\Support\Academico\AsignaturaInteligente::catalogo() as $item) {
                if ($normalizar($item['nombre']) === $nombre) {
                    $referencias = array_merge($referencias, $item['palabras_clave']);
                    break;
                }
            }
        }
        $coincide = false;
        foreach ($referencias as $referencia) {
            $referencia = $normalizar($referencia);
            if (mb_strlen($referencia) >= 4 && preg_match('/\b'.preg_quote($referencia, '/').'\b/u', $perfil)) {
                $coincide = true;
                break;
            }
        }
        return ['especialidad' => trim($especialidad), 'coincide' => $coincide,
            'requiere_motivo' => !$coincide,
            'mensaje' => $coincide ? 'La especialidad registrada tiene correspondencia orientativa con la materia.'
                : ($perfil === '' ? 'El docente no tiene especialidad registrada; documenta por qué imparte esta clase.'
                    : 'No se identificó correspondencia entre la especialidad «'.$especialidad.'» y «'.$materia.'». Si es un caso excepcional, explica el motivo.')];
    }

    public function analizarEspecialidad(?string $especialidad): array
    {
        $normalizada = Str::of((string) $especialidad)->squish()->lower()->title()->toString();
        $bloqueos = mb_strlen($normalizada) < 3 ? ['La especialidad profesional es incompleta.'] : [];

        return [
            'especialidad' => $normalizada,
            'completitud' => $bloqueos === [] ? 100 : 30,
            'estado_especialidad' => $bloqueos === [] ? 'Registrada' : 'Requiere revisión',
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }
}
