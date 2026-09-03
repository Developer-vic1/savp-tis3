<?php

namespace App\Support\AulaVirtual;

use App\Support\Core\SoporteInteligenteBase;

class OrientacionVocacionalInteligente extends SoporteInteligenteBase
{
    public const DIMENSIONES_RIASEC = [
        'R' => [
            'nombre' => 'Realista',
            'descripcion' => 'Afinidad con actividades prácticas, técnicas, manipulación de herramientas, tecnología y naturaleza.',
            'sugerencia_bth' => 'Mecánica, Electricidad, Agropecuaria, Construcción Civil, Sistemas Informáticos.',
        ],
        'I' => [
            'nombre' => 'Investigador',
            'descripcion' => 'Afinidad con el análisis lógico, indagación científica, resolución de problemas y ciencias exactas.',
            'sugerencia_bth' => 'Sistemas Informáticos, Química Industrial, Electromecánica, Salud Comunitaria.',
        ],
        'A' => [
            'nombre' => 'Artístico',
            'descripcion' => 'Afinidad con la creatividad, expresión visual, diseño, comunicación y pensamiento divergente.',
            'sugerencia_bth' => 'Diseño Gráfico, Comunicación, Artes Plásticas, Gastronomía, Textil y Confección.',
        ],
        'S' => [
            'nombre' => 'Social',
            'descripcion' => 'Afinidad con la interacción humana, enseñanza, trabajo comunitario, liderazgo y orientación a personas.',
            'sugerencia_bth' => 'Salud Comunitaria, Trabajo Social, Educación, Gestión Social.',
        ],
        'E' => [
            'nombre' => 'Emprendedor',
            'descripcion' => 'Afinidad con la iniciativa comercial, gestión de proyectos, negociación, organización y liderazgo de equipos.',
            'sugerencia_bth' => 'Contabilidad, Administración de Empresas, Comercio, Marketing.',
        ],
        'C' => [
            'nombre' => 'Convencional',
            'descripcion' => 'Afinidad con la estructuración de datos, gestión documental, procesos organizados y normativa contable.',
            'sugerencia_bth' => 'Contabilidad, Secretariado Ejecutivo, Gestión Documental, Administración.',
        ],
    ];

    /**
     * Interpreta las puntuaciones RIASEC de forma estrictamente orientativa y pedagógica.
     */
    public function interpretar(array $puntuaciones): array
    {
        $hallazgos = [];
        $datosCalculados = [];
        $sugerencias = [];
        $fuentes = ['Modelo Tipológico de Holland (RIASEC)', 'Currículo de Bachillerato Técnico Humanístico (BTH)'];

        arsort($puntuaciones);
        $perfilPredominante = array_key_first($puntuaciones) ?? 'R';
        $dimensionInfo = self::DIMENSIONES_RIASEC[$perfilPredominante] ?? self::DIMENSIONES_RIASEC['R'];

        $mensajeAfinidad = "Se observa afinidad orientativa con el perfil {$dimensionInfo['nombre']} ({$dimensionInfo['descripcion']}).";
        $sugerencias[] = $mensajeAfinidad;
        $sugerencias[] = "Áreas curriculares BTH sugeridas para exploración: {$dimensionInfo['sugerencia_bth']}.";

        $this->registrarHallazgo(
            $hallazgos,
            'AV_ORIENTACION_AFINIDAD',
            self::TIPO_PEDAGOGICA,
            self::COMP_SUGERENCIA,
            $mensajeAfinidad,
            self::RIESGO_BAJO,
            ['perfil' => $perfilPredominante, 'puntuaciones' => $puntuaciones]
        );

        $datosCalculados['perfil_predominante'] = $perfilPredominante;
        $datosCalculados['nombre_perfil'] = $dimensionInfo['nombre'];
        $datosCalculados['puntuaciones_ordenadas'] = $puntuaciones;
        $datosCalculados['mensaje_orientativo'] = 'Este análisis es de carácter estrictamente consultivo y formativo, diseñado para acompañar la toma de decisiones del estudiante y su entorno vocacional.';

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: self::ESTADO_OK,
            nivelRiesgo: self::RIESGO_BAJO,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            fuentesRegla: $fuentes
        );
    }
}
