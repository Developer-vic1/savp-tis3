<?php

namespace App\Support\Interfaz;

use Illuminate\Http\Request;

final class ErrorInstitucional
{
    public static function contexto(Request $request, int $estado): array
    {
        $mensajes = [
            400 => ['Revisemos esta solicitud', 'La información enviada no pudo interpretarse. Regresa y revisa los campos antes de continuar.', 'Solicitud no válida', 'clipboard-text'],
            401 => ['Inicia sesión para continuar', 'Necesitamos confirmar tu sesión antes de abrir este espacio.', 'Autenticación requerida', 'sign-in'],
            403 => ['Este espacio requiere otro permiso', 'Tu cuenta no tiene autorización para abrir esta página. Puedes volver a tu espacio habitual o pedir ayuda a soporte.', 'Acceso no permitido', 'shield-check'],
            404 => ['No encontramos esta página', 'El enlace puede haber cambiado o la página ya no estar disponible. Volvamos al lugar donde estabas trabajando.', 'Página no encontrada', 'map-trifold'],
            405 => ['Esta acción no está disponible aquí', 'Regresa a la pantalla anterior y usa las acciones disponibles en ella.', 'Método no permitido', 'cursor-click'],
            408 => ['La solicitud tardó demasiado', 'La conexión se interrumpió antes de completar la solicitud. Puedes volver e intentarlo nuevamente.', 'Tiempo de espera agotado', 'clock'],
            419 => ['Tu sesión necesita renovarse', 'Por seguridad, la sesión de esta página ha vencido. Inicia sesión nuevamente antes de continuar.', 'Sesión vencida', 'clock-countdown'],
            429 => ['Hagamos una pequeña pausa', 'Recibimos varias solicitudes seguidas. Espera un momento antes de volver a intentarlo.', 'Demasiadas solicitudes', 'hourglass'],
            500 => ['Algo interrumpió esta página', 'SAVP no pudo completar esta solicitud. Puedes regresar a tu trabajo o compartir el detalle con soporte para que te ayude.', 'Error del servidor', 'wrench'],
            502 => ['La conexión con el servicio se interrumpió', 'Un servicio no respondió correctamente. Vuelve a intentarlo en unos momentos.', 'Respuesta del servicio no válida', 'plugs-connected'],
            503 => ['SAVP necesita un momento', 'El servicio no está disponible temporalmente. Inténtalo nuevamente en unos momentos.', 'Servicio no disponible', 'coffee'],
            504 => ['Estamos esperando al servicio', 'Un servicio tardó más de lo esperado. Puedes regresar e intentarlo más tarde.', 'Tiempo de espera del servicio agotado', 'clock'],
        ];
        [$titulo, $mensaje, $tipo, $icono] = $mensajes[$estado];
        $nombre = 'Visitante';
        $rol = 'Sin sesión iniciada';
        $conSesion = false;
        // El soporte también debe poder abrirse cuando falla la conexión a la base de datos.
        try {
            if ($usuario = $request->user()) {
                $conSesion = true;
                $persona = $usuario->persona;
                $nombre = mb_strtoupper(trim(implode(' ', array_filter([$persona?->nom_per, $persona?->ape_pat_per, $persona?->ape_mat_per]))) ?: 'Usuario de SAVP');
                $rol = $usuario->getRoleNames()->implode(', ') ?: 'Rol por revisar';
            }
        } catch (\Throwable) {
            $nombre = 'Usuario de SAVP';
            $rol = 'No disponible en este momento';
        }
        $inicio = url($conSesion ? '/dashboard' : '/login');
        $volver = $inicio;
        $referencia = $request->headers->get('referer');
        if ($referencia && self::mismoOrigen($referencia, $request->getSchemeAndHttpHost()) && strtok($referencia, '?') !== $request->url()) {
            $volver = $referencia;
        }

        return compact('estado', 'titulo', 'mensaje', 'tipo', 'icono', 'nombre', 'rol', 'volver', 'inicio', 'conSesion') + ['pagina' => $request->url()];
    }

    private static function mismoOrigen(string $url, string $origen): bool
    {
        $partes = parse_url($url);
        $base = parse_url($origen);

        return $partes && ! isset($partes['user']) && ! isset($partes['pass'])
            && ($partes['scheme'] ?? '') === ($base['scheme'] ?? '')
            && ($partes['host'] ?? '') === ($base['host'] ?? '')
            && ($partes['port'] ?? null) === ($base['port'] ?? null);
    }
}
