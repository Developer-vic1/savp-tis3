# Validación final de Fusion_Sistema

Fecha: 2026-10-01. Estado de los gates locales: **SAVP_FUSION_SYSTEM_READY**, limitado al entorno validado. Publicación y SHA final se verifican después del commit/push y se entregan en la respuesta final; este archivo no intenta contener el hash de su propio commit. La base institucional no se migró ni se usó para las pruebas.

## Git y alcance

- BRANCH: Fusion_Sistema. BASE SHA: `1303d9bb0f9a89d9d2e97b8e95985610d7beadfd`.
- Estado inicial: limpio; los cambios siguientes fueron realizados en este checkout. Sin cambios iniciales inesperados que incorporar.
- Commit/publicación: un commit previsto, `Integracion funcional y cierre SAVP PETER 2 PETER 3`; exclusivamente origin/Fusion_Sistema. Sin merge final ni cambios en otros worktrees/ramas.
- FINAL LOCAL SHA / FINAL REMOTE SHA / WORKING TREE / COMMITS CREADOS: resultado de la comprobación de publicación en la entrega final.

## Procedencia y corpus

- SOURCE COUNT: 12 snapshots locales. Registro separado de 11 referencias (7 externas, 4 artefactos locales). REFERENCE COUNT: 11.
- Los snapshots HTML/PDF no cambiaron. Las referencias externas se validan como metadata, no como contenido local descargado.
- CORPUS CHUNKS: 773. CORPUS UNIQUE IDS: 773.
- CORPUS SHA: `3d46bde6855fc0ea23ef8f02d16f865bd797db1b5bed996b5b9d06152b2affde`.
- Comparación con BASE: mismos 773 IDs y textos; sólo 26 campos document_hash cambiaron, correspondientes a los cuatro HTML. PDFs/OCR se preservaron.

| SOURCE HASHES (snapshots) | SHA-256 |
|---|---|
| BO-ME-CURRICULO-BASE-2012 | `sha256:25c921ad12af79fa695856f51500706a0c3644522c5a6cb6e351a817572feed5` |
| BO-ME-BTH-RM-0244-2023 | `sha256:4e52e97cdc7ec18f0774db1bb88fb5c9b1119e8d2892877aecf4388055c0990f` |
| BO-UCB-LP-SIS-MALLA-2026 | `sha256:dc5710e7015b0475c03239e35175f63af58faa69820720fe5085a31fb7ae51c2` |
| BO-UCB-LP-SIS-PROFILE-2026 | `sha256:00f649e7065aa0865a6229cf5e0d33c669a425aaf0ebd7d63eb8d4e719b4872a` |
| BO-UCB-LP-PSI-MALLA-2026 | `sha256:b0123e032f4475fbf1dbd03802f34a9cebbc5e833827c4d756da5113fc38eb4a` |
| BO-UCB-LP-PSI-PROFILE-2026 | `sha256:d7a463df173ed5bb3a701673d26325e6278a52b35c97f575bf902c1f08469408` |
| BO-UCB-LP-CIV-MALLA-2026 | `sha256:831af84148a2dae9a909f84318dffaa43fa55bada1ad0da7ef24d022d7c7e8d9` |
| BO-UCB-LP-CIV-PROFILE-2026 | `sha256:313b8f661595ec040c048a28137c001bfe83ab3522f2b05331614676239410c9` |
| BO-UCB-LP-IAM-MALLA-2026 | `sha256:6f53c96223e67ba897f4684f0709150221d4fcc8680cb8adf39d7046f16a98e4` |
| BO-UCB-LP-IAM-PROFILE-2026 | `sha256:d13dd91d9eea56fb9e6949225d15779c31dfc8f51b992bfab37733af05961f98` |
| BO-ME-EVALUACION-REGULAR-RM-0190-2024 | `sha256:6cff6cc77f849225b122f4202ad8656ba808fb886be44cc647f6e1128f28b93a` |
| BO-UMSA-LP-CIV-PLAN-2023 | `sha256:750f43d245f1e87a9314fd7a260ed19defe60d2385acc06a0ff4d07cb8b8e582` |

## Índices

