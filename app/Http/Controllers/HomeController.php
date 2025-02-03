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
        $items = $this->getSystemCards();        

        return view('home', compact('breadcrumb','items'))->with('titulo', $this->titulo);
    }

    private function getSystemCards()
    {
        return [
            [
                'grupo' => 'Reservas e Movimentações',
                'backgroundIcone' => 'bg-info',
                'items' => [
                    [
                        'nome' => 'Reservas',
                        'route' => 'reserva.index',
                        'icone' => 'fas fa-calendar-check',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Reserva::count()
                    ],
                    [
                        'nome' => 'Status Reservas',
                        'route' => 'status_reserva.index',
                        'icone' => 'fas fa-check-circle',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusReserva::count()
                    ],
                    [
                        'nome' => 'Movimentações',
                        'route' => 'movimentacao.index',
                        'icone' => 'fas fa-truck-moving',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Movimentacao::count()
                    ],
                    [
                        'nome' => 'Status Movimentações',
                        'route' => 'status_movimentacao.index',
                        'icone' => 'fas fa-circle',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\StatusMovimentacao::count()
                    ],
                    [
                        'nome' => 'Tipo Movimentações',
                        'route' => 'tipo_movimentacao.index',
                        'icone' => 'fas fa-truck',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TipoMovimentacao::count()
                    ],
                ]
            ],
            [
                'grupo' => 'Configurações de Usuário',
                'backgroundIcone' => 'bg-warning',
                'items' => [
                    [
                        'nome' => 'Usuários',
                        'route' => 'usuario.index',
                        'icone' => 'fas fa-fw fa-user',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\User::count()
                    ],
                    [
                        'nome' => 'Regras',
                        'route' => 'regra.index',
                        'icone' => 'fas fa-key',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Regra::count()
                    ],
                    [
                        'nome' => 'Permissões',
                        'route' => 'permissao.index',
                        'icone' => 'fas fa-lock',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Permissao::count()
                    ],
                    [
                        'nome' => 'Termos de Consentimento',
                        'route' => 'termo_consentimento.index',
                        'icone' => 'fas fa-file-signature',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TermoConsentimento::count()
                    ],
                ]
            ],
            [
                'grupo' => 'Configurações de Reserva',
                'backgroundIcone' => 'bg-success',
                'items' => [
                    [
                        'nome' => 'Países',
                        'route' => 'pais.index',
                        'icone' => 'fas fa-globe',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Pais::count()
                    ],
                    [
                        'nome' => 'Estações',
                        'route' => 'estacao.index',
                        'icone' => 'fas fa-map-marker-alt',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Estacao::count()
                    ],
                    [
                        'nome' => 'Dias de Trabalho',
                        'route' => 'dia_util.index',
                        'icone' => 'fas fa-calendar-day',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\DiaUtil::count()
                    ],
                    [
                        'nome' => 'Horários Diários',
                        'route' => 'horario_diario.index',
                        'icone' => 'fas fa-clock',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\HorarioDiario::count()
                    ],
                ]
            ],
            [
                'grupo' => 'Configurações de Frota',
                'backgroundIcone' => 'bg-danger',
                'items' => [
                    [
                        'nome' => 'Veículos',
                        'route' => 'veiculo.index',
                        'icone' => 'fas fa-car',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Veiculo::count()
                    ],
                    [
                        'nome' => 'Cupons de Desconto',
                        'route' => 'cupom_desconto.index',
                        'icone' => 'fas fa-percentage',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\CupomDesconto::count()
                    ],
                    [
                        'nome' => 'Categorias de Dano',
                        'route' => 'categoria_dano.index',
                        'icone' => 'fas fa-tags',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\CategoriaDano::count()
                    ],
                    [
                        'nome' => 'Danos',
                        'route' => 'dano.index',
                        'icone' => 'fas fa-tools',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Dano::count()
                    ],
                    [
                        'nome' => 'Tipos de Frota',
                        'route' => 'tipo_frota.index',
                        'icone' => 'fas fa-list-alt',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\TipoFrota::count()
                    ],
                    [
                        'nome' => 'Grupos de Frota',
                        'route' => 'grupo_frota.index',
                        'icone' => 'fas fa-th-list',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\GrupoFrota::count()
                    ],
                    [
                        'nome' => 'Marcas',
                        'route' => 'marca.index',
                        'icone' => 'fas fa-certificate',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Marca::count()
                    ],
                    [
                        'nome' => 'Modelos',
                        'route' => 'modelo.index',
                        'icone' => 'fas fa-truck',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Modelo::count()
                    ],
                    [
                        'nome' => 'Segmentos',
                        'route' => 'segmento.index',
                        'icone' => 'fas fa-bars',
                        'prefix' => null,
                        'suffix' => null,
                        'valor' => \App\Models\Segmento::count()
                    ],
                ]
            ]
        ];
    }
}
