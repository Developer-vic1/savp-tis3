# Justificación por archivo

Checkout inicialmente limpio. Todos los archivos de esta tabla pertenecen a la tarea autorizada. No se copiaron carpetas completas ni se incorporaron commits de otras ramas. CSS/JS globales, snapshots fuente, composer.lock, uv.lock y configuración institucional permanecen sin cambios.

| Archivo | Motivo |
|---|---|
| `.env.example` | Configurar cliente central y límites de tiempo con placeholders; compatibilidad con variables anteriores. |
| `.gitattributes` | Conservar bytes de DEV/freeze/TEST; evitar ruptura de hashes en Git. |
| `ai-service/app/api/v2/riasec.py` | Compartir adaptación oficial de escala pública 1–5 en score y Analysis V2, conservar legacy. |
| `ai-service/app/contracts/v2.py` | Compartir adaptación oficial de escala pública 1–5 en score y Analysis V2, conservar legacy. |
| `ai-service/app/main.py` | Exponer Server-Timing de FastAPI para separar costes medidos. |
| `ai-service/app/riasec/public_contract.py` | Compartir adaptación oficial de escala pública 1–5 en score y Analysis V2, conservar legacy. |
| `ai-service/data/evaluation/cold_start_results.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/fusion_real_e2e.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/http_smoke_phase21.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/laravel_http_performance.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/performance_results.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/prompt_results.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/retrieval_dev_phase21.historical-1303d9b.json` | Conservar evidencia histórica exacta de BASE, separada del experimento actual. |
| `ai-service/data/evaluation/retrieval_dev_phase21.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/retrieval_freeze_phase21.historical-1303d9b.json` | Conservar evidencia histórica exacta de BASE, separada del experimento actual. |
| `ai-service/data/evaluation/retrieval_freeze_phase21.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/evaluation/retrieval_test_phase21.historical-1303d9b.json` | Conservar evidencia histórica exacta de BASE, separada del experimento actual. |
| `ai-service/data/evaluation/retrieval_test_phase21.json` | Artefacto de medición real actual; no tuning de TEST ni resultado institucional. |
| `ai-service/data/indexes/intfloat__multilingual_e5_small/chunks.jsonl` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/indexes/intfloat__multilingual_e5_small/index.faiss` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/indexes/intfloat__multilingual_e5_small/manifest.json` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/indexes/selected.json` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/indexes/sentence_transformers__paraphrase_multilingual_minilm_l12_v2/chunks.jsonl` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/indexes/sentence_transformers__paraphrase_multilingual_minilm_l12_v2/index.faiss` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/indexes/sentence_transformers__paraphrase_multilingual_minilm_l12_v2/manifest.json` | Índice real reconstruido o selección E5 congelada, asociado al corpus corregido. |
| `ai-service/data/processed/corpus.jsonl` | Reconstrucción selectiva de procedencia: 26 document_hash; IDs/texto preservados. |
| `ai-service/data/sources/sources.json` | Corregir únicamente los ocho campos hash/document_hash de cuatro HTML. |
| `ai-service/scripts/evaluate_retrieval_phase21.py` | Registrar Python/plataforma real en artefactos; parámetros sin cambios. |
| `ai-service/scripts/verify_peter3.py` | Identificar smoke TestClient como contrato en proceso, no socket real. |
| `ai-service/tests/contract/test_public_riasec_analysis.py` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `app/Actions/Fortify/CreateNewUser.php` | Rechazar cuenta pública sin identidad institucional y explicar solicitud de acceso. |
| `app/Http/Controllers/AulaVirtual/StudentOrientationController.php` | Orquestar operaciones autorizadas y derivar identidad exclusivamente del usuario. |
| `app/Http/Controllers/Estudiante/ExperienceController.php` | Orquestar operaciones autorizadas y derivar identidad exclusivamente del usuario. |
| `app/Models/AulaVirtual/OrientacionActividad.php` | Casts/fillable de snapshots oficiales en modelo existente. |
| `app/Services/AporteIngenieril/AporteIngenierilClient.php` | Contrato, precheck, extracción propia y cliente únicos; privacidad, tiempos, trazas y persistencia segura. |
| `app/Services/AporteIngenieril/DTO/AporteResponse.php` | Contrato, precheck, extracción propia y cliente únicos; privacidad, tiempos, trazas y persistencia segura. |
| `app/Services/AporteIngenieril/DTO/Peter3V2Contract.php` | Contrato, precheck, extracción propia y cliente únicos; privacidad, tiempos, trazas y persistencia segura. |
| `app/Services/AporteIngenieril/OrientationReadiness.php` | Contrato, precheck, extracción propia y cliente únicos; privacidad, tiempos, trazas y persistencia segura. |
| `app/Services/AporteIngenieril/StudentOrientationService.php` | Contrato, precheck, extracción propia y cliente únicos; privacidad, tiempos, trazas y persistencia segura. |
| `app/Support/PortableCheckConstraint.php` | Permitir CHECK mediante triggers en validación SQLite; no omitir restricciones. |
| `app/Support/WorkspaceNavigation.php` | Conectar navegación existente al módulo y añadir precarga explícita sin escritura estudiantil. |
| `config/services.php` | Configurar cliente central y límites de tiempo con placeholders; compatibilidad con variables anteriores. |
| `database/factories/UserFactory.php` | Crear fixtures compatibles con identidad cod_usu/cod_per del esquema real. |
| `database/migrations/2026_04_09_033080_create_inscripcion_estudiante_table.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_05_05_201751_create_horarios_tables.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_05_19_001028_create_documento_inscripcion_estudiante_table.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_05_24_051142_create_respaldo_gestion_academicas_table.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_09_30_100001_prepare_kardex_seguimiento_structure.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_09_30_100002_prepare_lms_units_structure.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_09_30_100003_prepare_database_notifications.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_09_30_100004_prepare_student_academic_goals.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_09_30_100005_prepare_institutional_calendar.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_09_30_100006_prepare_academic_integrity_checks.php` | Delegar CHECK a helper SQLite; mantener SQL PostgreSQL original. |
| `database/migrations/2026_10_01_000001_extend_orientation_peter3_snapshots.php` | Añadir snapshots reversibles e índice en tabla existente. |
| `docs/peter3/API_V2.md` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_EVIDENCE.json` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_FILE_CHANGES.md` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_INTEGRATION.md` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_LARAVEL_TESTS.json` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_NPM_AUDIT.md` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_NPM_AUDIT_AFTER.json` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_NPM_AUDIT_BEFORE.json` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `docs/peter3/FUSION_VALIDATION.md` | Documentar contrato público, arquitectura, evidencia, auditoría y límites actuales. |
| `package-lock.json` | Actualizar dependencias vulnerables dentro de major/rangos compatibles; preservar nombre del proyecto. |
| `package.json` | Actualizar dependencias vulnerables dentro de major/rangos compatibles; preservar nombre del proyecto. |
| `resources/views/aula-virtual/dashboard/estudiante.blade.php` | UI institucional en español, estados preventivos, fuentes y limitaciones; global CSS/JS preservados. |
| `resources/views/aula-virtual/orientacion/estudiante.blade.php` | UI institucional en español, estados preventivos, fuentes y limitaciones; global CSS/JS preservados. |
| `resources/views/aula-virtual/orientacion/peter3.blade.php` | UI institucional en español, estados preventivos, fuentes y limitaciones; global CSS/JS preservados. |
| `resources/views/auth/login.blade.php` | Admitir correos válidos sin limitar el login institucional a Gmail. |
| `resources/views/auth/register.blade.php` | Rechazar cuenta pública sin identidad institucional y explicar solicitud de acceso. |
| `resources/views/estudiante/area.blade.php` | UI institucional en español, estados preventivos, fuentes y limitaciones; global CSS/JS preservados. |
| `routes/aula_virtual.php` | Conectar navegación existente al módulo y añadir precarga explícita sin escritura estudiantil. |
| `routes/console.php` | Conectar navegación existente al módulo y añadir precarga explícita sin escritura estudiantil. |
| `routes/estudiante.php` | Conectar navegación existente al módulo y añadir precarga explícita sin escritura estudiantil. |
| `tests/Feature/PasswordResetTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Feature/ProfileInformationTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Feature/RegistrationPersistenceTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Feature/StudentOrientationIntegrationTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Feature/UpdatePasswordTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/OrientationFixture.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Unit/OrientationReadinessTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Unit/Peter3V2ClientTest.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/Unit/PortableCheckConstraintTest.php` | Permitir CHECK mediante triggers en validación SQLite; no omitir restricciones. |
| `tests/benchmark_peter3_http.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `tests/bootstrap_orientation_browser.php` | Validar contratos, errores, identidad/scope y persistencia; fixtures sólo en bases aisladas verificadas. |
| `docs/peter3/CURRENT_STATE.md` | Marcar resultados anteriores como históricos y enlazar la validación vigente de Fusion_Sistema. |
| `docs/peter3/FINAL_STATUS.md` | Marcar resultados anteriores como históricos y enlazar la validación vigente de Fusion_Sistema. |
| `docs/peter3/INTEGRATION_READINESS.md` | Marcar resultados anteriores como históricos y enlazar la validación vigente de Fusion_Sistema. |
