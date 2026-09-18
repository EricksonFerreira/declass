<?php

namespace App\Http\Controllers;

use App\Services\MenuSecoesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Hub de atalhos em cards — usado pelo menu inferior no mobile.
     * Sem {secao}: todas as areas. Com {secao}: apenas aquela area.
     */
    public function index(Request $request, ?string $secao = null): View
    {
        $secoes = app(MenuSecoesService::class)->secoes($request->user(), true);

        $secaoAtual = null;
        if ($secao) {
            $secoes = array_values(array_filter($secoes, fn ($s) => $s['slug'] === $secao));
            abort_if($secoes === [], 404);
            $secaoAtual = $secoes[0]['titulo'];
        }

        return view('menu', [
            'titulo' => $secaoAtual ?? 'Menu',
            'secoes' => $secoes,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $secaoAtual ?? 'Menu', 'active' => true],
            ],
        ]);
    }
}
