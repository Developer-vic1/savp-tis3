<?php

namespace App\Http\Controllers;

use App\Services\RegencyAccessService;
use Illuminate\Http\Request;

class RegencyAssignmentsController extends Controller
{
    public function __invoke(Request $request, RegencyAccessService $access)
    {
        return view('workspaces.mis-grados', $access->assignments($request->user()));
    }
}
