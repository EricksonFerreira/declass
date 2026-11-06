<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home(Request $request): View
    {
        return view('home', [
            'titulo' => 'Início',
            'breadcrumb' => [
                ['name' => 'Home', 'active' => true],
            ],
        ]);
    }

    public function dashboard(Request $request)
    {
        return redirect()->route('home');
    }
}
