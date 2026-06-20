<?php

namespace App\Services\Reportes;

use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Support\Reportes\ReporteAcademicoInteligente;

class DatosReporteVocacionalService
{
    // Perfiles RIASEC con descripción y carreras relacionadas
    protected array $perfilesRiasec = [
        'R' => [
            'nombre'      => 'Realista',
            'descripcion' => 'Prefiere actividades prácticas, mecánicas y físicas. Trabaja con herramientas, máquinas y objetos.',
            'carreras'    => ['Ingeniería Mecánica', 'Ingeniería Civil', 'Mecatrónica', 'Electricidad Industrial'],
            'fortalezas'  => ['Habilidad manual', 'Trabajo con herramientas', 'Precisión técnica'],
        ],
        'I' => [
            'nombre'      => 'Investigativo',
            'descripcion' => 'Disfruta observar, aprender, investigar y resolver problemas analíticos.',
            'carreras'    => ['Medicina', 'Ciencias Biológicas', 'Ingeniería de Sistemas', 'Investigación Científica'],
            'fortalezas'  => ['Análisis lógico', 'Curiosidad científica', 'Resolución de problemas'],
        ],
        'A' => [
            'nombre'      => 'Artístico',
            'descripcion' => 'Prefiere actividades creativas, artísticas, expresivas y no estructuradas.',
            'carreras'    => ['Diseño Gráfico', 'Arquitectura', 'Comunicación', 'Artes Visuales'],
            'fortalezas'  => ['Creatividad', 'Expresión artística', 'Pensamiento innovador'],
        ],
        'S' => [
            'nombre'      => 'Social',
            'descripcion' => 'Le gusta trabajar con personas, enseñar, ayudar y orientar a otros.',
            'carreras'    => ['Psicología', 'Trabajo Social', 'Educación', 'Enfermería'],
            'fortalezas'  => ['Empatía', 'Comunicación interpersonal', 'Liderazgo colaborativo'],
        ],
        'E' => [
            'nombre'      => 'Emprendedor',
            'descripcion' => 'Disfruta liderar, persuadir, gestionar y asumir roles de responsabilidad.',
            'carreras'    => ['Administración de Empresas', 'Derecho', 'Marketing', 'Emprendimiento'],
            'fortalezas'  => ['Liderazgo', 'Persuasión', 'Toma de decisiones'],
        ],
        'C' => [
            'nombre'      => 'Convencional',
            'descripcion' => 'Prefiere actividades ordenadas, sistemáticas, con datos y procedimientos definidos.',
            'carreras'    => ['Contaduría Pública', 'Economía', 'Administración Financiera', 'Sistemas de Información'],
            'fortalezas'  => ['Organización', 'Atención al detalle', 'Trabajo sistemático'],
        ],
    ];

    // Mapa de especialidades técnicas a perfil RIASEC dominante
    protected array $especialidadAriasec = [
        'sistemas'     => ['I', 'C', 'R'],
        'electrónica'  => ['R', 'I', 'C'],
        'electronica'  => ['R', 'I', 'C'],
        'mecánica'     => ['R', 'I', 'E'],
        'mecanica'     => ['R', 'I', 'E'],
        'contabilidad' => ['C', 'E', 'I'],
        'gastronomía'  => ['A', 'R', 'E'],
        'gastronomia'  => ['A', 'R', 'E'],
        'textil'       => ['A', 'R', 'C'],
        'belleza'      => ['A', 'S', 'E'],
        'carpintería'  => ['R', 'A', 'C'],
        'carpinteria'  => ['R', 'A', 'C'],
    ];

    public function __construct(
        protected ReporteAcademicoInteligente $soporte,
    ) {}

