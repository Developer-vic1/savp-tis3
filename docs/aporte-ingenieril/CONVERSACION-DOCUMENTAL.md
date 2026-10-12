# Conversación documental administrativa

## Propósito y límites

El asistente local interpreta consultas para revisar documentos académicos. No usa un modelo
generativo, no descarga un LLM y no aprende hechos universitarios a partir de conversaciones.
La identidad visible es «asistente documental de SAVP». Laravel puede añadir el nombre real del
administrador autenticado; Python no inventa ni conserva ese nombre.

La conversación usa únicamente los últimos turnos recibidos en la solicitud. No existe memoria
persistente de conversaciones en este módulo. Las instantáneas, documentos y expedientes
guardados son evidencia documental independiente del historial de mensajes.

## Entrenamiento local

`ai-service/scripts/entrenar_conversacion_documental.py` cuenta vocabulario español y pares de
palabras con un clasificador Naive Bayes multinomial. El artefacto resultante se guarda en
`ai-service/data/tutor/intenciones_modelo.json`. Sólo clasifica cuatro intenciones: saludo,
capacidades, consulta documental y consulta pedagógica general. No genera respuestas nuevas.

Datos revisables y sintéticos, sin datos personales. La ampliación del 11 de octubre añadió ejemplos de redacción conversacional, pero no hechos universitarios:

- Entrenamiento: 72 frases en cuatro intenciones.
- Desarrollo: 30 frases distintas; incluye dudas humanas, ortografía y redacción.
- Prueba histórica: 16 frases distintas, cuatro por intención. Ya había sido inspeccionada antes
  de la ampliación; su nueva puntuación es una regresión conocida, no una prueba ciega.
- Diagnóstico adversarial: 32 frases nuevas de seguimiento, nombres ambiguos y peticiones breves;
  se usa para detectar fallos, no para afirmar precisión general.

Las reglas de seguridad, términos universitarios, necesidad de evidencia y límites de orientación
siguen prevaleciendo sobre el clasificador. Su probabilidad no mide veracidad ni se presenta al
usuario como confianza en una respuesta académica.

## Resultado medido el 11 de octubre de 2026

Ejecución reproducible local:

- Vocabulario: 389 términos y pares de palabras; 72 ejemplos de entrenamiento.
- Desarrollo: modelo 27/30 y flujo 28/30.
- Prueba histórica ya inspeccionada: modelo 15/16 y flujo 16/16.
- Diagnóstico adversarial exploratorio: modelo 27/32 y flujo 26/32.

Las métricas están separadas por conjunto en `evaluacion_conversacion.json`; el conjunto histórico
no se vuelve a etiquetar como reservado después de haberlo visto. El modelo local solo ayuda a
clasificar intención. En producción sus clases SOCIAL y CAPABILITIES se aceptan con umbral;
las respuestas documentales siguen reglas de alcance, recuperador y fuentes. Por eso la exactitud
de cuatro clases del modelo no mide por sí sola la respuesta final, su tono ni su fidelidad a la
fuente. Estas muestras pequeñas no acreditan comprensión general ni satisfacción estudiantil.

El reporte contiene precisión, recuperación, F1 y matriz de confusión por intención. No se anuncia
un resultado del 100 % ni se mezclan los casos de desarrollo con la regresión histórica.

## Universidad, carrera y semestre

Para materias se identifican universidad y carrera. El semestre es opcional: si se pide uno,
la respuesta se limita a ese bloque; sin semestre, muestra el panorama de los bloques que tengan
transcripción verificada. Si faltan universidad o carrera, pide solo el dato necesario usando
un ejemplo de la misma consulta, sin cambiar de programa. Un seguimiento puede recuperar el
contexto del último mensaje del usuario; una precisión nueva prevalece.

En la pregunta «solo dime las carreras» de UNIFRANZ, el asistente usa la instantánea del índice
oficial de carreras, que actualmente contiene 17 enlaces. Distingue esta oferta publicada de las
carreras con malla revisada y no atribuye una carrera a una sede sin evidencia propia. No incorpora
resultados de UPB ni normas del Ministerio ni pega texto OCR.

