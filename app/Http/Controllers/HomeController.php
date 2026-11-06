<?php

namespace App\Http\Controllers;

use App\Services\SetupChecklistService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected SetupChecklistService $setupChecklistService,
    ) {}

    public function home(Request $request): View
    {
        return view('home', [
            'titulo' => 'Início',
            'breadcrumb' => [
                ['name' => 'Home', 'active' => true],
            ],
            'setupChecklist' => $this->setupChecklistService->itens($request->user()),
        ]);
    }

    public function dashboard(Request $request)
    {
        return redirect()->route('home');
    }
}
