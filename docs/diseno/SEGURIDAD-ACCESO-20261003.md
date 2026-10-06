# Protección del acceso institucional

Alcance: `/login`, su formulario y el icono institucional compartido. Se conserva Fortify, el segundo factor, CSRF, regeneración de sesión y autorización existente. No es una auditoría de todos los módulos ni una certificación de invulnerabilidad.

## Parámetros y comportamiento

`config/seguridad-acceso.php` fija tres fallos por ronda. Las pausas son 15, 30, 60, 120, 240 y 300 segundos; se mantienen en 300 para rondas posteriores. Una hora sin fallos elimina el historial y un acceso correcto reinicia la progresión. Intentos durante una pausa no consultan credenciales, no consumen otra oportunidad ni prolongan el plazo.

`ProtegerIntentosAcceso` se ejecuta en las rutas de Fortify y actúa únicamente sobre `POST login.store`, antes de autenticar. Cuenta las excepciones de validación y las respuestas que `Routing\Pipeline` ya convirtió desde esas excepciones. Esto incluye peticiones vacías enviadas directamente sin JavaScript. Los bloqueos JSON devuelven 429 y `Retry-After`; HTML redirige al formulario con error, correo conservado y plazo de pausa, nunca la contraseña. Solo se conservan identificadores de tipo string y la preferencia booleana de recordar sesión.

El historial usa una clave SHA-256 de correo normalizado más IP. Se emplea un cerrojo de caché para serializar peticiones del mismo ámbito. Además, el limitador de volumen permite 60 peticiones por minuto por IP aunque cambie el correo. Esta combinación reduce fuerza bruta local y rotación de identificadores; no cubre por sí sola ataques distribuidos desde muchas IP. El ámbito por correo e IP evita que una IP ajena bloquee globalmente una cuenta legítima.

La caché de seguridad predeterminada es `file`, persistente entre peticiones del servidor local, sin tablas nuevas ni escrituras de registros institucionales. En varias instancias se debe configurar `ACCESO_CACHE_STORE=redis` con un almacén realmente compartido y capaz de crear cerrojos. No usar `array` o `null` en producción: `array` solo se utiliza en las pruebas. CSRF y Fortify siguen aplicándose en el servidor.

## Interfaz y Recordarme

Tres intentos locales incompletos o mal formados generan la misma progresión de pausas. El estado de UX (cuatro números, sin correo ni contraseña) se conserva en `sessionStorage` de la pestaña. Se muestra cuenta regresiva y se impiden nuevos envíos por clic o Enter mientras dure. Esta pausa puede eludirse manipulando el navegador y no se considera una barrera de seguridad: el servidor verifica su propio historial. El temporizador se reconstruye al regresar desde la caché de navegación. Se mantienen los errores cerca del campo y la sacudida con soporte de movimiento reducido.

Recordarme viene marcado y envía `remember=1`; al desmarcarlo se envía `remember=0`, conservado al volver por un error. Fortify decide si emite su cookie persistente. La aplicación no guarda la contraseña en almacenamiento del navegador. Se conservan `autocomplete=username/current-password`: desmarcar Recordarme no puede garantizar que el navegador deje de ofrecer guardar contraseñas. [MDN documenta esta limitación](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/autocomplete).

El componente `icono-institucional` referencia el mismo archivo existente `public/image/LOGO FT3 A.jpg` usado por el formulario. Se comprobó visualmente el escudo con el nombre Franz Tamayo N.º 3. Landing, layout institucional y layout de invitados incluyen ese icono; Aula Virtual hereda el layout compartido.

## Strix y validación

Se instaló la skill oficial [penetration-testing-with-strix](https://github.com/usestrix/strix/tree/main/skills/penetration-testing-with-strix) en `C:/Users/LOQ/.codex/skills/penetration-testing-with-strix`. Disponible para selección en el próximo turno. No se instaló ni ejecutó su motor: Docker está instalado pero su daemon no responde; faltan configuración de modelo y clave de LLM. No se subió el repositorio, datos o credenciales a Strix Cloud, ni se realizó un pentest autónomo contra la base institucional. La skill advierte que un objetivo de código local se monta con permiso de escritura; cualquier ejecución futura debe usar una copia aislada que conserve el trabajo concurrente y un presupuesto autorizado de LLM.

Pruebas de PHP en SQLite configurado en memoria y caché de arrays, sin migrations ni usuarios reales: 12 casos, 62 aserciones. Cubren 15/30 segundos, no consulta durante bloqueo, POST vacío, JSON/HTML y `Retry-After`, conservación segura de campos, cookies de Recordarme activado/desactivado, regeneración de sesión, segundo factor, límite por IP con correos diferentes, tope, caducidad y normalización del ámbito. Cuatro pruebas Node verifican la máquina de pausas local, persistencia, reinicio y recuperación ante estado dañado. Vite compila y Pint valida los archivos PHP afectados.

QA en el navegador integrado: icono y logo del formulario resuelven la misma URL y la imagen carga; checkbox marcado inicialmente; tres clics vacíos bloquean 15 segundos; la ronda siguiente bloquea 30; Enter no envía durante la pausa y recargar conserva el plazo. Evidencia: `output/diseno/acceso-pausa-progresiva.png`. Se limpia exclusivamente el estado local creado durante QA; no se reinician bloqueos reales del servidor. No se certifica QA en Opera/Chrome ni un pentest de Strix.
