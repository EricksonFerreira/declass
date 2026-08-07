<?php

namespace App\Http\Controllers;

use App\Models\OperacaoFiscal;
use App\Models\RegraTributaria;
use App\Models\CreditoTributario;
use App\DTO\BreadCrumbItem;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;

class FiscalController extends Controller
{
    public function __construct(
        protected BreadcrumbService $breadcrumbService
    ) {}

    public function dashboard()
    {
        $operacoesCount = OperacaoFiscal::count();
        $creditosDisponiveis = CreditoTributario::where('status', 'DISPONIVEL')->sum('saldo');
        $debitosIbs = OperacaoFiscal::join('operacoes_fiscais_tributos', 'operacoes_fiscais.id', '=', 'operacoes_fiscais_tributos.operacao_fiscal_id')
            ->where('operacoes_fiscais_tributos.tributo', 'IBS')
            ->sum('operacoes_fiscais_tributos.valor');
        
        $debitosCbs = OperacaoFiscal::join('operacoes_fiscais_tributos', 'operacoes_fiscais.id', '=', 'operacoes_fiscais_tributos.operacao_fiscal_id')
            ->where('operacoes_fiscais_tributos.tributo', 'CBS')
            ->sum('operacoes_fiscais_tributos.valor');

        $saldoEstimado = max(0, ($debitosIbs + $debitosCbs) - $creditosDisponiveis);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', '#'),
        ];

        return view('fiscal.dashboard', compact(
            'operacoesCount', 'creditosDisponiveis', 'debitosIbs', 'debitosCbs', 'saldoEstimado', 'breadcrumb'
        ));
    }

    public function operacoes()
    {
        $operacoes = OperacaoFiscal::with(['locacao', 'tributos'])->paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Operações Fiscais', '#'),
        ];

        return view('fiscal.operacoes', compact('operacoes', 'breadcrumb'));
    }

    public function regras()
    {
        $regras = RegraTributaria::paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Regras Tributárias', '#'),
        ];

        return view('fiscal.regras', compact('regras', 'breadcrumb'));
    }

    public function creditos()
    {
        $creditos = CreditoTributario::paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Créditos Tributários', '#'),
        ];

        return view('fiscal.creditos', compact('creditos', 'breadcrumb'));
    }
}
