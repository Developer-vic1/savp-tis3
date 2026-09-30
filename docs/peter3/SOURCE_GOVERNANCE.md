# Gobernanza de fuentes — PETER 3

Estado auditado: 2026-09-29.

`ai-service/data/sources/sources.json` es el registro de snapshots documentales y
`ai-service/data/sources/references.json` registra referencias externas e internas que no son
parte del corpus. La regla de integridad es: una afirmación factual debe resolver a uno de esos
registros; un `source_id` documental debe tener snapshot local y SHA-256 válido.

## Inventario verificado

- 12 fuentes oficiales locales: 12/12 archivos presentes, 12/12 hashes SHA-256 válidos y
  12/12 URLs accesibles con HTTP 200 durante la auditoría.
- 11 referencias no documentales: O*NET, ISCO, trabajos de retrieval, OWASP y artefactos
  internos versionados.
- 0 identificadores huérfanos en catálogo, bridge, crosswalk, parámetros o evaluación.
- 0 colisiones entre `source_id` y `reference_id`.

| Source ID | Institución / contenido | SHA-256 (prefijo) |
|---|---|---|
| `BO-ME-CURRICULO-BASE-2012` | Ministerio de Educación, currículo base | `25c921ad12af` |
| `BO-ME-BTH-RM-0244-2023` | Ministerio de Educación, BTH | `4e52e97cdc7e` |
| `BO-ME-EVALUACION-REGULAR-RM-0190-2024` | Ministerio de Educación, evaluación regular | `6cff6cc77f84` |
| `BO-UCB-LP-SIS-MALLA-2026` | UCB, Sistemas, malla | `dc5710e7015b` |
| `BO-UCB-LP-SIS-PROFILE-2026` | UCB, Sistemas, perfil | `e421d607cd47` |
| `BO-UCB-LP-PSI-MALLA-2026` | UCB, Psicología, malla | `b0123e032f44` |
| `BO-UCB-LP-PSI-PROFILE-2026` | UCB, Psicología, perfil | `787f2c5fbcc1` |
| `BO-UCB-LP-CIV-MALLA-2026` | UCB, Civil, malla | `831af84148a2` |
| `BO-UCB-LP-CIV-PROFILE-2026` | UCB, Civil, perfil | `7f58bc18be6b` |
| `BO-UCB-LP-IAM-MALLA-2026` | UCB, Ambiental, malla | `6f53c96223e6` |
| `BO-UCB-LP-IAM-PROFILE-2026` | UCB, Ambiental, perfil | `c583444f9547` |
| `BO-UMSA-LP-CIV-PLAN-2023` | UMSA, Civil, plan | `750f43d245f1` |

La RM 0190/2024 es la evidencia normativa de la escala escolar total sobre 100 y el umbral de
promoción de 51; esos valores ya no se atribuyen a documentos que no los sustentan.

## Reglas

Los snapshots son inmutables: una actualización se incorpora con nuevo identificador, fecha y
hash. Cada chunk hereda fuente, institución, URL, hash, página o sección y método de extracción.
El corpus contiene solo documentos públicos; no incluye expedientes ni identificadores de
estudiantes. La vigencia institucional debe volver a verificarse antes de producción.
