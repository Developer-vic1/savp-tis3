# Mejora de lectura y organización curricular

## 1. Propósito y evidencia examinada

El flujo debe producir una malla organizada por semestre y dejar el texto original como respaldo. Reconocer caracteres, interpretar la estructura y aprobar asignaturas son tres tareas diferentes. Este documento registra una evaluación local del 09/10/2026; no describe un modelo entrenado ni una validación de todas las carreras.

Se examinó la instantánea oficial de Psicología de UNIFRANZ, URL `https://unifranz.edu.bo/wp-content/uploads/2026/07/psicologia.pdf`, SHA-256 `af003eb7451851e1a824cc390ffef561711e5cf80951852bf941dc45e8ae3c0d`, guardada en `ai-service/data/knowledge/colecciones/unifranz/instantaneas/`. El PDF tiene ocho páginas; la malla semestral está en las páginas 3 y 4. Se inspeccionaron los bloques digitales y ambas páginas renderizadas. Portada, menciones y microcredenciales se distinguen de la lista semestral.

## 2. Referencia manual de Psicología

Se contaron entradas visuales del plan, incluyendo electivas y proyectos integradores. No se cuentan otra vez las asignaturas repetidas en las menciones ni las listas de microcredenciales. Estos conteos son una referencia manual de este PDF, no una regla para otras carreras.

Las 54 etiquetas, con nombre, semestre, página, región, evidencia digital original y huella de fuente, se conservan en `ai-service/data/evaluation/psicologia_malla_etiquetada.json`. Las dos fuentes dañadas se corrigen en el nombre de la etiqueta visual, conservando intacto el texto digital defectuoso como respaldo. La etiqueta tiene estado `ETIQUETA_DE_EVALUACION_NO_PUBLICADA`; no modifica el manifiesto ni aprueba la oferta curricular.

| Semestre | Página | Entradas verificadas visualmente |
| --- | --- | --- |
| 1 | 3 | 6 |
| 2 | 3 | 6 |
| 3 | 3 | 7 |
| 4 | 3 | 6 |
| 5 | 4 | 7 |
| 6 | 4 | 9 |
| 7 | 4 | 10 |
| 8 | 4 | 3 |
| Total | 3–4 | 54 |

El octavo semestre tiene **Taller de Grado**, **Prácticas Profesionales** y **Proyecto Integrador Final**. El séptimo incluye **Electiva V (Psicología Jurídica – Forense)**, que usa un guion tipográfico. Se conserva el nombre publicado; una normalización de búsqueda puede uniformar ese guion sin alterar la evidencia original.

Dos nombres de la página 3 son legibles en la imagen pero están dañados en la capa digital:

| Semestre | Texto visual verificado | Región aproximada en puntos PDF `(x0, y0, x1, y1)` |
| --- | --- | --- |
| 3 | Inteligencia Artificial Aplicada | `(333.8, 248.8, 459.7, 262.5)` |
| 4 | Gestión de Conflictos y Manejo de Crisis | `(333.8, 420.0, 434.5, 443.7)` |

La página mide aproximadamente `510.24 × 793.70` puntos. El problema es una codificación de fuente dañada: disponer de muchos caracteres digitales no garantiza que todos sean correctos. No se debe corregir un desplazamiento aparente de caracteres sin contrastarlo con la imagen.

Una ejecución local de la selección por bloques, durante la revisión, produjo 51 candidatos: conteos `6, 6, 6, 5, 7, 9, 9, 3`. Faltaban las dos regiones anteriores y la electiva con guion tipográfico. Es una comprobación de cobertura de candidatos en una sola instantánea, no una medida general de precisión del reconocimiento. El manifiesto guardado puede pertenecer a una ejecución anterior; se debe registrar la versión del extractor al comparar resultados.

## 3. Fases del procesamiento

1. **Verificar el documento.** Comprobar institución, carrera, URL final, SHA-256, tipo, tamaño, fecha y sede declarada. Trabajar sobre la instantánea; no cambiar silenciosamente de versión mientras se procesa.
2. **Clasificar páginas y regiones.** Identificar dónde aparece la malla, títulos semestrales, columnas, filas y pies. Portada, testimonios, menciones y microcredenciales mantienen su categoría propia. Sus palabras no son materias semestrales por defecto.
3. **Recuperar texto con posición.** Usar bloques, palabras o spans con coordenadas, conservando orden y fuente. Evaluar por región controles inesperados, símbolos anómalos, palabras fragmentadas y caracteres de reemplazo. La decisión no depende únicamente del total de caracteres de la página.
4. **Reconocer regiones ilegibles.** Aplicar OCR al recorte que necesita lectura y conservar su coordenada original, página, motor, idioma y versión. En una página completamente de imagen, reconocer la región curricular completa. Acotar píxeles, tiempo y memoria. El ruido de una fotografía de portada no alimenta la lista de asignaturas.
5. **Reconstruir la estructura.** Asociar cada fila con su columna y encabezado semestral. Ordenar por semestre y coordenada vertical; unir líneas del mismo nombre y separar filas distintas. Preservar electivas, proyectos y símbolos como atributos pendientes de interpretación. No inferir prerrequisitos de un icono sin leyenda verificable.
6. **Validar y revisar.** Detectar semestres ausentes, duplicados, candidatos sospechosos y falta de evidencia. Presentar nombre, semestre, página, región y método al revisor. Una asignatura pasa al catálogo únicamente después de la revisión correspondiente.

