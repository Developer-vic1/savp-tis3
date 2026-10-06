# Diseño y experiencia institucional SAVP

Fecha de referencia: 2026-10-02. Alcance: continuidad de interfaces en este repositorio. Este documento registra la identidad presente en el código y el criterio indicado por el usuario; no certifica seguridad ni QA de todos los módulos.

## La esencia que se conserva

SAVP es un sistema educativo institucional construido alrededor de tareas reales. La referencia principal son los paneles de administrador: ayudan a completar una operación, muestran su resultado previsto, previenen errores y explican cómo resolverlos. Esa experiencia forma parte del diseño tanto como el verde institucional, las superficies, la tipografía y los iconos.

Una vista nueva debe sentirse perteneciente a SAVP y facilitar el trabajo de su actor. Puede tener una composición propia según la tarea; no necesita repetir un tablero de tarjetas idénticas. Las skills aportan soluciones a problemas concretos de UX, sin sustituir el sistema existente por una estética elegida desde un catálogo.

## Fuentes dentro del proyecto

Consulta solo las referencias pertinentes a la tarea:

| Necesidad | Referencia existente |
| --- | --- |
| Shell institucional, encabezado y tema | `resources/views/layouts/app.blade.php` |
| Tokens y componentes compartidos | `resources/css/app.css` |
| Aplicación del tema, avisos y gráficos | `resources/js/app.js` |
| Tipografía y configuración Tailwind | `tailwind.config.js` |
| Menú y navegación por actor | `resources/views/components/actor-menu.blade.php`, `app/Support/WorkspaceNavigation.php` |
| Formulario preventivo de gestión académica | `resources/views/livewire/admin/gestion-academica.blade.php` |
| Validación y análisis al guardar la gestión | `app/Livewire/Admin/GestionAcademica.php`, `app/Support/Academico/GestionAcademicaInteligente.php` |
| Vista previa académica de asignaturas | `resources/views/livewire/admin/gestion-asignatura.blade.php` |
| Planificación y vista previa de turnos | `resources/views/livewire/admin/gestion-turnos.blade.php` |
| Inventario y conexiones de Support Inteligente | `docs/implementacion-maestra/28-SUPPORT-INTELIGENTE.md` |
| Extensión del shell en Aula Virtual | `resources/views/aula-virtual/layouts/app.blade.php` |
| Componentes de Aula Virtual | `resources/views/aula-virtual/componentes/` |
| Adaptación especializada con tokens compartidos | `resources/css/gestion-conocimiento.css` |

El código actual prevalece sobre los valores descriptivos de esta guía. Si una referencia cambia, comprueba el componente vigente antes de reutilizarla. La inspección realizada confirma patrones estáticos en módulos representativos; no demuestra ejecución ni cobertura uniforme de todas las pantallas.

## Identidad visual

La fuente compartida es Figtree. La iconografía institucional usa Phosphor Duotone. Conserva logos, nombres y recursos existentes; usa iconos por significado y etiquetas accesibles cuando sean acciones sin texto. Los encabezados mantienen una jerarquía clara y vocabulario educativo: gestión, inscripción, período, asignatura, documentación y los términos reales del módulo.

Los colores y su adaptación claro/oscuro tienen como fuente los recursos existentes de `resources/css` y `resources/js`. Las variables `--ui-*` se definen en `resources/css/app.css`; `resources/js/app.js` aplica el tema y reutiliza los tokens en avisos y gráficos. Consulta también los recursos específicos del módulo. Esta guía describe sus roles sin duplicar sus valores:

| Rol | Token compartido |
| --- | --- |
| Fondo | `--ui-bg` |
| Superficie | `--ui-surface` |
| Texto principal | `--ui-text` |
| Texto secundario | `--ui-text-soft` |
| Texto de apoyo | `--ui-muted` |
| Borde | `--ui-border` |
| Acción institucional | `--ui-primary` |
| Información | `--ui-info` |
| Acento semántico existente | `--ui-violet` |
| Advertencia | `--ui-warning` |
| Error o peligro | `--ui-danger` |

