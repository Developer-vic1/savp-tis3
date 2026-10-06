<x-mail::message>
# ¡Qué gusto tenerte en SAVP!

Hola{{ $nombre ? ', '.$nombre : '' }}:

Te damos la bienvenida a la comunidad de la **Unidad Educativa Franz Tamayo N°3**. Preparamos tu acceso para que puedas participar en las actividades que corresponden a tu rol.

<x-mail::panel>
**Tu correo para entrar al sistema**

{{ $correoAcceso }}

Esta bienvenida se envió a: **{{ $correoEntrega }}**.
</x-mail::panel>

Para comenzar, elige una contraseña que solo tú conozcas. Si ya tienes una, se conservará hasta que completes el cambio.

<x-mail::button :url="$urlAcceso">
Elegir mi contraseña
</x-mail::button>

Tienes **{{ $minutos }} minutos** para usar el enlace, que funciona una sola vez. Si vence, la administración puede enviarte uno nuevo.

Después podrás [iniciar sesión en SAVP]({{ $urlInicio }}) con tu correo de acceso y la contraseña que elegiste.

Si no esperabas este correo o necesitas ayuda, comunícate con la administración de la unidad educativa.

¡Te acompañamos en este primer paso!

**Equipo de la Unidad Educativa Franz Tamayo N°3**

<x-slot:subcopy>
Si el botón no abre, copia este enlace en el navegador: [{{ $urlAcceso }}]({{ $urlAcceso }})
</x-slot:subcopy>
</x-mail::message>