Los límites de región deben derivarse del layout. Un único corte horizontal para toda la página puede eliminar una columna cuyo bloque semestral termina más abajo. Las coordenadas `words`/spans permiten recuperar títulos multilínea o separar dos columnas que PyMuPDF agrupó en un mismo bloque. La relación entre título y filas debe respetar el área de cada columna, en vez de depender de una sola distancia fija para todos los PDFs.

## 4. Evaluación reproducible

La referencia manual debe registrar PDF/hash, página, región, nombre exacto, semestre, categoría y decisión del revisor. Las correcciones quedan versionadas y separadas de las predicciones del extractor. La comparación debe permitir normalizaciones declaradas de espacios, mayúsculas y puntuación, conservando siempre el texto original.

Medir al menos precisión y cobertura de asignaturas, asignación correcta de semestre, duplicados, nombres partidos, entradas comerciales falsamente aceptadas, error de caracteres en regiones OCR y porcentaje con evidencia completa. Reportar tiempos, páginas omitidas y errores de lectura junto con las métricas. Mantener conjuntos independientes de desarrollo y evaluación por documento/carrera para evitar que las páginas del mismo folleto aparezcan en ambos.

La aceptación inicial de esta instantánea requiere recuperar los ocho semestres y las 54 entradas visuales, incluido el octavo semestre de tres entradas, sin añadir portada, menciones ni microcredenciales como nuevas materias. Ese criterio de caso sirve de regresión; no justifica publicar automáticamente otras mallas ni reemplaza una revisión de vigencia.

## 5. Qué significa mejorar o entrenar

Ajustar detección de regiones, normalización de guiones, orden de columnas y controles de calidad mejora el pipeline; no equivale a entrenar Tesseract. Registrar materias verificadas construye un conjunto etiquetado útil para evaluación. Solo después de reunir etiquetas suficientes, separar evaluación y documentar un experimento puede plantearse entrenamiento o ajuste de un modelo de reconocimiento o extracción.

La creación de fragmentos, reconstrucción de BM25 y generación de embeddings organizan o indexan evidencia. No entrenan por sí solos el OCR, no demuestran aprendizaje de estructura curricular y no validan un predictor vocacional. Ninguna salida no revisada se incorpora como ejemplo correcto de entrenamiento.

Un clasificador binario de bloques curriculares frente a bloques decorativos sí constituye un entrenamiento supervisado específico cuando utiliza etiquetas revisadas, parámetros aprendidos y evaluación separada. Debe declarar qué documento aporta cada ejemplo, cómo se eligieron los negativos y qué documentos quedaron fuera del ajuste. Un holdout de bloques de este único folleto mide desempeño interno de ese caso; no demuestra generalización a otra carrera, sede, tipografía o universidad. Ese clasificador decide candidatos/áreas, sin entrenar Tesseract ni aprobar las materias por sí mismo.

## 6. Presentación y publicación

La pantalla prioriza semestres y materias candidatas, seguida de advertencias concretas y evidencia consultable. Permite abrir o descargar el PDF con nombre humano; el SHA-256 permanece interno. La cobertura de lectura se expresa por separado de la revisión académica. Los cambios de lectura conservan la instantánea y versionan los fragmentos para que un fallo no invalide el resultado anterior.

La aprobación editorial sigue el [gobierno de conocimiento](KNOWLEDGE_GOVERNANCE.md). La colección, OCR, extracción de candidatos y descarga no modifican por sí solos las recomendaciones del estudiante, el catálogo publicado o el índice vectorial.

## 7. Oferta por sede en la ficha de carrera

Revisión del 09/10/2026: las 17 instantáneas HTML guardadas incluyen un bloque «Disponible en:» dentro de `main.career-page > div.bg-primary`. Su estructura relevante es un `span` con esa etiqueta y un `div` hermano con las siglas `CBBA`, `EA`, `LPZ` y `SCZ`. Los estados se codifican en las clases: `opacity-100` junto con `border-b-2` para sedes activas; `opacity-50` para las no activadas. La búsqueda debe quedar acotada a ese bloque del encabezado de la carrera cuya identidad ya se comprobó, excluyendo menú, pie y enlaces generales de sedes.

