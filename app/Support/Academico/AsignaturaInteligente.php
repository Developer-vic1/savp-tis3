<?php

namespace App\Support\Academico;

class AsignaturaInteligente
{
    public const TELEFONO_SOPORTE = '75836807';

    public const ESTADO_RECONOCIDA = 'RECONOCIDA';
    public const ESTADO_REDACTABLE = 'REDACTABLE';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';

    public const MIN_SIMILITUD_CATALOGO = 68;
    public const MIN_SIMILITUD_REDACTABLE = 72;
    public const MIN_SIMILITUD_DUPLICADO = 96;
    public const MIN_SIMILITUD_POSIBLE_DUPLICADO = 75;
    public const MIN_PUNTAJE_ACADEMICO_VALIDO = 70;

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGO INTELIGENTE DE ASIGNATURAS
    |--------------------------------------------------------------------------
    | Este catálogo no reemplaza la base de datos. Sirve como motor auxiliar
    | para interpretar, validar, sugerir y profesionalizar nombres de materias.
    |
    | La base curricular está organizada por campos formativos:
    | - Cosmos y Pensamiento
    | - Comunidad y Sociedad
    | - Vida, Tierra y Territorio
    | - Ciencia, Tecnología y Producción
    |--------------------------------------------------------------------------
    */