    /**
     * Obtiene datos vocacionales para reporte RIASEC general institucional.
     */
    public function obtenerGeneral(): array
    {
        // Intentar obtener datos reales de especialidades con calificaciones
        $calificaciones = Calificacion::with(['estudiante.especialidad', 'estudiante.persona'])
            ->where('est_cal', 'ACTIVO')
            ->get();

        $hayDatosReales = $calificaciones->count() > 0;

        // Construir resultados RIASEC por especialidad
        $resultadosPorEspecialidad = $calificaciones
            ->filter(fn ($c) => $c->estudiante?->especialidad)
            ->groupBy(fn ($c) => $c->estudiante->especialidad->nom_esp)
            ->map(function ($items, $esp) {
                $perfilRiasec = $this->riasecPorEspecialidad($esp);
                $promedio     = round((float) $items->avg('not_cal'), 2);
                $cantidad     = $items->pluck('cod_est')->unique()->count();

                return [
                    'especialidad'  => $esp,
                    'perfil_riasec' => $perfilRiasec,
                    'perfil_texto'  => implode('', $perfilRiasec),
                    'promedio'      => $promedio,
                    'estudiantes'   => $cantidad,
                    'compatibilidad'=> $this->calcularCompatibilidad($promedio),
                    'fortalezas'    => $this->fortalezasDe($perfilRiasec[0] ?? 'R'),
                    'carreras'      => $this->carrerasDe($perfilRiasec[0] ?? 'R'),
                ];
            })
            ->sortByDesc('estudiantes')
            ->values();

        // Distribución RIASEC global (suma de perfiles)
        $distribucionRiasec = $this->distribucionRiasecGlobal($resultadosPorEspecialidad);

        // Carreras más recomendadas globalmente
        $carrerasRecomendadas = $resultadosPorEspecialidad
            ->flatMap(fn ($r) => $r['carreras'])
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->keys()
            ->toArray();

        return [
            'hay_datos_reales'        => $hayDatosReales,
            'resultados_especialidad' => $resultadosPorEspecialidad,
            'distribucion_riasec'     => $distribucionRiasec,
            'carreras_recomendadas'   => $carrerasRecomendadas,
            'total_estudiantes'       => $calificaciones->pluck('cod_est')->unique()->count(),
            'perfil_institucional'    => $this->perfilInstitucional($distribucionRiasec),
            'interpretacion'          => $this->interpretacionGlobal($distribucionRiasec),
            'perfiles_riasec'         => $this->perfilesRiasec,
        ];
    }

    /**
     * Datos para reporte de compatibilidad de carreras.
     */
    public function obtenerCompatibilidad(): array
    {
        $datos = $this->obtenerGeneral();

        $compatibilidades = collect($datos['resultados_especialidad'])
            ->map(function ($esp) {
                [$area, $carreras] = $this->soporte->orientacionPorEspecialidad($esp['especialidad']);

                return [
                    'especialidad'       => $esp['especialidad'],
                    'perfil_riasec'      => $esp['perfil_texto'],
                    'promedio'           => $esp['promedio'],
                    'carreras'           => array_merge($esp['carreras'], $carreras),
                    'area_profesional'   => $area,
                    'compatibilidad_pct' => $esp['compatibilidad'],
                    'riesgo_academico'   => $this->riesgoAcademico($esp['promedio']),
                    'fortalezas'         => $esp['fortalezas'],
                    'observacion'        => $this->observacionCarrera($esp['especialidad'], $esp['promedio']),
                ];
            })
            ->values();

        return array_merge($datos, [
            'compatibilidades' => $compatibilidades,
        ]);
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
            default         => 40,
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
            . ($this->perfilesRiasec[$tipo]['descripcion'] ?? '')
            . " Se recomienda potenciar las áreas técnico-prácticas y vocacionales relacionadas.";
    }

    protected function riesgoAcademico(float $promedio): string
    {
        return match (true) {
            $promedio >= 70 => 'Bajo',
            $promedio >= 51 => 'Medio',
            $promedio >= 40 => 'Alto',
            default         => 'Crítico',
        };
    }

    protected function observacionCarrera(string $esp, float $promedio): string
    {
        $nivel = $this->riesgoAcademico($promedio);
        return "Especialidad {$esp}: rendimiento {$promedio}/100. Riesgo académico {$nivel}. "
            . ($promedio >= 70
                ? "El estudiante muestra condiciones favorables para continuar en carreras afines."
                : "Se recomienda refuerzo académico y orientación vocacional especializada.");
    }
}