| Modelo | Vectores | Dimensión | Corpus SHA |
|---|---:|---:|---|
| sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2 | 773 | 384 | `3d46bde6855fc0ea23ef8f02d16f865bd797db1b5bed996b5b9d06152b2affde` |
| intfloat/multilingual-e5-small | 773 | 384 | `3d46bde6855fc0ea23ef8f02d16f865bd797db1b5bed996b5b9d06152b2affde` |

Embeddings reales por sentence-transformers, CPU, FAISS exacto normalizado; no hashes ni vectores simulados. Python 3.14.0, Torch 2.13.0+cpu, sentence-transformers 6.1.0, Transformers 5.17.0, FAISS 1.15.1. Revisión exacta del modelo no expuesta por el runtime: `UNAVAILABLE_FROM_RUNTIME`; no se afirma una revisión de Hugging Face fijada.

## Retrieval DEV

DEV se ejecutó para ambos modelos tras reconstruir índices. No hubo tuning después de TEST. Métricas agregadas sobre 13 consultas con chunks relevantes; 2 consultas sin relevantes se informan aparte en los artefactos.

| Modelo / modo | Recall@1 | Recall@3 | Recall@5 | MRR | nDCG@5 | mediana ms | p95 ms |
|---|---:|---:|---:|---:|---:|---:|---:|
| sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2 / semantic | 0.0897 | 0.2051 | 0.2821 | 0.2718 | 0.2173 | 27.896 | 24162.9386 |
| sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2 / hybrid | 0.141 | 0.2821 | 0.3846 | 0.3489 | 0.3066 | 37.1268 | 48.2568 |
| intfloat/multilingual-e5-small / semantic | 0.1667 | 0.2692 | 0.5128 | 0.3943 | 0.3621 | 45.502 | 8265.0034 |
| intfloat/multilingual-e5-small / hybrid | 0.2564 | 0.4744 | 0.6026 | 0.5238 | 0.4817 | 56.363 | 199.0111 |

El p95 semántico de DEV incluye carga inicial fría. No se eliminó ni se presentó como rendimiento caliente.

## Freeze y TEST

- SELECTED MODEL: `intfloat/multilingual-e5-small`. PARAMETERS: `{"top_k": 10, "rrf_k": 60, "semantic_weight": 1.0, "lexical_weight": 1.0, "bm25_k1": 1.5, "bm25_b": 0.75}`.
- FREEZE SHA (bytes): `197b52aadccba4a260a3e7217b0a9f14b7ba94bc6d48eafa573b42660d5ff861`.
- DEV SHA congelado: `01a2b09c35a31ecd496173992c6a242674b02fece0787635e74bb1342e046cb5`. PARAMETER STATUS: `EXPERIMENTAL_UNCHANGED`.
- Artefactos históricos de BASE se conservan con sufijo historical-1303d9b, separados de la evidencia actual. La regla .gitattributes impide alterar los bytes de DEV/freeze/TEST por normalización de saltos de línea.
- TEST se ejecutó una sola vez después del freeze. Los verificadores posteriores leen resultados; no reevalúan TEST.

| TEST E5 híbrido | Valor |
|---|---:|
| recall_at_1 | 0.381 |
| recall_at_3 | 0.869 |
| recall_at_5 | 0.8929 |
| mrr | 0.6548 |
| ndcg_at_5 | 0.7043 |
| median_ms | 44.1105 |
| p95_ms | 51.4384 |

VERIFY RETRIEVAL: PASS, exit 0. Un PASS de procedencia/integridad no demuestra retrieval perfecto: Recall@1 = 0.3810 y puede faltar evidencia.

## Gates Peter 3 y Laravel

| Campo | Resultado |
|---|---|
| VERIFY SOURCES / CORPUS / E5 / MiniLM | PASS; hashes, 773 IDs y filas/dimensión comprobados |
| VERIFY PETER3 | PASS, exit 0; log local verify-peter3-release.log |
| PYTEST | 132 passed, 1 skipped (OCR real opt-in RUN_REAL_OCR no habilitado), 0 failed |
| COVERAGE | 91%; mínimo 90% satisfecho. No se repite el 92% histórico |
| RUFF | PASS |
| MYPY | PASS: app/scripts 83 archivos, repositorio 113 |
| PROMPTS / INJECTION | 13/13; 0 éxitos de inyección evaluada |
| HTTP SMOKE Python | PASS: TestClient en proceso, 8/8 scores concurrentes; no socket real |
| PHP VERSION / LARAVEL VERSION | 8.4.13 / 13.6.0 |
| PHP ARTISAN TEST | 236 passed, 7 skipped, 0 failed; 760 assertions |
| AUTH / RBAC / STUDENT SCOPE | PASS: login real, permiso revocado 403, otra identidad no ve snapshot ajeno |
| Composer / rutas / Blade / PHP syntax | PASS: composer validate, route:list, view:cache, 37 archivos PHP sin error |

