<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** Catálogo visual aislado: no utiliza Auth, modelos ni consultas institucionales. */
final class DisenoLocalController extends Controller
{
    private const CAPTURAS = [
        'admin.dashboard' => ['Administrador · Inicio', 'output/diseno/administrador-inicio-20261003.png'],
        'admin.gestion-academica' => ['Gestión académica', 'output/diseno/gestion-academica-20261004.png'],
        'admin.calendario' => ['Calendario e impacto', 'output/diseno/calendario-impacto-20261004.png'],
        'admin.gestion-docentes' => ['Docentes', 'output/diseno/docentes-redisenados-20261004.png'],
        'admin.gestion-estudiantes' => ['Estudiantes · Ficha lateral', 'output/diseno/estudiantes-ficha-lateral-20261004.png'],
        'direccion.dashboard' => ['Dirección · Inicio', 'output/playwright/dashboard-direccion-actual.png'],
        'secretaria.dashboard' => ['Secretaría · Inicio', 'output/playwright/dashboard-secretaria-actual.png'],
        'docente.dashboard' => ['Docente · Inicio', 'output/playwright/dashboard-docente-actual.png'],
        'estudiante.dashboard' => ['Estudiante · Inicio', 'output/playwright/dashboard-estudiante-actual.png'],
        'aula-inicio' => ['Aula Virtual · Docente · Inicio', 'output/playwright/lms-docente-inicio.png'],
        'aula-cursos' => ['Aula Virtual · Docente · Cursos', 'output/playwright/lms-docente-mis-cursos.png'],
        'aula-calendario' => ['Aula Virtual · Docente · Calendario', 'output/playwright/lms-docente-calendario.png'],
        'aula-reportes' => ['Aula Virtual · Docente · Reportes', 'output/playwright/lms-docente-reportes.png'],
        'aula-orientacion' => ['Aula Virtual · Docente · Orientación', 'output/playwright/lms-docente-orientacion.png'],
        'aula-estudiante' => ['Aula Virtual · Estudiante · Inicio', 'output/playwright/lms-estudiante-real.png'],
    ];

    public function acceso(Request $request)
    {
        return $this->permitido($request) ? redirect()->route('diseno.vistas') : view('diseno-local.acceso');
    }

    public function entrar(Request $request)
    {
        $validacion = Validator::make($request->only('clave'), ['clave' => ['required', 'string', 'max:200']], ['clave.required' => 'Escribe la clave del modo de diseño.']);
        if ($validacion->fails()) {
            return redirect()->route('diseno.acceso')->withErrors($validacion);
        }
        $datos = $validacion->validated();
        if (! Hash::check($datos['clave'], config('diseno-local.clave_hash'))) {
            // No conservar la contraseña en datos flash de la sesión.
            return redirect()->route('diseno.acceso')->withErrors(['clave' => 'La clave no coincide. Vuelve a intentarlo.']);
        }
        $request->session()->regenerate();
        $request->session()->put('diseno_local', ['vence' => now()->addMinutes(config('diseno-local.minutos'))->timestamp,
            'firma' => hash('sha256', config('diseno-local.clave_hash'))]);
        return redirect()->route('diseno.vistas');
    }

    public function salir(Request $request)
    {
        $request->session()->forget('diseno_local');
        return redirect()->route('diseno.acceso');
    }

    private function permitido(Request $request): bool
    {
        $acceso = $request->session()->get('diseno_local');
        return is_array($acceso) && ($acceso['vence'] ?? 0) > now()->timestamp
            && hash_equals(hash('sha256', config('diseno-local.clave_hash')), (string) ($acceso['firma'] ?? ''));
    }

    public function vistas(Request $request)
    {
        if (! $this->permitido($request)) {
            return redirect()->route('diseno.acceso');
        }
        $actores = ['admin' => 'Administrador', 'direccion' => 'Director', 'secretaria' => 'Secretaria',
            'regencia' => 'Regente', 'docente' => 'Docente', 'estudiante' => 'Estudiante', 'aula-virtual' => 'Aula Virtual'];
        $ventanas = [];
        foreach (Route::getRoutes() as $ruta) {
            $nombre = $ruta->getName();
            $prefijo = explode('.', $nombre ?? '')[0];
            if (! $nombre || ! isset($actores[$prefijo]) || ! in_array('GET', $ruta->methods(), true)) {
                continue;
            }
            if (preg_match('/(?:^|\.)(?:pdf|sql|zip|download|descargar|query|conexion|probar)(?:\.|$)/i', $nombre)) {
                continue;
            }
            $titulo = self::CAPTURAS[$nombre][0] ?? Str::headline(str_replace('.', ' · ', preg_replace('/^(admin|direccion|secretaria|regencia|docente|estudiante|aula-virtual)\./', '', $nombre)));
            $titulo = strtr($titulo, ['Gestion' => 'Gestión', 'Academica' => 'Académica', 'Academicos' => 'Académicos', 'Tecnicas' => 'Técnicas', 'Bitacora' => 'Bitácora', 'Evaluacion' => 'Evaluación', 'Documentacion' => 'Documentación', 'Institucion' => 'Institución', 'Configuracion' => 'Configuración']);
            $ventanas[] = ['id' => $nombre, 'actor' => $actores[$prefijo], 'titulo' => $titulo,
                'imagen' => isset(self::CAPTURAS[$nombre]) && is_file(base_path(self::CAPTURAS[$nombre][1])) ? route('diseno.imagen', ['captura' => $nombre]) : null];
        }
        foreach (self::CAPTURAS as $clave => [$titulo, $archivo]) {
            if (str_starts_with($clave, 'aula-') && is_file(base_path($archivo))) {
                $ventanas[] = ['id' => $clave, 'actor' => 'Aula Virtual', 'titulo' => $titulo, 'imagen' => route('diseno.imagen', ['captura' => $clave])];
            }
        }
        return view('diseno-local.vistas', ['ventanas' => $ventanas, 'actores' => array_values($actores)]);
    }

    public function imagen(Request $request, string $captura)
    {
        if (! $this->permitido($request)) {
            return response('Accede primero al modo de diseño.', 410);
        }
        if (! isset(self::CAPTURAS[$captura]) || ! is_file(base_path(self::CAPTURAS[$captura][1]))) {
            return response('No hay captura registrada para esta ventana.', 410);
        }
        // La ruta de archivo procede exclusivamente de esta lista fija.
        return response()->file(base_path(self::CAPTURAS[$captura][1]), ['Cache-Control' => 'no-store, private']);
    }
}
