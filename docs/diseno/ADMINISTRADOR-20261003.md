# Continuidad visual y panel de administrador

Fecha: 2026-10-03. Checkout: `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`.

## Alcance

Se conserva Figtree, Phosphor Duotone, los tokens `--ui-*`, `themeManager`, la navegación por actor y los permisos. La landing y el acceso usan ahora la misma tipografía y colores que el shell. El shell comparte un ancho máximo, espaciado y una cabecera adaptable; las vistas que lo extienden reciben esta base. Los módulos mantienen sus formularios y validaciones actuales.

El administrador organiza indicadores, distribución de la comunidad, actividad, seguimiento y catálogos. Los gráficos tienen altura reservada, barras por categorías, etiquetas breves con valores completos desplegables, presentación al entrar en pantalla y repetición explícita. Cambiar el tema actualiza las instancias. El movimiento reducido desactiva animaciones y desplazamientos de hover.

## Lectura inteligente de bitácora

`App\Support\Bitacora\BitacoraInteligente` extrae el vocabulario de la bitácora existente. El dashboard y el componente de bitácora comparten esas traducciones. `presentar()` devuelve título, detalle, resultado, fecha, icono y clase semántica; no escribe en la base ni usa un LLM.

Las reglas se sustentan en los códigos de acción y resultado ya registrados por el proyecto. Una operación fallida o bloqueada conserva ese resultado. Un código desconocido, un autor ausente o un resultado ausente no se convierten en una acción exitosa. Los valores anteriores/nuevos, IP, correos y errores internos no se incorporan al resumen del tablero. El registro original sigue disponible en la bitácora autorizada.

## Periodo de referencia

El catálogo `periodo_evaluacion` contiene nombre, orden y estado, sin fechas ni relación con una gestión. `orientarPeriodoActual()` reutiliza `GestionAcademicaInteligente::sugerirPeriodosEvaluacion()` y verifica gestión única del año de consulta, rango de gestión, nombre, orden y habilitación del catálogo.

Solo muestra un trimestre referencial, identificado como pendiente de confirmación institucional. No convierte fechas sugeridas en fechas oficiales. En ausencia de datos o ante un catálogo ambiguo presenta «Por confirmar»; fuera del rango curricular sugerido indica esa situación. Usa la fecha de La Paz. Para mostrar un periodo vigente oficial será necesario que el trabajo de datos provea fechas por gestión y una relación explícita con el periodo; este cambio no añade migraciones ni modifica registros.

## Ejecución y comprobación

En PowerShell, desde el checkout indicado:

```powershell
npm.cmd run build
php -d extension=php_pdo_sqlite.dll -d extension=php_sqlite3.dll vendor/phpunit/phpunit/phpunit tests/Unit/PanelAdministradorInteligenteTest.php tests/Unit/RoleDashboardResolverTest.php
```

Pruebas: 17 casos y 53 aserciones, con base SQLite en memoria según `phpunit.xml`. Compilación Vite correcta; sintaxis PHP, formato de los Supports y diferencias sin errores de whitespace.

El servidor ya estaba activo en `http://127.0.0.1:8010`; no se inició ni detuvo ningún servicio. Se revisó el administrador autenticado en escritorio y móvil: cabecera, nombre breve y rol, tema claro/oscuro, menú de perfil, menú móvil, búsqueda por permisos, gráficos, actividad y preferencia de movimiento reducido. Se midió el layout en anchos de 1366 px y aproximadamente 390 px, sin desbordes horizontales del administrador. Se revisaron tipografía y adaptación del acceso y de la landing.

Los estados de periodo sin datos, ambigüedad, fuera de rango y bitácora sin autor/resultado, fallida y bloqueada se verifican mediante pruebas; no se crearon eventos ni cuentas institucionales para simularlos. No se certifica QA visual de cada ventana del proyecto. El navegador integrado aplica su propia escala de representación: las medidas CSS de layout se verificaron por separado de las capturas. Se retiraron las emulaciones temporales al terminar.

## Corrección de la landing en 8011

