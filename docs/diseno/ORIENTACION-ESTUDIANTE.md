# Orientación del estudiante: decisiones de experiencia

Referencia: 8 de octubre de 2026. Esta ficha explica el recorrido y sus límites; la identidad compartida se consulta en [DESIGN.md](../../DESIGN.md). Para integración y metodología, consultar [04-APORTE-Y-METODOLOGIA.md](../sistema/04-APORTE-Y-METODOLOGIA.md).

## 1. Una tarea por ventana

| Etapa | Ruta | Para qué sirve |
|---|---|---|
| Responder | `/estudiante/intereses` | Contestar y revisar las 30 actividades del instrumento. |
| Comprender | `/estudiante/analisis` | Leer el perfil, sus motivos, la trayectoria disponible y generar el estudio. |
| Explorar | `/estudiante/futuro` | Elegir carrera y universidad, abrir materias documentadas y fuentes. |
| Prepararme | `/estudiante/plan` | Convertir una intención en una meta mediante el componente existente. |

Separar cuestionario y resultado evita que el alumno lea una interpretación mientras responde y reduce la cantidad de decisiones simultáneas. Guardar el cuestionario conduce a Mi análisis. La preparación escolar conserva su ventana `/estudiante/preparacion`; elegir una prioridad en el análisis orienta la navegación, sin cambiar respuestas ni guardar una meta automáticamente.

## 2. Dinamismo con propósito

El cuestionario se presenta en bloques de cinco actividades, manteniendo el orden, texto y escala del instrumento. El avance cuenta respuestas válidas; no premia elecciones ni anticipa carreras. Anterior, siguiente, ver todas e ir a la primera pendiente permiten revisar sin perder respuestas. El guardado requiere todas las actividades y explica qué falta.

Los detalles plegables acercan el motivo al dato: cómo se obtiene el puntaje, qué significa el área, por qué aparece una carrera y dónde consultar la malla. La transición comunica cambios de bloque o apertura; la preferencia de movimiento reducido elimina animaciones. Se conservan foco visible, controles con nombre, navegación por teclado y avisos de estado.

Durante el guardado y el análisis se anuncia una solicitud en curso y se bloquea el doble envío. No se muestran porcentajes de cálculo ni etapas ficticias del servidor. El error conserva las respuestas en pantalla y ofrece recuperación; antes de reenviar tras una interrupción se recomienda comprobar si el resultado quedó guardado.

## 3. Qué expresa cada evidencia

El puntaje RIASEC suma cinco actividades por área: la escala pública 1–5 se transforma a 0–4 y produce 0–20. Un cero válido es distinto de un dato ausente. Los resultados de otra escala se conservan como anteriores; no se dibujan ceros ni se mezclan con el formato actual. Los empates se explican sin convertir el orden de letras en una preferencia superior.

El perfil expresa intereses declarados; no mide inteligencia, aptitud, admisión ni probabilidad de éxito. La equivalencia psicométrica del instrumento para estudiantes bolivianos no está demostrada. La trayectoria permite seleccionar gestión, comparar materias y consultar cada nota de los períodos disponibles, sin inferir promoción o rendimiento anual con evidencia parcial.

El motor actual presenta el código del estudiante junto a referencias ocupacionales del catálogo. Tener una referencia documentada **no demuestra coincidencia personal**: no calcula compatibilidad ni un ranking. Las opciones se nombran como referencias para explorar. La evidencia de materias se explica por separado y no constituye convalidación universitaria. Las inferencias conservan su condición de propuestas que requieren revisión.

## 4. Seleccionar carrera, universidad y materias

Los selectores reutilizan el componente institucional. Primero se elige carrera; después se habilitan las universidades registradas para ella. En el modo universidad, la consulta se habilita al seleccionar una institución. Aplicar filtros abre las fichas pertinentes; no ejecuta inscripción ni elige una institución por el alumno.

Cada ficha permite revisar grado, duración, materias iniciales documentadas, motivos y fuentes. La lista inicial no equivale a una malla completa ni promete aprendizaje futuro. Si faltan materias estructuradas, se explica esa ausencia. Los PDF curriculares se rotulan como malla; las páginas generales, como fuente del programa. La oferta y la versión deben confirmarse según sede y gestión.