    public static function catalogo(): array
    {
        return [
            'MAT' => [
                'nombre' => 'Matemática',
                'sigla' => 'MAT',
                'horas' => 5,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Científica',
                'nivel' => 'Formación general',
                'descripcion' => 'Desarrolla razonamiento lógico, numérico, algebraico, geométrico y resolución de problemas.',
                'palabras_clave' => [
                    'mate',
                    'matematica',
                    'matematicas',
                    'aritmetica',
                    'algebra',
                    'geometria',
                    'calculo',
                    'razonamiento matematico',
                    'numeros',
                    'operaciones',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería de Sistemas',
                    'Ingeniería Electrónica',
                    'Ingeniería Civil',
                    'Ingeniería Industrial',
                    'Ingeniería Mecánica',
                    'Arquitectura',
                    'Economía',
                    'Contaduría Pública',
                    'Administración de Empresas',
                    'Estadística',
                    'Matemática Aplicada',
                    'Ciencia de Datos',
                ],
            ],

            'LCO' => [
                'nombre' => 'Comunicación y Lenguaje',
                'sigla' => 'LCO',
                'horas' => 4,
                'area' => 'Comunidad y Sociedad',
                'tipo' => 'Humanística',
                'nivel' => 'Formación general',
                'descripcion' => 'Fortalece comprensión lectora, expresión oral, escritura académica, argumentación y comunicación efectiva.',
                'palabras_clave' => [
                    'lenguaje',
                    'lengua',
                    'comunicacion',
                    'literatura',
                    'redaccion',
                    'ortografia',
                    'gramatica',
                    'lectura',
                    'escritura',
                    'comprension lectora',
                    'expresion oral',
                ],
                'carreras_relacionadas' => [
                    'Comunicación Social',
                    'Derecho',
                    'Psicología',
                    'Educación',
                    'Lingüística',
                    'Trabajo Social',
                    'Periodismo',
                    'Marketing',
                    'Relaciones Internacionales',
                    'Publicidad',
                ],
            ],

            'CSO' => [
                'nombre' => 'Ciencias Sociales',
                'sigla' => 'CSO',
                'horas' => 4,
                'area' => 'Comunidad y Sociedad',
                'tipo' => 'Social',
                'nivel' => 'Formación general',
                'descripcion' => 'Integra historia, geografía, ciudadanía, análisis social, cultura, economía básica y realidad nacional.',
                'palabras_clave' => [
                    'sociales',
                    'ciencias sociales',
                    'historia',
                    'geografia',
                    'civica',
                    'ciudadania',
                    'realidad nacional',
                    'sociologia',
                    'sociedad',
                    'cultura',
                ],
                'carreras_relacionadas' => [
                    'Derecho',
                    'Ciencias Políticas',
                    'Sociología',
                    'Historia',
                    'Turismo',
                    'Trabajo Social',
                    'Relaciones Internacionales',
                    'Antropología',
                    'Educación',
                    'Economía',
                ],
            ],

            'CBG' => [
                'nombre' => 'Ciencias Biológicas',
                'sigla' => 'CBG',
                'horas' => 3,
                'area' => 'Vida, Tierra y Territorio',
                'tipo' => 'Científica',
                'nivel' => 'Formación general',
                'descripcion' => 'Estudia seres vivos, cuerpo humano, salud, biodiversidad, ecosistemas y procesos biológicos.',
                'palabras_clave' => [
                    'biologia',
                    'biologica',
                    'biologicas',
                    'ciencias biologicas',
                    'vida',
                    'cuerpo humano',
                    'salud',
                    'ecosistema',
                    'biodiversidad',
                    'anatomia',
                    'seres vivos',
                ],
                'carreras_relacionadas' => [
                    'Medicina',
                    'Enfermería',
                    'Bioquímica',
                    'Odontología',
                    'Veterinaria',
                    'Biología',
                    'Nutrición',
                    'Fisioterapia',
                    'Ingeniería Ambiental',
                    'Biotecnología',
                ],
            ],

            'FIS' => [
                'nombre' => 'Física',
                'sigla' => 'FIS',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Científica',
                'nivel' => 'Formación general',
                'descripcion' => 'Desarrolla comprensión de fenómenos naturales, movimiento, energía, electricidad, ondas y medición.',
                'palabras_clave' => [
                    'fisica',
                    'mecanica',
                    'electricidad',
                    'energia',
                    'movimiento',
                    'ondas',
                    'circuitos',
                    'fuerza',
                    'velocidad',
                    'aceleracion',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Electrónica',
                    'Ingeniería Mecánica',
                    'Ingeniería Civil',
                    'Ingeniería Industrial',
                    'Ingeniería de Sistemas',
                    'Arquitectura',
                    'Física',
                    'Telecomunicaciones',
                    'Mecatrónica',
                    'Ingeniería Eléctrica',
                ],
            ],

            'QMC' => [
                'nombre' => 'Química',
                'sigla' => 'QMC',
                'horas' => 3,
                'area' => 'Vida, Tierra y Territorio',
                'tipo' => 'Científica',
                'nivel' => 'Formación general',
                'descripcion' => 'Estudia materia, sustancias, reacciones, laboratorio, composición química y procesos transformadores.',
                'palabras_clave' => [
                    'quimica',
                    'laboratorio',
                    'reacciones',
                    'materia',
                    'sustancias',
                    'compuestos',
                    'mezclas',
                    'elementos',
                    'moleculas',
                ],
                'carreras_relacionadas' => [
                    'Química',
                    'Bioquímica',
                    'Farmacia',
                    'Medicina',
                    'Ingeniería Química',
                    'Ingeniería Ambiental',
                    'Ingeniería de Alimentos',
                    'Nutrición',
                    'Biotecnología',
                ],
            ],

            'EFD' => [
                'nombre' => 'Educación Física',
                'sigla' => 'EFD',
                'horas' => 2,
                'area' => 'Comunidad y Sociedad',
                'tipo' => 'Físico-deportiva',
                'nivel' => 'Formación integral',
                'descripcion' => 'Fortalece condición física, salud, coordinación motriz, deporte, disciplina y trabajo colaborativo.',
                'palabras_clave' => [
                    'educacion fisica',
                    'fisica deportiva',
                    'deporte',
                    'gimnasia',
                    'salud fisica',
                    'actividad fisica',
                    'entrenamiento',
                    'atletismo',
                ],
                'carreras_relacionadas' => [
                    'Ciencias del Deporte',
                    'Fisioterapia',
                    'Educación Física',
                    'Nutrición',
                    'Medicina Deportiva',
                ],
            ],

            'LEX' => [
                'nombre' => 'Lengua Extranjera - Inglés',
                'sigla' => 'LEX',
                'horas' => 2,
                'area' => 'Comunidad y Sociedad',
                'tipo' => 'Idioma',
                'nivel' => 'Formación general',
                'descripcion' => 'Desarrolla comunicación básica e intermedia en idioma inglés, lectura técnica y comprensión intercultural.',
                'palabras_clave' => [
                    'ingles',
                    'idioma ingles',
                    'lengua extranjera',
                    'foreign language',
                    'english',
                    'ingles tecnico',
                ],
                'carreras_relacionadas' => [
                    'Turismo',
                    'Comercio Internacional',
                    'Relaciones Internacionales',
                    'Ingeniería de Sistemas',
                    'Comunicación Social',
                    'Lingüística',
                    'Administración Hotelera',
                    'Marketing',
                ],
            ],

            'VER' => [
                'nombre' => 'Valores y Espiritualidades',
                'sigla' => 'VER',
                'horas' => 2,
                'area' => 'Cosmos y Pensamiento',
                'tipo' => 'Ética-formativa',
                'nivel' => 'Formación integral',
                'descripcion' => 'Promueve ética, convivencia, valores sociocomunitarios, espiritualidad, respeto y responsabilidad social.',
                'palabras_clave' => [
                    'valores',
                    'espiritualidad',
                    'espiritualidades',
                    'etica',
                    'moral',
                    'convivencia',
                    'respeto',
                    'responsabilidad',
                    'valores humanos',
                ],
                'carreras_relacionadas' => [
                    'Psicología',
                    'Derecho',
                    'Educación',
                    'Trabajo Social',
                    'Filosofía',
                    'Teología',
                    'Ciencias Políticas',
                ],
            ],

            'CFS' => [
                'nombre' => 'Cosmovisiones y Filosofía',
                'sigla' => 'CFS',
                'horas' => 2,
                'area' => 'Cosmos y Pensamiento',
                'tipo' => 'Humanística',
                'nivel' => 'Formación general',
                'descripcion' => 'Analiza pensamiento filosófico, cosmovisiones, razonamiento crítico, identidad cultural y reflexión ética.',
                'palabras_clave' => [
                    'filosofia',
                    'cosmovisiones',
                    'cosmovision',
                    'pensamiento',
                    'logica',
                    'etica',
                    'reflexion',
                    'razonamiento critico',
                ],
                'carreras_relacionadas' => [
                    'Filosofía',
                    'Derecho',
                    'Psicología',
                    'Sociología',
                    'Educación',
                    'Ciencias Políticas',
                    'Antropología',
                    'Comunicación Social',
                ],
            ],

            'PSI' => [
                'nombre' => 'Psicología',
                'sigla' => 'PSI',
                'horas' => 2,
                'area' => 'Cosmos y Pensamiento',
                'tipo' => 'Orientación personal',
                'nivel' => 'Formación integral',
                'descripcion' => 'Apoya el autoconocimiento, orientación vocacional, conducta, emociones, relaciones y proyecto de vida.',
                'palabras_clave' => [
                    'psicologia',
                    'orientacion',
                    'vocacion',
                    'conducta',
                    'emociones',
                    'personalidad',
                    'proyecto de vida',
                    'autoconocimiento',
                ],
                'carreras_relacionadas' => [
                    'Psicología',
                    'Educación',
                    'Trabajo Social',
                    'Medicina',
                    'Recursos Humanos',
                    'Comunicación Social',
                    'Orientación Familiar',
                ],
            ],

            'EMU' => [
                'nombre' => 'Educación Musical',
                'sigla' => 'EMU',
                'horas' => 2,
                'area' => 'Comunidad y Sociedad',
                'tipo' => 'Artística',
                'nivel' => 'Formación integral',
                'descripcion' => 'Desarrolla expresión musical, ritmo, apreciación artística, creatividad, sensibilidad cultural y producción sonora.',
                'palabras_clave' => [
                    'musica',
                    'educacion musical',
                    'canto',
                    'ritmo',
                    'instrumentos',
                    'arte musical',
                    'sonido',
                    'melodia',
                ],
                'carreras_relacionadas' => [
                    'Música',
                    'Arte',
                    'Producción Audiovisual',
                    'Comunicación Social',
                    'Educación Artística',
                    'Diseño Sonoro',
                ],
            ],

            'APV' => [
                'nombre' => 'Artes Plásticas y Visuales',
                'sigla' => 'APV',
                'horas' => 2,
                'area' => 'Comunidad y Sociedad',
                'tipo' => 'Artística',
                'nivel' => 'Formación integral',
                'descripcion' => 'Fortalece creatividad visual, dibujo, composición, diseño, expresión gráfica y apreciación estética.',
                'palabras_clave' => [
                    'artes',
                    'artes plasticas',
                    'artes visuales',
                    'dibujo',
                    'pintura',
                    'diseno',
                    'creatividad',
                    'composicion',
                    'ilustracion',
                ],
                'carreras_relacionadas' => [
                    'Diseño Gráfico',
                    'Arquitectura',
                    'Artes Plásticas',
                    'Diseño de Interiores',
                    'Comunicación Visual',
                    'Publicidad',
                    'Producción Audiovisual',
                    'Diseño Industrial',
                ],
            ],

            'TTG' => [
                'nombre' => 'Técnica Tecnología General',
                'sigla' => 'TTG',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica general',
                'nivel' => 'Técnica Tecnológica General',
                'descripcion' => 'Base formativa técnica para desarrollar pensamiento productivo, innovación, emprendimiento y resolución práctica de problemas.',
                'palabras_clave' => [
                    'tecnica tecnologia general',
                    'tecnica tecnologica',
                    'tecnologia general',
                    'ttg',
                    'produccion',
                    'emprendimiento',
                    'innovacion',
                    'tecnologia',
                    'productiva',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería de Sistemas',
                    'Ingeniería Electrónica',
                    'Ingeniería Industrial',
                    'Administración de Empresas',
                    'Diseño Industrial',
                    'Emprendimiento',
                    'Mecatrónica',
                    'Ingeniería Comercial',
                ],
            ],

            'INF' => [
                'nombre' => 'Informática',
                'sigla' => 'INF',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica tecnológica',
                'nivel' => 'Formación técnica',
                'descripcion' => 'Introduce computación, sistemas operativos, herramientas digitales, pensamiento computacional y uso responsable de TIC.',
                'palabras_clave' => [
                    'informatica',
                    'computacion',
                    'computadoras',
                    'compus',
                    'tic',
                    'tecnologia',
                    'computadora',
                    'ofimatica',
                    'software',
                    'hardware',
                    'herramientas digitales',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería de Sistemas',
                    'Informática',
                    'Ingeniería de Software',
                    'Ciencia de Datos',
                    'Ciberseguridad',
                    'Telecomunicaciones',
                    'Diseño Gráfico Digital',
                    'Sistemas Informáticos',
                ],
            ],

            'PRG' => [
                'nombre' => 'Programación',
                'sigla' => 'PRG',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Desarrolla algoritmos, lógica de programación, estructuras de control, resolución computacional y construcción de software.',
                'palabras_clave' => [
                    'programacion',
                    'programar',
                    'algoritmos',
                    'codigo',
                    'codificacion',
                    'software',
                    'desarrollo',
                    'python',
                    'php',
                    'java',
                    'logica de programacion',
                    'desarrollo de software',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería de Sistemas',
                    'Ingeniería de Software',
                    'Ciencia de Datos',
                    'Inteligencia Artificial',
                    'Ciberseguridad',
                    'Desarrollo Web',
                    'Robótica',
                    'Informática',
                ],
            ],

            'BDD' => [
                'nombre' => 'Base de Datos',
                'sigla' => 'BDD',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Introduce modelado de datos, tablas, relaciones, consultas, integridad de información y administración básica de datos.',
                'palabras_clave' => [
                    'base de datos',
                    'bases de datos',
                    'sql',
                    'postgresql',
                    'postgres',
                    'mysql',
                    'tablas',
                    'consultas',
                    'modelo relacional',
                    'datos almacenados',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería de Sistemas',
                    'Ciencia de Datos',
                    'Ingeniería de Software',
                    'Administración de Bases de Datos',
                    'Analítica de Datos',
                    'Informática',
                ],
            ],

            'ROB' => [
                'nombre' => 'Robótica Educativa',
                'sigla' => 'ROB',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Integra electrónica, programación, sensores, automatización y diseño de prototipos para resolver problemas reales.',
                'palabras_clave' => [
                    'robotica',
                    'robots',
                    'arduino',
                    'sensores',
                    'automatizacion',
                    'prototipos',
                    'electronica',
                    'mecatronica',
                    'prototipos electronicos',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Electrónica',
                    'Mecatrónica',
                    'Ingeniería de Sistemas',
                    'Robótica',
                    'Automatización Industrial',
                    'Telecomunicaciones',
                    'Ingeniería Mecánica',
                ],
            ],

            'ELT' => [
                'nombre' => 'Electrónica Básica',
                'sigla' => 'ELT',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Forma en circuitos, componentes electrónicos, medición, soldadura, sensores, electricidad básica y diagnóstico técnico.',
                'palabras_clave' => [
                    'electronica',
                    'circuitos',
                    'resistencias',
                    'sensores',
                    'electricidad',
                    'soldadura',
                    'componentes',
                    'componentes electronicos',
                    'soldadura electronica',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Electrónica',
                    'Telecomunicaciones',
                    'Mecatrónica',
                    'Ingeniería Eléctrica',
                    'Automatización Industrial',
                    'Ingeniería de Sistemas',
                ],
            ],

            'EFC' => [
                'nombre' => 'Educación Financiera y Contable',
                'sigla' => 'EFC',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica económica',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Fortalece manejo de presupuestos, contabilidad básica, costos, ahorro, finanzas personales y análisis económico.',
                'palabras_clave' => [
                    'contabilidad',
                    'finanzas',
                    'educacion financiera',
                    'costos',
                    'presupuesto',
                    'economia',
                    'ahorro',
                    'dinero',
                    'libros contables',
                ],
                'carreras_relacionadas' => [
                    'Contaduría Pública',
                    'Economía',
                    'Administración de Empresas',
                    'Ingeniería Comercial',
                    'Auditoría',
                    'Finanzas',
                    'Banca',
                ],
            ],

            'EMD' => [
                'nombre' => 'Emprendimiento Productivo',
                'sigla' => 'EMD',
                'horas' => 2,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Productiva',
                'nivel' => 'Formación técnica',
                'descripcion' => 'Desarrolla iniciativa, modelos de negocio, costos, innovación, planificación productiva y cultura emprendedora.',
                'palabras_clave' => [
                    'emprendimiento',
                    'negocio',
                    'negocios',
                    'empresa',
                    'crear empresa',
                    'produccion',
                    'costos',
                    'innovacion',
                    'proyecto productivo',
                    'liderazgo emprendedor',
                ],
                'carreras_relacionadas' => [
                    'Administración de Empresas',
                    'Ingeniería Comercial',
                    'Marketing',
                    'Contaduría Pública',
                    'Economía',
                    'Ingeniería Industrial',
                    'Emprendimiento',
                ],
            ],

            'DTC' => [
                'nombre' => 'Dibujo Técnico',
                'sigla' => 'DTC',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica gráfica',
                'nivel' => 'Formación técnica',
                'descripcion' => 'Desarrolla representación gráfica, planos, escalas, vistas, normas técnicas y comunicación visual aplicada.',
                'palabras_clave' => [
                    'dibujo tecnico',
                    'planos',
                    'dibujo de planos',
                    'escalas',
                    'vistas',
                    'arquitectura',
                    'diseno tecnico',
                    'autocad',
                ],
                'carreras_relacionadas' => [
                    'Arquitectura',
                    'Ingeniería Civil',
                    'Diseño Industrial',
                    'Construcción Civil',
                    'Topografía',
                    'Ingeniería Mecánica',
                    'Diseño de Interiores',
                ],
            ],

            'EST' => [
                'nombre' => 'Estadística Aplicada',
                'sigla' => 'EST',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Científica',
                'nivel' => 'Formación general',
                'descripcion' => 'Desarrolla análisis de datos, tablas, gráficos, medidas estadísticas, probabilidad e interpretación cuantitativa.',
                'palabras_clave' => [
                    'estadistica',
                    'datos',
                    'probabilidad',
                    'graficos',
                    'promedio',
                    'promedios',
                    'analisis cuantitativo',
                    'muestra',
                    'encuestas',
                ],
                'carreras_relacionadas' => [
                    'Ciencia de Datos',
                    'Ingeniería de Sistemas',
                    'Economía',
                    'Administración',
                    'Psicología',
                    'Medicina',
                    'Investigación Social',
                    'Estadística',
                ],
            ],

            'INV' => [
                'nombre' => 'Investigación Aplicada',
                'sigla' => 'INV',
                'horas' => 2,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Investigativa',
                'nivel' => 'Formación general',
                'descripcion' => 'Introduce métodos de investigación, formulación de problemas, análisis de datos, proyectos y comunicación científica.',
                'palabras_clave' => [
                    'investigacion',
                    'metodologia',
                    'metodo cientifico',
                    'proyecto',
                    'proyectos',
                    'problema',
                    'hipotesis',
                    'analisis de datos',
                    'ciencia',
                    'hacer tesis',
                ],
                'carreras_relacionadas' => [
                    'Todas las áreas universitarias',
                    'Ingeniería de Sistemas',
                    'Medicina',
                    'Derecho',
                    'Psicología',
                    'Educación',
                    'Ciencias Sociales',
                    'Comunicación Social',
                ],
            ],

            'MAM' => [
                'nombre' => 'Medio Ambiente y Madre Tierra',
                'sigla' => 'MAM',
                'horas' => 2,
                'area' => 'Vida, Tierra y Territorio',
                'tipo' => 'Ambiental',
                'nivel' => 'Formación integral',
                'descripcion' => 'Promueve conciencia ambiental, gestión de residuos, sostenibilidad, cambio climático y cuidado de la Madre Tierra.',
                'palabras_clave' => [
                    'medio ambiente',
                    'madre tierra',
                    'ecologia',
                    'sostenibilidad',
                    'reciclaje',
                    'cambio climatico',
                    'gestion ambiental',
                    'naturaleza',
                    'ambiente',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Ambiental',
                    'Biología',
                    'Agronomía',
                    'Gestión Ambiental',
                    'Ingeniería Civil',
                    'Turismo',
                    'Ingeniería de Recursos Naturales',
                ],
            ],

            'GAS' => [
                'nombre' => 'Gastronomía y Nutrición',
                'sigla' => 'GAS',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Integra técnicas culinarias, inocuidad alimentaria, nutrición básica, producción gastronómica y emprendimiento alimentario.',
                'palabras_clave' => [
                    'gastronomia',
                    'cocina',
                    'chef',
                    'nutricion',
                    'alimentos',
                    'reposteria',
                    'inocuidad',
                    'inocuidad alimentaria',
                    'comida',
                ],
                'carreras_relacionadas' => [
                    'Gastronomía',
                    'Nutrición',
                    'Ingeniería de Alimentos',
                    'Administración Hotelera',
                    'Turismo',
                    'Emprendimiento',
                    'Administración de Empresas',
                ],
            ],

            'MCA' => [
                'nombre' => 'Mecánica Automotriz',
                'sigla' => 'MCA',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Desarrolla mantenimiento, diagnóstico, sistemas mecánicos, motores, electricidad automotriz y seguridad técnica.',
                'palabras_clave' => [
                    'mecanica automotriz',
                    'automotriz',
                    'motor',
                    'motores',
                    'vehiculos',
                    'autos',
                    'mantenimiento',
                    'diagnostico automotriz',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Mecánica',
                    'Ingeniería Automotriz',
                    'Mecatrónica',
                    'Ingeniería Industrial',
                    'Electromecánica',
                    'Ingeniería Eléctrica',
                ],
            ],

            'MCI' => [
                'nombre' => 'Mecánica Industrial',
                'sigla' => 'MCI',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Forma en herramientas, máquinas, procesos productivos, mantenimiento industrial, seguridad y manufactura.',
                'palabras_clave' => [
                    'mecanica industrial',
                    'maquinas',
                    'herramientas',
                    'manufactura',
                    'mantenimiento industrial',
                    'torno',
                    'soldadura',
                    'herramientas industriales',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Industrial',
                    'Ingeniería Mecánica',
                    'Mecatrónica',
                    'Electromecánica',
                    'Automatización Industrial',
                    'Ingeniería de Producción',
                ],
            ],

            'CIV' => [
                'nombre' => 'Construcción Civil',
                'sigla' => 'CIV',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Desarrolla lectura de planos, materiales, procesos constructivos, seguridad, mediciones y control de obra básica.',
                'palabras_clave' => [
                    'construccion',
                    'construccion civil',
                    'obra',
                    'albanileria',
                    'materiales',
                    'materiales de construccion',
                    'planos',
                    'estructuras',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería Civil',
                    'Arquitectura',
                    'Construcción Civil',
                    'Topografía',
                    'Ingeniería Ambiental',
                    'Diseño de Interiores',
                ],
            ],

            'TEX' => [
                'nombre' => 'Textiles y Confecciones',
                'sigla' => 'TEX',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Integra diseño, patronaje, confección, manejo de máquinas, producción textil y emprendimiento creativo.',
                'palabras_clave' => [
                    'textiles',
                    'confecciones',
                    'confeccion',
                    'costura',
                    'moda',
                    'patronaje',
                    'maquina de coser',
                    'diseno textil',
                ],
                'carreras_relacionadas' => [
                    'Diseño de Modas',
                    'Diseño Textil',
                    'Ingeniería Textil',
                    'Emprendimiento',
                    'Diseño Industrial',
                    'Administración de Empresas',
                ],
            ],

            'CAR' => [
                'nombre' => 'Carpintería y Diseño en Madera',
                'sigla' => 'CAR',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Forma en diseño, medición, corte, ensamblaje, seguridad, acabados y producción de mobiliario.',
                'palabras_clave' => [
                    'carpinteria',
                    'madera',
                    'muebles',
                    'ebanisteria',
                    'diseno en madera',
                    'herramientas',
                    'herramientas de madera',
                ],
                'carreras_relacionadas' => [
                    'Diseño Industrial',
                    'Arquitectura',
                    'Ingeniería Industrial',
                    'Emprendimiento',
                    'Diseño de Interiores',
                    'Construcción Civil',
                ],
            ],

            'BEI' => [
                'nombre' => 'Belleza Integral',
                'sigla' => 'BEI',
                'horas' => 4,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica especializada',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Desarrolla estética, bioseguridad, cuidado personal, atención al cliente, emprendimiento y servicios integrales.',
                'palabras_clave' => [
                    'belleza',
                    'belleza integral',
                    'estetica',
                    'cosmetologia',
                    'peluqueria',
                    'maquillaje',
                    'bioseguridad',
                    'cuidado personal',
                ],
                'carreras_relacionadas' => [
                    'Cosmetología',
                    'Estética Integral',
                    'Emprendimiento',
                    'Marketing',
                    'Administración de Empresas',
                    'Salud y Bienestar',
                    'Comunicación Social',
                ],
            ],

            'IAE' => [
                'nombre' => 'Inteligencia Artificial Aplicada',
                'sigla' => 'IAE',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica complementaria',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Introduce fundamentos de inteligencia artificial, análisis de datos, automatización y aplicaciones educativas o productivas.',
                'palabras_clave' => [
                    'inteligencia artificial',
                    'ia',
                    'machine learning',
                    'aprendizaje automatico',
                    'modelos predictivos',
                    'redes neuronales',
                    'automatizacion inteligente',
                ],
                'carreras_relacionadas' => [
                    'Ingeniería de Sistemas',
                    'Ciencia de Datos',
                    'Inteligencia Artificial',
                    'Ingeniería de Software',
                    'Robótica',
                    'Automatización Industrial',
                ],
            ],

            'UXI' => [
                'nombre' => 'Diseño UX/UI',
                'sigla' => 'UXI',
                'horas' => 3,
                'area' => 'Ciencia, Tecnología y Producción',
                'tipo' => 'Técnica creativa',
                'nivel' => 'Especialización técnica',
                'descripcion' => 'Desarrolla diseño de interfaces, experiencia de usuario, prototipado, accesibilidad y evaluación de usabilidad.',
                'palabras_clave' => [
                    'ux',
                    'ui',
                    'ux ui',
                    'diseno ux',
                    'diseno ui',
                    'interfaces',
                    'experiencia de usuario',
                    'prototipos',
                    'figma',
                    'usabilidad',
                ],
                'carreras_relacionadas' => [
                    'Diseño Gráfico',
                    'Ingeniería de Sistemas',
                    'Diseño de Interacción',
                    'Comunicación Visual',
                    'Desarrollo Web',
                    'Marketing Digital',
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | INTERPRETACIÓN PRINCIPAL
    |--------------------------------------------------------------------------
    */

    public static function interpretar(?string $entrada, array $existentes = []): array
    {
        $original = trim((string) $entrada);
        $normalizado = self::normalizar($original);

        if ($normalizado === '') {
            return self::respuestaBase(
                valido: false,
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: 'Escribe el nombre de una asignatura para analizarla.',
                estadoInteligente: self::ESTADO_BLOQUEADA,
                requiereSoporte: false
            );
        }

        if (self::entradaClaramenteInvalida($normalizado)) {
            return self::respuestaBloqueada(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: self::mensajeSoporte()
            );
        }

        $coincidenciasExistentes = self::buscarCoincidencias($original, $existentes);
        $duplicadoExacto = self::tieneDuplicadoExacto($coincidenciasExistentes);
        $posibleDuplicado = self::tienePosibleDuplicado($coincidenciasExistentes);

        $mejorCatalogo = self::mejorCoincidenciaCatalogo($normalizado);

        if ($mejorCatalogo !== null) {
            return self::respuestaDesdeCatalogo(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                siglaCatalogo: $mejorCatalogo['sigla'],
                confianza: $mejorCatalogo['confianza'],
                coincidenciasExistentes: $coincidenciasExistentes,
                duplicadoExacto: $duplicadoExacto,
                posibleDuplicado: $posibleDuplicado
            );
        }

        $redaccion = self::redactarComoMateria($original);

        if (! $redaccion['puede_redactarse']) {
            return self::respuestaBloqueada(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: self::mensajeSoporte(),
                coincidencias: $coincidenciasExistentes,
                confianza: $redaccion['confianza_redaccion']
            );
        }

        return self::respuestaDesdeRedaccion(
            entradaOriginal: $original,
            entradaNormalizada: $normalizado,
            redaccion: $redaccion,
            coincidenciasExistentes: $coincidenciasExistentes,
            duplicadoExacto: $duplicadoExacto,
            posibleDuplicado: $posibleDuplicado
        );
    }

    public static function desdeSigla(string $sigla): array
    {
        $sigla = mb_strtoupper(trim($sigla));
        $catalogo = self::catalogo();

        if (! isset($catalogo[$sigla])) {
            return self::respuestaBase(
                valido: false,
                entradaOriginal: $sigla,
                entradaNormalizada: self::normalizar($sigla),
                mensaje: 'No se encontró una asignatura institucional con esa sigla.',
                estadoInteligente: self::ESTADO_BLOQUEADA,
                requiereSoporte: false
            );
        }

        $asignatura = $catalogo[$sigla];

        return array_merge(
            self::respuestaBase(
                valido: true,
                entradaOriginal: $sigla,
                entradaNormalizada: self::normalizar($sigla),
                mensaje: 'Asignatura seleccionada desde catálogo institucional.',
                estadoInteligente: self::ESTADO_RECONOCIDA,
                requiereSoporte: false
            ),
            self::mapearAsignaturaResultado($asignatura, [
                'es_catalogo' => true,
                'es_nueva' => false,
                'duplicado' => false,
                'posible_duplicado' => false,
                'requiere_revision' => false,
                'confianza' => 100,
            ])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPUESTAS ESTRUCTURADAS
    |--------------------------------------------------------------------------
    */

    private static function respuestaDesdeCatalogo(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $siglaCatalogo,
        int $confianza,
        array $coincidenciasExistentes,
        bool $duplicadoExacto,
        bool $posibleDuplicado
    ): array {
        $asignatura = self::catalogo()[$siglaCatalogo];

        $advertencias = [];

        if ($duplicadoExacto) {
            $advertencias[] = 'Ya existe una asignatura con nombre o sigla equivalente. No se recomienda registrar duplicados.';
        } elseif ($posibleDuplicado) {
            $advertencias[] = 'Existen asignaturas similares registradas. Verifica si corresponde reutilizar una existente.';
        }

        if ($confianza < 85) {
            $advertencias[] = 'La coincidencia no es completamente exacta. Revisa si la sugerencia corresponde a la intención real.';
        }

        return array_merge(
            self::respuestaBase(
                valido: ! $duplicadoExacto,
                entradaOriginal: $entradaOriginal,
                entradaNormalizada: $entradaNormalizada,
                mensaje: $duplicadoExacto
                    ? 'No se puede registrar esta asignatura porque parece duplicada.'
                    : 'Asignatura reconocida desde el catálogo institucional.',
                estadoInteligente: self::ESTADO_RECONOCIDA,
                coincidencias: $coincidenciasExistentes,
                requiereSoporte: false
            ),
            self::mapearAsignaturaResultado($asignatura, [
                'es_catalogo' => true,
                'es_nueva' => false,
                'duplicado' => $duplicadoExacto,
                'posible_duplicado' => $posibleDuplicado,
                'requiere_revision' => $confianza < 85 || $posibleDuplicado,
                'confianza' => $confianza,
                'advertencias' => $advertencias,
            ])
        );
    }

    private static function respuestaDesdeRedaccion(
        string $entradaOriginal,
        string $entradaNormalizada,
        array $redaccion,
        array $coincidenciasExistentes,
        bool $duplicadoExacto,
        bool $posibleDuplicado
    ): array {
        $nombreSugerido = $redaccion['nombre_sugerido'];
        $siglaSugerida = $redaccion['sigla_sugerida'];
        $estado = $redaccion['estado_inteligente'];

        $advertencias = [];

        if ($estado === self::ESTADO_REDACTABLE) {
            $advertencias[] = 'La entrada fue redactada profesionalmente como materia académica. Revisa la sugerencia antes de guardar.';
        }

        if ($estado === self::ESTADO_REVISION) {
            $advertencias[] = 'La asignatura parece académicamente válida, pero no pertenece al catálogo base. Debe revisarse institucionalmente antes de usarla en planes o calificaciones.';
        }

        if ($duplicadoExacto) {
            $advertencias[] = 'Ya existe una asignatura equivalente. No se recomienda crear un duplicado.';
        } elseif ($posibleDuplicado) {
            $advertencias[] = 'Existen asignaturas similares registradas. Verifica si corresponde reutilizar una existente.';
        }

        $asignatura = [
            'nombre' => $nombreSugerido,
            'sigla' => $siglaSugerida,
            'horas' => self::sugerirHoras($nombreSugerido),
            'area' => self::sugerirArea($nombreSugerido),
            'tipo' => self::sugerirTipo($nombreSugerido),
            'nivel' => self::sugerirNivel($nombreSugerido),
            'descripcion' => $estado === self::ESTADO_REDACTABLE
                ? 'Asignatura redactada formalmente por el motor inteligente a partir de una entrada informal.'
                : 'Asignatura propuesta fuera del catálogo base. Debe validarse académicamente antes de consolidarse como materia institucional.',
            'palabras_clave' => self::extraerPalabrasClave($nombreSugerido),
            'carreras_relacionadas' => self::sugerirCarreras($nombreSugerido),
        ];

        return array_merge(
            self::respuestaBase(
                valido: ! $duplicadoExacto,
                entradaOriginal: $entradaOriginal,
                entradaNormalizada: $entradaNormalizada,
                mensaje: $duplicadoExacto
                    ? 'No se puede registrar esta asignatura porque parece duplicada.'
                    : $redaccion['mensaje_redaccion'],
                estadoInteligente: $estado,
                coincidencias: $coincidenciasExistentes,
                requiereSoporte: false
            ),
            self::mapearAsignaturaResultado($asignatura, [
                'es_catalogo' => false,
                'es_nueva' => true,
                'duplicado' => $duplicadoExacto,
                'posible_duplicado' => $posibleDuplicado,
                'requiere_revision' => $estado === self::ESTADO_REVISION || $posibleDuplicado,
                'confianza' => $redaccion['confianza_redaccion'],
                'advertencias' => $advertencias,
            ])
        );
    }

    private static function respuestaBloqueada(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $mensaje,
        array $coincidencias = [],
        int $confianza = 0
    ): array {
        return array_merge(
            self::respuestaBase(
                valido: false,
                entradaOriginal: $entradaOriginal,
                entradaNormalizada: $entradaNormalizada,
                mensaje: $mensaje,
                estadoInteligente: self::ESTADO_BLOQUEADA,
                coincidencias: $coincidencias,
                requiereSoporte: true
            ),
            [
                'duplicado' => false,
                'posible_duplicado' => self::tienePosibleDuplicado($coincidencias),
                'requiere_revision' => true,
                'requiere_soporte' => true,
                'telefono_soporte' => self::TELEFONO_SOPORTE,
                'confianza' => $confianza,
                'descripcion' => 'Entrada no reconocida como asignatura académica válida.',
                'advertencias' => [
                    'No se puede crear la asignatura porque el sistema no logró descifrar una materia académica válida.',
                    'Revisa la redacción o contacta con soporte académico al ' . self::TELEFONO_SOPORTE . ' para validar la creación de una nueva materia.',
                ],
            ]
        );
    }

    private static function respuestaBase(
        bool $valido,
        string $entradaOriginal,
        string $entradaNormalizada,
        string $mensaje,
        string $estadoInteligente = '',
        array $coincidencias = [],
        bool $requiereSoporte = false
    ): array {
        return [
            'valido' => $valido,
            'entrada_original' => $entradaOriginal,
            'entrada_normalizada' => $entradaNormalizada,
            'mensaje' => $mensaje,
            'estado_inteligente' => $estadoInteligente,
            'es_catalogo' => false,
            'es_nueva' => false,
            'duplicado' => false,
            'posible_duplicado' => false,
            'requiere_revision' => false,
            'requiere_soporte' => $requiereSoporte,
            'telefono_soporte' => self::TELEFONO_SOPORTE,
            'confianza' => 0,
            'nombre' => '',
            'sigla' => '',
            'horas' => 0,
            'area' => '',
            'tipo' => '',
            'nivel' => '',
            'descripcion' => '',
            'palabras_clave' => [],
            'carreras_relacionadas' => [],
            'advertencias' => [],
            'sugerencias' => self::catalogoSugerencias(),
            'coincidencias' => $coincidencias,
        ];
    }

    private static function mapearAsignaturaResultado(array $asignatura, array $extra = []): array
    {
        return array_merge([
            'nombre' => $asignatura['nombre'] ?? '',
            'sigla' => mb_strtoupper((string) ($asignatura['sigla'] ?? '')),
            'horas' => (int) ($asignatura['horas'] ?? 2),
            'area' => $asignatura['area'] ?? '',
            'tipo' => $asignatura['tipo'] ?? '',
            'nivel' => $asignatura['nivel'] ?? '',
            'descripcion' => $asignatura['descripcion'] ?? '',
            'palabras_clave' => $asignatura['palabras_clave'] ?? [],
            'carreras_relacionadas' => $asignatura['carreras_relacionadas'] ?? [],
        ], $extra);
    }

    /*
    |--------------------------------------------------------------------------
    | REDACCIÓN PROFESIONAL
    |--------------------------------------------------------------------------
    */

    public static function redactarComoMateria(?string $entrada): array
    {
        $original = trim((string) $entrada);
        $normalizado = self::normalizar($original);

        if ($normalizado === '' || self::entradaClaramenteInvalida($normalizado)) {
            return [
                'puede_redactarse' => false,
                'nombre_sugerido' => '',
                'sigla_sugerida' => '',
                'confianza_redaccion' => 0,
                'estado_inteligente' => self::ESTADO_BLOQUEADA,
                'mensaje_redaccion' => 'No se pudo descifrar esta entrada como una asignatura académica válida.',
                'requiere_soporte' => true,
            ];
        }

        foreach (self::mapaRedaccionAcademica() as $grupo) {
            foreach ($grupo['entradas'] as $entradaReferencia) {
                $referenciaNormalizada = self::normalizar($entradaReferencia);
                $similitud = self::calcularSimilitud($normalizado, $referenciaNormalizada);

                if (
                    $similitud >= self::MIN_SIMILITUD_REDACTABLE
                    || str_contains($normalizado, $referenciaNormalizada)
                    || str_contains($referenciaNormalizada, $normalizado)
                ) {
                    return [
                        'puede_redactarse' => true,
                        'nombre_sugerido' => $grupo['nombre'],
                        'sigla_sugerida' => $grupo['sigla'],
                        'confianza_redaccion' => max($similitud, 82),
                        'estado_inteligente' => self::ESTADO_REDACTABLE,
                        'mensaje_redaccion' => 'La entrada fue interpretada y redactada como una asignatura académica formal.',
                        'requiere_soporte' => false,
                    ];
                }
            }
        }

        $puntaje = self::puntajeAcademico($normalizado);

        if ($puntaje >= self::MIN_PUNTAJE_ACADEMICO_VALIDO) {
            $nombre = self::formatearNombreAcademico($original);

            return [
                'puede_redactarse' => true,
                'nombre_sugerido' => $nombre,
                'sigla_sugerida' => self::generarSigla($nombre),
                'confianza_redaccion' => $puntaje,
                'estado_inteligente' => self::ESTADO_REVISION,
                'mensaje_redaccion' => 'La entrada parece corresponder a una asignatura académica, pero no está en el catálogo institucional. Debe revisarse antes de consolidarla.',
                'requiere_soporte' => false,
            ];
        }

        return [
            'puede_redactarse' => false,
            'nombre_sugerido' => '',
            'sigla_sugerida' => '',
            'confianza_redaccion' => $puntaje,
            'estado_inteligente' => self::ESTADO_BLOQUEADA,
            'mensaje_redaccion' => 'No se pudo descifrar esta entrada como una asignatura académica válida.',
            'requiere_soporte' => true,
        ];
    }

    private static function mapaRedaccionAcademica(): array
    {
        $mapa = [];

        foreach (self::catalogo() as $asignatura) {
            $mapa[] = [
                'nombre' => $asignatura['nombre'],
                'sigla' => $asignatura['sigla'],
                'entradas' => array_values(array_unique(array_merge(
                    [$asignatura['nombre'], $asignatura['sigla']],
                    $asignatura['palabras_clave'] ?? []
                ))),
            ];
        }

        $mapa[] = [
            'nombre' => 'Informática',
            'sigla' => 'INF',
            'entradas' => [
                'cosas de compus',
                'manejo de computadora',
                'clases de computadora',
                'computadoras del colegio',
                'herramientas digitales',
                'uso de tecnologia',
            ],
        ];

        $mapa[] = [
            'nombre' => 'Programación',
            'sigla' => 'PRG',
            'entradas' => [
                'hacer programas',
                'crear paginas web',
                'desarrollar sistemas',
                'hacer codigo',
                'codigos',
                'clases de codigo',
            ],
        ];

        $mapa[] = [
            'nombre' => 'Emprendimiento Productivo',
            'sigla' => 'EMD',
            'entradas' => [
                'negocios escolares',
                'crear negocio',
                'vender productos',
                'proyecto de negocio',
                'hacer emprendimientos',
            ],
        ];

        $mapa[] = [
            'nombre' => 'Investigación Aplicada',
            'sigla' => 'INV',
            'entradas' => [
                'hacer investigaciones',
                'como investigar',
                'hacer proyecto',
                'proyecto de grado',
                'metodologia de investigacion',
            ],
        ];

        return $mapa;
    }

    /*
    |--------------------------------------------------------------------------
    | COINCIDENCIAS Y DUPLICADOS
    |--------------------------------------------------------------------------
    */

    public static function buscarCoincidencias(string $entrada, array $existentes): array
    {
        $normalizado = self::normalizar($entrada);
        $siglaEntrada = self::generarSigla($entrada);
        $coincidencias = [];

        foreach ($existentes as $item) {
            $nombre = (string) ($item['nom_asi'] ?? $item['nombre'] ?? '');
            $sigla = (string) ($item['sig_asi'] ?? $item['sigla'] ?? '');
            $codigo = (string) ($item['cod_asi'] ?? $item['codigo'] ?? '');

            if ($nombre === '' && $sigla === '') {
                continue;
            }

            $nombreNormalizado = self::normalizar($nombre);
            $similitudNombre = self::calcularSimilitud($normalizado, $nombreNormalizado);
            $mismaSigla = $sigla !== '' && mb_strtoupper($sigla) === mb_strtoupper($siglaEntrada);

            if ($similitudNombre >= 70 || $mismaSigla) {
                $coincidencias[] = [
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'sigla' => mb_strtoupper($sigla),
                    'similitud' => $similitudNombre,
                    'misma_sigla' => $mismaSigla,
                    'tipo' => $similitudNombre >= self::MIN_SIMILITUD_DUPLICADO || $mismaSigla ? 'ALTA' : 'MEDIA',
                    'mensaje' => $similitudNombre >= self::MIN_SIMILITUD_DUPLICADO || $mismaSigla
                        ? 'Posible duplicado fuerte.'
                        : 'Asignatura similar. Requiere revisión.',
                ];
            }
        }

        usort($coincidencias, fn(array $a, array $b) => $b['similitud'] <=> $a['similitud']);

        return $coincidencias;
    }

    private static function tieneDuplicadoExacto(array $coincidencias): bool
    {
        foreach ($coincidencias as $coincidencia) {
            if (
                ($coincidencia['similitud'] ?? 0) >= self::MIN_SIMILITUD_DUPLICADO
                || ($coincidencia['misma_sigla'] ?? false)
            ) {
                return true;
            }
        }

        return false;
    }

    private static function tienePosibleDuplicado(array $coincidencias): bool
    {
        foreach ($coincidencias as $coincidencia) {
            if (($coincidencia['similitud'] ?? 0) >= self::MIN_SIMILITUD_POSIBLE_DUPLICADO) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | SUGERENCIAS INTELIGENTES
    |--------------------------------------------------------------------------
    */

    public static function generarSigla(string $nombre): string
    {
        $normalizado = self::normalizar($nombre);

        foreach (self::catalogo() as $sigla => $asignatura) {
            if (self::coincideConAsignatura($normalizado, $asignatura)) {
                return $sigla;
            }
        }

        $palabras = self::palabrasSignificativas($normalizado);

        if (count($palabras) >= 3) {
            return mb_strtoupper(
                mb_substr($palabras[0], 0, 1)
                    . mb_substr($palabras[1], 0, 1)
                    . mb_substr($palabras[2], 0, 1)
            );
        }

        if (count($palabras) === 2) {
            return mb_strtoupper(
                mb_substr($palabras[0], 0, 1)
                    . mb_substr($palabras[1], 0, 2)
            );
        }

        if (count($palabras) === 1) {
            return mb_strtoupper(mb_substr($palabras[0], 0, 3));
        }

        return 'ASI';
    }

    public static function sugerirHoras(string $nombre): int
    {
        $normalizado = self::normalizar($nombre);

        foreach (self::catalogo() as $asignatura) {
            if (self::coincideConAsignatura($normalizado, $asignatura)) {
                return (int) $asignatura['horas'];
            }
        }

        if (self::contieneAlguna($normalizado, [
            'programacion',
            'informatica',
            'electronica',
            'mecanica',
            'construccion',
            'gastronomia',
            'textiles',
            'carpinteria',
            'belleza',
        ])) {
            return 4;
        }

        if (self::contieneAlguna($normalizado, [
            'estadistica',
            'dibujo',
            'base de datos',
            'robotica',
            'financiera',
            'contable',
            'inteligencia artificial',
            'ux',
            'ui',
        ])) {
            return 3;
        }

        return 2;
    }

    private static function sugerirArea(string $nombre): string
    {
        $normalizado = self::normalizar($nombre);

        if (self::contieneAlguna($normalizado, [
            'filosofia',
            'valores',
            'espiritualidad',
            'psicologia',
            'etica',
            'cosmovision',
        ])) {
            return 'Cosmos y Pensamiento';
        }

        if (self::contieneAlguna($normalizado, [
            'lenguaje',
            'comunicacion',
            'sociales',
            'historia',
            'geografia',
            'arte',
            'musica',
            'educacion fisica',
            'ingles',
        ])) {
            return 'Comunidad y Sociedad';
        }

        if (self::contieneAlguna($normalizado, [
            'biologia',
            'quimica',
            'ambiente',
            'madre tierra',
            'salud',
            'ecologia',
            'nutricion',
        ])) {
            return 'Vida, Tierra y Territorio';
        }

        return 'Ciencia, Tecnología y Producción';
    }

    private static function sugerirTipo(string $nombre): string
    {
        $normalizado = self::normalizar($nombre);

        if (self::contieneAlguna($normalizado, [
            'programacion',
            'informatica',
            'robotica',
            'electronica',
            'mecanica',
            'construccion',
            'textiles',
            'carpinteria',
            'gastronomia',
            'belleza',
            'base de datos',
            'inteligencia artificial',
            'ux',
            'ui',
        ])) {
            return 'Técnica especializada';
        }

        if (self::contieneAlguna($normalizado, [
            'matematica',
            'fisica',
            'quimica',
            'biologia',
            'estadistica',
        ])) {
            return 'Científica';
        }

        if (self::contieneAlguna($normalizado, [
            'lenguaje',
            'filosofia',
            'sociales',
            'historia',
            'psicologia',
            'valores',
        ])) {
            return 'Humanística';
        }

        if (self::contieneAlguna($normalizado, [
            'musica',
            'arte',
            'dibujo',
            'visual',
            'diseno',
        ])) {
            return 'Artística';
        }

        return 'Complementaria';
    }

    private static function sugerirNivel(string $nombre): string
    {
        $normalizado = self::normalizar($nombre);

        if (self::contieneAlguna($normalizado, [
            'programacion',
            'robotica',
            'electronica',
            'mecanica',
            'construccion',
            'gastronomia',
            'textiles',
            'carpinteria',
            'belleza',
            'base de datos',
            'inteligencia artificial',
            'ux',
            'ui',
        ])) {
            return 'Especialización técnica';
        }

        if (self::contieneAlguna($normalizado, [
            'tecnologia',
            'emprendimiento',
            'informatica',
        ])) {
            return 'Técnica Tecnológica General';
        }

        return 'Formación general';
    }

    private static function sugerirCarreras(string $nombre): array
    {
        $normalizado = self::normalizar($nombre);
        $carreras = [];

        foreach (self::catalogo() as $asignatura) {
            if (
                self::calcularSimilitud($normalizado, $asignatura['nombre']) >= 60
                || self::coincideConAsignatura($normalizado, $asignatura)
            ) {
                $carreras = array_merge($carreras, $asignatura['carreras_relacionadas']);
            }
        }

        if (empty($carreras)) {
            if (self::contieneAlguna($normalizado, ['programacion', 'informatica', 'datos', 'software', 'inteligencia artificial'])) {
                $carreras = ['Ingeniería de Sistemas', 'Ingeniería de Software', 'Ciencia de Datos'];
            } elseif (self::contieneAlguna($normalizado, ['empresa', 'negocio', 'finanzas', 'contabilidad'])) {
                $carreras = ['Administración de Empresas', 'Contaduría Pública', 'Ingeniería Comercial'];
            } elseif (self::contieneAlguna($normalizado, ['salud', 'biologia', 'nutricion'])) {
                $carreras = ['Medicina', 'Enfermería', 'Nutrición'];
            } elseif (self::contieneAlguna($normalizado, ['dibujo', 'diseno', 'arte', 'visual'])) {
                $carreras = ['Diseño Gráfico', 'Arquitectura', 'Comunicación Visual'];
            } else {
                $carreras = ['Orientación vocacional pendiente de validación'];
            }
        }

        return array_values(array_unique($carreras));
    }

    public static function catalogoSugerencias(): array
    {
        return array_values(array_map(
            fn(array $asignatura) => [
                'nombre' => $asignatura['nombre'],
                'sigla' => $asignatura['sigla'],
                'horas' => $asignatura['horas'],
                'area' => $asignatura['area'],
                'tipo' => $asignatura['tipo'],
                'nivel' => $asignatura['nivel'],
            ],
            self::catalogo()
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SIMILITUD, NORMALIZACIÓN Y VALIDACIÓN
    |--------------------------------------------------------------------------
    */

    public static function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        $texto = strtr($texto, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ]);

        $texto = preg_replace('/[^a-z0-9\s]/u', ' ', $texto) ?? '';
        $texto = preg_replace('/\s+/', ' ', $texto) ?? '';
        $texto = trim($texto);

        $reemplazos = [
            'matematicas' => 'matematica',
            'fisicas' => 'fisica',
            'quimicas' => 'quimica',
            'biologicas' => 'biologica',
            'ciencias biologicas' => 'ciencias biologicas',
            'artes plasticas visuales' => 'artes plasticas y visuales',
            'lengua extranjera ingles' => 'ingles',
            'tecnica tecnologia general' => 'tecnica tecnologica general',
            'tecnologia general' => 'tecnica tecnologica general',
            'compus' => 'computadoras',
            'compu' => 'computadora',
            'programacion basica' => 'programacion',
            'electronica basica' => 'electronica',
            'bd' => 'base de datos',
            'bdd' => 'base de datos',
        ];

        return $reemplazos[$texto] ?? $texto;
    }

    public static function calcularSimilitud(string $a, string $b): int
    {
        $a = self::normalizar($a);
        $b = self::normalizar($b);

        if ($a === '' || $b === '') {
            return 0;
        }

        if ($a === $b) {
            return 100;
        }

        similar_text($a, $b, $porcentajeSimilarText);

        $distancia = levenshtein($a, $b);
        $maxLength = max(strlen($a), strlen($b));

        $porcentajeLevenshtein = $maxLength > 0
            ? (1 - min($distancia, $maxLength) / $maxLength) * 100
            : 0;

        $resultado = max($porcentajeSimilarText, $porcentajeLevenshtein);

        if (str_contains($a, $b) || str_contains($b, $a)) {
            $resultado += 8;
        }

        return (int) min(round($resultado), 100);
    }

    public static function esCorreccionMenor(string $antes, string $despues): bool
    {
        $antesNormalizado = self::normalizar($antes);
        $despuesNormalizado = self::normalizar($despues);

        if ($antesNormalizado === $despuesNormalizado) {
            return true;
        }

        return self::calcularSimilitud($antesNormalizado, $despuesNormalizado) >= 92;
    }

    private static function mejorCoincidenciaCatalogo(string $normalizado): ?array
    {
        $mejor = null;

        foreach (self::catalogo() as $sigla => $asignatura) {
            $similitudNombre = self::calcularSimilitud($normalizado, $asignatura['nombre']);
            $similitudPalabras = 0;

            foreach ($asignatura['palabras_clave'] as $palabraClave) {
                $claveNormalizada = self::normalizar($palabraClave);

                $similitudPalabras = max(
                    $similitudPalabras,
                    self::calcularSimilitud($normalizado, $claveNormalizada)
                );

                if (
                    mb_strlen($normalizado) >= 4
                    && (
                        str_contains($normalizado, $claveNormalizada)
                        || str_contains($claveNormalizada, $normalizado)
                    )
                ) {
                    $similitudPalabras = max($similitudPalabras, 92);
                }
            }

            $confianza = max($similitudNombre, $similitudPalabras);

            if (mb_strtoupper($normalizado) === mb_strtoupper($sigla)) {
                $confianza = 100;
            }

            if (! $mejor || $confianza > $mejor['confianza']) {
                $mejor = [
                    'sigla' => $sigla,
                    'confianza' => $confianza,
                ];
            }
        }

        if (! $mejor || $mejor['confianza'] < self::MIN_SIMILITUD_CATALOGO) {
            return null;
        }

        return $mejor;
    }

    private static function coincideConAsignatura(string $normalizado, array $asignatura): bool
    {
        if (self::calcularSimilitud($normalizado, $asignatura['nombre']) >= 88) {
            return true;
        }

        foreach ($asignatura['palabras_clave'] ?? [] as $palabraClave) {
            $claveNormalizada = self::normalizar($palabraClave);

            if ($normalizado === $claveNormalizada) {
                return true;
            }

            if (
                mb_strlen($normalizado) >= 4
                && (
                    str_contains($normalizado, $claveNormalizada)
                    || str_contains($claveNormalizada, $normalizado)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private static function puntajeAcademico(string $normalizado): int
    {
        $puntaje = 0;

        $palabrasAcademicasFuertes = [
            'matematica',
            'fisica',
            'quimica',
            'biologia',
            'lenguaje',
            'comunicacion',
            'historia',
            'geografia',
            'filosofia',
            'psicologia',
            'programacion',
            'informatica',
            'electronica',
            'robotica',
            'contabilidad',
            'finanzas',
            'emprendimiento',
            'investigacion',
            'estadistica',
            'dibujo',
            'mecanica',
            'construccion',
            'gastronomia',
            'textiles',
            'carpinteria',
            'belleza',
            'ambiente',
            'tecnologia',
            'datos',
            'base',
            'ingles',
            'musica',
            'artes',
            'inteligencia',
            'software',
            'diseno',
            'salud',
            'nutricion',
        ];

        foreach ($palabrasAcademicasFuertes as $palabra) {
            if (str_contains($normalizado, self::normalizar($palabra))) {
                $puntaje += 25;
            }
        }

        $cantidadPalabras = count(array_filter(explode(' ', $normalizado)));

        if ($cantidadPalabras >= 2) {
            $puntaje += 15;
        }

        if (mb_strlen($normalizado) >= 8) {
            $puntaje += 10;
        }

        if (preg_match('/^[a-z0-9\s]+$/u', $normalizado)) {
            $puntaje += 5;
        }

        if (self::entradaClaramenteInvalida($normalizado)) {
            $puntaje -= 70;
        }

        return max(0, min($puntaje, 100));
    }

    private static function entradaClaramenteInvalida(string $normalizado): bool
    {
        $normalizado = trim($normalizado);

        if ($normalizado === '') {
            return true;
        }

        if (mb_strlen($normalizado) < 3) {
            return true;
        }

        if (preg_match('/^(.)\1{3,}$/u', $normalizado)) {
            return true;
        }

        if (preg_match('/^[0-9\s]+$/u', $normalizado)) {
            return true;
        }

        $invalidas = [
            'random',
            'cualquier cosa',
            'cualquiera',
            'nose',
            'no se',
            'prueba',
            'test',
            'testing',
            'asd',
            'asdf',
            'qwerty',
            'inventado',
            'inventada',
            'dragones',
            'magia',
            'magico',
            'juego',
            'minecraft',
            'free fire',
            'tiktok',
            'materia random',
            'materia cualquiera',
            'sin nombre',
            'nada',
            'xxx',
        ];

        foreach ($invalidas as $invalida) {
            if (str_contains($normalizado, self::normalizar($invalida))) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATEO
    |--------------------------------------------------------------------------
    */

    private static function formatearNombreAcademico(string $nombre): string
    {
        $nombre = self::formatearNombre($nombre);

        $reemplazosProfesionales = [
            'Tics' => 'Tecnologías de Información y Comunicación',
            'Tic' => 'Tecnologías de Información y Comunicación',
            'Compus' => 'Computación',
            'Computadoras' => 'Informática',
            'Programacion' => 'Programación',
            'Electronica' => 'Electrónica',
            'Robotica' => 'Robótica',
            'Fisica' => 'Física',
            'Quimica' => 'Química',
            'Matematica' => 'Matemática',
            'Biologia' => 'Biología',
            'Estadistica' => 'Estadística',
            'Investigacion' => 'Investigación',
            'Mecanica' => 'Mecánica',
            'Gastronomia' => 'Gastronomía',
            'Ingles' => 'Inglés',
            'Diseno' => 'Diseño',
            'Automatizacion' => 'Automatización',
        ];

        foreach ($reemplazosProfesionales as $buscar => $reemplazar) {
            $nombre = str_replace($buscar, $reemplazar, $nombre);
        }

        return trim($nombre);
    }

    private static function formatearNombre(string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre) ?? '');

        if ($nombre === '') {
            return '';
        }

        $minusculas = mb_strtolower($nombre);

        $palabrasMenores = [
            'de',
            'del',
            'la',
            'las',
            'el',
            'los',
            'y',
            'en',
            'para',
            'con',
            'a',
        ];

        $partes = explode(' ', $minusculas);

        $partes = array_map(function (string $palabra) use ($palabrasMenores) {
            if (in_array($palabra, $palabrasMenores, true)) {
                return $palabra;
            }

            return mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
        }, $partes);

        return implode(' ', $partes);
    }

    private static function extraerPalabrasClave(string $nombre): array
    {
        return self::palabrasSignificativas(self::normalizar($nombre));
    }

    private static function palabrasSignificativas(string $normalizado): array
    {
        $palabrasIgnoradas = [
            'de',
            'del',
            'la',
            'las',
            'el',
            'los',
            'y',
            'en',
            'para',
            'con',
            'a',
            'por',
            'al',
            'un',
            'una',
            'unos',
            'unas',
        ];

        return array_values(array_filter(
            explode(' ', $normalizado),
            fn(string $palabra) => mb_strlen($palabra) >= 3 && ! in_array($palabra, $palabrasIgnoradas, true)
        ));
    }

    private static function contieneAlguna(string $texto, array $palabras): bool
    {
        $texto = self::normalizar($texto);

        foreach ($palabras as $palabra) {
            if (str_contains($texto, self::normalizar($palabra))) {
                return true;
            }
        }

        return false;
    }

    public static function mensajeSoporte(): string
    {
        return 'No se pudo descifrar la entrada como una asignatura académica válida. Revisa la redacción o contacta con soporte académico al ' . self::TELEFONO_SOPORTE . ' para validar la creación de una nueva materia.';
    }
}