En la ventana original de 1846 px, la navegación de ancho completo comprimía el enlace de marca hasta 0 px y los enlaces carecían de separación efectiva. Se reemplazó esa distribución por una cuadrícula explícita: marca y accesos en la primera fila, navegación en la segunda. La cabecera sticky reserva su altura real; el inicio ya no depende de un espacio fijo para compensar una cabecera superpuesta.

Había 29 bloques con opacidad 0 a la espera de entrar en pantalla. Ahora su estado base es visible. IntersectionObserver solo aplica un desplazamiento breve de entrada; la ausencia de JavaScript, las capturas completas y el movimiento reducido no ocultan contenido.

Comprobación en `http://127.0.0.1:8011/`: anchos CSS efectivos de 320, 391, 768, 1024, 1367 y 1847 px, sin desbordes horizontales; marca sin compresión, navegación móvil abierta/cerrada, tema claro/oscuro y movimiento reducido. Evidencia del inicio: `output/diseno/landing-corregida-8011.jpg`. El zoom original se conserva; la adaptación depende del ancho disponible. Todas las emulaciones temporales se retiraron.

La portada limita el contenido a 72 rem y el título a 36 px con el tamaño de fuente predeterminado. En escritorio de poca altura usa título de 32 px y espaciado compacto. En una ventana CSS de 1280 × 568 se midieron 106 px de cabecera y 455 px de portada; los indicadores terminaban a 524 px, visibles dentro de la ventana.

La navegación por anclas conserva la URL y el historial, identifica la sección activa y cierra el menú móvil. Al llegar muestra los bloques visibles con una transición de 420 ms y separación de 45 ms entre tarjetas, limitada a 225 ms. El contenido nunca parte de opacidad 0. La preferencia de movimiento reducido elimina desplazamientos suaves y cancela las animaciones en curso. Se comprobaron los destinos Especialidades, Institucional y Sistema, nueve tarjetas de especialidades visibles, navegación desde el menú móvil, temas y ausencia de errores nuevos en consola. La escala física del sistema operativo sigue siendo una configuración del dispositivo; no se manipula el zoom para contrarrestarla.

## Distribución fluida al ampliar el espacio disponible

El ajuste posterior reemplaza los límites de 72 rem de la landing y 100 rem del shell por `--ui-content-width: 100%`. Las siete secciones, cabecera y pie aprovechan el espacio disponible con un margen compartido (`--ui-page-gutter`). Especialidades y vida estudiantil usan columnas automáticas con ancho mínimo de tarjeta de 18 rem. Los indicadores de la portada también se expanden con su tarjeta.

Las páginas que extienden `layouts.app`, incluidas las de Aula Virtual, reciben el contenedor fluido. Se liberan los límites de ancho de los contenedores principales directos; las cuadrículas pueden contraerse sin forzar el ancho de sus hijos y las tablas ocupan el contenedor. Los límites de párrafos y diálogos se mantienen para preservar lectura y tareas existentes. No se modifica el zoom ni se escribe en la base de datos.

Verificación en el navegador integrado en `http://localhost:8000/#inicio`: anchos CSS de 320, 390, 769, 1280, 1920 y 2560 px, sin desborde horizontal. Las tarjetas de especialidades pasaron respectivamente por 1, 1, 2, 3, 5 y 7 columnas. En la ventana original de 2530 px, las siete secciones ocuparon 2404 px frente a los 1152 px del límite anterior. Compilación Vite correcta. Se retiró la emulación temporal. Captura: `output/diseno/landing-ancho-fluido-8000-vista.jpg` (recorte de la superficie de página; el capturador integrado agrega área vacía a su imagen).

Este ajuste se aplica al shell compartido, pero esta ronda de mediciones visuales cubre la landing. La comparación real en Chrome y Opera sigue pendiente debido al bloqueo del control automático que no pudo verificar la URL; no se presenta la emulación del navegador integrado como esa comparación.

## Una sección activa en la landing

