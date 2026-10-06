<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Oficial\Sistema\User;
use App\Services\BitacoraService;
use App\Services\RoleDashboardResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return Response
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google and log them in.
     *
     * @return RedirectResponse
     */
    public function callback()
    {
        try {
            $context = session('google_login_context');
            $loginRoute = $context === 'aula_virtual'
                ? 'aula-virtual.login'
                : 'login';

            $googleUser = Socialite::driver('google')->user();

            if (! $googleUser || ! $googleUser->getEmail()) {
                try {
                    if (class_exists(BitacoraService::class)) {
                        BitacoraService::registrar(
                            'error_google_auth',
                            'users',
                            null,
                            'Google Auth',
                            null,
                            'El proveedor de Google no devolvió una dirección de correo válida.',
                            'ERROR',
                            'ERROR'
                        );
                    }
                } catch (Throwable $e) {
                    report($e);
                }

                if ($context === 'aula_virtual') {
                    session()->forget('google_login_context');
                }

                return redirect()
                    ->route($loginRoute)
                    ->with('error', 'No se pudo iniciar sesión con Google. Intenta nuevamente.');
            }

            $email = strtolower($googleUser->getEmail());
            $user = User::where('email', $email)->first();

            // ⚠️ SI NO EXISTE, NO CREAR EL USUARIO
            if (! $user) {
                try {
                    if (class_exists(BitacoraService::class)) {
                        BitacoraService::registrar(
                            'intento_google_no_registrado',
                            'users',
                            null,
                            'Google Auth',
                            $email,
                            'Intento de inicio de sesión con cuenta de Google no registrada en la institución.',
                            'WARNING',
                            'FALLIDO'
                        );
                    }
                } catch (Throwable $e) {
                    report($e);
                }

                if ($context === 'aula_virtual') {
                    session()->forget('google_login_context');
                }

                return redirect()
                    ->route($loginRoute)
                    ->with('google_auth_error', true);
            }

            if ($user->est_usu !== 'ACTIVO') {
                session()->forget('google_login_context');

                return redirect()->route($loginRoute)->with('error', 'Tu cuenta no tiene acceso habilitado. Comunícate con administración.');
            }

            // 🔑 Actualizar campos de autenticación de Google de forma segura
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'auth_provider' => 'google',
                'email_verified_at' => $user->email_verified_at ?? now(),
                'last_login_at' => now(),
            ])->save();

            // Autenticar al usuario
            Auth::login($user, true);
            session()->regenerate();

            // Registrar en bitácora
            try {
                if (class_exists(BitacoraService::class)) {
                    BitacoraService::registrar(
                        'login_google_exitoso',
                        'users',
                        $user->cod_usu,
                        'Google Auth',
                        $user->email,
                        'Inicio de sesión exitoso utilizando Google OAuth.'
                    );
                }
            } catch (Throwable $e) {
                report($e);
            }

            // Redirigir según rol
            if ($context === 'aula_virtual') {
                session()->forget('google_login_context');

                if (! $user->can('Acceso_Aula_Virtual')) {
                    Auth::logout();

                    return redirect()
                        ->route('aula-virtual.login')
                        ->with('aula_virtual_access_error', true);
                }

                return redirect()->route('aula-virtual.inicio');
            }

            $workspace = app(RoleDashboardResolver::class)->routeFor($user);
            if ($workspace && Route::has($workspace)) {
                return redirect()->route($workspace);
            }

            return redirect()->route('dashboard');

        } catch (Throwable $e) {
            // Registrar error en bitácora
            try {
                if (class_exists(BitacoraService::class)) {
                    BitacoraService::registrar(
                        'error_google_auth',
                        'users',
                        null,
                        'Google Auth',
                        null,
                        'Excepción al procesar el callback de Google: '.$e->getMessage(),
                        'ERROR',
                        'ERROR',
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (Throwable $logEx) {
                report($logEx);
            }

            report($e);

            if (session('google_login_context') === 'aula_virtual') {
                session()->forget('google_login_context');

                return redirect()
                    ->route('aula-virtual.login')
                    ->with('error', 'No se pudo iniciar sesion con Google. Intenta nuevamente.');
            }

            return redirect()
                ->route('login')
                ->with('error', 'No se pudo iniciar sesión con Google. Intenta nuevamente.');
        }
    }
}
