<?php

namespace App\Support\Academico;

use App\Models\EspecialidadTecnica;
use App\Support\CatalogoInteligenteBase;

class EspecialidadTecnicaInteligente extends CatalogoInteligenteBase
{
    public const ESTADO_RECONOCIDA = 'RECONOCIDA';
    public const ESTADO_REDACTABLE = 'REDACTABLE';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';
    public const ESTADO_DUPLICADA = 'DUPLICADA';
    public const ESTADO_INCOMPLETA = 'INCOMPLETA';

    public static function catalogo(): array
    {
        return [
            'Sistemas Informáticos' => [
                'nombre_formal' => 'Sistemas Informáticos',
                'sigla' => 'SYS',
                'familia_profesional' => 'Tecnología y Computación',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias para el desarrollo de software, mantenimiento de hardware, redes de datos y administración de sistemas informáticos.',
                'competencias_tecnicas' => ['Desarrollo de software', 'Mantenimiento de computadoras', 'Diseño de bases de datos', 'Configuración de redes'],
                'habilidades_practicas' => ['Ensamblaje de hardware', 'Cableado estructurado', 'Instalación de sistemas operativos'],
                'habilidades_cognitivas' => ['Pensamiento lógico', 'Resolución de algoritmos', 'Abstracción de datos'],
                'asignaturas_relacionadas' => ['Matemática', 'Programación', 'Base de Datos', 'Robótica Educativa'],
                'carreras_relacionadas' => ['Ingeniería de Sistemas', 'Ingeniería de Software', 'Ciencia de Datos', 'Ciberseguridad', 'Telecomunicaciones'],
                'areas_profesionales' => ['Tecnología de la información', 'Desarrollo web y móvil', 'Soporte técnico de redes'],
                'perfil_riasec' => 'Investigativo / Realista',
                'inteligencias_relacionadas' => ['Lógico-Matemática', 'Espacial'],
                'actividades_compatibles' => ['Programación en código', 'Modelado lógico', 'Diagnóstico de hardware'],
                'palabras_clave' => ['computacion', 'computadora', 'programas', 'sistemas', 'software', 'hardware', 'informatica', 'codigo', 'web'],
                'sinonimos' => ['compus', 'computacion', 'programas', 'programacion', 'informatica'],
                'errores_comunes' => ['sistemas informaticos', 'sistemas', 'computadoras'],
                'nivel_tecnico' => 90,
                'nivel_productivo' => 85,
                'nivel_tecnologico' => 95,
                'nivel_social' => 70,
                'nivel_artistico' => 60,
                'nivel_administrativo' => 75,
                'explicacion_vocacional' => 'Orientado al diseño, construcción y mantenimiento de ecosistemas tecnológicos e informáticos aplicados.',
                'acciones_recomendadas' => ['Realizar cursos en programación web', 'Participar en olimpiadas de robótica', 'Obtener certificaciones en redes CISCO'],
            ],
            'Gastronomía' => [
                'nombre_formal' => 'Gastronomía',
                'sigla' => 'GAS',
                'familia_profesional' => 'Turismo y Servicios alimentarios',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Servicios',
                'descripcion_institucional' => 'Forma competencias culinarias profesionales, técnicas de repostería, inocuidad alimentaria, costos y gestión de restaurantes.',
                'competencias_tecnicas' => ['Técnicas culinarias básicas y avanzadas', 'Reposteria y panaderia', 'Higiene y manipulación de alimentos', 'Costos de menú'],
                'habilidades_practicas' => ['Uso de cuchillos', 'Elaboración de masas', 'Decoración de platos', 'Manejo de hornos'],
                'habilidades_cognitivas' => ['Planificación de menús', 'Creatividad sensorial', 'Cálculo de costos alimentarios'],
                'asignaturas_relacionadas' => ['Química', 'Gastronomía y Nutrición', 'Emprendimiento Productivo'],
                'carreras_relacionadas' => ['Gastronomía', 'Administración Hotelera', 'Nutrición y Dietética', 'Turismo'],
                'areas_profesionales' => ['Culinaria profesional', 'Panadería y repostería', 'Gestión de restaurantes y catering'],
                'perfil_riasec' => 'Artístico / Emprendedor',
                'inteligencias_relacionadas' => ['Cinestésico-Corporal', 'Interpersonal'],
                'actividades_compatibles' => ['Preparación de recetas', 'Diseño de platos artísticos', 'Cálculo de costos culinarios'],
                'palabras_clave' => ['cocina', 'cocinar', 'chef', 'alimentos', 'comida', 'reposteria', 'panaderia', 'restaurante'],
                'sinonimos' => ['chef', 'cocina', 'gastronomia y nutricion', 'reposteria'],
                'errores_comunes' => ['gastronimia', 'gastronomia y alimentacion'],
                'nivel_tecnico' => 85,
                'nivel_productivo' => 90,
                'nivel_tecnologico' => 60,
                'nivel_social' => 80,
                'nivel_artistico' => 85,
                'nivel_administrativo' => 70,
                'explicacion_vocacional' => 'Orientado a la expresión artística culinaria y administración eficiente de servicios alimentarios seguros e innovadores.',
                'acciones_recomendadas' => ['Desarrollar un recetario propio de cocina fusión', 'Tomar talleres de inocuidad alimentaria', 'Iniciar un pequeño emprendimiento de postres'],
            ],
            'Contabilidad' => [
                'nombre_formal' => 'Contabilidad',
                'sigla' => 'CON',
                'familia_profesional' => 'Administración y Finanzas',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Administración',
                'descripcion_institucional' => 'Forma competencias para el registro de transacciones comerciales, análisis de estados financieros y cumplimiento tributario.',
                'competencias_tecnicas' => ['Registro de asientos contables', 'Elaboración de estados financieros', 'Cálculo de impuestos', 'Gestión de planillas de sueldos'],
                'habilidades_practicas' => ['Manejo de hojas de cálculo (Excel)', 'Uso de software contable', 'Archivado ordenado de facturas'],
                'habilidades_cognitivas' => ['Análisis numérico', 'Atención al detalle', 'Comprensión normativa tributaria'],
                'asignaturas_relacionadas' => ['Matemática', 'Educación Financiera y Contable', 'Emprendimiento Productivo'],
                'carreras_relacionadas' => ['Contaduría Pública', 'Administración de Empresas', 'Economía', 'Finanzas', 'Auditoría'],
                'areas_profesionales' => ['Contabilidad general', 'Tributación e impuestos', 'Auditoría interna'],
                'perfil_riasec' => 'Convencional / Emprendedor',
                'inteligencias_relacionadas' => ['Lógico-Matemática'],
                'actividades_compatibles' => ['Balance de libros', 'Auditoría de facturas', 'Elaboración de presupuestos'],
                'palabras_clave' => ['conta', 'administracion', 'impuestos', 'finanzas', 'cuentas', 'costos', 'presupuesto', 'libros diario'],
                'sinonimos' => ['conta', 'contaduria', 'administracion y finanzas'],
                'errores_comunes' => ['contabilidad general', 'conta general'],
                'nivel_tecnico' => 80,
                'nivel_productivo' => 75,
                'nivel_tecnologico' => 70,
                'nivel_social' => 65,
                'nivel_artistico' => 50,
                'nivel_administrativo' => 95,
                'explicacion_vocacional' => 'Orientado a la estructuración financiera y control contable preciso de empresas y proyectos productivos.',
                'acciones_recomendadas' => ['Dominar Excel avanzado', 'Aprender el uso de sistemas ERP contables', 'Realizar prácticas simuladas de declaraciones juradas'],
            ],
            'Construcción Civil' => [
                'nombre_formal' => 'Construcción Civil',
                'sigla' => 'CIV',
                'familia_profesional' => 'Edificación e Infraestructura',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias para la interpretación de planos, cálculo de materiales, albañilería básica y control de calidad en obras de edificación.',
                'competencias_tecnicas' => ['Interpretación de planos arquitectónicos y estructurales', 'Cálculo de volúmenes de obra', 'Técnicas de albañilería e instalaciones sanitarias', 'Seguridad en obra'],
                'habilidades_practicas' => ['Uso de herramientas manuales de obra', 'Mezclas de morteros', 'Replanteo en terreno'],
                'habilidades_cognitivas' => ['Razonamiento espacial', 'Planificación de etapas constructivas', 'Prevención de riesgos'],
                'asignaturas_relacionadas' => ['Matemática', 'Física', 'Dibujo Técnico', 'Construcción Civil'],
                'carreras_relacionadas' => ['Ingeniería Civil', 'Arquitectura', 'Construcción Civil', 'Topografía', 'Diseño de Interiores'],
                'areas_profesionales' => ['Supervisor de obras civiles', 'Dibujante técnico de planos', 'Contratista de obras menores'],
                'perfil_riasec' => 'Realista / Investigativo',
                'inteligencias_relacionadas' => ['Espacial', 'Cinestésico-Corporal'],
                'actividades_compatibles' => ['Lectura de planos AutoCAD', 'Supervisión de vaciados de hormigón', 'Cálculo de presupuestos de obra'],
                'palabras_clave' => ['construccion', 'obra', 'planos', 'albanileria', 'cemento', 'fierro', 'ingenieria civil', 'maqueta'],
                'sinonimos' => ['construccion', 'construccion civil', 'obras civiles'],
                'errores_comunes' => ['construcción', 'construciones civiles'],
                'nivel_tecnico' => 88,
                'nivel_productivo' => 90,
                'nivel_tecnologico' => 65,
                'nivel_social' => 70,
                'nivel_artistico' => 75,
                'nivel_administrativo' => 75,
                'explicacion_vocacional' => 'Orientado al desarrollo material de infraestructura bajo criterios técnicos rigurosos, seguros y eficientes.',
                'acciones_recomendadas' => ['Aprender AutoCAD o Revit básico', 'Visitar obras en ejecución supervisadas', 'Capacitarse en normas de seguridad y salud ocupacional'],
            ],
            'Electrónica' => [
                'nombre_formal' => 'Electrónica',
                'sigla' => 'ELT',
                'familia_profesional' => 'Electricidad y Automatización',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias en análisis de circuitos eléctricos, soldadura de placas electrónicas, uso de microcontroladores y automatización industrial básica.',
                'competencias_tecnicas' => ['Diseño y soldadura de circuitos impresos (PCB)', 'Programación de microcontroladores (Arduino)', 'Manejo de instrumentos de medición (Multímetro, osciloscopio)', 'Diagnóstico de fallas electrónicas'],
                'habilidades_practicas' => ['Soldadura con estaño', 'Armado de circuitos en protoboard', 'Cableado de tableros eléctricos'],
                'habilidades_cognitivas' => ['Análisis abstracto de circuitos', 'Pensamiento estructurado lógico', 'Deducción sistemática de fallas'],
                'asignaturas_relacionadas' => ['Física', 'Robótica Educativa', 'Electrónica Básica'],
                'carreras_relacionadas' => ['Ingeniería Electrónica', 'Mecatrónica', 'Ingeniería Eléctrica', 'Telecomunicaciones', 'Automatización'],
                'areas_profesionales' => ['Soporte de equipos electrónicos', 'Automatización industrial y robótica', 'Diseño de hardware IoT'],
                'perfil_riasec' => 'Realista / Investigativo',
                'inteligencias_relacionadas' => ['Lógico-Matemática', 'Espacial'],
                'actividades_compatibles' => ['Diseño de circuitos', 'Soldadura de componentes SMD', 'Programación de automatismos'],
                'palabras_clave' => ['electricidad', 'circuitos', 'cableado', 'sensores', 'soldar', 'arduino', 'chips', 'mantenimiento electronico'],
                'sinonimos' => ['electricidad', 'electronica basica', 'automatizacion'],
                'errores_comunes' => ['electronica', 'electricistas'],
                'nivel_tecnico' => 92,
                'nivel_productivo' => 85,
                'nivel_tecnologico' => 92,
                'nivel_social' => 60,
                'nivel_artistico' => 55,
                'nivel_administrativo' => 65,
                'explicacion_vocacional' => 'Orientado al análisis, diseño y control de la energía y señales eléctricas para la automatización e innovación tecnológica.',
                'acciones_recomendadas' => ['Construir prototipos robóticos con Arduino', 'Aprender diseño de circuitos impresos en software como EasyEDA', 'Realizar prácticas de soldadura segura'],
            ],
            'Textiles y Confecciones' => [
                'nombre_formal' => 'Textiles y Confecciones',
                'sigla' => 'TEX',
                'familia_profesional' => 'Diseño y Manufactura Textil',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias en diseño de modas, patronaje industrial, operación de máquinas de costura semi-industriales y control de calidad textil.',
                'competencias_tecnicas' => ['Patronaje y trazado de prendas', 'Operación de máquinas de coser industriales', 'Técnicas de ensamblado textil', 'Diseño básico de modas y fichas técnicas'],
                'habilidades_practicas' => ['Corte de telas', 'Hilvanado y costura recta', 'Mantenimiento preventivo de máquinas de coser'],
                'habilidades_cognitivas' => ['Apreciación del color y la textura', 'Geometría aplicada al patronaje', 'Creatividad estética'],
                'asignaturas_relacionadas' => ['Artes Plásticas y Visuales', 'Textiles y Confecciones', 'Emprendimiento Productivo'],
                'carreras_relacionadas' => ['Diseño de Modas', 'Diseño Textil', 'Ingeniería Textil', 'Administración de Empresas'],
                'areas_profesionales' => ['Diseñador y patronista independiente', 'Técnico de producción en talleres textiles', 'Control de calidad en industrias de confección'],
                'perfil_riasec' => 'Artístico / Realista',
                'inteligencias_relacionadas' => ['Espacial', 'Cinestésico-Corporal'],
                'actividades_compatibles' => ['Diseño de figurines', 'Confección de uniformes o ropa de temporada', 'Cálculo de rendimientos de telas'],
                'palabras_clave' => ['costura', 'ropa', 'telas', 'moda', 'patrones', 'maquina de coser', 'confeccion', 'sastreria'],
                'sinonimos' => ['costura', 'ropa', 'diseño de modas', 'confeccion'],
                'errores_comunes' => ['textil', 'textiles'],
                'nivel_tecnico' => 80,
                'nivel_productivo' => 90,
                'nivel_tecnologico' => 55,
                'nivel_social' => 75,
                'nivel_artistico' => 90,
                'nivel_administrativo' => 70,
                'explicacion_vocacional' => 'Orientado a la expresión estética y productiva de indumentaria textil con calidad comercial.',
                'acciones_recomendadas' => ['Presentar una mini-colección de prendas recicladas', 'Aprender técnicas de patronaje digital', 'Estructurar costos detallados de confección'],
            ],
            'Mecánica Industrial' => [
                'nombre_formal' => 'Mecánica Industrial',
                'sigla' => 'MCI',
                'familia_profesional' => 'Metalmecánica y Manufactura',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias en el mecanizado por arranque de viruta (torno, fresadora), soldadura por arco eléctrico, mantenimiento de plantas industriales y seguridad metalmecánica.',
                'competencias_tecnicas' => ['Operación de tornos paralelos y fresadoras', 'Soldadura por arco y oxiacetilénica', 'Lectura de planos mecánicos y tolerancias', 'Mantenimiento electromecánico industrial'],
                'habilidades_practicas' => ['Torneado de ejes', 'Afilado de herramientas', 'Soldadura de perfiles de acero'],
                'habilidades_cognitivas' => ['Precisión matemática milimétrica', 'Planificación del orden de mecanizado', 'Diagnóstico de desgaste de componentes'],
                'asignaturas_relacionadas' => ['Física', 'Dibujo Técnico', 'Mecánica Industrial'],
                'carreras_relacionadas' => ['Ingeniería Mecánica', 'Ingeniería Industrial', 'Electromecánica', 'Metalurgia'],
                'areas_profesionales' => ['Operador metalmecánico especializado', 'Técnico de soldadura estructural', 'Mantenimiento de maquinaria industrial'],
                'perfil_riasec' => 'Realista / Investigativo',
                'inteligencias_relacionadas' => ['Espacial', 'Cinestésico-Corporal'],
                'actividades_compatibles' => ['Mecanizado de piezas de repuesto', 'Estructuras soldadas complejas', 'Mantenimiento preventivo de engranajes'],
                'palabras_clave' => ['metal', 'soldadura', 'maquinas', 'herramientas', 'torno', 'fresadora', 'mecanizado', 'planta industrial'],
                'sinonimos' => ['torno', 'soldador', 'metalmecanica', 'soldadura'],
                'errores_comunes' => ['mecanico industrial', 'mecanicos industriales'],
                'nivel_tecnico' => 92,
                'nivel_productivo' => 90,
                'nivel_tecnologico' => 75,
                'nivel_social' => 55,
                'nivel_artistico' => 60,
                'nivel_administrativo' => 65,
                'explicacion_vocacional' => 'Orientado al modelado y mantenimiento físico de componentes metálicos y sistemas de producción mecánica pesada.',
                'acciones_recomendadas' => ['Aprender modelado 3D mecánico en SolidWorks', 'Capacitarse en soldadura calificada', 'Realizar prácticas en talleres metalmecánicos homologados'],
            ],
            'Mecánica Automotriz' => [
                'nombre_formal' => 'Mecánica Automotriz',
                'sigla' => 'MCA',
                'familia_profesional' => 'Mantenimiento de Vehículos',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias en diagnóstico y mantenimiento de motores de combustión interna, sistemas de transmisión, dirección, suspensión, frenos y electricidad automotriz.',
                'competencias_tecnicas' => ['Mantenimiento preventivo y correctivo de motores', 'Diagnóstico de sensores y actuadores por escáner', 'Reparación de sistemas de frenos ABS', 'Instalación de circuitos eléctricos del vehículo'],
                'habilidades_practicas' => ['Desarmado y armado de bloques de motor', 'Uso de escáner de diagnóstico OBD-II', 'Medición de compresión de cilindros'],
                'habilidades_cognitivas' => ['Deducción lógica de fallas mediante diagramas eléctricos', 'Análisis termodinámico de combustión', 'Interpretación de códigos de error de computadoras automotrices'],
                'asignaturas_relacionadas' => ['Física', 'Mecánica Automotriz', 'Robótica Educativa'],
                'carreras_relacionadas' => ['Ingeniería Automotriz', 'Ingeniería Mecánica', 'Mecatrónica', 'Electromecánica'],
                'areas_profesionales' => ['Técnico diagnosticador en concesionarias', 'Jefe de taller de servicio automotriz', 'Especialista en sistemas de inyección electrónica'],
                'perfil_riasec' => 'Realista / Investigativo',
                'inteligencias_relacionadas' => ['Lógico-Matemática', 'Cinestésico-Corporal'],
                'actividades_compatibles' => ['Ajuste de motores', 'Diagnóstico de inyección electrónica', 'Mantenimiento de cajas de transmisión'],
                'palabras_clave' => ['auto', 'motores', 'vehiculo', 'frenos', 'inyeccion', 'escaner', 'taller automotriz', 'automotores'],
                'sinonimos' => ['autos', 'vehiculos', 'taller de autos', 'mecanico'],
                'errores_comunes' => ['mecanica automotris', 'mecanico automotriz'],
                'nivel_tecnico' => 90,
                'nivel_productivo' => 88,
                'nivel_tecnologico' => 85,
                'nivel_social' => 70,
                'nivel_artistico' => 50,
                'nivel_administrativo' => 70,
                'explicacion_vocacional' => 'Orientado al diagnóstico resolutivo y mantenimiento preventivo-correctivo de sistemas de propulsión y seguridad automotriz.',
                'acciones_recomendadas' => ['Aprender diagnóstico de vehículos híbridos y eléctricos', 'Estudiar diagramas de inyección de marcas líderes', 'Participar en prácticas de desarmado de motores reales'],
            ],
            'Carpintería' => [
                'nombre_formal' => 'Carpintería',
                'sigla' => 'CAR',
                'familia_profesional' => 'Diseño y Procesamiento de la Madera',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Tecnología',
                'descripcion_institucional' => 'Forma competencias en el diseño de muebles, corte y maquinado de madera y tableros derivados, técnicas de ensamblado, acabados y lacados.',
                'competencias_tecnicas' => ['Diseño de mobiliario ergonómico', 'Operación de sierras circulares, tupíes y garlopas', 'Técnicas de ensamble clásico y moderno', 'Procesos de pulido y aplicación de lacas/barnices'],
                'habilidades_practicas' => ['Corte de tableros de melamina y madera sólida', 'Tallado básico decorativo', 'Instalación de bisagras y correderas de cajón'],
                'habilidades_cognitivas' => ['Cálculo y optimización de planos de corte', 'Apreciación de la veta y consistencia de maderas', 'Diseño tridimensional conceptual'],
                'asignaturas_relacionadas' => ['Dibujo Técnico', 'Carpintería y Diseño en Madera', 'Emprendimiento Productivo'],
                'carreras_relacionadas' => ['Diseño Industrial', 'Diseño de Interiores', 'Arquitectura', 'Ingeniería Forestal'],
                'areas_profesionales' => ['Diseñador de muebles independiente', 'Carpintero artesanal e industrial', 'Instalador de acabados en madera'],
                'perfil_riasec' => 'Realista / Artístico',
                'inteligencias_relacionadas' => ['Espacial', 'Cinestésico-Corporal'],
                'actividades_compatibles' => ['Construcción de escritorios u organizadores', 'Restauración de muebles antiguos', 'Cálculo de presupuestos de carpintería'],
                'palabras_clave' => ['madera', 'muebles', 'ebanisteria', 'sierra', 'cepillo', 'melamina', 'barniz', 'lijado'],
                'sinonimos' => ['muebles', 'carpinteria y diseño en madera', 'ebanisteria'],
                'errores_comunes' => ['carpintería de madera', 'madera ebanisteria'],
                'nivel_tecnico' => 82,
                'nivel_productivo' => 88,
                'nivel_tecnologico' => 60,
                'nivel_social' => 60,
                'nivel_artistico' => 88,
                'nivel_administrativo' => 65,
                'explicacion_vocacional' => 'Orientado a la transformación creativa y técnica de la madera sólida y tableros en piezas funcionales y artísticas.',
                'acciones_recomendadas' => ['Diseñar y construir un mueble modular moderno', 'Capacitarse en el uso de herramientas eléctricas inalámbricas', 'Aprender técnicas de optimización de cortes mediante software'],
            ],
            'Belleza Integral' => [
                'nombre_formal' => 'Belleza Integral',
                'sigla' => 'BEI',
                'familia_profesional' => 'Estética y Cuidado Personal',
                'campo_formativo' => 'Ciencia, Tecnología y Producción',
                'area_bth' => 'Servicios',
                'descripcion_institucional' => 'Forma competencias en peluquería, maquillaje profesional, cosmetología básica, cuidado de uñas, bioseguridad y gestión de salones de estética.',
                'competencias_tecnicas' => ['Cortes de cabello y tinturación', 'Maquillaje para eventos sociales', 'Manicura, pedicura y uñas acrílicas', 'Procedimientos de higiene facial y corporal con bioseguridad'],
                'habilidades_practicas' => ['Manejo de tijeras y navajas', 'Aplicación de bases y sombras', 'Uso seguro de esterilizadores y químicos cosméticos'],
                'habilidades_cognitivas' => ['Visagismo y análisis de facciones', 'Comprensión de reacciones químicas de tintes', 'Atención y empatía con clientes'],
                'asignaturas_relacionadas' => ['Química', 'Ciencias Biológicas', 'Belleza Integral', 'Emprendimiento Productivo'],
                'carreras_relacionadas' => ['Cosmetología y Estética Integral', 'Fisioterapia', 'Marketing', 'Química Cosmética'],
                'areas_profesionales' => ['Estilista profesional', 'Maquillador independiente o para medios', 'Gestor o propietario de salón de belleza'],
                'perfil_riasec' => 'Artístico / Emprendedor',
                'inteligencias_relacionadas' => ['Cinestésico-Corporal', 'Interpersonal'],
                'actividades_compatibles' => ['Cortes de tendencia', 'Asesoría de imagen facial', 'Tratamientos hidratantes de piel'],
                'palabras_clave' => ['maquillaje', 'peinados', 'cabello', 'peluqueria', 'uñas', 'estetica', 'cosmetologia', 'salón de belleza'],
                'sinonimos' => ['peluqueria', 'estetica', 'maquillaje', 'belleza integral'],
                'errores_comunes' => ['belleza', 'peluquerias'],
                'nivel_tecnico' => 80,
                'nivel_productivo' => 88,
                'nivel_tecnologico' => 50,
                'nivel_social' => 88,
                'nivel_artistico' => 90,
                'nivel_administrativo' => 72,
                'explicacion_vocacional' => 'Orientado a la mejora estética y cuidado personal saludable mediante técnicas de cosmetología y estilismo profesional.',
                'acciones_recomendadas' => ['Aprender colorimetría capilar avanzada', 'Diseñar un plan de bioseguridad sanitaria para un salón', 'Realizar portafolio de fotos de peinados y maquillajes propios'],
            ],
        ];
    }