El ajuste más reciente sustituye la navegación entre secciones apiladas por una vista con una sola sección activa. Inicio, Institucional, Académico, Especialidades, Vida estudiantil, Sistema y Contacto se alternan mediante sus botones. Se conserva la URL con el identificador de sección, el historial del navegador y la recarga del destino elegido. La altura mínima usa el espacio disponible de la ventana menos la altura real de la cabecera, medida de nuevo cuando cambia su distribución. Alejar el zoom amplía la sección actual sin descubrir la siguiente.

Cada cambio enfoca el título correspondiente y presenta sus tarjetas con una transición breve y escalonada. El movimiento reducido cancela las animaciones. El pie aparece en Contacto. En móvil, una sección larga permite desplazamiento vertical para consultar todas sus tarjetas; las otras secciones siguen ocultas. Sin JavaScript, el contenido conserva la presentación normal de todas las secciones visibles.

QA visual en el navegador integrado en `http://localhost:8000/#inicio`: las siete opciones mostraron exactamente una sección activa, sin desbordes horizontales ni bloques invisibles. Se comprobaron Atrás, recarga, cierre del menú móvil al navegar, temas claro/oscuro y movimiento reducido. En anchos CSS de 320, 390, 1280 y 2560 px se mantuvo una sola sección visible. En la ventana original de 2530 × 1443 px, Inicio ocupó los 1336 px disponibles debajo de la cabecera de 107 px. Se retiraron las emulaciones temporales. Compilación Vite y sintaxis del JavaScript correctas. Evidencia: `output/diseno/landing-una-seccion.jpg`, recortada únicamente para retirar el área adicional que genera el capturador integrado.

## Inicio abierto e innovación orientada al estudiante

Se conserva una sección activa, con altura libre y desplazamiento vertical cuando su contenido lo requiere. La alineación inicial reemplaza el centrado vertical que producía grandes espacios vacíos. Inicio presenta la bienvenida, el panel oscuro de identidad institucional y el recorrido visual hacia estudios superiores, uno debajo del otro. El panel oscuro conserva el fondo original, Bachillerato Técnico, Especialidades e insignia SAVP; la fotografía del colegio reutiliza el recurso existente.

Sistema presenta intereses, exploración de carreras y apoyo al estudio en lenguaje cercano. El recorrido ilustrado representa pasos de exploración, sin porcentajes ni resultados ficticios. Dos iconos tienen una animación breve de cuatro segundos, sin repetición continua; la preferencia de movimiento reducido la elimina. Los contenidos describen las ventanas existentes de intereses, futuro académico y asistente, sin afirmar disponibilidad en vivo del servicio de generación. El acceso lleva al inicio de sesión del Aula Virtual. Consultar la inscripción conduce al contacto institucional para conocer requisitos y fechas, sin crear un proceso de inscripción ni publicar convocatorias inexistentes.

Comprobación en el navegador integrado: Inicio y Sistema en anchos CSS de 320, 390, 1280 y 2560 px, sin desborde horizontal ni bloques con opacidad cero. Las siete secciones siguen disponibles por navegación. Se verificaron carga de imagen e iconos, tema claro/oscuro, preferencia de movimiento reducido, destino de inscripción y regreso mediante Atrás; no aparecieron errores en consola. Compilación Vite y sintaxis JavaScript correctas. Las capturas `output/diseno/inicio-panel-oscuro.png` y `output/diseno/innovacion-humanizada.png` se revisaron visualmente y retiran únicamente el área vacía adicional del capturador. Se retiraron las emulaciones de viewport y movimiento. Esta comprobación no equivale a QA en Chrome u Opera ni a validar el servicio de orientación en vivo.

## Scroll libre, transiciones y recorrido interactivo

La corrección posterior elimina la vista que ocultaba las otras secciones. Toda la landing y el pie permanecen disponibles mediante scroll normal. El menú y los botones de sección anterior/siguiente desplazan hacia las anclas y conservan URL, historial y foco del título. El indicador activo sigue la sección con mayor área visible; al terminar la página identifica Contacto. Los controles adicionales aparecen en escritorio y respetan los extremos del recorrido. En anchos CSS de 1280 y 1920 px se midieron los paneles claro y oscuro de Inicio juntos, alineados en dos columnas. En móvil y en el espacio amplio de 2560 px se reorganizan sin perder contenido. Esta adaptación se basa en el espacio disponible, sin modificar el zoom del usuario.