Las vistas usan estos tokens o las clases compartidas que los consumen; no fijan una paleta alternativa con valores hexadecimales ni duplican el tema con estilos aislados. Cuando JavaScript necesite un color resuelto, reutiliza el mecanismo existente que lee las variables CSS y responde a `theme-changed`, como el de los gráficos. Conserva `themeManager`, la clase `dark` y la preferencia `savp-theme`; verifica los elementos dinámicos al alternar el tema.

Usa las variantes suaves, de borde, hover y sombra definidas junto a estos tokens. El éxito reutiliza la familia institucional. El color refuerza un significado acompañado de texto o icono; no basta para explicar un estado. Comprueba el contraste del par concreto, especialmente en botones y fondos de modo oscuro.

El shell incorpora fondos sutiles verdes y azules, paneles y una navegación con acentos por grupo. Conserva estos detalles cuando correspondan. No elimines un recurso institucional válido solo porque una skill lo clasifique como un patrón frecuente.

## Componentes y composición

Reutiliza el shell y los componentes del módulo más cercano. En la base compartida existen `ui-card`, `ui-card-soft`, `ui-panel`, `ui-title`, `ui-subtitle`, `ui-input`, `ui-select`, `ui-textarea`, `ui-btn`, `ui-badge` y componentes para tablas, alertas y modales. Algunas pantallas administrativas tienen variantes propias, como las de Gestión Académica: inspecciona sus estilos antes de mezclarlos.

Los apartados plegables utilizan `x-plegable-institucional`: icono relacionado con su contenido, título, descripción y contador opcionales. La variante `compacto` corresponde a valores de gráficos o detalles dentro de fichas. Conserva los atributos de Alpine/Livewire y la apertura condicional del apartado; utiliza el contenido por slot sin copiar la cabecera, la flecha ni sus estilos. Consulta [uso del plegable compartido](docs/diseno/PLEGABLE-INSTITUCIONAL.md).

Conserva la diferencia entre superficies: paneles de trabajo, encabezados, tablas, modales y campos no tienen que recibir el mismo radio ni la misma sombra. Ordena según la tarea: contexto, datos o filtros, zona de trabajo, diagnóstico y acción. Las vistas previas acompañan el formulario donde ayuden a entender el resultado; en móvil se reorganizan en un orden de lectura útil.

El menú procede de `WorkspaceNavigation` y respeta actor y permisos. Aula Virtual comparte el shell institucional y añade componentes adecuados al estudiante o docente. Una vista de estudiante no recibe controles administrativos para parecerse visualmente a la referencia.

## Prevención en tres capas

Este es el criterio del usuario para formularios y operaciones nuevas. Reutiliza las reglas existentes del dominio y verifica cada capa afectada; no inventes reglas para completar una lista. En una pantalla de consulta conserva diagnóstico y estados pertinentes sin añadir un formulario innecesario.

| Capa | Experiencia requerida | Límite |
| --- | --- | --- |
| Frontend | Requisitos visibles, validación al momento apropiado, errores cerca del campo, estado de la acción y vista previa cuando aporte valor | No garantiza validez, autorización ni persistencia |
| Backend | Validación independiente, autorización y ámbito del actor, análisis de bloqueos antes de escribir, errores recuperables y consistencia de la operación | Nunca confía en un botón deshabilitado, un identificador o un resultado enviado por el navegador |
| Support Inteligente | Análisis contextual, casos extremos, advertencias, sugerencias y explicación basados en contratos y evidencia del dominio | Una recomendación no se convierte en hecho institucional ni ejecuta cambios por sí sola |

Gestión Académica ejemplifica esta secuencia: `puedeGuardarGestion` calcula condiciones de la interfaz; `crearGestionAcademica()` valida en el servidor; `analizarCreacion()` determina si puede continuar antes de la escritura. Esta referencia no implica que todos los módulos usen las mismas claves, reglas o límites.

### Validación y vista previa

Explica qué falta y cómo corregirlo. Un botón bloqueado necesita una razón visible y accesible. Al enviar un formulario inválido, conserva los datos y dirige al usuario al error; una notificación aislada no sustituye los mensajes junto a los campos.

