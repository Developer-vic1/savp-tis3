<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GestionPersonalInstitucional extends Controller
{
    public function index()
    {
        return view('admin.gestion-personal-institucional');
    }
}
