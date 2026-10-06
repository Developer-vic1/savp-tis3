# Integración funcional en Fusion_Sistema

Esta versión conecta el espacio estudiantil existente con el análisis V2, el instrumento
RIASEC oficial, la búsqueda de fuentes y el tutor estructurado. La validación del 1 de octubre
de 2026 utilizó SQLite aislado y FastAPI por HTTP real en loopback. No se modificó la base
institucional. Resultados y límites: [FUSION_VALIDATION.md](FUSION_VALIDATION.md).

## Arquitectura y acceso

El navegador llama únicamente a Laravel. `StudentOrientationController` usa
`StudentOrientationService` y el cliente existente `AporteIngenierilClient`; no hay un segundo
cliente ni una llamada JavaScript directa a FastAPI. La clave viaja en `X-SAVP-AI-Key` desde
el servidor. El cliente deriva un pseudónimo HMAC del identificador estudiantil y `APP_KEY`.
No envía nombres, correo, documento, teléfonos ni direcciones.

La identidad procede de `User.cod_per` y `CursoVirtualService::estudianteDeUsuario`, con
estudiante activo. Se mantienen el actor Estudiante, autenticación, verificación y permisos
del Aula Virtual, incluido `Orientacion_Academica_Profesional`. El controlador prohíbe
`student_id` del navegador. Consultas, persistencia y snapshots se limitan al estudiante
autenticado. No se crean roles ni permisos de producción. Los roles de las pruebas existen
únicamente en las bases aisladas.

## Rutas y contratos

| Operación | FastAPI | Laravel |
|---|---|---|
| Salud | GET `/health` | Cliente y comando de precarga |
| Instrumento | GET `/api/v2/riasec/instrument` | GET `/aula-virtual/mi-orientacion?section=riasec` |
| Puntajes | POST `/api/v2/riasec/score` | POST `/aula-virtual/mi-orientacion/riasec` |
| Perfil y carreras | POST `/api/v2/analysis` | POST `/aula-virtual/mi-orientacion/analisis` |
| Fuentes | POST `/api/v1/knowledge/search` | POST `/aula-virtual/mi-orientacion/consulta`, modo `fuentes` |
| Tutor | POST `/api/v1/tutor/query` | POST `/aula-virtual/mi-orientacion/consulta`, modo `tutor` |

El dashboard y `/estudiante/intereses` abren el estado del perfil. Futuro académico,
preparación y asistente conducen a análisis, perfil y tutor. Fuentes conserva sus materiales
académicos y añade acceso a las fuentes de orientación. El explorador local anterior conserva
su historial, se identifica como anterior y ya no se abre automáticamente.

### RIASEC público y análisis V2

Los textos y cinco opciones de los 30 ítems proceden del endpoint oficial, sin una copia PHP.
Laravel guarda respuestas enteras 1–5 con `item_id` e `instrument_version` y no calcula puntajes.
Aporte Ingenieril SAVP comparte `PublicRiasecData.to_vocational()` entre score y análisis: allí valida versión,
IDs, escala y convierte una sola vez a la representación interna 0–4. El análisis recibe:

```json
{
  "schema_version": "2.0",
  "student_id": "pseudonimo-generado-en-servidor",
  "riasec_public": {
    "instrument_version": "version-devuelta-por-el-instrumento",
    "responses": [{"item_id": 1, "value": 3}]
  }
}
```

El ejemplo abrevia la lista: una solicitud real requiere los 30 ítems únicos. `vocational`
interno sigue aceptándose para consumidores anteriores, pero enviar ambos bloques causa 422.
El instrumento validado devuelve seis sumas 0–20, códigos ordenados, Holland code, limitaciones
y trace ID. Las pruebas comparan score público con Student Profile V2; no duplican el cálculo
en Laravel.

### Precheck y evidencia

Son obligatorios identidad/perfil, notas con escala/contexto y RIASEC. BTH es obligatorio cuando
la inscripción indica que aplica; `NO_APLICA` no bloquea. Una inscripción sin estado BTH no se
interpreta como no aplica. Asistencia e historia son recomendadas; actividad e intereses
declarados son opcionales. Falta de evidencia permanece ausente y nunca se convierte en cero.

Notas activas con `cod_pas` y contexto verificable alimentan escala oficial 0–100. Históricos
sin `cod_pas` siguen consultables en el progreso, pero no se les inventa contexto analítico.
Asistencia entra sólo si todas las observaciones del periodo permiten conteos binarios
respaldados por catálogo; porcentajes parciales no se convierten en clases. Actividad usa tareas
publicadas/cerradas de cursos propios y entregas distintas. BTH usa la inscripción y especialidad
registradas. La pantalla muestra la disponibilidad de cada bloque y las limitaciones del resultado.

## Persistencia y trazabilidad

Se extiende `orientacion_actividades` con JSON de respuestas públicas, score y análisis,
fecha del análisis y hash SHA-256 de respuestas; índice `(cod_est, riasec_input_hash)`.
La FK estudiantil ya existe. No se crea una tabla equivalente. Un envío igual al último
instrumento oficial no duplica la actividad; un instrumento distinto crea otro snapshot.
Un análisis exitoso actualiza el snapshot de esa actividad; no se implementa un historial
separado de cada regeneración del análisis. Fallos HTTP no sobrescriben resultados anteriores.

La migración `2026_10_01_000001_extend_orientation_peter3_snapshots.php` es incremental y tiene
`down`. Su reversión quita los campos nuevos y, por tanto, elimina sus snapshots: requiere
preservarlos antes de cualquier reversión institucional. Se probó up/down/up sólo en memoria.
No se ejecutó esta migración en la base institucional; allí debe habilitarse mediante el
procedimiento de despliegue autorizado. La UI informa si faltan sus columnas.

