# Auditoría final pre-commit PETER 3

Fecha: **2026-09-30 (America/La_Paz)**. Evidencia: working tree local de `feature/APORTE`. Esta auditoría no atribuye autoría a archivos ni reutiliza resultados de informes previos. Los comandos de Python se ejecutaron con el intérprete 3.12.14 del entorno de Codex, porque `python`, `uv` y una `.venv` de proyecto no están disponibles. No se realizó commit ni operación Git de publicación.

## 1. AUDIT STATUS

**FAIL.** Hay bloqueos de integridad de fuentes e índices, piezas de alcance V2 ausentes y gates de ejecución sin verificar.

## 2. PRE-COMMIT DECISION

**NOT_SAFE_TO_COMMIT.**

## 3. BRANCH

`git branch --show-current` → `feature/APORTE` (PASS).

## 4. GIT WORKTREE

Snapshot anterior a las ediciones: `git status --short`, `git diff --stat` y `git diff` vacíos; `git diff --check` PASS. Modified/staged/deleted/untracked: 0/0/0/0. El estado final se registra en la sección 60.

## 5. FILES OUTSIDE PETER3 SCOPE

Ninguno en el snapshot inicial ni entre las ediciones de esta auditoría. No se editó Laravel, PostgreSQL ni la integración PHP.

## 6. PYTEST

Comando desde `ai-service`: `python -m pytest` con Python 3.12.14 disponible → exit 1, `No module named pytest`. **UNVERIFIED**; no hay cifras nuevas de passed/failed/skipped/warnings. Hay 46 funciones `test_*` declaradas antes de dos funciones añadidas en esta auditoría, pero ese conteo no equivale a tests ejecutados. Además, `test_source_manifest_is_official_unique_and_matches_local_hashes` exige hashes válidos y fallaría con los cuatro HTML actuales.

## 7. COVERAGE

`python -m pytest --cov=app` → exit 1, `No module named pytest`. **UNVERIFIED**; no reutilizar porcentajes históricos.

## 8. RUFF

`python -m ruff check .` → exit 1, `No module named ruff`. **UNVERIFIED**.

## 9. MYPY

`python -m mypy app scripts` y `python -m mypy .` → exit 1, `No module named mypy`. **UNVERIFIED**. No se añadieron ignores globales.

## 10. GIT DIFF CHECK

`git diff --check` PASS antes de editar y tras la corrección RIASEC. Repetir al cierre con el estado final.

## 11. FASTAPI IMPORT

`python -c "from app.main import app"` → exit 1, `ModuleNotFoundError: fastapi`. Los 61 archivos Python encontrados pasaron `ast.parse`; eso solo verifica sintaxis, **no** importación ni ausencia de ciclos en runtime.

## 12. ROUTES

Por `app/main.py` y los decoradores: `GET /health`, `POST /api/v1/analysis`, `POST /api/v1/knowledge/search`, `POST /api/v1/tutor/query`. `POST /api/v2/analysis` **ausente**; `/api/v1/tutor/message` ausente. OpenAPI no se pudo generar por falta de FastAPI.

## 13. HEALTH

El código declara 200 con `status=ok`, `service=savp-ai`, `service_version=0.1.0`, `schema_version=1.0`. HTTP real **UNVERIFIED**.

## 14. API V1

`analysis` y el contrato `schema_version=1.0` existen. `app/recommendation/engine.py` produce ranking, afinidad/preparación/compatibilidad globales y brechas según umbrales configurados. Clasificación: **LEGACY_EXPERIMENTAL_BASELINE**; backward compatibility HTTP no reejecutada.

## 15. API V2

**NOT_IMPLEMENTED.** Faltan router, contrato V2, `student_profile_v2.py`, motor de evidencia V2, snapshot/response V2 y versiones de bridge/crosswalk por respuesta. La validación `extra="forbid"` existe en los contratos V1, pero no demuestra un contrato V2.

## 16. RIASEC

