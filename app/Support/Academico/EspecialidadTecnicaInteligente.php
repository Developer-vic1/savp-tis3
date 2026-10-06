<?php

namespace App\Support\Academico;

use App\Models\Oficial\Academico\EspecialidadTecnica;
use App\Support\CatalogoInteligenteBase;

class EspecialidadTecnicaInteligente extends CatalogoInteligenteBase
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = $this->normalizarTexto($datos['nom_esp'] ?? '');
        $descripcion = $this->normalizarDescripcion($datos['des_esp'] ?? '');
        $duplicidad = $this->analizarDuplicidad($nombre, EspecialidadTecnica::all(), $ignorarCodigo);
        $sugerencias = $this->orientacion($nombre);

        if ($descripcion === '') {
            $descripcion = $sugerencias['descripcion'];
        }

        $bloqueos = [];
        if (($sugerencias['tipo'] ?? '') === 'asignatura') {
            $bloqueos[] = 'Este nombre corresponde a una materia curricular, no a una especialidad técnica. Gestiona las materias en Asignaturas.';
        }
        if (mb_strlen($nombre) < 3 || !($sugerencias['reconocida'] ?? false)) {
            $bloqueos[] = 'No se confirmó una especialidad técnica del catálogo revisado. Revisa su denominación y el plan autorizado; no se acepta por similitud de palabras.';
        }
        if ($duplicidad['exacto'] || $duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe una especialidad igual o críticamente similar.';
        }

        return [
            'datos' => ['nom_esp' => $nombre, 'des_esp' => $descripcion, 'est_esp' => $datos['est_esp'] ?? 'ACTIVO'],
            'duplicidad' => $duplicidad,
            'sugerencias' => $sugerencias,
            'completitud' => $this->completitud(compact('nombre', 'descripcion'), ['nombre', 'descripcion']),
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }

    public function orientacion(string $nombre): array
    {
        $texto = $this->canonico($nombre);
        $mapa = [
            'Sistemas Informáticos'=>['sistemas informaticos','informatica'],
            'Contabilidad'=>['contabilidad'],
            'Electrónica'=>['electronica'],
            'Mecánica Industrial'=>['mecanica industrial'],
            'Mecánica Automotriz'=>['mecanica automotriz'],
            'Gastronomía'=>['gastronomia'],
            'Textiles y Confección'=>['textiles y confeccion','textil y confeccion'],
            'Belleza Integral'=>['belleza integral'],
            'Carpintería en Madera y Metal'=>['carpinteria en madera y metal'],
        ];
        $familias = [
            'Sistemas Informáticos'=>'Tecnologías digitales', 'Contabilidad'=>'Gestión administrativa',
            'Electrónica'=>'Tecnología y mantenimiento', 'Mecánica Industrial'=>'Tecnología y mantenimiento',
            'Mecánica Automotriz'=>'Tecnología y mantenimiento', 'Gastronomía'=>'Servicios y alimentación',
            'Textiles y Confección'=>'Producción y transformación', 'Belleza Integral'=>'Servicios personales',
            'Carpintería en Madera y Metal'=>'Producción y transformación',
        ];
        foreach ($mapa as $canonico=>$alias) {
            if (in_array($texto,$alias,true)) {
                return ['reconocida'=>true,'tipo'=>'especialidad','familia'=>$familias[$canonico],'nombre'=>$canonico,'area'=>'Ciencia, Tecnología y Producción',
                    'riasec'=>'No se infiere un perfil personal desde el nombre de una especialidad.',
                    'carreras'=>[], 'descripcion'=>'Oferta técnica: '.$canonico.'. Los contenidos específicos deben corresponder al plan aprobado.',
                    'fuente'=>'Denominaciones revisadas del catálogo SAVP. La identificación no sustituye la autorización documental.'];
            }
        }
        $materias = ['matematica','matematicas','fisica','quimica','biologia','ciencias biologicas','ciencias sociales','comunicacion y lenguaje','lenguaje','ingles','lengua extranjera - ingles','educacion musical','musica','educacion fisica','artes plasticas y visuales','cosmovisiones y filosofia','psicologia','valores y espiritualidades','tecnica tecnologia general','tecnica tecnologia especializada','programacion'];
        $esMateria = in_array($texto, $materias, true) || preg_match('/^(?:asignatura|materia)\b/u', $texto) === 1;
        return ['reconocida'=>false,'tipo'=>$esMateria?'asignatura':'no_identificada','familia'=>'Revisión requerida','area'=>'Pendiente de revisión curricular','riasec'=>'No determinado',
            'carreras'=>[], 'descripcion'=>'', 'fuente'=>'No hay una coincidencia precisa en el catálogo revisado.'];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_esp;
    }
}