Selecciona validación en vivo, al perder foco o al enviar según el campo, coste y comportamiento existente. En Livewire usa mecanismos compatibles con la versión instalada y evita enviar consultas costosas por cada pulsación. No robes el foco mientras se escribe ni anuncies continuamente el mismo diagnóstico.

La vista previa muestra lo que producirían los datos actuales. Se identifica como provisional y no se presenta como registro guardado. Si el análisis está en curso o falla, indícalo; no muestres como vigente el diagnóstico de una entrada anterior.

### Errores, advertencias y sugerencias

Respeta la separación del Support real entre bloqueos, advertencias y sugerencias. Algunos contratos usan `puede_continuar`, otros `valido`, `puede_crear` o estados propios: inspecciona el caller y el resultado antes de interpretarlos.

- Un bloqueo explica la condición que impide continuar y la corrección posible.
- Una advertencia comunica el riesgo sin simular una prohibición que el contrato no establece.
- Una sugerencia ofrece una alternativa, con aplicación explícita cuando cambie datos. No sustituye decisiones del actor ni permisos.

Los fallos del servidor se comunican con instrucciones útiles sin revelar secretos, consultas SQL o trazas internas. Conserva los controles y la trazabilidad existentes del módulo. Cuando una operación afecte registros o relaciones, revisa también los casos de duplicidad, cambios concurrentes y dependencias que realmente maneja su dominio.

### Respaldo del Support Inteligente

Para añadir una regla, identifica su procedencia: norma institucional verificable, fuente técnica, evidencia del proyecto o criterio preventivo propuesto. Registra la regla, su alcance, excepciones, caso normal y caso extremo en la documentación pertinente. Si falta respaldo, indícalo y no lo presentes como investigación concluida.

Reutiliza los Supports existentes antes de crear otro motor o duplicar lógica en Alpine y PHP. Los parámetros de una regla del servidor deben seguir su contrato; no copies límites de otra pantalla por semejanza visual. Falta de evidencia no equivale a cero, y una sugerencia no equivale a resultado confirmado.

## Estados y accesibilidad

En las interacciones modificadas contempla estados vacío, carga, válido, inválido, bloqueado, advertencia, éxito y fallo. Muestra datos reales; un dato ausente se distingue de un valor cero. Una pantalla sin registros orienta hacia la siguiente acción autorizada.

Conserva etiquetas, foco visible, navegación con teclado y la preferencia de movimiento reducido. En modales verifica foco de entrada, orden, cierre y devolución del foco. Evita transmitir errores solo con bordes rojos. Asegura lectura y operación en móvil, con zoom, nombres extensos y modo oscuro. Las tablas pueden tener desplazamiento contenido cuando sea necesario, sin desbordar toda la página ni ocultar acciones esenciales.

La animación explica una interacción o transición de estado. Reutiliza el movimiento discreto existente; no añadas coreografías, dependencias o efectos de landing page a una tarea administrativa sin una necesidad concreta.

## Uso de skills y verificación

`ui-ux-pro-max` sirve para consultar problemas específicos de formularios, accesibilidad, disposición y stack. `web-design-guidelines` complementa la revisión. Las skills de Laravel y seguridad ayudan a revisar el comportamiento del servidor cuando ese trabajo forma parte del encargo. Ninguna reemplaza el Support del dominio ni demuestra cumplimiento por instalarse.

Antes de implementar, elige una referencia existente y explica brevemente qué reutilizas y qué necesita variar por la tarea. Para una página adicional usa este sistema; generar una identidad desde cero corresponde a un proyecto nuevo o a un rediseño solicitado.

Después verifica las capas afectadas con evidencia proporcional al cambio: pruebas de la lógica modificada y revisión visual cuando haya cambios de interfaz. En operaciones preventivas, verifica que una petición directa al servidor no eluda un bloqueo del navegador. Usa datos y entornos de prueba autorizados. No publiques como validado lo que solo fue inspeccionado.

El nombre de presentación del servicio de análisis y tutoría es **Aporte Ingenieril SAVP**. Consulta `docs/aporte-ingenieril/DENOMINACION_INSTITUCIONAL.md` para la denominación y sus límites de compatibilidad.
