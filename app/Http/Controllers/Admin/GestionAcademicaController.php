<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GestionAcademicaController extends Controller
{
    public function index()
    {
        return view('admin.gestion-academica');
    }
}