Las entradas combinan desplazamiento lateral, escala sutil en imágenes y aparición escalonada de tarjetas; pueden repetirse al volver a una sección. Las superficies conservan opacidad visible en su estado base. La preferencia de movimiento reducido cancela las transiciones y desplazamientos. El scroll de rueda sigue disponible y no se intercepta.

«Tu próximo capítulo» incorpora tres estaciones pulsables, elecciones breves, pistas, contador de estaciones visitadas y reinicio. El marcador recorre la curva SVG durante 650 ms y el tramo azul acompaña el avance. Cambiar de estación conserva las elecciones de esta demostración; reiniciar las limpia. Con movimiento reducido, el marcador alcanza directamente su destino. Las elecciones solo viven en memoria de la página: no guardan un perfil, no consultan servicios ni producen recomendaciones de carrera. La cabecera identifica el recorrido como ejemplo. La orientación personal continúa en las ventanas autorizadas del estudiante. Se retiró del pie la frase «Proyecto académico de ingeniería de sistemas».

Se comprobó en el navegador integrado el recorrido completo, selección y respuesta de cada etapa, retorno con elecciones conservadas, activación con Enter, reinicio, extremos del marcador SVG, preferencia de movimiento reducido, tema claro/oscuro, navegación anterior/siguiente y menú móvil. Mediciones en anchos CSS de 320, 390, 1280, 1920 y 2560 px: siete secciones disponibles y sin desborde horizontal. Compilación Vite y sintaxis JavaScript correctas; sin errores nuevos en consola. La evidencia `output/diseno/recorrido-interactivo-eleccion.png` es un recorte de una elección visible, tomado de la captura de la aplicación. El capturador de página no produjo un encuadre completo fiable de esta sección, por lo que las mediciones del layout se distinguen de esa evidencia visual parcial. No se certifica QA en Chrome u Opera.
## Inicio de sesión centrado

La pantalla `/login` superaba la altura disponible al sumar el logo separado, el mínimo de 620 px de la tarjeta y sus espacios internos. Se compactaron la marca y el formulario: ancho máximo de 36 rem, altura natural y conjunto centrado con `100svh`. En ventanas bajas el documento conserva el scroll para acceder a todos los controles. El componente de tarjeta acepta atributos para aplicar esta variante exclusivamente al acceso institucional; las otras vistas conservan su composición.

QA en el navegador integrado: el formulario completo cabe sin scroll en 1366×768 y 1280×640; centrado en 390×845; en 320×569 el contenido desplaza verticalmente sin desbordes horizontales ni superposición entre distintivo, regreso y tema. Se verificaron el error local de correo y el botón bloqueado, tema claro/oscuro y consola sin errores. No se enviaron credenciales ni se probó autenticación del servidor. Compilación Vite y `git diff --check` correctos. Evidencia visual: `output/diseno/login-centrado.png`.

La mejora posterior del acceso reemplaza el ojo por Phosphor Duotone (`ph-eye` / `ph-eye-slash`), con etiqueta dinámica, estado pulsado y control de 48 px. El botón Ingresar permite intentar la validación local aunque los campos estén vacíos: el envío sigue bloqueado por el manejador del formulario hasta completar los datos. Esto sustituye el botón deshabilitado inicial, que impedía explicar qué faltaba. Cada campo tiene error asociado, `aria-invalid`, sacudida cancelable de 360 ms y foco al primer error; al corregirlo se retira el aviso. La preferencia de movimiento reducido cancela la sacudida. El servidor sigue validando de manera independiente. Un rechazo del servidor tiene aviso inline y conserva el correo mediante `old('email')`; esta rama se inspeccionó, sin enviar un intento de autenticación.

