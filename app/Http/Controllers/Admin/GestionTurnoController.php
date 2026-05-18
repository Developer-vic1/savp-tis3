<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GestionTurnoController extends Controller
{
    public function index()
    {
        return view('admin.gestion-turnos');
    }
}
