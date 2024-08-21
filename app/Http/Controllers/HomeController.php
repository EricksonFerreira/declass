<?php

namespace App\Http\Controllers;

use App\Models\Permissao;
use App\Models\Regra;
use App\Models\TipoTransacao;
use App\Models\User;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $titulo;
    protected $breadcrumbService;
    public function __construct(BreadcrumbService $breadcrumbService)
    {
        $this->titulo = 'Home';
        $this->breadcrumbService = $breadcrumbService;
    }

    public function home()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbUnico('Home');

        $items = [
            [
                'nome' => 'Usuários',
                'route' => 'usuario.index',
                'icone' => 'fa fa-users',
                'backgroundIcone' => 'bg-warning',
                'prefix' => null,
                'suffix' => null,
                'valor' => User::count()
            ],
            [
                'nome' => 'Regras',
                'route' => 'regra.index',
                'icone' => 'fa fa-key',
                'backgroundIcone' => 'bg-warning',
                'prefix' => null,
                'suffix' => null,
                'valor' => Regra::count()
            ],
            [
                'nome' => 'Permissões',
                'route' => 'permissao.index',
                'icone' => 'fa fa-lock',
                'backgroundIcone' => 'bg-warning',
                'prefix' => null,
                'suffix' => null,
                'valor' => Permissao::count()
            ],
            [
                'nome' => 'Tipo Transações',
                'route' => 'tipotransacao.index',
                'icone' => 'fa fa-money-check-alt',
                'backgroundIcone' => 'bg-blue',
                'prefix' => null,
                'suffix' => null,
                'valor' => TipoTransacao::count()
            ],
        ];

        return view('home', compact('breadcrumb','items'))->with('titulo', $this->titulo);
    }
}
