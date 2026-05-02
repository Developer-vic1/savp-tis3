<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GestionPersonalInstitucional extends Controller
{
    public function index()
    {
        return view('admin.gestion-personal-institucional');
    }
}
