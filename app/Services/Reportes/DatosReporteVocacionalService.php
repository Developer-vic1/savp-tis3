<?php

namespace App\Services\Reportes;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionResultado;
use App\Support\Reportes\ReporteAcademicoInteligente;

class DatosReporteVocacionalService
{
    // Perfiles RIASEC con descripción y carreras relacionadas
    protected array $perfilesRiasec = [
        'R' => [
            'nombre' => 'Realista',
            'descripcion' => 'Prefiere actividades prácticas, mecánicas y físicas. Trabaja con herramientas, máquinas y objetos.',
            'carreras' => ['Ingeniería Mecánica', 'Ingeniería Civil', 'Mecatrónica', 'Electricidad Industrial'],
            'fortalezas' => ['Habilidad manual', 'Trabajo con herramientas', 'Precisión técnica'],
        ],
        'I' => [
            'nombre' => 'Investigativo',
            'descripcion' => 'Disfruta observar, aprender, investigar y resolver problemas analíticos.',
            'carreras' => ['Medicina', 'Ciencias Biológicas', 'Ingeniería de Sistemas', 'Investigación Científica'],
            'fortalezas' => ['Análisis lógico', 'Curiosidad científica', 'Resolución de problemas'],
        ],
        'A' => [
            'nombre' => 'Artístico',
            'descripcion' => 'Prefiere actividades creativas, artísticas, expresivas y no estructuradas.',
            'carreras' => ['Diseño Gráfico', 'Arquitectura', 'Comunicación', 'Artes Visuales'],
            'fortalezas' => ['Creatividad', 'Expresión artística', 'Pensamiento innovador'],
        ],
        'S' => [
            'nombre' => 'Social',
            'descripcion' => 'Le gusta trabajar con personas, enseñar, ayudar y orientar a otros.',
            'carreras' => ['Psicología', 'Trabajo Social', 'Educación', 'Enfermería'],
            'fortalezas' => ['Empatía', 'Comunicación interpersonal', 'Liderazgo colaborativo'],
        ],
        'E' => [
            'nombre' => 'Emprendedor',
            'descripcion' => 'Disfruta liderar, persuadir, gestionar y asumir roles de responsabilidad.',
            'carreras' => ['Administración de Empresas', 'Derecho', 'Marketing', 'Emprendimiento'],
            'fortalezas' => ['Liderazgo', 'Persuasión', 'Toma de decisiones'],
        ],
        'C' => [
            'nombre' => 'Convencional',
            'descripcion' => 'Prefiere actividades ordenadas, sistemáticas, con datos y procedimientos definidos.',
            'carreras' => ['Contaduría Pública', 'Economía', 'Administración Financiera', 'Sistemas de Información'],
            'fortalezas' => ['Organización', 'Atención al detalle', 'Trabajo sistemático'],
        ],
    ];

    // Mapa de especialidades técnicas a perfil RIASEC dominante
    protected array $especialidadAriasec = [
        'sistemas' => ['I', 'C', 'R'],
        'electrónica' => ['R', 'I', 'C'],
        'electronica' => ['R', 'I', 'C'],
        'mecánica' => ['R', 'I', 'E'],
        'mecanica' => ['R', 'I', 'E'],
        'contabilidad' => ['C', 'E', 'I'],
        'gastronomía' => ['A', 'R', 'E'],
        'gastronomia' => ['A', 'R', 'E'],
        'textil' => ['A', 'R', 'C'],
        'belleza' => ['A', 'S', 'E'],
        'carpintería' => ['R', 'A', 'C'],
        'carpinteria' => ['R', 'A', 'C'],
    ];

    public function __construct(
        protected ReporteAcademicoInteligente $soporte,
    ) {}

