<?php

namespace App\Http\Controllers;

use App\Models\OperacaoFiscal;
use App\Models\RegraTributaria;
use App\Models\CreditoTributario;
use App\Models\ClassificacaoFiscal;
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

    // --- REGRAS TRIBUTÁRIAS ---
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

    public function regrasCriar()
    {
        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Regras Tributárias', route('fiscal.regras')),
            new BreadCrumbItem('Nova Regra', '#'),
        ];

        return view('fiscal.regras_form', compact('breadcrumb'));
    }

    public function regrasSalvar(Request $request)
    {
        $validated = $request->validate([
            'tributo' => 'required|string|in:IBS,CBS,PIS,COFINS',
            'tipo_operacao' => 'required|string|max:50',
            'aliquota' => 'required|numeric|min:0|max:100',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'gera_credito' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['gera_credito'] = $request->has('gera_credito');
        $validated['ativo'] = $request->has('ativo');

        RegraTributaria::create($validated);

        return redirect()->route('fiscal.regras')->with('success', 'Regra tributária criada com sucesso!');
    }

    public function regrasEditar($id)
    {
        $regra = RegraTributaria::findOrFail($id);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Regras Tributárias', route('fiscal.regras')),
            new BreadCrumbItem('Editar Regra', '#'),
        ];

        return view('fiscal.regras_form', compact('regra', 'breadcrumb'));
    }

    public function regrasAtualizar(Request $request, $id)
    {
        $regra = RegraTributaria::findOrFail($id);

        $validated = $request->validate([
            'tributo' => 'required|string|in:IBS,CBS,PIS,COFINS',
            'tipo_operacao' => 'required|string|max:50',
            'aliquota' => 'required|numeric|min:0|max:100',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'gera_credito' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['gera_credito'] = $request->has('gera_credito');
        $validated['ativo'] = $request->has('ativo');

        $regra->update($validated);

        return redirect()->route('fiscal.regras')->with('success', 'Regra tributária atualizada com sucesso!');
    }

    public function regrasExcluir($id)
    {
        $regra = RegraTributaria::findOrFail($id);
        $regra->delete();

        return redirect()->route('fiscal.regras')->with('success', 'Regra tributária excluída com sucesso!');
    }

    // --- CRÉDITOS TRIBUTÁRIOS ---
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

    public function creditosCriar()
    {
        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Créditos Tributários', route('fiscal.creditos')),
            new BreadCrumbItem('Novo Crédito', '#'),
        ];

        return view('fiscal.creditos_form', compact('breadcrumb'));
    }

    public function creditosSalvar(Request $request)
    {
        $validated = $request->validate([
            'tributo' => 'required|string|in:IBS,CBS',
            'valor_original' => 'required|numeric|min:0.01',
            'origem' => 'required|string|max:100',
            'data_apropriacao' => 'required|date',
        ]);

        $validated['empresa_id'] = app(\App\Support\EmpresaContext::class)->empresaId() ?: 1;
        $validated['valor_utilizado'] = 0.00;
        $validated['saldo'] = $validated['valor_original'];
        $validated['status'] = 'DISPONIVEL';

        CreditoTributario::create($validated);

        return redirect()->route('fiscal.creditos')->with('success', 'Crédito tributário registrado com sucesso!');
    }

    // --- CLASSIFICAÇÕES FISCAIS (NCM) ---
    public function classificacoes()
    {
        $classificacoes = ClassificacaoFiscal::paginate(15);

        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Classificações Fiscais', '#'),
        ];

        return view('fiscal.classificacoes', compact('classificacoes', 'breadcrumb'));
    }

    public function classificacoesCriar()
    {
        $breadcrumb = [
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Módulo Fiscal', route('fiscal.dashboard')),
            new BreadCrumbItem('Classificações Fiscais', route('fiscal.classificacoes')),
            new BreadCrumbItem('Nova Classificação', '#'),
        ];

        return view('fiscal.classificacoes_form', compact('breadcrumb'));
    }

    public function classificacoesSalvar(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'ncm' => 'required|string|max:8',
            'tipo_bem' => 'required|string|max:50',
        ]);

        ClassificacaoFiscal::create($validated);

        return redirect()->route('fiscal.classificacoes')->with('success', 'Classificação fiscal (NCM) criada com sucesso!');
    }
}