Los 7 skips son funciones de API tokens/verificación de email deshabilitadas y el caso de pantalla de registro deshabilitado cuando esa feature está habilitada. No queda un skip por falta de SQLite en esta ejecución. La activación temporal usa una INI de proceso heredada por el subproceso Artisan, luego eliminada; php.ini permanece intacto. Tests con RefreshDatabase reconstruyen únicamente memoria aislada. No hubo seeders ni escrituras institucionales.

## Frontend y npm

| Campo | Resultado |
|---|---|
| NODE / NPM | 25.0.0 / 11.6.2 |
| NPM CI / VITE BUILD | PASS; Vite 8.3.2 |
| NPM AUDIT LOW / MODERATE / HIGH / CRITICAL | 0 / 0 / 0 / 0 |
| REMAINING RISKS | Ningún aviso npm conocido en esta ejecución; no es certificación universal de seguridad |

Antes: 1 low, 2 moderate, 6 high, 2 critical. Clasificación runtime/build/dev, rutas, avisos y versiones en [FUSION_NPM_AUDIT.md](FUSION_NPM_AUDIT.md). Sin audit fix --force ni saltos de major directos. CSS y JS globales preservados.

## Integración, persistencia y UI

| Campo solicitado | Resultado / evidencia |
|---|---|
| PETER3 CLIENT / HEALTH | PASS, cliente existente ampliado y Uvicorn por socket 127.0.0.1:8001 |
| RIASEC INSTRUMENT / SCORE | PASS, 30 ítems oficiales; raw 1–5; score único en Python |
| PRECHECK | PASS, análisis bloqueado antes de network si faltan obligatorios; BTH no aplica no bloquea |
| ANALYSIS V2 | PASS, snapshot COMPLETE, pseudónimo validado y input_hash |
| KNOWLEDGE / TUTOR | PASS, respuesta HTTP 200 con fuentes/trace; tutor STRUCTURED |
| TRACE ID | PASS, coherencia header/body/version/pseudónimo; trazas en fusion_real_e2e.json y benchmark |
| TIMEOUT | Límites 5 s para salud/instrumento/score; consultas default 10, cap 30; precarga default 90, cap 180; conexión default 3, cap 10 |
| 503 / 422 / 401 / 403 / 500 | PASS de tests de cliente con HTTP fake; no se afirma que todos ocurrieron en servicio real |
| SERVICE DOWN | PASS: conexión rechazada real en navegador, aviso accesible; score guardado permanece; test de fallo preserva snapshot |
| RIASEC RAW / RIASEC SCORE / ANALYSIS / TRACEABILITY | PASS en SQLite aislado; migración incremental up/down/up preserva actividad anterior |
| DASHBOARD / PRECHECK / RIASEC / RIASEC RESULT | PASS en navegador autenticado |
| ANALYSIS / KNOWLEDGE / TUTOR | PASS en navegador autenticado con backend real |
| RESPONSIVE / DARK MODE | Revisados a 1440x900 y 390x844; scrollWidth 1425/376 sin overflow horizontal; tema dark confirmado |
| SPANISH COPY / ACCESSIBILITY | Labels, legends, radios requeridos, foco visible por teclado, alerts/status, loading/disabled y estados vacíos; revisión básica, sin certificación WCAG integral |
| MOBILE DRAWER | PASS, menú responsivo existente abierto y cerrado con sus controles accesibles en navegador |

La prueba visual se realizó con datos explícitos de validación. Screenshots temporales permanecen en storage/logs y no se publican en Git. Las consultas a fuentes muestran fragmentos recuperados, no completan datos faltantes. La UI muestra limitaciones y procedencia.

## E2E real

