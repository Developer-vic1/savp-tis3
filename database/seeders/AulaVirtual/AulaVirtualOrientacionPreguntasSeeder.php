<?php

namespace Database\Seeders\AulaVirtual;

use App\Models\AulaVirtual\OrientacionPregunta;
use Illuminate\Database\Seeder;

class AulaVirtualOrientacionPreguntasSeeder extends Seeder
{
    public function run(): void
    {
        $preguntas = [
            ['tecnico_practico', 'Me interesa construir, reparar o ensamblar objetos utilizando herramientas, equipos o dispositivos tecnológicos.'],
            ['tecnico_practico', 'Disfruto realizar actividades prácticas donde pueda aplicar conocimientos técnicos en situaciones reales.'],
            ['tecnico_practico', 'Me llama la atención trabajar con computadoras, circuitos, máquinas, sensores o sistemas tecnológicos.'],
            ['tecnico_practico', 'Prefiero aprender haciendo prácticas, experimentos o proyectos antes que solo leyendo teoría.'],
            ['tecnico_practico', 'Me interesa resolver problemas técnicos relacionados con instalaciones, mantenimiento, programación o dispositivos electrónicos.'],
            ['analitico_cientifico', 'Me gusta analizar información para encontrar causas, patrones o posibles soluciones.'],
            ['analitico_cientifico', 'Disfruto resolver problemas de lógica, matemática, física o razonamiento estructurado.'],
            ['analitico_cientifico', 'Me interesa investigar cómo funcionan las cosas y comprobar resultados con datos.'],
            ['analitico_cientifico', 'Me siento cómodo interpretando gráficos, tablas, resultados académicos o información numérica.'],
            ['analitico_cientifico', 'Me atraen las carreras donde se requiere precisión, análisis, investigación y pensamiento crítico.'],
            ['creativo_expresivo', 'Me gusta crear ideas, diseños, presentaciones, contenidos visuales o propuestas originales.'],
            ['creativo_expresivo', 'Disfruto expresar mis ideas mediante dibujos, textos, exposiciones, música, diseño o recursos digitales.'],
            ['creativo_expresivo', 'Me interesa buscar soluciones diferentes o innovadoras cuando enfrento un problema.'],
            ['creativo_expresivo', 'Prefiero actividades donde pueda imaginar, diseñar o transformar algo de manera creativa.'],
            ['creativo_expresivo', 'Me atraen las carreras relacionadas con comunicación, diseño, producción, arte, innovación o creación de contenidos.'],
            ['social_comunitario', 'Me interesa ayudar a otras personas a resolver dudas, aprender o superar dificultades.'],
            ['social_comunitario', 'Disfruto trabajar con grupos, escuchar opiniones y colaborar con mis compañeros.'],
            ['social_comunitario', 'Me gustaría participar en actividades que mejoren la vida de una comunidad o grupo de personas.'],
            ['social_comunitario', 'Me siento cómodo explicando temas, orientando o acompañando a otros estudiantes.'],
            ['social_comunitario', 'Me atraen las carreras relacionadas con educación, salud, psicología, trabajo social o apoyo comunitario.'],
            ['liderazgo_emprendimiento', 'Me interesa organizar equipos, coordinar actividades o tomar decisiones en un grupo.'],
            ['liderazgo_emprendimiento', 'Disfruto proponer ideas para iniciar proyectos, negocios, campañas o actividades escolares.'],
            ['liderazgo_emprendimiento', 'Me siento capaz de asumir responsabilidades cuando un equipo necesita dirección.'],
            ['liderazgo_emprendimiento', 'Me atrae negociar, presentar propuestas, convencer con argumentos o defender una idea.'],
            ['liderazgo_emprendimiento', 'Me interesan las carreras relacionadas con administración, emprendimiento, dirección, ventas o gestión de proyectos.'],
            ['organizativo_administrativo', 'Me gusta ordenar información, registrar datos, planificar actividades o llevar controles.'],
            ['organizativo_administrativo', 'Me interesa trabajar con documentos, reportes, presupuestos, inventarios o procesos administrativos.'],
            ['organizativo_administrativo', 'Prefiero actividades donde existan reglas claras, procedimientos y organización.'],
            ['organizativo_administrativo', 'Me siento cómodo revisando detalles para evitar errores en registros, cuentas o documentos.'],
            ['organizativo_administrativo', 'Me atraen las carreras relacionadas con contabilidad, administración, secretariado, finanzas, gestión documental o procesos institucionales.'],
        ];

        foreach ($preguntas as $index => [$dimension, $texto]) {
            $orden = $index + 1;

            OrientacionPregunta::updateOrCreate(
                ['codigo' => 'ORAV-' . str_pad((string) $orden, 2, '0', STR_PAD_LEFT)],
                [
                    'dimension' => $dimension,
                    'texto' => $texto,
                    'tipo' => 'likert',
                    'orden' => $orden,
                    'visible' => true,
                ]
            );
        }
    }
}