Verificación interactiva: ojo abierto/tachado, tipo de input y etiqueta al alternar; intento vacío sin navegación, ambos mensajes y foco en correo; correo mal formado y recuperación al corregirlo. Se midieron transformaciones de los campos durante la sacudida y ausencia de transformación con movimiento reducido. Sin errores de consola. La captura de validación (`output/diseno/login-validacion.png`) tiene un encuadre parcial; no prueba por sí sola el centrado. La evidencia final del nuevo icono es el recorte `output/diseno/login-icono-contrasena.png`; el centrado se comprobó mediante las mediciones de layout indicadas arriba. No se enviaron contraseñas ni se modificaron datos institucionales.

## Ajuste del administrador: distribución, roles y turnos

El límite anterior exigía 70 rem de contenido para alinear saludo/calendario y los seis indicadores. Se reduce a 56 rem y el espaciado de indicadores se adapta al contenedor. No se cambia el zoom, la fuente base ni los permisos. Los gráficos admiten columnas de 18 rem para aprovechar el mismo ancho de escritorio.

El gráfico circular de roles se sustituye por barras con cantidades escritas. La vista inicial compara el equipo institucional sin el rol Estudiante; el total estudiantil se presenta por separado. «Todos los roles» conserva la comparación completa. Los valores desplegables siguen la selección y las cantidades representan asignaciones, no cuentas únicas. Si solo hay estudiantes, se muestra directamente la vista completa.

Inscripciones permite alternar curso/turno, con transición y valores visibles. La consulta adicional agrupa inscripciones ACTIVAS de la gestión activa única y exige los permisos Inscripciones y Turnos. Un turno ausente se identifica como «Sin turno asignado» mediante left join; no se elimina de los totales. Sin gestión o permisos no se agrega información al cliente. Es una lectura agregada; no se modifican registros.

El trimestre ya era calculado por `orientarPeriodoActual`. La interfaz ahora muestra «Periodo académico», su nombre y rango, sin el texto «por confirmar» ni menciones técnicas a Support. El contrato sigue siendo REFERENCIAL: el catálogo no guarda fechas oficiales por gestión y el cálculo reutiliza el calendario sugerido existente. No se convierte este resultado en confirmación institucional ni se cambia una decisión académica. Cuando faltan fechas o hay ambigüedad, se muestra «Calendario no disponible»; las reglas de fuera de rango permanecen intactas.

Verificación en `http://localhost:8000/admin`, navegador integrado:

- Ancho CSS medido de 1280 × 720, contenido de aproximadamente 918 px: saludo/calendario en dos columnas, seis indicadores y tres gráficos. Ninguna tarjeta ni el documento desborda horizontalmente.
- Ancho CSS de 1366 px: seis indicadores y tres gráficos; sin desbordes de tarjetas.
- Móvil de 390 × 700: dos columnas de indicadores y un gráfico por fila, sin desbordes; tema oscuro comprobado y luego restablecido a claro.
- Alternancia de roles y curso/turno, selección de turno con Enter y actualización de aria-pressed, etiqueta del canvas y valores desplegables. La lectura actual por turno dio Mañana: 600, coincidente con el total de inscripciones. Consola sin errores tras la compilación final.
- PHPUnit: `tests/Unit/PanelAdministradorInteligenteTest.php`, 7 pruebas y 42 aserciones correctas. Incluye períodos sin fechas, ambiguos y fuera de rango; no se alteraron datos institucionales para simularlos. Vite, Pint del controlador y git diff --check correctos.

Las dimensiones se comprobaron después de estabilizar la emulación, pues el navegador integrado usa una escala propia. Las capturas emuladas mostraron recortes o artefactos de composición y no se usan como prueba visual de escritorio. `output/diseno/admin-distribucion-graficos.png` contiene el encabezado final en una vista reducida y demuestra el intervalo del trimestre; no demuestra por sí sola los seis indicadores ni los gráficos. Se retiraron las emulaciones temporales. No se certifica QA en Opera o Chrome ni una revisión visual de todas las ventanas del sistema.