- LOGIN / PRECHECK / RIASEC INSTRUMENT / RIASEC SCORE / PERSISTENCE / ANALYSIS V2 / KNOWLEDGE / TUTOR / SOURCES / LIMITATIONS / TRACE ID: PASS.
- Tráfico real: Laravel HTTP client → Uvicorn. Login y vistas también recorridos por navegador contra Laravel 8002. PHPUnit dispara Laravel en proceso y llama FastAPI por socket; se distinguen ambos alcances.
- Último score trace: `baf132aa-cadb-4317-b7b0-899be269f836`. Último análisis trace: `28737627-63e1-44b8-9fae-ad7172f382de`.
- Base de navegador: archivo SQLite exclusivo fusion-e2e.sqlite; pruebas de framework: :memory:. Sin estudiantes institucionales ni credenciales institucionales.

## Performance de integración

Windows 11, CPU AMD64 Family 25 Model 117, 8 núcleos físicos / 16 lógicos, memoria 16 GB, embeddings CPU. 20 repeticiones por operación. Se conservan todos los samples y máximos en laravel_http_performance.json. Mediana = media de observaciones 10/11; p95 nearest-rank = observación 19 de 20.

| Operación | PHP+HTTP mediana | PHP+HTTP p95 | FastAPI mediana | Transporte/PHP/scheduling mediana | min total | max total |
|---|---:|---:|---:|---:|---:|---:|
| health | 4.268 | 4.796 | 1.076 | 3.132 | 3.922 | 5.881 |
| riasec | 20.765 | 27.667 | 2.578 | 18.077 | 16.446 | 30.935 |
| analysis_v2 | 72.608 | 88.187 | 6.752 | 64.911 | 62.013 | 105.004 |
| knowledge | 47.805 | 66.403 | 39.711 | 7.605 | 42.984 | 67.277 |
| tutor | 48.247 | 64.708 | 41.042 | 7.001 | 42.171 | 66.263 |
| pipeline | 172.538 | 196.418 | 85.328 | 83.151 | 150.343 | 265.825 |

Primera precarga con Uvicorn recién iniciado (PID 32192, sin llamada previa al modelo): **24.76555 s**. Luego se midió el runtime caliente. Importación de aplicación en otro proceso fresco: **2.360 s**, sin cargar embeddings/FAISS/LLM; una observación, no SLA.

Overhead = duración exterior del cliente PHP menos Server-Timing de FastAPI; incluye transporte loopback, validación de contratos y scheduling. No aísla CPU PHP puro. El benchmark Python caliente separado registró mediana/p95: análisis 8.130/10.061, retrieval 49.515/59.571, tutor 51.362/60.489 y pipeline 93.174/115.188 ms. No son mediciones equivalentes a HTTP completo ni se ocultan outliers. Hubo otros procesos locales de validación; no se garantiza host exclusivo ni un SLA productivo.

## Limitaciones y bloqueos

- CITATION SUPPORT: NOT_EVALUATED.
- RIASEC BOLIVIA: sin validación psicométrica boliviana específica.
- BRIDGE: 17 relaciones, 15 inferencias documentales, 2 hipótesis, 0 relaciones directamente documentadas; requiere revisión experta.
- CROSSWALK: relación carrera/ocupación, no equivalencia ni garantía laboral.
- LLM: opcional, deshabilitado; no probado un proveedor LLM real.
- SUCCESS PREDICTION: no implementada ni afirmada. Sin XGBoost ni entrenamiento sintético.
- Catálogo no exhaustivo; retrieval puede omitir evidencia. Las fixtures son datos de prueba, no datos de entrenamiento ni validación psicométrica.
- BLOCKERS críticos de la validación aislada: ninguno. Habilitación institucional: migración/configuración aún requieren su despliegue autorizado; no se realizaron escrituras institucionales. PostgreSQL institucional y auditoría WCAG integral no se declaran probados.

## Evidencia verificable

[Arquitectura y operación](FUSION_INTEGRATION.md), [cambios por archivo](FUSION_FILE_CHANGES.md), [hashes y gates](FUSION_EVIDENCE.json), [tests Laravel](FUSION_LARAVEL_TESTS.json). Artefactos actuales/históricos en ai-service/data/evaluation.
