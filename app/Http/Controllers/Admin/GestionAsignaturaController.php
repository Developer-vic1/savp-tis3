<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GestionAsignaturaController extends Controller
{
    public function index()
    {
        return view('admin.gestion-asignatura');
    }
}
