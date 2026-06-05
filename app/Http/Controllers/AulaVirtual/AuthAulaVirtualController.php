<?php

namespace App\Http\Controllers\AulaVirtual;

use App\Http\Controllers\Controller;

class AuthAulaVirtualController extends Controller
{
    public function login()
    {
        return view('aula-virtual.auth.login');
    }

    public function redirectToGoogle()
    {
        session(['google_login_context' => 'aula_virtual']);

        return redirect()->route('google.redirect');
    }
}
