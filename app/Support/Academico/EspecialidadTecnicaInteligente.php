<?php

namespace App\Support\Academico;

use App\Models\EspecialidadTecnica;
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
        if (mb_strlen($nombre) < 3) {
            $bloqueos[] = 'El nombre debe identificar claramente la especialidad técnica.';
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
            'sistemas' => ['Ciencia, Tecnología y Producción', 'Investigativo / Realista', ['Ingeniería de Sistemas', 'Informática', 'Ciencia de Datos', 'Telecomunicaciones']],
            'electron' => ['Ciencia, Tecnología y Producción', 'Realista / Investigativo', ['Ingeniería Electrónica', 'Telecomunicaciones', 'Mecatrónica']],
            'contab' => ['Ciencia, Tecnología y Producción', 'Convencional / Emprendedor', ['Contaduría Pública', 'Administración de Empresas', 'Economía']],
            'gastronom' => ['Comunidad y Sociedad', 'Artístico / Emprendedor', ['Gastronomía', 'Turismo', 'Administración de Servicios']],
            'mecan' => ['Ciencia, Tecnología y Producción', 'Realista / Investigativo', ['Ingeniería Mecánica', 'Ingeniería Industrial', 'Mecatrónica']],
            'textil' => ['Ciencia, Tecnología y Producción', 'Artístico / Realista', ['Diseño Textil', 'Diseño de Modas', 'Ingeniería Industrial']],
        ];

        foreach ($mapa as $clave => [$area, $riasec, $carreras]) {
            if (str_contains($texto, $clave)) {
                return [
                    'area' => $area,
                    'riasec' => $riasec,
                    'carreras' => $carreras,
                    'descripcion' => "Especialidad técnica orientada al desarrollo de competencias aplicadas en {$nombre}, vinculada con la formación BTH y el proyecto de vida estudiantil.",
                ];
            }
        }

        return [
            'area' => 'Ciencia, Tecnología y Producción',
            'riasec' => 'Por determinar con orientación vocacional',
            'carreras' => ['Área técnica relacionada', 'Emprendimiento productivo'],
            'descripcion' => "Especialidad técnica orientada al desarrollo de competencias aplicadas en {$nombre}.",
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_esp;
    }
}