Se comprobó que [Sistemas UCB La Paz, malla 2026](https://lpz.ucb.edu.bo/wp-content/uploads/2025/11/SIS-2026.pdf) es un PDF curricular de una página y coincide con las cinco materias iniciales del catálogo. La [página del programa](https://lpz.ucb.edu.bo/pregrado/ingenieria-de-sistemas/) presenta perfil, duración y enlaces a mallas de distintas gestiones. Esta comprobación respalda esos enlaces concretos, no todo el catálogo ni una matrícula futura.

## 5. Motivos educativos y fuentes

- [Manual O*NET Interest Profiler](https://www.onetcenter.org/reports/IP_Manual.html): fundamenta el uso para exploración de intereses y orientación. No certifica la adaptación de SAVP ni convierte una ocupación en una carrera universitaria.
- [CESE: teoría de carga cognitiva en la práctica](https://education.nsw.gov.au/about-us/education-data-and-research/cese/publications/practical-guides-for-educators-/cognitive-load-theory-in-practice): respalda reducir información innecesaria y organizar explicaciones. Separar ventanas y usar bloques son decisiones de diseño derivadas de ese principio; no se ha medido su efecto sobre abandono o atención en SAVP.
- [IES: organizar instrucción y estudio](https://ies.ed.gov/ncee/wwc/PracticeGuide/1): recomienda combinar gráficos con explicación y conectar conceptos abstractos con representaciones concretas. Se aplica mostrando el puntaje junto al significado del área y acercando materias a fuentes verificables, sin afirmar validación educativa propia.

## 6. Estados y verificación

Evidencia de la separación inicial de ventanas: 8 de octubre de 2026. Las ejecuciones y el QA siguientes fueron comunicados por los agentes responsables de la implementación y la revisión del navegador. Este hito es anterior a la trayectoria detallada y al borrador durable; la evidencia de la entrega ampliada se registra en las secciones 7 y 8.

| Comprobación | Evidencia y límite al redactar esta ficha |
|---|---|
| Código y contratos | Inspeccionados: ventanas separadas, escala, resultado anterior, evidencia, selección y navegación. Esto no demuestra QA visual. |
| Pruebas antes de separar ventanas | El equipo informó ejecuciones satisfactorias de PHP, JavaScript y Python sobre la versión previa. Son evidencia histórica; no validan el split posterior. |
| QA antes de separar ventanas | Revisados cuestionario por bloques y botones en escritorio claro; corregida la representación de puntajes ausentes como cero. |
| PHP tras separar ventanas | Resultado final: 26 pruebas aprobadas y 197 aserciones. Incluyen cuestionario, presentación del perfil y fuentes de futuro académico; no representan la suite global. `FuentesFuturoAcademicoTest` aporta dos pruebas y 11 aserciones sobre fuente MINEDU mezclada con una carrera y ausencia de malla. |
| JavaScript tras separar ventanas | 12 pruebas aprobadas: cinco de intereses y siete del selector institucional. |
| Compilación | Build de Vite y caché de vistas Blade completados correctamente. Esto no reemplaza QA de interacción. |
| Suite histórica de integración | `StudentOrientationIntegrationTest` no se ejecutó en esta versión por su preparación dependiente de PostgreSQL. No se afirma validación actual de esa suite. |
| Navegador con estudiante real | Revisados escritorio y anchos móviles 340/390 en claro y oscuro: cuestionario 0/30, separación de Mi análisis, menú del estudiante y resultado histórico sin ceros ficticios. No se respondieron preferencias por el alumno. |
| Navegador con fixture aislada | El servicio real produjo un análisis `COMPLETE` con siete perfiles sobre datos técnicos de prueba. Verificados avance, conservación al cambiar bloque, redirección a Mi análisis, explicaciones de áreas/empate/gestiones, tres prioridades dinámicas y filtros dependientes carrera/universidad con resultado GET correcto. No se escribió en la base institucional. |
| Carga y fallo con fixture | Verificados aviso de carga, bloqueo de doble envío y fallo 503 con conservación de las 30 respuestas. El fallo de estudio muestra recuperación y dirige el foco al aviso `role="alert"`. El estado de sesión 419 está cubierto en JavaScript, sin QA visual de sesión caducada. |
| Mi análisis y Mi futuro académico | Revisados en pantalla angosta y modo oscuro: explicación del análisis expandida, campos dependientes de futuro y títulos de fuentes. Esta revisión complementa el QA del cuestionario; no constituye una auditoría completa de accesibilidad. |
| Movimiento y teclado | Con movimiento reducido emulado, animación `none` y transición `0s`; después se restauró la preferencia del navegador. Comprobadas apertura del selector con ArrowDown y llegada del foco con Tab. No se realizó auditoría completa con tecnologías de asistencia. |
| Fuentes | Corregida la confusión entre currículo escolar MINEDU y malla universitaria. En la ficha revisada, únicamente la fuente curricular UCB se etiqueta como malla y las demás conservan su título pertinente. Verificadas también ausencia de malla y protección contra fuente escolar mezclada mediante pruebas específicas. |
| Evidencia incompleta | Confirmados resultado histórico y empate. Examinados en DOM estados vacíos, servicio desconectado, histórico y futuro sin estudio, incluyendo el enlace a Mi análisis. La inspección DOM no certifica todos los estados visuales ni la cobertura global de requisitos bloqueantes. |

Las pruebas que crean o vacían registros usan exclusivamente una base aislada. Un estudio calculado sobre una fixture técnica sirve para verificar contratos y presentación; no es el resultado del estudiante institucional. No responder el cuestionario por el alumno ni inventar preferencias, carreras compatibles o materias.

## 7. Trayectoria detallada y datos usados

`TrayectoriaIntereses` resume el `payload` del estudiante ya autorizado y el mapa de gestiones preparado por `StudentOrientationService`. No consulta tablas, no modifica registros y no amplía el permiso de la ventana. La gestión actual y el historial permanecen separados; el año procede del catálogo de gestión, nunca de una suposición sobre su identificador.

El selector institucional permite revisar una gestión. La vista inicial muestra la tabla con notas y cálculo; los botones «Ver notas y cálculo» y «Comparar materias» alternan entre tabla y barras, sin presentar ambas al mismo tiempo. La tabla del plegable institucional conserva el nombre real de cada período, todas sus notas y la fórmula visible. El promedio simple por materia es **suma de las notas disponibles ÷ cantidad de esas notas**. Cero participa; ausencia, valor inválido o fuera de su escala no se sustituye por cero. Si hay varios registros de una materia en un período se muestran todos y se explica que cada nota cuenta una vez. No se mezclan escalas diferentes para calcular un promedio.

La cobertura se expresa como períodos observados en esa gestión y períodos de la materia con nota. El contrato no entrega un catálogo de períodos esperados: no se presupone que existan tres trimestres ni se afirma que falte el tercero. Los trimestres identificados se cuentan únicamente cuando el nombre del período los señala. «—» significa ausencia de nota disponible, y se explica que pueden existir otros períodos no representados.

| Cálculo o lectura | Datos que utiliza | Datos que no utiliza para ese cálculo |
|---|---|---|
| Puntajes RIASEC | Versión del instrumento y las 30 respuestas; transformación 1–5 a 0–4 y suma de cinco actividades por área. | Notas, asistencia, universidad preferida y objetivos no cambian estos puntajes. |
| Trayectoria y promedio mostrado | Gestión, materia, notas, nombre/orden de período y escala publicados en el contexto autorizado. | El perfil RIASEC no modifica las notas; no se añaden ponderaciones, notas ausentes ni calificaciones de otros estudiantes. |
| Estudio de orientación | Respuestas y evidencia académica, histórica, técnica, asistencia y actividades disponibles según el contrato. | No prueba admisión, aprobación ni compatibilidad personal de una carrera por su presencia en el catálogo. |

La explicación del instrumento usa un ejemplo abstracto identificado como ejemplo, sin duplicar las actividades ni anticipar qué respuesta produciría una carrera. La trayectoria no sustituye el promedio anual oficial ni ejecuta una decisión académica.

Verificación de esta ampliación, 8 de octubre de 2026: `TrayectoriaInteresesTest` pasó inicialmente con cinco pruebas y 30 aserciones, sin acceso a base de datos. Cubre separación por gestión/materia, ceros, ausencias, nombres de períodos, varios registros, escalas distintas y contexto vacío. En la consulta del estudiante real se comprobaron 36 notas y tres trimestres para 2021, y 24 notas y dos trimestres para 2026: son conteos observados de esa cuenta en esta revisión, no totales institucionales permanentes.

Se verificaron selector de gestión, fórmulas de la tabla, barras y cambio de vista mediante Enter. En la revisión móvil el ancho de página fue 340 px, sin desplazamiento horizontal global; la emulación nominal del navegador era 390 px y el ancho interior observado 354 px. En oscuro, la tabla de aproximadamente 800 px conserva su desplazamiento dentro de un contenedor de 253 px. Esto comprueba esos tamaños y controles concretos, sin afirmar una auditoría completa de accesibilidad.

## 8. Borrador para continuar otro día

«Guardar borrador» conserva manualmente las respuestas incompletas en la cuenta del estudiante. No hay guardado automático: antes de salir debe aparecer la confirmación del servidor. La interfaz muestra fecha del último guardado, actividades pendientes y cambios nuevos sin guardar; al salir con cambios sin confirmación solicita atención mediante el aviso del navegador. La continuidad pertenece al estudiante, gestión y versión del instrumento; no mezcla respuestas de otras identidades, gestiones o versiones.

Un borrador no genera perfil ni análisis parcial. El perfil previamente finalizado permanece conservado mientras se responde. Cuando se completan las 30 actividades, la finalización usa el mismo registro del borrador y el resultado completo sigue el flujo de Mi análisis. Los fallos conservan el borrador confirmado anterior y las respuestas actuales en la pantalla; una respuesta de carga o un error no equivale a guardado.

| Verificación de la entrega ampliada | Evidencia y límite |
|---|---|
| PHP | 48 pruebas y 373 aserciones aprobadas mediante PHPUnit con `php -d extension=pdo_sqlite`. Es el conjunto focalizado ejecutado para esta entrega; no se afirma suite global. |
| JavaScript | 15 pruebas aprobadas. Incluyen la regresión del botón de borrador que obtiene el formulario mediante `$root`; evita depender del elemento del botón que dispara la acción. |
| Recursos y formato | Compilación Vite, caché de vistas Blade y revisión Pint aprobadas. |
| Continuidad del borrador | En la fixture HTTP aislada del puerto 8019 se respondieron dos actividades, se guardó el borrador y, tras recargar, se recuperaron ambas selecciones. |
| Fallo de guardado | Con una tercera respuesta nueva y fallo 503, permanecieron dos respuestas confirmadas en el servidor y las tres en la interfaz. No se escribió en la base institucional. |
| Límites del QA | El caso de sesión 419 conserva cobertura en JavaScript, sin QA visual de sesión caducada. La revisión de teclado, móvil y tema descrita no sustituye una auditoría completa con tecnologías de asistencia. |

Las respuestas de la fixture solo prueban continuidad y estados del formulario; no representan preferencias del alumno real ni se usan para atribuirle una carrera.

## 9. Comparación de áreas Holland, sin porcentaje de compatibilidad

Decisión de método para la ampliación: se cuenta cuántas áreas destacadas del estudiante aparecen en cada referencia ocupacional documentada. Es una descripción de conjuntos creada para SAVP; no es una correlación, una probabilidad de admisión o éxito, ni una validación de compatibilidad con la carrera.

Se toma como umbral el tercer puntaje más alto del estudiante y se incluyen **todas** las áreas que alcanzan ese puntaje, conservando los empates. Por ello el conjunto puede contener tres, cuatro o cinco áreas. El orden de las letras no aumenta el conteo. Si todas las áreas tienen el mismo puntaje, ninguna destaca y se suspende la comparación numérica; se muestra el perfil completo. Sin cuestionario vigente o sin código ocupacional estructurado, único y respaldado por fuente, el conteo también queda ausente.

Ejemplo de cálculo, separado de cualquier predicción: con S=19, C=17, R=16 e I=16 se incluyen S, C, R e I. Una referencia IC comparte I y C: «2 de 4 áreas de tu perfil; 2 de 2 áreas de esta referencia». Omitir I por el desempate alfabético cambiaría artificialmente el resultado. Una referencia de dos letras no está incompleta: O*NET publica códigos resumidos de una a tres letras. El denominador se muestra siempre con su tamaño real. El conteo de cero significa ausencia de áreas compartidas en ese resumen, no que la carrera deba descartarse.

El [manual O*NET](https://www.onetcenter.org/dl_files/IP_Manual.pdf), capítulo 1, describe esos códigos y la disposición R–I–A–S–E–C; el capítulo 7 explica la correspondencia mediante perfiles completos. El [informe de retornos de carreras, octubre de 2025](https://www.onetcenter.org/reports/IP_Career_Returns.html) confirma que la correlación compara las formas de los perfiles del usuario y la ocupación. Para reproducir ese procedimiento harían falta los seis valores ocupacionales documentados, ausentes del catálogo actual; no se inventan valores desde las letras ni se interpreta un texto narrativo como contrato numérico.

Un hexágono puede explicar la disposición del modelo y sus áreas vecinas. Su distancia visual no aporta puntos adicionales ni justifica penalizar áreas opuestas. Las barras del estudiante utilizan sus puntajes reales; la ocupación se representa mediante las letras que efectivamente publica la referencia, sin simular un vector de seis puntajes.

La comparación permite investigar también una carrera que no sea la esperada: se explica el motivo, se revisan materias y fuentes y se conserva la decisión del alumno. Comparar dos o tres fichas facilita una decisión concreta sin presentar el orden del catálogo como ranking. Las inferencias entre carrera y ocupación conservan su advertencia; una carrera universitaria no equivale a una ocupación.

## 10. Meta personal, compromisos e historial

Decisión de persistencia de la ampliación: reutilizar `SeguimientoAcademico` con tipo `PLAN_PERSONAL`, ámbito `ESTUDIANTE` y visibilidad `PERSONAL`. El documento versionado del plan se conserva como JSON en el atributo de texto existente `pro_acc_seg`. No requiere tablas nuevas ni añadir atributos o migraciones para este flujo; tampoco convierte una intención estudiantil en hecho del kardex o decisión académica institucional.

El acceso corresponde al usuario estudiante y a su inscripción vigente. `Perfil_Academico` controla el plan; el catálogo de orientación mantiene además su permiso propio. «Personal» designa el ámbito del flujo y su control de acceso: no promete privacidad absoluta frente a las capacidades de operación y administración institucional. No se copian calificaciones al plan ni se amplía el acceso a información de otros estudiantes.

La meta identifica qué quiere conseguir el alumno, por qué le importa y qué acción concreta se compromete a realizar. El progreso, cuando hay acciones definidas, describe únicamente las acciones completadas respecto del total de ese plan. No mide motivación, aptitud ni posibilidades universitarias. Sin acciones, se informa que aún falta definirlas. Las fechas y compromisos provienen de la decisión del alumno; no se inventan ni se aplican por seleccionar una sugerencia.

Cambiar la carrera u objetivo central exige una confirmación informada: «Cambiar mi meta y actualizar mis intereses». El mensaje explica que se inicia un plan nuevo y que debe completarse otro cuestionario para relacionar esa ruta con los intereses actuales. Esta repetición es una política del flujo solicitado, no una obligación psicométrica demostrada por O*NET. Explorar una ficha o cambiar una fecha, acción o estado no impone por sí solo ese reinicio.

El plan anterior se conserva en versiones cerradas y el nuevo comienza con su propio avance. Iniciar desde cero no borra el historial, las notas ni las respuestas completas anteriores. Las revisiones se añaden conservando el pasado; un borrador o una sugerencia no se presenta como plan finalizado. Las escrituras y la confirmación deben validarse en el servidor, con ámbito del actor, además de los controles preventivos de la interfaz.

La ampliación se verificó con 79 pruebas PHP y 600 aserciones mediante PHPUnit con `pdo_sqlite`, además de las pruebas del contrato Python y de las interacciones JavaScript. Compilaron las vistas Blade y los recursos; el QA visual recorrió análisis, comparación y plan en el navegador, incluidos móvil y modo oscuro. Una base PostgreSQL temporal confirmó que el cambio de carrera exige un nuevo cuestionario, inicia otro plan con avance cero y conserva la versión anterior con su progreso. No se guardó una meta real por el estudiante durante el QA. Las cifras de las secciones 6–8 corresponden a fases anteriores y no se suman a estas comprobaciones.

## 11. Diagnóstico y conservación al generar el estudio

El 8 de octubre de 2026 se distinguieron dos fallos sucesivos: el servicio Python calculaba correctamente el análisis, pero PostgreSQL rechazaba el guardado; después de reparar la persistencia, la redirección al mismo documento con un ancla conservaba la pantalla anterior. Una respuesta HTTP 200 de Python no confirma por sí sola ni persistencia ni presentación al estudiante. En la solicitud revisada, Python respondió correctamente y su tiempo registrado fue aproximadamente 177 ms.

La base institucional protege las actividades finalizadas como historia inmutable. El flujo anterior intentaba ejecutar un `UPDATE` sobre esa actividad para agregar `analysis_snapshot`; el disparador de PostgreSQL lo rechazaba. Se conserva esa protección: generar el estudio crea una nueva versión autocontenida con las respuestas, puntaje, hash y gestión del perfil de origen, junto al análisis y su fecha de generación. Las fechas originales de inicio y finalización del cuestionario se conservan; no se presenta el cálculo del estudio como una nueva fecha de respuesta del alumno. Los consumidores actuales leen la última versión sin perder el perfil previo.

Antes de insertar, la transacción bloquea al estudiante y vuelve a comprobar el perfil y la gestión. Un cambio mientras Python calcula devuelve 422 e invita a revisar el perfil antes de generar otra vez. Un fallo de persistencia devuelve 503 e indica que el estudio fue calculado pero su guardado no pudo confirmarse. Ambos conservan el cuestionario y los estudios anteriores; el mensaje de almacenamiento se distingue de la indisponibilidad del servicio de análisis. La deduplicación compara el contenido del estudio y no solo el hash del cuestionario, porque la evidencia académica puede cambiar sin modificar las respuestas.

La validación en PostgreSQL temporal reprodujo la protección real de registros finalizados y confirmó la inserción de una nueva versión, la conservación del registro original y la preservación de sus fechas. En un primer intento de preparación aislada, la configuración clonada conservó el nombre de conexión `pgsql`; un `INSERT` artificial dirigido por error al destino institucional fue rechazado por una clave foránea. No quedó una fila artificial guardada. Se corrigió la selección de conexión y se añadieron guardas del destino antes de repetir la prueba en la base temporal. Ese primer intento fallido se registra como una limitación de la preparación de QA, separada de la comprobación posterior satisfactoria.

Después, la generación solicitada por el usuario conservó un estudio institucional `COMPLETE`, con contrato válido y perfil vigente. La pantalla aún mostraba «Generar mi análisis» sin las opciones: la lectura directa del contexto ya devolvía el estudio correcto. Se ejecutó `php artisan view:clear` y se recargó la ventana, lo que hizo visible el resultado. La inspección posterior identificó que `location.assign('/estudiante/analisis#mi-analisis')` desde esa misma página solo cambia el fragmento, sin ejecutar un nuevo GET; la limpieza de Blade no era la causa de la actualización de datos, sino la recarga posterior. El envío ahora fuerza recarga cuando apunta al mismo documento. Una prueba de JavaScript cubre esa regresión. El indicador «Estudio guardado» muestra la fecha del análisis para diferenciarlo de la fecha del cuestionario.

La comparación del estudio normaliza recursivamente las claves de objetos JSON y conserva el orden de las listas. Esto evita duplicar una versión cuando PostgreSQL JSONB reordena las claves. La prueba temporal también confirmó que repetir el mismo estudio no inserta otra actividad.

El conjunto final de esta reparación pasó con **55 pruebas PHP y 449 aserciones**, y Pint aprobó los archivos afectados. Se comprobaron errores de almacenamiento, cambios concurrentes del perfil, conservación histórica, versiones del motor y deduplicación. En navegador se verificó el estudio guardado y visible después de recargar, con la fecha mostrada en horario de Bolivia.

Esta evidencia cubre el diagnóstico, conservación, guardado y presentación de ese flujo. No acredita toda la suite de integración, una auditoría global de accesibilidad, una nueva revisión completa de movimiento ni validez psicométrica o compatibilidad personal con las carreras mostradas. Los recuentos anteriores permanecen asociados a sus hitos en las secciones 6 y 8.

## 12. Carrera antes que universidad y decisión económica

El resumen anterior tomaba las tres primeras carreras distintas del estudio. Como el catálogo está ordenado por `career_id`, las tres fichas visibles procedían de UCB La Paz. Eso transmitía un sesgo de institución que el análisis de intereses no justifica. La vista actual agrupa primero por nombre normalizado de carrera, conserva cada programa por su `career_id` y muestra todas las opciones disponibles. Ingeniería Civil reúne UCB y UMSA; Ingeniería de Sistemas reúne la ficha UCB y una referencia informativa EMI pendiente de revisión. Ingeniería de Sistemas e Innovación Digital de Unifranz e Ingeniería de Sistemas Computacionales de UPB permanecen como carreras distintas: sus títulos y mallas no se declaran equivalentes.

El catálogo de código ampliado el 08/10/2026 contiene once programas vinculados a seis universidades: siete fichas del corpus validado y cuatro referencias externas pendientes, entre ellas EMI y tres carreras de la Universidad Pública de El Alto. Las fichas UPEA del Vicerrectorado prueban la existencia de Ingeniería de Sistemas, Ingeniería Civil y Psicología; sus PDF de malla requieren OCR y confirmación de vigencia antes de incorporar asignaturas. El estudio ya guardado del estudiante conserva la versión anterior del catálogo hasta generar otro estudio. Los códigos ocupacionales están documentados para los cuatro programas UCB y para Civil UMSA. Las demás fichas sin ese código se muestran como «relación de intereses pendiente de documentar», nunca como coincidencia cero ni con el conteo prestado de otra sede. La comparación selecciona carreras; el paso siguiente abre universidades, mallas y procedencia de cada materia y referencia antes de elegir un programa para el plan.

El catálogo y el contrato del estudio no contienen aranceles, costo total, transporte ni condiciones de beca comparables por sede y gestión. La base institucional dispone de atributos de costo/moneda/beca en `version_oferta_academica`, pero las ocho versiones consultadas no tienen valores en esos campos. No se infiere que una institución sea más barata por su nombre o tipo, ni se trasladan cifras de cursos preuniversitarios a colegiaturas. La UI indica qué confirmar y enlaza fuentes institucionales consultadas: [becas UCB La Paz](https://lpz.ucb.edu.bo/becas/), [convocatorias UMSA](https://convocatorias.umsa.bo/es/), [becas y admisiones Unifranz](https://unifranz.edu.bo/admisiones/becas/), [becas y admisión UPB](https://www.upb.edu/postula-a-la-upb) y [admisión EMI](https://www.emi.edu.bo/universidad/admision). La ruta anterior `/index.php/universidad/admision` de EMI mostró contenido comprometido; no debe enlazarse. Una convocatoria puede cerrar o cambiar, y sus beneficios no constituyen elegibilidad automática del estudiante. Para una comparación monetaria futura se necesitan monto, concepto, moneda, sede, gestión, fecha de consulta y URL oficial para **cada** programa. La matrícula UMSA de Bs 27 encontrada en una página general pertenecía a una sección de gestión 2024 y no se usa como costo 2026.
