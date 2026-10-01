# Contexto maestro de PETER 3

> Documento histórico de arranque. Su descripción del primer hito y del worktree original no describe el estado actual. La auditoría vigente está en `PETER3_FINAL_AUDIT.md` (2026-09-30).

Última actualización: 2026-09-28 (America/La_Paz).

## Identidad y alcance

**HECHO DOCUMENTADO.** PETER 3 construye un servicio Python/FastAPI autónomo para análisis académico-vocacional. Laravel conserva identidad, autorización, privacidad, persistencia institucional y el mapeo al contrato HTTP. Python no consulta PostgreSQL ni modelos Eloquent.

**DECISIÓN DE DISEÑO.** El servicio funciona sin OpenAI, GPU, XGBoost, OCR, embeddings o LLM. Esas capacidades son posteriores y opcionales.

**HECHO DOCUMENTADO.** Solo se crean o modifican `ai-service/**` y `docs/peter3/**`. No se realizan commit, push, merge, rebase, migraciones ni seeders.

## Realidad Git encontrada

- Repositorio original: `C:\laragon\www\savp-tis3`.
- Rama original activa: `feature/incorporacion-prevenciones-integrales`, commit `33383566b12a5cb683baddabbe5f40f3ce649383`.
- La base local `integration/savp-consolidado` existe en `a5fb7eac5efafa0ac9530cc41b77852869a740ae`.
- El worktree original contenía cambios Laravel y ocho Markdown institucionales no versionados. Un cambio directo de rama fue rechazado por Git porque sobrescribiría tres archivos.
- **DECISIÓN DE DISEÑO.** Para preservar ese trabajo, `feature/APORTE` fue creada desde la base requerida en el worktree aislado `C:\laragon\www\savp-tis3-aporte`.
- El worktree de PETER 3 estaba limpio al comenzar.

## Auditoría documental

Se leyeron completamente los tres Markdown versionados, los ocho Markdown institucionales no versionados encontrados en el worktree original y los cuatro informes históricos de `resources/INFORMES`.

| Grupo | Clasificación | Hallazgo |
|---|---|---|
| `README.md` | estado genérico | README estándar de Laravel; no describe SAVP. |
| `resources/markdown/policy.md`, `terms.md` | privacidad/legal | plantillas vacías. |
| `00_MASTER_ROLES_SAVP.md` | arquitectura, contratos, Git | confirma Laravel → DTO → cliente → PETER 3 y los cuatro endpoints conceptuales. No está versionado en la base. |
| `01_...` a `06_...` | roles, autorización, UX | delimitan alcances de Admin, Director, Secretaría, Regente, Docente y Estudiante. No están versionados en la base. |
| `07_MATRIZ_RBAC_GITFLOW.md` | RBAC, Git | confirma privacidad por alcance y degradación segura si PETER 3 falla. No está versionado en la base. |
| `resources/INFORMES/*.txt` | historia y estado | describen evolución Laravel/BD. Son antecedentes, no validación científica. |

## Frontera de código encontrada

No existen `AporteIngenierilClient`, DTOs de integración, `AnalysisService`, `KnowledgeService` ni `TutorService` acordados. Sí existe `DatosReporteVocacionalService`, que asigna perfiles RIASEC por nombre de especialidad y convierte promedios en una “compatibilidad”. También existe `EspecialidadTecnicaInteligente`, con mapas manuales de especialidad a RIASEC/carreras.

**DECISIÓN DE DISEÑO.** Esos cálculos Laravel no se importan ni se consideran evidencia psicométrica. El servicio mantiene afinidad y preparación por separado.

## Principios invariantes

1. `missing` y `null` nunca equivalen a cero.
2. Afinidad no equivale a preparación ni a probabilidad de éxito.
3. RIASEC describe intereses exploratorios; no diagnostica personalidad.
4. Un resultado solo se calcula con evidencia suficiente y conserva versiones, hash de entrada, trazabilidad y faltantes.
5. Los fixtures sintéticos prueban comportamiento; no validan precisión científica.
6. El estudiante conserva agencia: se presentan opciones para explorar y áreas que puede reforzar.

## Estado de fuentes

La investigación RIASEC usa fuentes oficiales del National Center for O*NET Development. No se encontró evidencia de validación específica en Bolivia. Currículo boliviano, BTH, universidades, embeddings, OCR y LLM local quedan fuera del primer hito hasta estabilizar el núcleo.