    public static function contarFamiliasRegistradas(): int
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('especialidad_tecnica', 'fam_pro_esp')) {
            $familiasDB = EspecialidadTecnica::where('est_esp', 'ACTIVO')
                ->whereNotNull('fam_pro_esp')
                ->where('fam_pro_esp', '!=', '')
                ->distinct()
                ->pluck('fam_pro_esp')
                ->toArray();
            if (!empty($familiasDB)) {
                return count($familiasDB);
            }
        }

        $catalogo = self::catalogo();
        $familias = [];
        $registros = EspecialidadTecnica::where('est_esp', 'ACTIVO')->pluck('nom_esp');
        
        foreach ($registros as $nomEsp) {
            foreach ($catalogo as $nombreFormal => $meta) {
                similar_text(mb_strtolower($nomEsp), mb_strtolower($nombreFormal), $sim);
                if ($sim >= 80) {
                    $familias[] = $meta['familia_profesional'];
                    break;
                }
            }
        }
        
        return count(array_unique($familias));
    }
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombreOriginal = trim((string) ($datos['nom_esp'] ?? ''));
        $descripcionOriginal = trim((string) ($datos['des_esp'] ?? ''));
        $estado = $datos['est_esp'] ?? 'ACTIVO';

        $nombreNormalizado = $this->normalizarEntrada($nombreOriginal);
        $completitud = $this->completitud($datos, ['nom_esp', 'des_esp']);

        if ($nombreNormalizado === '') {
            return $this->respuestaBase(
                valido: false,
                datos: ['nom_esp' => '', 'des_esp' => $descripcionOriginal, 'est_esp' => $estado],
                estadoInteligente: 'INCOMPLETA',
                mensaje: 'El nombre de la especialidad no puede estar vacío.',
                confianza: 0,
                validezAcademica: 0,
                puedeGuardar: false
            );
        }

        // Check if the input is trash / invalid
        if ($this->detectarEntradaInvalida($nombreNormalizado)) {
            return $this->respuestaBase(
                valido: false,
                datos: ['nom_esp' => $nombreNormalizado, 'des_esp' => $descripcionOriginal, 'est_esp' => $estado],
                estadoInteligente: 'BLOQUEADA',
                mensaje: 'No se reconoció una especialidad técnica BTH válida.',
                confianza: 5,
                validezAcademica: 0,
                bloqueos: ['El nombre ingresado no coincide con el catálogo de especialidades BTH.'],
                puedeGuardar: false
            );
        }

        // Check duplicates in database
        $duplicidad = $this->detectarDuplicidad($nombreNormalizado, $ignorarCodigo);
        $coincidencias = [];
        if ($duplicidad['exacto'] || $duplicidad['aproximado_critico'] || $duplicidad['aproximado_leve']) {
            $coincidencias[] = [
                'codigo' => $duplicidad['registro']['codigo'] ?? null,
                'nombre' => $duplicidad['registro']['nombre'] ?? '',
                'similitud' => $duplicidad['similitud'] ?? 0,
                'exacto' => $duplicidad['exacto'],
            ];
        }

        // Catalog lookup
        $mejorCatalogo = $this->buscarMejorCoincidenciaCatalogo($nombreNormalizado);
        $maxSim = $mejorCatalogo ? $mejorCatalogo['similitud'] : 0;

        $datosFinales = [
            'nom_esp' => ($mejorCatalogo && $maxSim >= 80) ? $mejorCatalogo['nombre_formal'] : $nombreNormalizado,
            'des_esp' => $descripcionOriginal ?: (($mejorCatalogo && $maxSim >= 60) ? $mejorCatalogo['descripcion_institucional'] : "Especialidad técnica orientada al desarrollo de competencias aplicadas en {$nombreNormalizado}."),
            'est_esp' => $estado,
        ];

        $advertencias = [];
        $bloqueos = [];
        $puedeGuardar = true;
        $explicacion = '';
        $accionesRecomendadas = [];
        $visualizacion = [];
        $validezAcademica = 0;
        $estadoInteligente = 'REQUIERE_REVISION';
        $confianza = $this->calcularConfianza($nombreNormalizado, ['similitud' => $maxSim]);

        // Classification based on confidence level
        if ($confianza < 25) {
            $estadoInteligente = 'BLOQUEADA';
            $validezAcademica = 0;
            $puedeGuardar = false;
            $bloqueos[] = 'No se reconoció una especialidad técnica BTH válida.';
            $advertencias[] = 'Sugerencia: Escriba una especialidad BTH reconocible (por ejemplo: Sistemas Informáticos, Gastronomía, Contabilidad, etc.).';
            $explicacion = 'Entrada indescifrable o sin coincidencia en el catálogo de especialidades BTH.';
            $visualizacion = [
                'mostrar_mapa_completo' => false,
                'mostrar_vista_previa_limitada' => false,
                'perfil_riasec' => null,
                'campo_formativo' => null,
                'area_bth' => null,
                'familia_profesional' => null,
                'carreras_relacionadas' => [],
                'asignaturas_relacionadas' => [],
                'competencias_tecnicas' => [],
                'habilidades_practicas' => [],
                'habilidades_cognitivas' => [],
                'areas_profesionales' => [],
                'inteligencias_relacionadas' => [],
                'actividades_compatibles' => [],
                'niveles' => [],
                'color_hex' => '#dc2626',
            ];
        } elseif ($confianza >= 25 && $confianza <= 59) {
            $estadoInteligente = 'REQUIERE_REVISION';
            $validezAcademica = 30;
            $puedeGuardar = true;
            $advertencias[] = 'La especialidad requiere revisión por la dirección. Posible coincidencia baja con: "' . ($mejorCatalogo['nombre_formal'] ?? '') . '".';
            $explicacion = 'Posible relación con ' . ($mejorCatalogo['nombre_formal'] ?? '') . ', pero con nivel de confianza bajo.';
            $visualizacion = [
                'mostrar_mapa_completo' => false,
                'mostrar_vista_previa_limitada' => false,
                'perfil_riasec' => null,
                'campo_formativo' => null,
                'area_bth' => null,
                'familia_profesional' => null,
                'carreras_relacionadas' => [],
                'asignaturas_relacionadas' => [],
                'competencias_tecnicas' => [],
                'habilidades_practicas' => [],
                'habilidades_cognitivas' => [],
                'areas_profesionales' => [],
                'inteligencias_relacionadas' => [],
                'actividades_compatibles' => [],
                'niveles' => [],
                'color_hex' => '#d97706',
            ];
        } elseif ($confianza >= 60 && $confianza <= 79) {
            $estadoInteligente = 'REDACTABLE';
            $validezAcademica = 70;
            $puedeGuardar = true;
            $advertencias[] = 'Coincidencia probable. Se sugiere aplicar la corrección a la especialidad formal: "' . ($mejorCatalogo['nombre_formal'] ?? '') . '".';
            $explicacion = 'Coincidencia probable. Se muestra vista previa limitada académica-vocacional.';
            $visualizacion = [
                'mostrar_mapa_completo' => false,
                'mostrar_vista_previa_limitada' => true,
                'perfil_riasec' => $mejorCatalogo['perfil_riasec'] ?? '',
                'campo_formativo' => $mejorCatalogo['campo_formativo'] ?? '',
                'area_bth' => $mejorCatalogo['area_bth'] ?? '',
                'familia_profesional' => $mejorCatalogo['familia_profesional'] ?? '',
                'carreras_relacionadas' => [],
                'asignaturas_relacionadas' => $mejorCatalogo['asignaturas_relacionadas'] ?? [],
                'competencias_tecnicas' => [],
                'habilidades_practicas' => [],
                'habilidades_cognitivas' => [],
                'areas_profesionales' => [],
                'inteligencias_relacionadas' => [],
                'actividades_compatibles' => [],
                'niveles' => [],
                'color_hex' => $this->colorEspecialidad($mejorCatalogo['sigla'] ?? 'GEN'),
            ];
        } else {
            // confianza >= 80
            $estadoInteligente = 'RECONOCIDA';
            $validezAcademica = 100;
            $puedeGuardar = true;
            $explicacion = $mejorCatalogo['explicacion_vocacional'] ?? '';
            $accionesRecomendadas = $mejorCatalogo['acciones_recomendadas'] ?? [];
            $visualizacion = [
                'mostrar_mapa_completo' => true,
                'mostrar_vista_previa_limitada' => false,
                'perfil_riasec' => $mejorCatalogo['perfil_riasec'] ?? '',
                'campo_formativo' => $mejorCatalogo['campo_formativo'] ?? '',
                'area_bth' => $mejorCatalogo['area_bth'] ?? '',
                'familia_profesional' => $mejorCatalogo['familia_profesional'] ?? '',
                'carreras_relacionadas' => $mejorCatalogo['carreras_relacionadas'] ?? [],
                'asignaturas_relacionadas' => $mejorCatalogo['asignaturas_relacionadas'] ?? [],
                'competencias_tecnicas' => $mejorCatalogo['competencias_tecnicas'] ?? [],
                'habilidades_practicas' => $mejorCatalogo['habilidades_practicas'] ?? [],
                'habilidades_cognitivas' => $mejorCatalogo['habilidades_cognitivas'] ?? [],
                'areas_profesionales' => $mejorCatalogo['areas_profesionales'] ?? [],
                'inteligencias_relacionadas' => $mejorCatalogo['inteligencias_relacionadas'] ?? [],
                'actividades_compatibles' => $mejorCatalogo['actividades_compatibles'] ?? [],
                'niveles' => [
                    'Técnico' => $mejorCatalogo['nivel_tecnico'] ?? 50,
                    'Productivo' => $mejorCatalogo['nivel_productivo'] ?? 50,
                    'Tecnológico' => $mejorCatalogo['nivel_tecnologico'] ?? 50,
                    'Social' => $mejorCatalogo['nivel_social'] ?? 50,
                    'Artístico' => $mejorCatalogo['nivel_artistico'] ?? 50,
                    'Administrativo' => $mejorCatalogo['nivel_administrativo'] ?? 50,
                ],
                'color_hex' => $this->colorEspecialidad($mejorCatalogo['sigla'] ?? 'GEN'),
            ];
        }

        // Duplicate checks override
        if ($duplicidad['exacto']) {
            $bloqueos[] = 'Existe una especialidad con este nombre exacto o equivalente en el sistema.';
            $puedeGuardar = false;
            $estadoInteligente = 'DUPLICADA';
            $confianza = 100;
        } elseif ($duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe una especialidad críticamente similar registrada (' . $duplicidad['registro']['nombre'] . '). Evite duplicidades.';
            $puedeGuardar = false;
            $estadoInteligente = 'DUPLICADA';
            $confianza = (int) $duplicidad['similitud'];
        } elseif ($duplicidad['aproximado_leve']) {
            $advertencias[] = 'Existe una especialidad levemente similar registrada (' . $duplicidad['registro']['nombre'] . '). Verifique.';
        }

        $puedeMostrarMapa = ($estadoInteligente === 'RECONOCIDA');
        $orientacion = ($estadoInteligente === 'RECONOCIDA' && $mejorCatalogo) ? $this->construirOrientacion($mejorCatalogo) : null;

        return [
            'datos' => $datosFinales,
            'estado_inteligente' => $estadoInteligente,
            'confianza' => (int) $confianza,
            'validez_academica' => $validezAcademica,
            'completitud_formulario' => $completitud,
            'puede_guardar' => $puedeGuardar,
            'puede_mostrar_mapa' => $puedeMostrarMapa,
            'duplicidad' => $duplicidad,
            'coincidencias' => $coincidencias,
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => $mejorCatalogo ? [$mejorCatalogo['nombre_formal']] : [],
            'ejemplos_validos' => self::ejemplosValidos(),
            'mensaje' => empty($bloqueos) ? 'Análisis completado.' : implode(' ', $bloqueos),
            'explicacion' => $explicacion,
            'orientacion' => $orientacion,
            'visualizacion' => $visualizacion,
        ];
    }

    public function normalizarEntrada(string $texto): string
    {
        return $this->normalizarTexto($texto);
    }

    public function detectarEntradaInvalida(string $texto): bool
    {
        $texto = trim($texto);
        if ($texto === '') return true;
        if (mb_strlen($texto) < 3) return true;
        
        $canonico = $this->canonico($texto);
        $invalidas = ['prueba', 'test', 'testing', 'dummy', 'fake', 'simulado', 'inventado', 'asd', 'qwerty'];
        foreach ($invalidas as $invalida) {
            if (str_contains($canonico, $invalida)) {
                return true;
            }
        }
        
        $vowelCount = preg_match_all('/[aeiouáéíóúü]/i', $texto);
        $letterCount = preg_match_all('/[a-zA-Z]/', $texto);
        if ($letterCount > 6 && $vowelCount === 0) {
            return true;
        }
        if ($letterCount > 8 && ($vowelCount / $letterCount) < 0.15) {
            return true;
        }
        
        $mejor = $this->buscarMejorCoincidenciaCatalogo($texto);
        if (!$mejor || ($mejor['similitud'] ?? 0) <= 25) {
            if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/i', $texto)) {
                return true;
            }
        }
        
        return false;
    }

    public function buscarCoincidenciaCatalogo(string $texto): array
    {
        $mejor = $this->buscarMejorCoincidenciaCatalogo($texto);
        return $mejor ?: [];
    }

    public function calcularConfianza(string $texto, array $candidatos): int
    {
        if ($this->detectarEntradaInvalida($texto)) {
            return 0;
        }
        return $candidatos['similitud'] ?? 0;
    }

    public function detectarDuplicidad(string $nombre, ?string $ignorarCodigo = null): array
    {
        return $this->analizarDuplicidad($this->normalizarTexto($nombre), EspecialidadTecnica::all(), $ignorarCodigo);
    }

    public function construirOrientacion(array $catalogo): array
    {
        return [
            'perfil_riasec' => $catalogo['perfil_riasec'] ?? 'Realista',
            'carreras_relacionadas' => $catalogo['carreras_relacionadas'] ?? [],
            'asignaturas_relacionadas' => $catalogo['asignaturas_relacionadas'] ?? [],
            'explicacion_vocacional' => $catalogo['explicacion_vocacional'] ?? '',
            'acciones_recomendadas' => $catalogo['acciones_recomendadas'] ?? [],
            'inteligencias_relacionadas' => $catalogo['inteligencias_relacionadas'] ?? [],
        ];
    }

    public function mapearCamposPersistibles(array $datos, array $analisis): array
    {
        if (($analisis['estado_inteligente'] ?? '') === 'BLOQUEADA') {
            return [
                'nom_esp' => $datos['nom_esp'] ?? '',
                'des_esp' => $datos['des_esp'] ?? '',
                'est_esp' => $datos['est_esp'] ?? 'ACTIVO',
                'clas_bth_esp' => false,
                'est_int_esp' => 'BLOQUEADA',
                'conf_esp' => $analisis['confianza'] ?? 0,
                'val_aca_esp' => 0,
                'fec_cla_esp' => now(),
            ];
        }

        $vis = $analisis['visualizacion'] ?? [];
        
        return [
            'nom_esp' => $datos['nom_esp'] ?? '',
            'des_esp' => $datos['des_esp'] ?? '',
            'est_esp' => $datos['est_esp'] ?? 'ACTIVO',
            
            'sig_esp' => $vis['sigla'] ?? ($mejorCatalogo['sigla'] ?? null),
            'fam_pro_esp' => $vis['familia_profesional'] ?? null,
            'cam_for_esp' => $vis['campo_formativo'] ?? null,
            'area_bth_esp' => $vis['area_bth'] ?? null,
            
            'niv_tec_esp' => $vis['niveles']['Técnico'] ?? null,
            'niv_pro_esp' => $vis['niveles']['Productivo'] ?? null,
            'niv_tecno_esp' => $vis['niveles']['Tecnológico'] ?? null,
            'niv_soc_esp' => $vis['niveles']['Social'] ?? null,
            'niv_art_esp' => $vis['niveles']['Artístico'] ?? null,
            'niv_adm_esp' => $vis['niveles']['Administrativo'] ?? null,
            
            'comp_tec_esp' => $vis['competencias_tecnicas'] ?? [],
            'hab_pra_esp' => $vis['habilidades_practicas'] ?? [],
            'hab_cog_esp' => $vis['habilidades_cognitivas'] ?? [],
            'asi_rel_esp' => $vis['asignaturas_relacionadas'] ?? [],
            'car_rel_esp' => $vis['carreras_relacionadas'] ?? [],
            'area_pro_esp' => $vis['areas_profesionales'] ?? [],
            
            'perfil_riasec_esp' => explode(' / ', $vis['perfil_riasec'] ?? ''),
            'int_mul_esp' => $vis['inteligencias_relacionadas'] ?? [],
            'act_comp_esp' => $vis['actividades_compatibles'] ?? [],
            'pal_cla_esp' => $vis['palabras_clave'] ?? [],
            'sin_esp' => $vis['sinonimos'] ?? [],
            'err_com_esp' => $vis['errores_comunes'] ?? [],
            
            'exp_voc_esp' => $analisis['explicacion'] ?? null,
            'acc_rec_esp' => $analisis['acciones_recomendadas'] ?? [],
            
            'est_int_esp' => $analisis['estado_inteligente'] ?? 'REQUIERE_REVISION',
            'conf_esp' => $analisis['confianza'] ?? 0,
            'val_aca_esp' => $analisis['validez_academica'] ?? 0,
            'clas_bth_esp' => ($analisis['estado_inteligente'] === 'RECONOCIDA' || $analisis['estado_inteligente'] === 'REDACTABLE'),
            'fec_cla_esp' => now(),
        ];
    }

    public static function ejemplosValidos(): array
    {
        return ['Sistemas Informáticos', 'Gastronomía', 'Contabilidad', 'Construcción Civil', 'Electrónica', 'Textiles y Confecciones', 'Mecánica Industrial', 'Mecánica Automotriz', 'Carpintería', 'Belleza Integral'];
    }

    public static function estadoVisual(string $estado): array
    {
        return match ($estado) {
            'RECONOCIDA' => ['clase' => 'ui-badge-success', 'texto' => 'Reconocida BTH'],
            'REDACTABLE' => ['clase' => 'ui-badge-info', 'texto' => 'Corregible'],
            'REQUIERE_REVISION' => ['clase' => 'ui-badge-warning', 'texto' => 'En revisión'],
            'BLOQUEADA' => ['clase' => 'ui-badge-danger', 'texto' => 'Bloqueada'],
            'DUPLICADA' => ['clase' => 'ui-badge-danger', 'texto' => 'Duplicada'],
            'INCOMPLETA' => ['clase' => 'ui-badge-muted', 'texto' => 'Incompleta'],
            default => ['clase' => 'ui-badge-muted', 'texto' => 'Pendiente']
        };
    }

    public function puedeMostrarMapa(array $analisis): bool
    {
        return (bool) ($analisis['puede_mostrar_mapa'] ?? false);
    }

    public function debeBloquear(array $analisis): bool
    {
        return (bool) (!($analisis['puede_guardar'] ?? false));
    }

    private function buscarMejorCoincidenciaCatalogo(string $normalizado): ?array
    {
        $mejor = null;
        $maxSim = 0;
        $esCorreccion = false;

        $normalizadoCanonico = $this->canonico($normalizado);

        // Mappings informales a formales
        $correcciones = [
            'compus' => 'Sistemas Informáticos',
            'computacion' => 'Sistemas Informáticos',
            'computadora' => 'Sistemas Informáticos',
            'informatica' => 'Sistemas Informáticos',
            'programas' => 'Sistemas Informáticos',
            'software' => 'Sistemas Informáticos',
            'sistemas' => 'Sistemas Informáticos',
            'chef' => 'Gastronomía',
            'cocina' => 'Gastronomía',
            'comida' => 'Gastronomía',
            'gastronomia y nutricion' => 'Gastronomía',
            'conta' => 'Contabilidad',
            'contaduria' => 'Contabilidad',
            'administracion y finanzas' => 'Contabilidad',
            'electricidad' => 'Electrónica',
            'electricista' => 'Electrónica',
            'autos' => 'Mecánica Automotriz',
            'automotores' => 'Mecánica Automotriz',
            'taller automotriz' => 'Mecánica Automotriz',
            'costura' => 'Textiles y Confecciones',
            'ropa' => 'Textiles y Confecciones',
            'confeccion' => 'Textiles y Confecciones',
            'diseño de modas' => 'Textiles y Confecciones',
            'muebles' => 'Carpintería',
            'ebanisteria' => 'Carpintería',
            'peluqueria' => 'Belleza Integral',
            'estetica' => 'Belleza Integral',
            'maquillaje' => 'Belleza Integral',
            'construccion' => 'Construcción Civil',
            'obras civiles' => 'Construcción Civil',
        ];

        // Check if there's a direct synonym / correction
        if (isset($correcciones[$normalizadoCanonico])) {
            $formal = $correcciones[$normalizadoCanonico];
            $mejor = self::catalogo()[$formal];
            $mejor['es_correccion'] = true;
            $mejor['similitud'] = 100;
            return $mejor;
        }

        // Fuzzy matches or keyword inclusion
        foreach (self::catalogo() as $nombre => $item) {
            similar_text($normalizadoCanonico, $this->canonico($nombre), $sim);

            if ($sim > $maxSim) {
                $maxSim = $sim;
                $mejor = $item;
                $esCorreccion = ($sim < 95); // If not completely identical, it is a correction
            }

            // check synonyms
            foreach ($item['synonimos'] ?? [] as $syn) {
                similar_text($normalizadoCanonico, $this->canonico($syn), $simSyn);
                if ($simSyn > $maxSim) {
                    $maxSim = $simSyn;
                    $mejor = $item;
                    $esCorreccion = true;
                }
            }

            // check keywords
            foreach ($item['palabras_clave'] ?? [] as $keyword) {
                if (str_contains($normalizadoCanonico, $this->canonico($keyword))) {
                    if ($maxSim < 85) {
                        $maxSim = 85;
                        $mejor = $item;
                        $esCorreccion = true;
                    }
                }
            }
        }

        if ($mejor !== null) {
            $mejor['es_correccion'] = $esCorreccion;
            $mejor['similitud'] = $maxSim;
            return $mejor;
        }

        return null;
    }

    private function respuestaBase(
        bool $valido,
        array $datos,
        string $estadoInteligente,
        string $mensaje,
        int $confianza = 0,
        int $validezAcademica = 0,
        array $bloqueos = [],
        bool $puedeGuardar = false
    ): array {
        return [
            'datos' => $datos,
            'estado_inteligente' => $estadoInteligente,
            'confianza' => $confianza,
            'validez_academica' => $validezAcademica,
            'completitud_formulario' => $this->completitud($datos, ['nom_esp', 'des_esp']),
            'puede_guardar' => $puedeGuardar,
            'puede_mostrar_mapa' => false,
            'duplicidad' => ['exacto' => false, 'aproximado_critico' => false, 'aproximado_leve' => false, 'similitud' => 0, 'registro' => null],
            'coincidencias' => [],
            'bloqueos' => $bloqueos,
            'advertencias' => [$mensaje],
            'sugerencias' => array_keys(self::catalogo()),
            'ejemplos_validos' => self::ejemplosValidos(),
            'mensaje' => $mensaje,
            'explicacion' => '',
            'orientacion' => null,
            'visualizacion' => [
                'mostrar_mapa_completo' => false,
                'mostrar_vista_previa_limitada' => false,
                'perfil_riasec' => null,
                'campo_formativo' => null,
                'area_bth' => null,
                'familia_profesional' => null,
                'carreras_relacionadas' => [],
                'asignaturas_relacionadas' => [],
                'competencias_tecnicas' => [],
                'habilidades_practicas' => [],
                'habilidades_cognitivas' => [],
                'areas_profesionales' => [],
                'inteligencias_relacionadas' => [],
                'actividades_compatibles' => [],
                'niveles' => [],
                'color_hex' => '#dc2626',
            ],
        ];
    }

    private function colorEspecialidad(string $sigla): string
    {
        return match (mb_strtoupper($sigla)) {
            'SYS' => '#3b82f6', // blue
            'GAS' => '#f59e0b', // amber
            'CON' => '#10b981', // emerald
            'CIV' => '#ef4444', // red
            'ELT' => '#8b5cf6', // purple
            'TEX' => '#ec4899', // pink
            'MCI' => '#6b7280', // gray
            'MCA' => '#f97316', // orange
            'CAR' => '#78350f', // brown
            'BEI' => '#d946ef', // fuchsia
            default => '#6366f1' // indigo
        };
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_esp;
    }
}