El JSON tiene 30 reactivos, cinco por R/I/A/S/E/C. V1 recibe enteros 0–4 y suma 0–20. La [web oficial](https://onetinterestprofiler.org/es/) y [API oficial](https://services.onetcenter.org/reference/mpp/ip/ip_questions_30) usan 1–5; el [manual](https://www.onetcenter.org/dl_files/IP_Manual.pdf) documenta una codificación histórica 0–4. Se añadió conversión explícita 1→0 … 5→4 y pruebas; comprobación aislada con Python estándar PASS, suite pytest pendiente. El instrumento mide intereses, no aptitud, IQ ni éxito. La [licencia oficial de herramientas](https://www.onetcenter.org/license_tools.html) permite reproducción literal bajo CC BY-ND 4.0, sujeta a sus condiciones.

## 17. LEARNING ANALYTICS

Inspección: normalización `(score-min)/(max-min)*100`, media, mínimo/máximo, desviación poblacional, materias, áreas, períodos, pendiente lineal, cobertura, asistencia y actividad. Un faltante se representa con `None` en múltiples rutas; `missing != 0` se prueba en integración V1. Los cálculos no se reejecutaron. `consistency_ratio = max(0,1-pstdev/50)` es heurística V1 y entra indebidamente en `preparation` V1 junto con cobertura temporal.

## 18. RECOMMENDATION V2

**NOT_IMPLEMENTED.** V1 mezcla afinidad RIASEC/BTH/interés con pesos 0.6/0.25/0.15, preparación con pesos 0.8/0.1/0.1 (conocimiento/consistencia/cobertura temporal) y compatibilidad 0.55/0.45. Los objetivos RIASEC por carrera y `required=65/70/75` no tienen validación científica documentada. El bridge V1 contiene pesos experimentales. La calidad de evidencia no está separada como perfil V2; no hay relación ocupacional ni robustness V2.

## 19. INVARIANTS

V1 tiene pruebas estáticas de que cambiar notas no cambia afinidad, de evidencia insuficiente, orden estable y escala de notas; no hay suite V2 que cubra los 12 invariantes solicitados. En particular, **preparación cambia con cobertura temporal** por código, por lo que el invariante V2 correspondiente falla en el diseño heredado si se lo presenta como V2. No se identificó cálculo de IQ o probabilidad de éxito; eso no sustituye una prueba ejecutada.

## 20. SOURCES

Registro real: **11** fuentes (dos ministeriales, ocho UCB y una UMSA), todas marcadas `official=true`. Cada entrada tiene ID, título, institución, tipo, URL, fecha de consulta, ruta, hash, versión y estado. Faltan en las 11 entradas `country`, `authority_tier`, `scope`, `license` y `limitations` del esquema exigido. No hay fuente OIT/ISCO ni registro para el ID O*NET usado en `sources_used` de V1. La oficialidad y vigencia del contenido no se revalidaron en línea para cada fuente; los PDF ministeriales requieren revisión de vigencia.

## 21. HASHES

SHA-256 recalculado de los 11 snapshots: **7 VALID, 4 MISMATCH, 0 MISSING**. Mismatch: `BO-UCB-LP-SIS-PROFILE-2026` (esperado `e421d607…`, actual `995a0e85…`); `BO-UCB-LP-PSI-PROFILE-2026` (`787f2c5f…` vs `8a2cc097…`); `BO-UCB-LP-CIV-PROFILE-2026` (`7f58bc18…` vs `125e078f…`); `BO-UCB-LP-IAM-PROFILE-2026` (`c583444f…` vs `37720c8c…`). Los cuatro archivos tienen LF sin CRLF; el desajuste no se debe a conversión de saltos de línea. No se actualizaron hashes sin recuperar procedencia.

## 22. REFERENTIAL INTEGRITY

Catálogo, puente V1, corpus y dataset maestro de retrieval: **0 IDs de fuentes huérfanos** contra las 11 entradas. Los 20 query IDs son únicos y sus `relevant_chunk_ids` existen con source ID coincidente. Excepción global: `ONET-MINI-IP-2.0-ES` aparece en `app/domain/student_profile.py` como fuente usada y no figura en el registro ni en un registro interno explícito. V2/crosswalk/prompts/parameters no existen, así que la meta global `orphan_source_ids=0` no está cumplida.

## 23. BRIDGE V2

`secondary_university_v2.json` ausente. Hay 12 relaciones V1 con fuente secundaria/universitaria y revisión experta pendiente; no tienen los estados V2 exigidos ni `limitations` por relación. Ninguna relación se debe tratar como equivalencia curricular o validación experta.

## 24. PARAMETER GOVERNANCE

`data/parameters/registry.json` ausente. BM25 `k1=1.5`, `b=0.75`, RRF `k=60`, chunking `1200` caracteres/`180` de solape, `top_k` y timeout LLM son defaults de ingeniería sin clasificación documental central. No atribuirles evaluación empírica.

## 25. CATALOG

Cinco ofertas con IDs únicos: Sistemas, Psicología, Civil y Ambiental UCB La Paz, y Civil UMSA La Paz. Se preserva la identidad de las dos ofertas de Civil. `entry_profile=null` explícito; el perfil profesional UMSA Civil también es nulo. Los source IDs resuelven, pero cuatro perfiles HTML tienen hash inválido.

## 26. CROSSWALK

`career_occupation_v1.json` ausente. No se verificaron códigos ISCO/O*NET, versión ni perfiles RIASEC ocupacionales. **CARRERA ≠ OCUPACIÓN**.

## 27. CORPUS

Recuento desde `corpus.jsonl`: **689 fragmentos/IDs únicos, 11 fuentes, 628 DIGITAL, 35 OCR, 26 HTML**. El manifiesto registra 387 páginas digitales, 22 OCR y 11 advertencias. El chunking usa **caracteres** (1200 objetivo, 180 solape), conserva sección/página y metadatos. La procedencia completa está bloqueada por los cuatro hashes HTML.

## 28. FAISS

Los dos archivos `index.faiss` tienen cabecera `IxFI`, dimensión **384** y `ntotal=689`; cada `chunks.jsonl` tiene 689 IDs en el mismo orden que el corpus. Texto, título y sección usados para embeddings coinciden exactamente. Pero los dos manifiestos registran `corpus_sha256=216b088a…` y el corpus actual es `fe64464f…`; las copias del índice carecen de metadatos OCR de 35 filas. Integridad **FAIL**. No se reconstruyó ni se alteró un hash sin validar las fuentes de origen.

## 29. RETRIEVAL

Existe búsqueda semántica, BM25 y fusión RRF; E5 usa `query: `/`passage: ` en el código. `selected.json` selecciona `intfloat/multilingual-e5-small`. La evaluación vigente no pudo reejecutarse (`scripts/evaluate_retrieval.py` falla al importar `psutil`). Las métricas de `selected.json` son históricas, no PASS actual.

## 30. RETRIEVAL DEV

`retrieval_dev.json` ausente. **NOT_EVALUATED**; no hay partición que permita ajustar sin tocar TEST.

## 31. RETRIEVAL TEST

`retrieval_test.json` ausente. Las 20 consultas maestras tienen IDs y referencias existentes, pero no demuestran disyunción DEV/TEST ni soporte semántico de cada `expected_answer`. **NOT_EVALUATED**.

## 32. PROMPT ENGINEERING

`app/prompts/**`, registro, versiones, hashes y evaluación de prompts ausentes. El adaptador LLM contiene reglas en código y JSON de evidencia. **PARTIAL**, sin evaluación reproducible.

## 33. PROMPT INJECTION

No hay suite adversarial de usuario/documentos. El LLM restringe IDs de citas a IDs recuperados, pero no verifica si una cita **soporta** una afirmación ni aísla instrucciones dentro de texto recuperado. La protección contra instrucciones maliciosas es **UNVERIFIED**.

## 34. TUTOR

Ruta y proveedor estructurado offline presentes. Por código se abstiene sin evidencia y evita escoger carrera ante frases reconocidas; produce extractos con fuente. El runtime, determinismo real y citas no se probaron en esta auditoría por dependencias ausentes. Los extractos recuperados no constituyen validación automática del claim.

## 35. LLM LOCAL

Desactivado por defecto; adaptador HTTP `llama.cpp` con timeout y fallback estructurado. Runtime `127.0.0.1:8091`, calidad, TTFT, tokens/s, RAM/VRAM: **NOT_EVALUATED_RUNTIME_UNAVAILABLE**; no se hizo inferencia.

## 36. TECHNICAL E2E

No existe `tests/integration/test_peter3_full_e2e.py`. No puede recorrerse el pipeline exigido porque V2, crosswalk y prompt/citation evaluation faltan. Las pruebas de integración V1 existentes no son ese E2E. **NOT_IMPLEMENTED**.

## 37. DEMO STATUS

**NO_USER_DEMO_IMPLEMENTED.** Hay scripts técnicos de benchmark, corpus, retrieval y sensibilidad; no son módulo visible de demo. No se encontró `run_peter3_full_demo.py` ni `run_analysis_v2_demo.py`.

## 38. PERFORMANCE

Benchmark actual **NOT_EVALUATED**: `scripts/benchmark.py` falla al importar `psutil`. `PERFORMANCE.md` conserva una medición histórica de análisis V1 del 2026-09-28, marcada como tal. No hay medición actual separada de analysis/retrieval/tutor/full pipeline, warmup, mediana y p95.

## 39. SECURITY

Escaneo por patrones comunes en `ai-service/**` y `docs/peter3/**`: sin coincidencias de claves o secretos en texto examinado; esto no es un detector exhaustivo. La API key es **opcional** y, vacía, no autentica rutas V1. Los errores inesperados devuelven mensaje genérico con trace ID; el logger conserva excepción local. No se probaron PII, inyección, JSON inválido, timeout ni indisponibilidad de dependencias en HTTP.

## 40. REPRODUCIBILITY

Python disponible: **3.12.14**. Versión declarada del servicio `0.1.0`; criterios `v1_experimental`; bridge `secondary-university-v1-experimental`; catálogo `bo-careers-1.0.0`; corpus `bo-official-corpus-1.0.0`; retrieval `bm25-rrf-v1.0.0`; instrumento `2.0-es-2025`; modelo seleccionado E5. Versiones V2, crosswalk y prompt: ausentes. Versiones instaladas de dependencias: no disponibles.

## 41. README

Se actualizó `ai-service/README.md` para distinguir V1 experimental, V2 ausente, escala RIASEC, estado pre-commit y requisitos del tutor. Sus comandos de instalación siguen sin reejecutarse en este entorno.

## 42. DOCUMENTATION

`CURRENT_STATE.md`, `REQUIREMENTS_MATRIX.md`, `LIMITATIONS.md`, README, API, conocimiento, fuentes, puente, OCR, retrieval y RIASEC se corrigieron o marcaron con estado actual. `DEVELOPMENT_LOG.md`, `MASTER_CONTEXT.md` y `PERFORMANCE.md` se etiquetaron como registros históricos. Queda una revisión editorial completa de los demás documentos y la ejecución que respalde claims funcionales.

## 43. DSRM COMPLIANCE

El informe académico original no está disponible; la matriz evalúa los requisitos descritos en el encargo, no el texto completo del PDF.

| Activity | Expected evidence | Actual evidence | Status | Gap | Action |
|---|---|---|---|---|---|
| 1. Problema/motivación | necesidad y contexto | `ARCHITECTURE.md`, `MASTER_CONTEXT.md` histórico | PARTIAL | informe original no revisado | contrastar con informe |
| 2. Objetivos | objetivos medibles | `REQUIREMENTS_MATRIX.md` actualizada | PARTIAL | V2 y criterios de evaluación sin cierre | fijar objetivos verificables |
| 3. Diseño/desarrollo | artefactos y trazabilidad | V1, corpus, índices, docs; V2 ausente | PARTIAL | integridad y alcance | resolver blockers |
| 4. Demostración | casos/ejecuciones reales | fixtures y tests V1 existentes en código | PARTIAL | E2E no ejecutado; no se exige UI demo | ejecutar escenarios/E2E |
| 5. Evaluación | pruebas, métricas, análisis | dataset maestro y resultados históricos | NOT_COMPLIANT | tests/DEV/TEST/prompt/performance actuales | evaluar de cero |
| 6. Comunicación | informe y documentación coherente | docs PETER 3 y esta auditoría | PARTIAL | informe académico ausente/desalineado | actualizar informe |

## 44. ICONIX COMPLIANCE

El análisis de robustez ICONIX (boundary/control/entity) es distinto de la sensibilidad del ranking.

| Artifact | Global Project | Peter3 | Status |
|---|---|---|---|
| Requerimientos | informe original no disponible | matriz presente, actualizada | PARTIAL |
| Modelo de dominio | no verificado | clases Python, sin modelo ICONIX probado | UNVERIFIED |
| Casos de uso | no verificado | API/contratos, sin casos ICONIX completos | UNVERIFIED |
| Análisis de robustez ICONIX | no verificado | no identificado | NOT_VERIFIED |
| Diagramas de secuencia | no verificado | no identificados | NOT_VERIFIED |
| Modelo de clases | no verificado | clases Python no equivalen al artefacto | NOT_VERIFIED |

## 45. PROJECT PHASES COMPLIANCE

Fases citadas en el encargo; informe original no disponible para contraste literal.

| Phase | What Report Promises | What Exists | Peter3 Responsibility | Status | Action Required |
|---|---|---|---|---|---|
| FASE 1 | sistema base académico | Laravel/BD fuera de esta auditoría | OUTSIDE_PETER3_SCOPE | UNVERIFIED_GLOBAL | revisar con PETER 2 |
| FASE 2 | perfil académico-vocacional | V1 academia, RIASEC, BTH y actividad; no V2 | parcial | PARTIAL | completar/verificar V2 |
| FASE 3 | análisis/recomendación | ranking V1 experimental; V2 no existe | principal | PARTIAL | alinear alcance y lenguaje no predictivo |
| FASE 4 | simulación y validación de decisión | no hay interacción de escenarios/elección contrastada | no implementada como escrita | NOT_IMPLEMENTED_AS_WRITTEN | UPDATE_REPORT o diseñar funcionalidad justificada |

## 46. XGBOOST STATUS

El código PETER 3 no usa XGBoost; README lo excluye. No se encontró el informe académico actual para comprobar si su objetivo general aún lo menciona. **REPORT_UPDATE_REQUIRED si el informe mantiene esa promesa**; no implementar XGBoost solo por redacción.

## 47. PREDICTIVE MODEL STATUS

No se identificó ground truth ni evaluación de predicción de éxito. El ranking V1 es heurístico y experimental; **no** debe presentarse como clasificador, aptitud, viabilidad STEM o probabilidad de éxito.

## 48. REPORT MISMATCHES

Informe académico original **UNAVAILABLE** en el repositorio. Según el encargo, la fase 4 promete simulación/validación que no existe; posibles menciones de XGBoost/predicción requieren comprobación directa. Los documentos PETER 3 sí contenían cifras viejas de 818 chunks y claims de suites pasadas; se actualizaron o etiquetaron históricos.

## 49. SCIENTIFIC LIMITATIONS

RIASEC refleja intereses; notas reflejan desempeño observado; BTH no establece equivalencia universitaria. Pesos, objetivos y umbrales V1 carecen de validación local. Falta de evidencia no implica cero, bajo nivel ni incapacidad. Similaridad semántica y robustez de ranking no validan pedagogía ni predicen éxito.

## 50. TECHNICAL LIMITATIONS

Dependencias no instaladas, V2 y artefactos de gobernanza ausentes, hashes de fuentes e índice inconsistentes, corpus no regenerado con snapshots verificados, falta de particiones y E2E, modelo de embeddings no cargado en runtime.

## 51. FILES CREATED

`docs/peter3/PETER3_FINAL_AUDIT.md`, `FINAL_STATUS.md` e `INTEGRATION_READINESS.md`.

## 52. FILES MODIFIED

`ai-service/README.md`, `app/riasec/scoring.py`, `data/instruments/onet_mini_ip_v2_es.json`, `tests/unit/test_riasec.py`; en `docs/peter3/`: `API_CONTRACT.md`, `CURRENT_STATE.md`, `DATA_CONTRACT.md`, `DEVELOPMENT_LOG.md`, `KNOWLEDGE_BASE.md`, `LIMITATIONS.md`, `MASTER_CONTEXT.md`, `OCR_PIPELINE.md`, `PERFORMANCE.md`, `REQUIREMENTS_MATRIX.md`, `RIASEC_IMPLEMENTATION.md`, `RIASEC_RESEARCH.md`, `SECONDARY_UNIVERSITY_BRIDGE.md`, `SEMANTIC_RETRIEVAL.md`, `SOURCE_GOVERNANCE.md`.

## 53. UNTRACKED FILES

Solo los tres archivos nuevos de documentación de la sección 51. No se encontró archivo PETER 3 ignorado accidentalmente en el snapshot inicial. Un `__pycache__` generado por el intento de importación se eliminó.

## 54. SECRETS CHECK

Sin coincidencias de patrones comunes en texto escaneado. `.env.example` está versionado; `.env` real está ignorado y no apareció en el inventario. **PARTIAL** por no usar un detector especializado ni inspeccionar contenido binario.

## 55. BINARY/LARGE FILE RISKS

`minedu_curriculo_sistema_educativo.pdf` pesa **35,467,762 bytes** y `minedu_reglamento_bth_2023.pdf` **7,539,406 bytes**. Cada índice FAISS pesa 1,058,349 bytes. Los PDF y modelos/cache deben revisarse según política Git antes de congelar; no se hizo commit. Los modelos descargados no están en el working tree y deben documentarse para instalación limpia.

## 56. INTEGRATION READINESS

**NOT_READY.** Contratos y errores V1 están documentados en `INTEGRATION_READINESS.md`; V2 ausente, datos/índices no íntegros, autenticación opcional y endpoints sin smoke test. No se implementó consumidor Laravel.

## 57. BLOCKERS

**CODE/DATA:** cuatro hashes HTML; hash de corpus en índices; ausencia de V2 y piezas exigidas (crosswalk, parámetros, prompts, DEV/TEST, E2E). **VERIFICATION:** entorno sin dependencias para pytest, coverage, Ruff, mypy, import, retrieval, API y benchmark. **DOCUMENTATION:** informe académico original ausente y fase 4 descrita sin implementación.

## 58. REQUIRED REPORT UPDATES

Cuando esté disponible el informe: retirar o justificar XGBoost y lenguaje predictivo, delimitar V1/V2, documentar fase 4 como `NOT_IMPLEMENTED_AS_WRITTEN` o definir su implementación, alinear DSRM/ICONIX con artefactos comprobables y citar resultados reejecutados.

## 59. SAFE-TO-COMMIT JUSTIFICATION

No se cumplen las condiciones para `SAFE_TO_COMMIT`: integridad de fuentes e índices falla y la verificación funcional no se ejecutó. La discrepancia del informe por sí sola podría tratarse como `REPORT_UPDATE_REQUIRED`, pero no elimina los blockers de código/datos.

## 60. FINAL GIT STATUS

`git status --short` tras las ediciones: **19 modified tracked, 3 untracked, 0 staged, 0 deleted**; todos dentro de `ai-service/**` o `docs/peter3/**`. `git diff --check` PASS. No se realizó `git add`, commit, push, merge, rebase ni PR.

## 61. NEXT STEP

**PETER 3 todavía no debe congelarse. Los siguientes blockers deben resolverse antes del commit:** recuperar/probar la procedencia de los cuatro HTML, regenerar o conciliar el corpus y ambos índices, completar o replanificar formalmente V2 y los artefactos faltantes, instalar las dependencias del lock y reejecutar todos los gates, incluido E2E y smoke HTTP. La decisión de commit corresponde al responsable Git.