Las preguntas «cuántas universidades tenemos registradas» y «qué universidades tenemos en
revisión» consultan el mismo registro nominal que `/conocimiento/directorio-nacional`, mediante
`app/knowledge/registro_nacional.py`. El conteo es de fichas históricas únicas y se acompaña de
desglose por departamento; una institución puede figurar en varios departamentos. Cada institución
enumerada muestra sus departamentos. Se cita únicamente la guía ministerial que originó ese
registro, sin citas de carreras ajenas. El panel administrativo añade en tiempo real el conteo de
universidades activas con sede en PostgreSQL. Esa tabla sirve a vínculos académicos institucionales;
el registro nacional guarda identidades históricas, estado de verificación web y cobertura documental.
No se cargan registros históricos como universidades `ACTIVA` por deducir vigencia de un sitio web.

La malla oficial de Sistemas e Innovación Digital UNIFRANZ, con hash SHA-256 registrado,
se contrastó visualmente en las páginas 3 y 4. `mallas_academicas.json` guarda nueve bloques
`TRANSCRIPCION_VISUAL_VERIFICADA` con la página y los nombres visibles. El tutor usa solo esos
bloques para dar materias y enlaza la página exacta. En octavo y noveno separa asignaturas comunes
de las dos menciones, Inteligencia Artificial y Ciencia de Datos o Ciberseguridad y Blockchain:
la estudiante o el estudiante elige una, no cursa ambas listas simultáneamente. No se deduce una
gestión académica del nombre del archivo ni se traslada la malla a otra sede.

Se corrigieron dos atribuciones erróneas:

1. Una consulta de segundo, tercero u otro semestre ya no añade «primer semestre» a la búsqueda.
2. La respuesta ya no sustituye un fragmento por `initial_subjects` del catálogo. «Materias iniciales»
   sin evidencia semestral explícita no prueba que el listado sea del primer semestre.

El filtro exige la universidad y carrera nombradas. Si no hay coincidencia, no vuelve a fuentes de
otra universidad. Un semestre sólo se obtiene de una sección identificada o de encabezados
explícitos en el texto. En bloques con varios semestres se conserva exclusivamente el solicitado.
La fuente, página y cita originales se mantienen. No se publican automáticamente candidatos de
lectura de imágenes como materias validadas.

La malla real de Sistemas UCB imprime el rótulo «Semestres» y bloques numerados delante de
filas código/UVE/materia/requisito. El corpus aplana esos renglones; el filtro reconoce también
esta gramática únicamente si hay dos números consecutivos y filas explícitas. Así recupera las
cinco materias del primer semestre y las seis del segundo directamente del documento publicado,
sin recurrir a `initial_subjects`. La respuesta conserva la cita de la página 1.

Una comparación con evidencia de una sola universidad informa que la revisión es parcial. La
ausencia de materias de un semestre se explica con lenguaje administrativo, sin pedir al usuario
interpretar palabras internas como «corpus» o «tutor».

## Verificación

Las pruebas focales verifican ambas preguntas reales del usuario: solo carreras UNIFRANZ sin
fuentes ajenas, y materias de Sistemas de los nueve semestres con páginas correctas y ramas de
mención diferenciadas. Comprueban además UCB 1.º y 2.º, seguimiento, universidad ajena y
ausencia de evidencia. Ruff y mypy se ejecutaron sobre los archivos Python modificados.
La evaluación de intenciones se informa por separado de estas pruebas funcionales.

Las pruebas de integración usan `structured-answer-v3.2.0` y comprueban que el corpus real no
atribuya listas del catálogo al primer semestre cuando falta evidencia de ese semestre.

Tres regresiones adicionales consultan el recuperador real con conexiones bloqueadas: primer
semestre UCB/Sistemas, segundo semestre sin materias del primero y un semestre ausente. La
búsqueda semestral considera hasta 32 candidatos antes del filtro estricto, pues los primeros
ocho resultados generales podían excluir el bloque correcto de una malla aunque estuviera
publicado. La respuesta conserva hasta cinco fragmentos finales y sus fuentes.

Comandos desde `ai-service`:

```powershell
& .venv/Scripts/python.exe scripts/entrenar_conversacion_documental.py
& .venv/Scripts/python.exe -m pytest tests/unit/test_conversacion_documental.py tests/unit/test_tutor.py --no-cov
```
