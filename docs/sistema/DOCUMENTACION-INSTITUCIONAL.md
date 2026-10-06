# Referencias y cambios curriculares documentados

Directorio: `C:\laragon\www\savp-reestructuracion`. Página: `/documentacion-institucional`.

- Acceso: Administrador o Director vigente único. Las imágenes se entregan por una ruta autenticada; no se publican en `storage/public`.
- Archivos originales: `storage/app/private/documentacion/referencias/`. BD: `referencia_documental` conserva ruta, SHA-256, titular, usuario y vigencia. Las versiones anteriores permanecen almacenadas.
- La firma pertenece al registro de personal del Director vigente. Al cambiar la autoridad, la consulta compartida `ReferenciaDocumentalService::actuales()` excluye la firma anterior. El sello continúa como referencia de la institución.
- El calendario de Laravel ejecuta `documentacion:avisar-firma` cada cinco minutos y publica un aviso personal si falta la firma vigente. Requiere el scheduler existente activo; no ejecuta envío por correo ni WhatsApp. En desarrollo: `php artisan schedule:work`; detener con Ctrl+C. Comprobar calendario: `php artisan schedule:list`.

## Lectura y comparación

`ReferenciaDocumentalService::comparar()` utiliza Python de `ai-service/.venv` y Pillow/PyMuPDF. Extrae imágenes incrustadas del PDF, normaliza sus trazos y compara cada una con las referencias vigentes; devuelve resultados y sus identificadores/huellas. Bloquea documentos sin ambas coincidencias. No autentica una firma, no detecta falsificaciones y no realiza OCR de una hoja escaneada completa. No se envían imágenes a servicios externos.

Asignaturas y especialidades reutilizan `lector-documento-institucional`. Los campos explícitos de la carta son un contrato de extracción de SAVP, no un formato certificado por MINEDU. Además del contenido y las imágenes, se exige comprobar la autorización con la autoridad emisora, indicando canal y motivo. La bitácora registra una lectura rechazada, sin crear oferta académica. Una autorización aprobada completa los campos y exige confirmación antes de guardar.

La creación queda en `incorporacion_curricular` para la siguiente gestión, conserva PDF privado, revisión y huella, y publica un aviso en las bandejas institucionales. Se incorpora al catálogo mediante el botón correspondiente cuando su año es la única gestión activa. Las correcciones mantienen el estado y la trayectoria existente, con PDF y bitácora.

## Esquema y comprobación

Migraciones incrementales: `2026_10_04_000020` y `2026_10_04_000021`. Se verificaron creación y reversión en PostgreSQL aislado antes de aplicarlas. Respaldo previo privado: `storage/app/private/respaldos/documentacion-20261005-033217.dump`; el índice del archivo fue comprobado con `pg_restore -l`.

Pruebas sin escribir datos institucionales:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/ReferenciaDocumentalTest.php tests/Unit/DocumentoCambioCurricularTest.php
ai-service/.venv/Scripts/python.exe scripts/academico/prueba_comparacion_documental.py
```

Los dos scripts de preparación/importación son operaciones administrativas locales autorizadas para esta carga inicial; no son endpoints web ni seeders masivos. No repetir la preparación para comprobar el estado: usar lecturas de registros y huellas. Conservar juntos respaldo de BD y archivos privados al restaurar.
