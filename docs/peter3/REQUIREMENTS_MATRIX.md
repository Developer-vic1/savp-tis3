# Matriz de requisitos PETER 3

Fecha de auditoría: 2026-09-28  
Rama de trabajo: `feature/APORTE`  
Base exigida y verificada: `integration/savp-consolidado` (`a5fb7eac5efafa0ac9530cc41b77852869a740ae`)

Esta matriz es el control de alcance del aporte ingenieril. Un bloque solo puede marcarse
como `VERIFICADO` cuando existe implementación funcional, pruebas ejecutables, evidencia
reproducible y documentación consistente. La existencia de una interfaz, fixture o stub no
cuenta como implementación completa.

| ID | Requisito verificable | Estado inicial | Evidencia inicial | Criterio de cierre |
|---|---|---|---|---|
| R01 | Aislamiento Git y cambios solo en `ai-service/**` y `docs/peter3/**` | VERIFICADO | Worktree dedicado; HEAD/base coinciden; árbol solo contiene cambios permitidos | Auditoría final de `git status` y diff de rutas |
| R02 | Perfil estudiantil consolidado y trazable | VERIFICADO | Snapshot, hash, academia, asistencia, tareas, RIASEC, BTH e intereses consumidos por el flujo; suite 35/35 | Mantener en regresión E2E |
| R03 | Learning Analytics: promedios, áreas, tendencia, variabilidad y cobertura temporal | VERIFICADO | Normalización, agregaciones, cobertura y nulos probados; suite 25/25 | Mantener en regresión E2E |
| R04 | Learning Analytics: asistencia, cumplimiento, atrasos, notas y regularidad | VERIFICADO | Totales/detalle, notas, regularidad y evidencia insuficiente probados; suite 25/25 | Mantener en regresión E2E |
| R05 | RIASEC O*NET Mini-IP v2 en español, puntaje bruto/normalizado/cobertura | VERIFICADO | 30 ítems, bruto, normalizado y cobertura probados; suite 25/25 | Mantener en regresión E2E |
| R06 | Catálogo real de carreras bolivianas con fuentes oficiales | VERIFICADO | 2 universidades, 5 identidades de carrera, 11 fuentes oficiales con hash; suite 35/35 | Ampliación futura sin presentarlo como exhaustivo |
| R07 | Conocimiento oficial de secundaria/BTH | VERIFICADO | 2 fuentes ministeriales, ingesta completa y corpus versionado dentro de 818 chunks | Mantener vigencia bajo revisión |
| R08 | Puente secundaria/BTH–universidad | VERIFICADO | 12 relaciones versionadas con fuentes, justificación y `NEEDS_EXPERT_REVIEW`; suite 35/35 | Revisión experta sigue siendo limitación explícita |
| R09 | Afinidad experimental 60% RIASEC, 25% BTH, 15% interés declarado | VERIFICADO | Componentes, cobertura, pesos externos y separación de notas probados | Mantener sensibilidad y revisión experta |
| R10 | Preparación académica con cobertura y nulo si falta evidencia | VERIFICADO | Escalas, cobertura, umbral 0.40 y nulos probados | Mantener sensibilidad y revisión experta |
| R11 | Compatibilidad sugerida 55% afinidad, 45% preparación | VERIFICADO | Componentes/pesos visibles; sensibilidad 80/20 y 20/80 ejecutada | No denominar probabilidad de éxito |
| R12 | Ranking determinista sin LLM | VERIFICADO | Orden reproducible, desempate por ID, supresión a cobertura 0.70; suite 35/35 | Mantener en E2E |
| R13 | Fortalezas, brechas y ruta de refuerzo priorizada | VERIFICADO | Evidencia estructurada, fórmula de brecha y prerrequisitos probados | Mantener en E2E |
| R14 | Ingesta PDF digital, PDF escaneado, HTML y TXT | VERIFICADO | Pipeline y casos digital/imagen/HTML/TXT probados; corpus 818 chunks | Mantener en regresión |
| R15 | OCR en español y control de calidad | VERIFICADO | EasyOCR real: confianza 0.8691 en fixture; 22 páginas oficiales OCR; QA visual ejecutado | Tesseract no requerido; documentar costo CPU |
| R16 | Chunking con metadatos completos | VERIFICADO | 818 IDs únicos; fuente, URL, institución, tipo, página/sección, hash, versión, método y confianza validados | Mantener contrato en retrieval |
| R17 | Evaluación A/B de dos embeddings multilingües | FALTANTE | No existe | Dataset versionado y Recall@k/MRR/nDCG comparables y reproducibles |
| R18 | Índice vectorial FAISS exacto y versionado | FALTANTE | No existe | Construcción, persistencia, carga, manifiesto y prueba de recuperación |
| R19 | `POST /api/v1/knowledge/search` funcional | PLACEHOLDER | Responde 503 de forma deliberada | Búsqueda real con resultados, puntajes, citas y manejo de consulta vacía |
| R20 | Respuesta estructurada basada en evidencia | FALTANTE | No existe | Resumen, evidencia, incertidumbre, fuentes y contrato probado |
| R21 | Tutor estructurado y trazable | PLACEHOLDER | Respuesta fija sin fuentes | Recuperación, citas, límites, seguimiento y pruebas |
| R22 | Inferencia local real de LLM evaluada | FALTANTE | No existe | Modelo cuantizado ejecutado, español/grounding evaluados, RAM/VRAM/latencia medidos |
| R23 | Flujo E2E completo con fixture canónico | FALTANTE | Solo pruebas unitarias del núcleo | Script/prueba desde snapshot hasta ranking, búsqueda y tutor, sin stubs |
| R24 | Rendimiento reproducible | PARCIAL | Benchmark preliminar del núcleo | p50/p95 por etapa, recuperación, API y LLM, hardware y método documentados |
| R25 | Seguridad/privacidad y minimización de datos | PARCIAL | API key y notas iniciales | Amenazas, retención, logs, datos sensibles, pruebas de autenticación y límites |
| R26 | Documentación técnica solicitada y coherente con el código | PARCIAL | Documentos base; varios describen trabajo como futuro/opcional | Todos los documentos requeridos, comandos exactos y cero afirmaciones infladas |

## Regla de trazabilidad por bloque

Antes de implementar cada bloque se registran cinco elementos:

1. problema concreto que resuelve;
2. evidencia o fuente que justifica el diseño;
3. componente y archivos responsables;
4. contrato de entrada/salida y comportamiento ante evidencia insuficiente;
5. pruebas que demuestran el comportamiento normal, límite y de error.

Los documentos especializados de `docs/peter3/` son la fuente detallada de esa
trazabilidad. Esta matriz conserva el estado global y se actualizará únicamente con
resultados ejecutados.

## Hallazgos que bloquean una declaración de completitud

- La búsqueda de conocimiento devuelve actualmente un `503` intencional.
- El tutor actual no recupera ni cita evidencia.
- No existe todavía corpus oficial, catálogo universitario, puente curricular ni índices.
- No existe evaluación real de embeddings ni de un LLM local.
- No existe prueba E2E que conecte todos los componentes.

Por lo anterior, el aporte no se declara completo en este estado.