La [ficha oficial de Arquitectura](https://unifranz.edu.bo/carreras/arquitectura/) enumera visualmente las cuatro siglas, pero la instantánea `8df0fd1f27ffbb0174dd88a5e0de10c3d079c5d135f5019bf3692923e68adf87` activa solamente Santa Cruz. La Paz, El Alto y Cochabamba tienen `opacity-50`; su sección Tour 360 enlaza únicamente Santa Cruz. Una conversión de HTML a texto pierde la diferencia de opacidad y devolvería falsamente cuatro sedes. No se debe considerar que una sigla presente en el texto acredita oferta.

Se volvió a consultar esa URL por el transporte HTTPS validado durante la revisión: respuesta HTTP 200, URL final idéntica y SHA-256 `f6c78ef96db201b7c64e77cd59b2b26f97fc3104abd816163ed703e81ddff38f`. El HTML actual conservó exactamente los estados anteriores: Santa Cruz activa y las otras tres siglas en `opacity-50`. Esta consulta de comprobación no sustituyó la instantánea guardada ni el manifiesto.

Las sedes activadas en las instantáneas guardadas del 09/10/2026 son:

| Carrera | Sedes activas en la ficha guardada |
| --- | --- |
| Derecho | Cochabamba, El Alto, La Paz, Santa Cruz |
| Psicología | Cochabamba, El Alto, La Paz, Santa Cruz |
| Comunicación y Periodismo Digital | La Paz, Santa Cruz |
| Negocios y Gestión Empresarial | Cochabamba, El Alto, La Paz, Santa Cruz |
| Turismo y Hotelería | El Alto, La Paz, Santa Cruz |
| Gastronomía y Artes Culinarias | La Paz, Santa Cruz |
| Auditoría Financiera y Control de Gestión | Cochabamba, El Alto, La Paz, Santa Cruz |
| Ingeniería Comercial | Cochabamba, El Alto, La Paz, Santa Cruz |
| Ingeniería Económica y Financiera | Cochabamba, La Paz |
| Arquitectura | Santa Cruz |
| Diseño Digital y Producción Transmedia | Cochabamba, El Alto, La Paz, Santa Cruz |
| Publicidad y Marketing | Cochabamba, La Paz, Santa Cruz |
| Bioquímica y Farmacia | Cochabamba, El Alto, La Paz, Santa Cruz |
| Enfermería | El Alto, Santa Cruz |
| Medicina | Cochabamba, El Alto, La Paz, Santa Cruz |
| Odontología | Cochabamba, El Alto, La Paz, Santa Cruz |
| Ingeniería de Sistemas e Innovación Digital | Cochabamba, El Alto, La Paz, Santa Cruz |

Esta tabla reproduce la evidencia de instantáneas y no acredita cupos, apertura de grupo ni admisión para otra gestión. El resultado del parser debe guardar código, nombre de sede, estado visual, selector/fragmento, fecha y hash HTML. Una sede marcada en baja opacidad puede usar el estado `NO_INDICADA` y la etiqueta «No indicada como disponible en esta ficha», sin inventar una afirmación universal de inexistencia. Si falta la etiqueta, cambian las clases o hay señales contradictorias, la oferta queda pendiente de verificación; no se hereda de otra carrera, de un menú global o de la existencia de una malla PDF general. La sede del PDF se atribuye únicamente si su contenido o rótulo académico la declara; la oferta de la ficha no demuestra por sí sola que cada PDF sea una versión específica de esa sede.

### Implementación y comprobación de sedes

`sedes_universitarias.py` interpreta únicamente los indicadores del bloque «Disponible en:» de la carrera verificada. Guarda `campus_availability` (estado individual), `campus_evidence` (fuente, fecha y documento) y `campuses` (solo sedes activas). Si encuentra indicadores duplicados, clases ambiguas o identidad distinta, mantiene pendiente la oferta. No toma ciudades de menús, testimonios ni direcciones del pie.

Posteriormente, `scripts/actualizar_sedes_unifranz.py` sí volvió a descargar y guardar la ficha actual de Arquitectura, conservando sus versiones anteriores. La oferta persistida es Santa Cruz. Las otras 16 carreras usan sus instantáneas verificadas del mismo día; no se atribuye una consulta nueva a cada una. Los filtros muestran La Paz (15), El Alto (12), Cochabamba (12) y Santa Cruz (16). Se comprobó en navegador que buscar Arquitectura dentro de La Paz no devuelve oferta y que Santa Cruz sí la muestra con su evidencia.

Estas propiedades se agregan al manifiesto documental existente para evitar que la oferta de una carrera se confunda con una malla por sede. No se añadieron tablas ni se publicaron nuevas ofertas al catálogo del estudiante. Una malla sin evidencia específica de sede queda pendiente de confirmación. La duración de Arquitectura conserva la frase publicada «4 años y medio», sin truncarla a cuatro años.

Pasaron 30 pruebas Python de colección/sedes, 17 de frontend y 11 Laravel con 55 aserciones. Pasaron compilación Vite/Blade, Ruff, mypy y Pint.

## 8. Entrenamiento ejecutado y validación de integración

Se ejecutó `scripts/entrenar_clasificador_curricular.py` con los 147 bloques de la copia de Psicología: 54 materias etiquetadas visualmente y 93 bloques de portada, encabezados, menciones y otro contenido fuera de la malla semestral. La división fija con semilla `20261009` dejó 117 ejemplos para ajuste y 30 para evaluación. La regresión logística usa nueve características de texto y posición curricular, 400 iteraciones y umbral 0,5. No requiere un servicio externo ni datos estudiantiles.

El entrenamiento tardó **0,201 segundos**. En la reserva obtuvo 6 verdaderos positivos, 23 verdaderos negativos, 1 falso positivo y ningún falso negativo: precisión 85,7 % y sensibilidad 100 %. El clasificador se complementa con los límites de región, exclusión de encabezados y controles de texto. Son métricas de una reserva del mismo folleto, sin certificación de otras carreras ni universidades.

La prueba de integración completa recuperó **54 de 54 nombres y asociaciones semestrales** de la referencia visual, distribuidos `6/6/7/6/7/9/10/3`. Releyó las dos regiones dañadas en lugar de sustituir letras por suposiciones. La primera ejecución de esa lectura de Psicología tardó **1,8076 segundos**. Esta comprobación utiliza el mismo documento anotado y no constituye una evaluación externa del modelo.

Artefactos: `data/evaluation/psicologia_malla_etiquetada.json`, `data/evaluation/entrenamiento_clasificador_curricular.json` y el modelo local `data/knowledge/herramientas/clasificador_curricular.json`. El último permanece fuera de Git y puede reproducirse con el script. Los archivos originales y sus fragmentos se conservan.

`POST /conocimiento/colecciones/unifranz/analizar` exige Administrador y permiso de revisión; no recibe texto, URL ni aprobación desde el navegador. El Aporte reserva un único proceso y responde con estado real. `analisis_curricular.py` reconoce regiones, `clasificador_bloques.py` evalúa candidatos y `organizar_materias` los ordena/deduplica por encabezados de semestre. La pantalla consulta el avance, presenta carpetas seleccionables y detiene el escaneo al finalizar, respetando movimiento reducido.

El procesamiento real de las once mallas guardadas terminó en **116,9415 segundos**. Esta duración incluye lectura, reconocimiento localizado, clasificación, organización y escrituras del avance; no es tiempo de entrenamiento. Algunas mallas mantienen nombres pendientes, que se muestran como tales. Las seis descargas que no respondieron siguen pendientes y no forman parte de esta ejecución. La referencia visual exhaustiva de 54 materias cubre Psicología; los conteos de las otras mallas no están certificados por esa prueba.

Una corrección posterior separó encabezados fusionados de columnas: la detección usa las coordenadas de palabras y conserva los bloques para los nombres de materias. En Enfermería, esto recuperó los encabezados 1, 2 y 4 que faltaban; ahora se identifican ocho semestres. Una prueba sintética protege el caso de dos títulos «Segundo/Cuarto» dentro del mismo bloque. El reconocimiento localizado utiliza [PyMuPDF Pixmap.pdfocr_tobytes](https://pymupdf.readthedocs.io/en/latest/pixmap.html#Pixmap.pdfocr_tobytes).

## 9. Verificación final de la entrega

Pasaron seis pruebas nuevas de análisis Python (incluida lectura localizada real y contraste de los 54 nombres), 10 Laravel con 52 aserciones y 46 de frontend. Pasaron Ruff, mypy del analizador/clasificador, Pint, compilación Vite y caché Blade. La inspección de escritorio confirmó selección del octavo semestre, sus tres materias y que el escaneo solo aparece durante el trabajo. Se comprobó el tema oscuro en escritorio; el intento de captura móvil tuvo una limitación del navegador, por lo que no se certifica QA visual móvil completa en esta entrega. El CSS utiliza dos columnas estrechas y cuatro en escritorio, con movimiento reducido y botones accesibles.
