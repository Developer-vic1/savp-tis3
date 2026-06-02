<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            if (!$googleUser || !$googleUser->getEmail()) {
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

                return redirect()
                    ->route('login')
                    ->with('error', 'No se pudo iniciar sesión con Google. Intenta nuevamente.');
            }

            $email = strtolower($googleUser->getEmail());
            $user = User::where('email', $email)->first();

            // ⚠️ SI NO EXISTE, NO CREAR EL USUARIO
            if (!$user) {
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

                return redirect()
                    ->route('login')
                    ->with('google_auth_error', true);
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
            if ($user->hasRole('Administrador') && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('Director') && Route::has('director.dashboard')) {
                return redirect()->route('director.dashboard');
            } elseif ($user->hasRole('Docente') && Route::has('docente.dashboard')) {
                return redirect()->route('docente.dashboard');
            } elseif ($user->hasRole('Estudiante') && Route::has('estudiante.aula-virtual')) {
                return redirect()->route('estudiante.aula-virtual');
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
                        'Excepción al procesar el callback de Google: ' . $e->getMessage(),
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

            return redirect()
                ->route('login')
                ->with('error', 'No se pudo iniciar sesión con Google. Intenta nuevamente.');
        }
    }
}
