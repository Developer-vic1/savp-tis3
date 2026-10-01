# Checkpoint WIP de fase 2.1 — reconstrucción pendiente

## Actualización local del 2026-10-01

El checkpoint `b02a392` ya está publicado en `origin/work/peter3-mejoras-fase2`.
Un entorno limpio creado desde `uv.lock` reproduce el bloqueo de `torch._C`, por lo que
el intento de reconstrucción no guardó índices. Mypy 1.20.2 sí inicia allí y pasa
`mypy app scripts` (82 archivos) y `mypy .` (111 archivos) después de corregir tres
errores reales de tipos. La suite termina con 125 passed, 2 failed, 1 skipped y 92 % de
cobertura. El diagnóstico de `librt.internal` de abajo corresponde al entorno anterior.
La segunda laptop se sincroniza por GitHub, sin acceso remoto de ejecución desde esta sesión.

Este checkpoint preserva los cambios locales de fase 2 y prepara su continuación en una segunda laptop. **No es el commit final de fase 2 ni autoriza integración.** Rama: `work/peter3-mejoras-fase2`; base protegida: `ae386c741c850a89adc586131c9f974ac458e628`.

## Diagnóstico reproducido en la primera laptop

Python 3.12.10, Windows 11 10.0.26200, AMD64. Paquetes instalados desde `uv.lock`: Torch 2.14.0, sentence-transformers 6.1.0, transformers 5.17.0, faiss-cpu 1.15.1, mypy 1.20.2 y librt 0.15.0. `import faiss` funciona. `import torch` falla al cargar `torch._C` con `ImportError: DLL load failed ... Una directiva de Control de aplicaciones bloqueó este archivo`. `python -m mypy app scripts` falla igual al cargar `librt.internal`.

El registro `Microsoft-Windows-CodeIntegrity/Operational` contiene eventos **3033 y 3077** para ambos archivos `.pyd`, con política `{0283ac0f-fff1-49ae-ada1-8a933130cad6}` y exigencia de firma empresarial. `Get-AuthenticodeSignature` informa `NotSigned`. Esto identifica un bloqueo de política del equipo actual; no demuestra que mypy haya validado el código. Cambiar Python o alterar el algoritmo de embeddings no resuelve esa exigencia de firma.

Los dos fallos de pytest se clasifican `EXPECTED_STALE_INDEX`:

| Prueba | Evidencia | Causa |
|---|---|---|
| `tests/unit/test_index_integrity.py::test_faiss_indexes_cardinality_and_dimension` | Manifiesto `bda50b…`, corpus `ebd98f…` | Ambos índices conservan el hash anterior. |
| `tests/integration/test_peter3_full_e2e.py::test_peter3_full_e2e_with_real_corpus_and_selected_index` | Búsqueda HTTP 503 en vez de 200 | La carga del índice rechaza el hash anterior. |

El smoke HTTP real de esta máquina está en `ai-service/data/evaluation/http_smoke_phase21.json`:
health, instrumento, score y análisis V2 respondieron 200; knowledge y tutor, 503; 8/8
peticiones RIASEC concurrentes respondieron 200. Incluye latencias y trace IDs.

Una ejecución posterior del verificador global sufrió una demora anómala de 37 minutos en
pytest y el arranque HTTP superó su espera de 20 segundos; se interrumpió durante mypy.
El smoke independiente anterior sí respondió. El verificador ahora limita cada gate a 300
segundos para informar timeout en vez de quedar indefinidamente activo. No se declara PASS global.

Fuentes y corpus pasan `python scripts/verify_sources.py --upstream-only`: 12 snapshots válidos, 773 chunks, IDs únicos, sin textos vacíos ni fuentes huérfanas. SHA-256 definitivo: `ebd98f3fb35058af6ff074673cccc56053d9f2ee064ee31a86ed8d25c3e4c5ce`. E5 usa `query: ` y `passage: `; MiniLM permanece como alternativa. Ninguno de los dos índices fue alterado para aparentar validez.

## Continuación obligatoria en equipo compatible

Usar `C:\laragon\www\savp-tis3-aporte` en la segunda laptop, nunca el worktree antiguo del
escritorio. Hacer `git fetch origin`, comprobar la rama `work/peter3-mejoras-fase2` y que
`git rev-parse HEAD` y `git rev-parse origin/work/peter3-mejoras-fase2` sean
`b02a3926a5a5487a1c0dec70069e335c37eb2615`. Instalar Python 3.12 y dependencias según
`uv.lock`, en CPU. Antes de generar vectores, comprobar `import torch`, `torch.rand(1)`,
`import faiss` y `python -m mypy --version`. No usar CUDA ni embeddings sintéticos.

Las correcciones de tipos y esta revalidación del 2026-10-01 aún son cambios locales de la
primera laptop, posteriores a `b02a392`; GitHub no los transferirá hasta un commit autorizado.
Si la reconstrucción funciona en la segunda laptop, conservar los resultados reales y reunir
los cambios de código y documentación antes de ejecutar el gate final y crear ese commit.

Desde `ai-service`, ejecutar en este orden:

```powershell
python -m uv sync --locked --group dev
.\.venv\Scripts\python.exe scripts\verify_sources.py --upstream-only
.\.venv\Scripts\python.exe scripts\rebuild_indexes_phase21.py
.\.venv\Scripts\python.exe scripts\verify_sources.py
.\.venv\Scripts\python.exe -m pytest tests\unit\test_index_integrity.py --no-cov
.\.venv\Scripts\python.exe scripts\evaluate_retrieval_phase21.py dev
```

Revisar `data/evaluation/retrieval_dev_phase21.json`: métricas de queries con evidencia, casos sin chunk relevante y latencias de E5 y MiniLM, semántico e híbrido. Mantener los parámetros actuales salvo justificación en DEV. Después congelar **un** modelo evaluado en DEV; el ejemplo conserva E5:

```powershell
.\.venv\Scripts\python.exe scripts\evaluate_retrieval_phase21.py freeze --model-id intfloat/multilingual-e5-small
.\.venv\Scripts\python.exe scripts\evaluate_retrieval_phase21.py test
```

`test` exige el marcador de congelación, verifica hash y selección, y no sobrescribe un resultado TEST existente. No ajustar configuración a partir de TEST. Revisar manualmente `NO_ANSWER`, `AMBIGUOUS` y `ADVERSARIAL`, además de abstención y citas del tutor. La evaluación de soporte semántico de citas sigue `NOT_EVALUATED` hasta disponer de anotaciones por afirmación.

Después ejecutar prompts, tutor, E2E, smoke HTTP, suite con cobertura, Ruff, mypy y benchmark integral; finalmente `scripts/verify_peter3.py`. Registrar versiones, entorno y cualquier métrica obtenida sin sustituir los resultados históricos. Si todos los gates críticos pasan, actualizar la documentación de fase 2.1 y recién entonces crear el commit final solicitado y hacer push. Mientras tanto, `PETER3_NOT_READY`.