Las respuestas conservan trace ID, huella de entrada y versiones del motor, criterios,
instrumento, bridge, catálogo y crosswalk. El cliente valida JSON, estructura utilizada por
la vista, pseudónimo, versión del score y coherencia de los trace IDs. Los logs registran
endpoint, status, trace ID, latencia y fecha; no registran payloads ni secretos. `Server-Timing`
mide el procesamiento de FastAPI para separar costes de integración.

## Configuración y arranque local

`config/services.php` concentra `PETER3_ENABLED`, `PETER3_BASE_URL`, `PETER3_API_KEY`,
`PETER3_CONNECT_TIMEOUT`, `PETER3_REQUEST_TIMEOUT` y `PETER3_COLD_START_TIMEOUT`.
Se conservan los alias anteriores `PETER3_API_URL` y `PETER3_TIMEOUT`. `.env.example` contiene
placeholders, integración deshabilitada y URL local; no contiene una clave. `.env` no cambió.

En terminales separadas, desde este checkout:

```powershell
# FastAPI: asignar la clave privada en esta terminal, sin publicarla.
$env:SAVP_AI_API_KEY = '<clave-interna>'
ai-service/.venv/Scripts/python.exe -m uvicorn app.main:app --app-dir ai-service --host 127.0.0.1 --port 8001

# Laravel: configurar la misma clave y una BD previamente autorizada.
$env:PETER3_ENABLED = 'true'
$env:PETER3_BASE_URL = 'http://127.0.0.1:8001'
$env:PETER3_API_KEY = '<misma-clave-interna>'
php artisan serve

# Frontend, otra terminal.
npm run dev -- --host 127.0.0.1
```

No hacen falta Docker, un proveedor cloud ni un modelo. `php artisan peter3:warmup` comprueba
salud y carga el corpus documental BM25 con una búsqueda sin guardar actividad estudiantil.
Puede ejecutarse al arrancar el servicio para detectar un corpus no disponible. Las consultas
personales no se cachean.

Salud/instrumento/score usan 5 s; consultas normales admiten 1–30 s (por defecto 10);
conexión 1–10 s (por defecto 3); precarga explícita 1–180 s (por defecto 90).
No se reintentan solicitudes ni se siguen redirects. Una URL con credenciales, query o fragmento
se rechaza. 422 pide revisar datos; 401/403/500/503 y conexión/timeout dan un aviso genérico
sin traceback. Los resultados guardados permanecen accesibles durante una caída.

## Validación reproducible aislada

`tests/TestCase.php` exige SQLite `:memory:` y URL vacía antes de los traits de base de datos.
La DLL PDO SQLite existente se habilita por proceso; no se modificó `php.ini`:

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit
ai-service/.venv/Scripts/python.exe ai-service/scripts/verify_peter3.py
npm ci
npm run build
npm audit --json
```

El verificador Python usa su directorio interno. Su smoke de contratos es FastAPI TestClient
en proceso, no tráfico por socket. El E2E Laravel y el benchmark HTTP sí llaman a Uvicorn real.
Los tests Laravel existentes con RefreshDatabase reconstruyen solamente SQLite en memoria;
no se lanzó `migrate:fresh` contra un archivo ni una conexión institucional.

Para la prueba visual se verificó el archivo exacto `storage/framework/fusion-e2e.sqlite` antes
de ejecutar `tests/bootstrap_orientation_browser.php`. Configurar `DB_CONNECTION=sqlite`,
`DB_DATABASE` con ese path absoluto y `DB_URL` vacío. La fixture de prueba usa
`orientation@example.test` / `password`; nunca debe crearse fuera de esta base aislada.
La clave de sesión se genera localmente y se guarda sólo en el path ignorado
`storage/logs/peter3-session.key`. `PETER3_REAL_E2E=1` habilita el caso real con servicio 8001;
sin esa variable se declara SKIP. `tests/benchmark_peter3_http.php` lee la fixture visual
completada y mide 20 repeticiones por operación, sin persistir nuevas actividades.

Diez migraciones existentes ahora delegan sus CHECK al helper portable: PostgreSQL recibe
el SQL original; SQLite aislado utiliza triggers INSERT/UPDATE. Esto permitió validar las
pruebas de BD existentes sin desactivar restricciones. Se probaron valores inválidos,
JSON, hash y semántica NULL. No sustituye una prueba de migración sobre PostgreSQL institucional.

## Límites científicos y operativos

Soporte semántico de citas: `NOT_EVALUATED`. RIASEC no tiene validación psicométrica específica
para estudiantes bolivianos. Bridge contiene inferencias y requiere revisión experta;
crosswalk relaciona carrera/ocupación y no establece equivalencia. El catálogo no es
exhaustivo y retrieval puede omitir evidencia. El tutor opera en modo estructurado; LLM
es opcional y no se habilitó. No hay XGBoost, entrenamiento sintético, ranking global,
porcentaje compuesto, carrera correcta ni predicción validada de éxito. Las fixtures de
validación se identifican explícitamente como datos de prueba y no son evidencia estudiantil real.

La inscripción pública genérica no tenía contrato para crear `cod_usu`/`cod_per`; ahora presenta
solicitud de acceso institucional y rechaza crear cuentas sin identidad vinculada. El login
admite correos válidos y mantiene autorización del servidor. Las pruebas de perfil/password
se ajustaron al esquema institucional y a la política de contraseña existente.
