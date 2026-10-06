# Validación ejecutada sin PostgreSQL

Resultado vigente JUnit support-full-tests.xml: 222 tests, 191 PASS (126 Unit y 65 Feature), 31 SKIP, 582 aserciones, 0 failures/errors. cierre-tests.xml conserva el resultado anterior (120 PASS/45 SKIP). Los 45 casos omitidos originales están revisados en 26-TESTS-SKIPPED.md: 14 ahora PASS; persisten 30 casos dependientes del entorno y 1 alternativa de registro deshabilitado. No se presentan como flujos DB probados.

TestCase exige SQLite :memory: y URL vacía antes de cualquier trait de DB; falta pdo_sqlite, por lo que tests RefreshDatabase/Migrations/etc. saltan antes de ejecutar migrations. No se habilitó el driver. Builder toSql compila consultas, no las ejecuta. HTTP/Livewire mocks y Storage/HTTP fakes ejercitan fronteras sin tocar datos institucionales.

Cobertura significativa: actor equivocado/inactivo/ambiguo, guest, curso ajeno, entrega propia con alcance revocado, devolución sin calificar, permiso de nota independiente, filtros correlacionados de Regencia/inscripción, privados/historial, flags cerrados, metas ajenas, Locked y fallback Peter 3. El PDF Docente genera bytes PDF reales con conteos fake de prueba; no es reporte institucional validado. Reporte Regente prueba SQL scoped y denegación, no PDF con datos.

Support: 28 casos de lógica local (20 SupportPreventiveTest, 7 InstitutionalRoleGovernanceTest y 1 regla Kardex sin persistencia) y 14 de integración (6 Personas, 2 Kardex, 6 TeacherGradeSupportTest). El subconjunto de 8 regresiones descrito en 29 ya está incluido en esos totales y no se suma otra vez. Consultas y drawers de Secretaría, filtros SQL correlacionados y borrador de metas tienen pruebas adicionales. Los dobles de Service/transacción certifican fronteras, no persistencia real.

Composer validate, Pint, lint PHP, php -l de seis migrations, route:list, Blade compilado/lint y npm run build: evidencias support-* vigentes. No se ejecutó up/down/pretend. NPM audit informó 11 paquetes vulnerables (2 critical), documentados individualmente en 27-NPM-VULNERABILITIES.md; locks sin cambios.

Pendientes: PG aprobado, DDL/rollback/transacciones/concurrencia/FKs y pruebas completas por ventana; servicio Aporte Ingenieril SAVP real y UX autenticada seis actores/light-dark/responsive. El TestCase actual bloquea PG: preparar clase/config de testing independiente revisada por Peter 1 antes de cambiarlo; no basta ajustar DB_DATABASE en .env.
