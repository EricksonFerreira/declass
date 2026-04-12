<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Services\BreadcrumbService;

class IndicadoresController extends Controller
{
    protected BreadcrumbService $breadcrumbService;

    public function __construct(BreadcrumbService $breadcrumbService)
    {
        $this->breadcrumbService = $breadcrumbService;
    }

    public static function lista(): array
    {
        return [
            'reservas' => [
                'slug'      => 'reservas',
                'nome'      => 'Reservas',
                'descricao' => 'Volume ao longo do tempo, origem das reservas e ranking de colaboradores.',
                'icone'     => 'fas fa-calendar-check',
                'cor'       => 'c-teal',
                'secao'     => 'reservas',
            ],
            'financeiro' => [
                'slug'      => 'financeiro',
                'nome'      => 'Financeiro',
                'descricao' => 'Evolução de receita, gateways de pagamento e taxa de aprovação de transações.',
                'icone'     => 'fas fa-wallet',
                'cor'       => 'c-emerald',
                'secao'     => 'financeiro',
            ],
            'frota' => [
                'slug'      => 'frota',
                'nome'      => 'Frota & Ocupação',
                'descricao' => 'Distribuição dos veículos por grupo de frota e taxa de ocupação no período.',
                'icone'     => 'fas fa-car',
                'cor'       => 'c-indigo',
                'secao'     => 'frota',
            ],
            'movimentacoes' => [
                'slug'      => 'movimentacoes',
                'nome'      => 'Movimentações',
                'descricao' => 'Distribuição de movimentações por status e total no período.',
                'icone'     => 'fas fa-route',
                'cor'       => 'c-amber',
                'secao'     => 'movimentacoes',
            ],
            'satisfacao' => [
                'slug'      => 'satisfacao',
                'nome'      => 'Satisfação do Cliente',
                'descricao' => 'CSAT, NPS, distribuição de promotores e detratores e rankings por dimensão.',
                'icone'     => 'fas fa-smile',
                'cor'       => 'c-rose',
                'secao'     => 'satisfacao',
            ],
            'analise_avancada' => [
                'slug'      => 'analise_avancada',
                'nome'      => 'Análise Avançada da Frota',
                'descricao' => 'Saúde da frota, eficiência, lucro real, proteção FIPE e diagnóstico detalhado por veículo.',
                'icone'     => 'fas fa-chart-line',
                'cor'       => 'c-purple',
                'secao'     => 'analise_avancada',
            ],
        ];
    }

    public function index()
    {
        $indicadores  = self::lista();
        $homeSections = app(HomeController::class)->getHomeSectionPermissions();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbUnico('Indicadores');

        return view('indicadores.index', compact('indicadores', 'homeSections', 'breadcrumb'))
            ->with('titulo', 'Indicadores');
    }

    public function show(string $slug)
    {
        $lista = self::lista();

        if (!array_key_exists($slug, $lista)) {
            abort(404);
        }

        $indicador     = $lista[$slug];
        $home          = app(HomeController::class);
        $homeSections  = $home->getHomeSectionPermissions();
        $estacoesFiltro = $home->estacoesParaFiltro();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Indicadores', route('indicadores.index')),
            new BreadCrumbItem($indicador['nome']),
        ]);

        return view('indicadores.show', compact('indicador', 'homeSections', 'estacoesFiltro', 'breadcrumb'))
            ->with('titulo', $indicador['nome']);
    }
}
