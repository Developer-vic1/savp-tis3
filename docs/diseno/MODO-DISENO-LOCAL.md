# Catálogo visual local de SAVP

Entrada: `http://127.0.0.1:8000/diseno`. Clave configurada mediante hash en `.env`; el valor original no se registra aquí. La sesión visual dura 30 minutos y no autentica una cuenta institucional.

## Alcance actual

Catálogo por Administrador, Director, Secretaría, Regente, Docente, Estudiante y Aula Virtual. Permite seleccionar rol, buscar ventanas y ver las capturas disponibles sin restricciones entre categorías. Reutiliza las capturas locales ya existentes de revisión visual, con su condición de referencia anterior señalada explícitamente.

Las ventanas sin captura aparecen como pendientes. No se implementó un sustituto interactivo de todas las interfaces; el catálogo no modifica ni ejecuta las ventanas reales. No debe anunciarse como acceso funcional a todos los módulos ni como un modo administrador del sistema.

## Límites técnicos

- Solo entorno `local`, opción habilitada y conexión de loopback con host local.
- Clave comprobada con `Hash::check`; nunca se guarda la clave recibida en datos flash.
- CSRF estándar y límite de cinco intentos por minuto.
- Sesión separada por clave de propósito, vencimiento y firma vinculada al hash configurado. No usa `Auth::login`, no asigna roles y no altera Gates o Policies.
- El middleware de cuenta institucional se omite únicamente en las rutas del catálogo. El control local y de clave se aplica independientemente. Las rutas institucionales mantienen sus middleware originales.
- Rutas propias de acceso, catálogo, capturas y salida; ninguna invoca acciones institucionales. Los POST sirven exclusivamente para entrar y salir del catálogo.
- Imágenes mediante una lista fija de archivos; no se recibe una ruta arbitraria ni se expone la carpeta completa.
- Las respuestas no se almacenan en caché y se marcan para no indexarse.
- Configuración deshabilitada por defecto. Para retirar el modo, establecer `SAVP_DISENO_ENABLED=false` en `.env`.

La configuración actual de sesiones y caché es de archivos. El controlador visual no consulta modelos ni la conexión institucional. Las referencias de imágenes pueden contener datos visibles de las revisiones anteriores; no son datos actuales de la BD.

## Administrador solicitado

Se ejecutó únicamente `Database\Seeders\Oficial\REALES\ADMINISTRADOR\AdministradorSistemaSeeder` después de terminar la carga institucional que bloqueaba las inserciones. Se verificaron correo, contraseña mediante hash, estado activo, actor Administrador, datos personales indicados y 35 permisos del rol oficial, incluido `Gestion_Academica`. No se repitió el seeder general ni se aplicaron migraciones.

El teléfono solicitado quedó incorporado al seeder y al registro de la persona. La sesión real de Administrador abre Inicio y Gestión académica. Se corrigieron sus consultas de lectura que suponían `plan_asignatura.cod_gea`: el esquema oficial relaciona los planes y horarios mediante `grupo_academico`. Los conteos de clases incluyen planes de asignatura y especialidad; las calificaciones se delimitan por inscripciones de la gestión. Los resúmenes y pendientes de las siete gestiones se consultaron sin error, con sintaxis PHP comprobada. La revisión de navegador confirmó la pantalla académica y una captura del Aula Virtual en el catálogo; no equivale a revisar todas las acciones de los módulos.