    /**
     * Obtiene datos vocacionales para reporte RIASEC general institucional.
     */
    public function obtenerGeneral(): array
    {
        // Usa únicamente instrumentos RIASEC registrados; ORAV y notas no son respuestas RIASEC.
        $resultados = OrientacionResultado::with('carreras', 'actividad')
            ->where('estado', 'generado')->where('mod_ors', 'onet-mini-ip-2.0-es')
            ->whereNotNull('rea_ors')->orderByDesc('id')->get()->unique('cod_est');
        $grupos = $resultados->groupBy('perfil_predominante')->map(function ($items, $perfil) {
            $letras = array_values(array_filter(str_split($perfil), fn ($tipo) => isset($this->perfilesRiasec[$tipo])));
            $carreras = $items->flatMap(fn ($resultado) => $resultado->carreras)->pluck('carrera')->filter()->unique()->values()->all();

            return ['especialidad' => $perfil, 'perfil_riasec' => $letras, 'perfil_texto' => $perfil,
                'promedio' => null, 'estudiantes' => $items->count(),
                'compatibilidad' => $items->flatMap(fn ($resultado) => $resultado->carreras)->avg('compatibilidad'),
                'fortalezas' => $items->flatMap(fn ($resultado) => $resultado->carreras)->flatMap(fn ($carrera) => $carrera->fortalezas ?? [])->unique()->values()->all(),
                'carreras' => $carreras];
        })->sortByDesc('estudiantes')->values();
        $distribucion = array_fill_keys(array_keys($this->perfilesRiasec), 0);
        foreach ($resultados as $resultado) {
            $principal = substr($resultado->perfil_predominante ?? '', 0, 1);
            if (array_key_exists($principal, $distribucion)) {
                $distribucion[$principal]++;
            }
        }

        return ['hay_datos_reales' => $resultados->isNotEmpty(), 'resultados_especialidad' => $grupos,
            'distribucion_riasec' => $distribucion,
            'carreras_recomendadas' => $resultados->flatMap(fn ($resultado) => $resultado->carreras)->pluck('carrera')->filter()->countBy()->sortDesc()->take(10)->keys()->all(),
            'total_estudiantes' => $resultados->count(), 'perfil_institucional' => $this->perfilInstitucional($distribucion),
            'interpretacion' => 'Distribución del último resultado RIASEC registrado por estudiante. No se deduce de notas ni de una especialidad y no decide automáticamente una carrera.',
            'perfiles_riasec' => $this->perfilesRiasec];
    }

    /**
     * Datos para reporte de compatibilidad de carreras.
     */
    public function obtenerCompatibilidad(): array
    {
        $datos = $this->obtenerGeneral();

        return $datos + ['compatibilidades' => collect($datos['resultados_especialidad'])->map(fn ($perfil) => [
            'especialidad' => $perfil['perfil_texto'], 'perfil_riasec' => $perfil['perfil_texto'],
            'promedio' => null, 'carreras' => $perfil['carreras'], 'area_profesional' => 'RESULTADOS REGISTRADOS DE ORIENTACIÓN',
            'compatibilidad_pct' => $perfil['compatibilidad'], 'riesgo_academico' => 'No evaluado en este reporte',
            'fortalezas' => $perfil['fortalezas'], 'observacion' => 'Compatibilidades conservadas en las recomendaciones del resultado; no son promedios académicos.',
        ])];
    }

    // ── Métodos auxiliares ────────────────────────────────────────────────────

    protected function riasecPorEspecialidad(string $esp): array
    {
        $texto = mb_strtolower($esp);
        foreach ($this->especialidadAriasec as $keyword => $perfil) {
            if (str_contains($texto, $keyword)) {
                return $perfil;
            }
        }

        return ['R', 'I', 'C'];
    }

    protected function calcularCompatibilidad(float $promedio): int
    {
        return match (true) {
            $promedio >= 90 => 95,
            $promedio >= 70 => 80,
            $promedio >= 51 => 60,
            default => 40,
        };
    }

    protected function fortalezasDe(string $tipo): array
    {
        return $this->perfilesRiasec[$tipo]['fortalezas'] ?? ['Capacidades técnicas', 'Aprendizaje continuo'];
    }

    protected function carrerasDe(string $tipo): array
    {
        return $this->perfilesRiasec[$tipo]['carreras'] ?? ['Área técnica relacionada'];
    }

    protected function distribucionRiasecGlobal($resultados): array
    {
        $conteo = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];
        foreach ($resultados as $r) {
            foreach (str_split($r['perfil_texto']) as $letra) {
                if (isset($conteo[$letra])) {
                    $conteo[$letra] += $r['estudiantes'];
                }
            }
        }
        arsort($conteo);

        return $conteo;
    }

    protected function perfilInstitucional(array $distribucion): string
    {
        $top = array_slice(array_keys($distribucion), 0, 3);

        return implode('', $top);
    }

    protected function interpretacionGlobal(array $distribucion): string
    {
        $top = array_keys(array_slice($distribucion, 0, 1));
        $tipo = $top[0] ?? 'R';
        $nombre = $this->perfilesRiasec[$tipo]['nombre'] ?? 'Técnico';

        return "El perfil institucional predominante es {$nombre} ({$tipo}). "
            .($this->perfilesRiasec[$tipo]['descripcion'] ?? '')
            .' Se recomienda potenciar las áreas técnico-prácticas y vocacionales relacionadas.';
    }

    protected function riesgoAcademico(float $promedio): string
    {
        return match (true) {
            $promedio >= 70 => 'Bajo',
            $promedio >= 51 => 'Medio',
            $promedio >= 40 => 'Alto',
            default => 'Crítico',
        };
    }

    protected function observacionCarrera(string $esp, float $promedio): string
    {
        $nivel = $this->riesgoAcademico($promedio);

        return "Especialidad {$esp}: rendimiento {$promedio}/100. Riesgo académico {$nivel}. "
            .($promedio >= 70
                ? 'El estudiante muestra condiciones favorables para continuar en carreras afines.'
                : 'Se recomienda refuerzo académico y orientación vocacional especializada.');
    }
}
