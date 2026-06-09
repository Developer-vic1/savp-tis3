<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TipoVinculacionEstudianteController extends Controller
{
    public function index(): View
    {
        return view('admin.tipo-vinculacion-estudiante');
    }
}
