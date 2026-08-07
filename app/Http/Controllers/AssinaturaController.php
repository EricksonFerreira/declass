<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Services\BreadcrumbService;
use App\Services\EmpresaAlertaService;
use App\Services\EmpresaPlanoService;
use App\Services\StripePixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class AssinaturaController extends Controller
{
    public function __construct(
        private EmpresaPlanoService $planoService,
        private StripePixService $stripePixService,
        private EmpresaAlertaService $alertaService,
        private BreadcrumbService $breadcrumbService,
    ) {}

    // ── Gestão global (superadmin / gerenciar-planos) ───────────────────────

    public function index(Request $request): View
    {
        $titulo = 'Assinaturas';
        $subtitulo = 'Gestão de planos contratados por cada locadora';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Assinaturas', null),
        ]);

        $query = EmpresaPlano::with(['plano', 'empresa'])
            ->orderByRaw("FIELD(status, 'inadimplente', 'aguardando_pagamento', 'ativo', 'trial', 'cancelado')")
            ->orderBy('data_vencimento');

        $empresaFiltro = trim((string) $request->input('empresa', ''));
        $statusFiltro = trim((string) $request->input('status', ''));
        $planoFiltro = trim((string) $request->input('plano_id', ''));

        if ($statusFiltro !== '') {
            $query->where('status', $statusFiltro);
        }
        if ($planoFiltro !== '') {
            $query->where('plano_id', $planoFiltro);
        }

        $assinaturas = $query->paginate(20)->withQueryString();
        $planos = Plano::orderBy('nome')->get();

        return view('assinatura.index', compact('assinaturas', 'planos', 'breadcrumb', 'titulo', 'subtitulo'));
    }

    public function monitoramentoPagamentos(Request $request): View
    {
        $titulo = 'Monitoramento de Pagamentos';
        $subtitulo = 'Acompanhamento das assinaturas pendentes, vencidas e proximas do vencimento';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Monitoramento', null),
        ]);

        $status = trim((string) $request->input('status', 'pendente'));
        $prazo = (int) $request->input('prazo', 7);
        if ($prazo <= 0) {
            $prazo = 7;
        }

        $baseQuery = EmpresaPlanoPagamento::query()
            ->with(['empresa', 'empresaPlano.plano'])
            ->whereIn('status', ['pendente', 'vencido'])
            ->whereHas('empresaPlano');

        $totais = [
            'pendentes' => (clone $baseQuery)->count(),
            'empresas' => (clone $baseQuery)->distinct('empresa_id')->count('empresa_id'),
            'vencidos' => (clone $baseQuery)->where('data_vencimento', '<', now()->startOfDay())->count(),
            'proximos' => (clone $baseQuery)->whereBetween('data_vencimento', [now()->startOfDay(), now()->copy()->addDays($prazo)->endOfDay()])->count(),
            'a_vencer_mes' => (clone $baseQuery)->whereBetween('data_vencimento', [now()->startOfDay(), now()->copy()->addDays(30)->endOfDay()])->count(),
        ];

        $filtrosAtivos = [
            'empresa' => trim((string) $request->input('empresa', '')),
            'plano_id' => trim((string) $request->input('plano_id', '')),
            'status' => trim((string) $request->input('status', 'pendente')),
            'prazo' => $prazo,
        ];

        $query = EmpresaPlanoPagamento::query()
            ->with(['empresa', 'empresaPlano.plano'])
            ->whereHas('empresaPlano')
            ->whereIn('status', ['pendente', 'vencido'])
            ->when($request->filled('empresa'), function ($q) use ($request) {
                $empresa = trim((string) $request->input('empresa'));
                $q->whereHas('empresa', fn ($empresaQuery) => $empresaQuery->where('nome', 'like', "%{$empresa}%"));
            })
            ->when($request->filled('plano_id'), fn ($q) => $q->whereHas('empresaPlano', fn ($planoQuery) => $planoQuery->where('plano_id', $request->input('plano_id'))))
            ->when($status === 'pendente', fn ($q) => $q->where('data_vencimento', '>=', now()->startOfDay()))
            ->when($status === 'vencido', fn ($q) => $q->where('data_vencimento', '<', now()->startOfDay()))
            ->when($status === 'proximo', fn ($q) => $q->whereBetween('data_vencimento', [now()->startOfDay(), now()->copy()->addDays($prazo)->endOfDay()]))
            ->orderByRaw('CASE WHEN data_vencimento < ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderBy('data_vencimento')
            ->orderBy('empresa_id');

        $pagamentos = $query->paginate(20)->withQueryString();

        $listaVencidos = (clone $baseQuery)
            ->where('data_vencimento', '<', now()->startOfDay())
            ->orderBy('data_vencimento')
            ->limit(12)
            ->get();

        $listaProximos = (clone $baseQuery)
            ->whereBetween('data_vencimento', [now()->startOfDay(), now()->copy()->addDays($prazo)->endOfDay()])
            ->orderBy('data_vencimento')
            ->limit(12)
            ->get();

        $listaAcompanhamento = (clone $baseQuery)
            ->where('data_vencimento', '>', now()->copy()->addDays($prazo)->endOfDay())
            ->orderBy('data_vencimento')
            ->limit(12)
            ->get();

        $proximosDias = [3, 7, 10, 15, 30];
        $planos = Plano::orderBy('nome')->get();

        return view('assinatura.monitoramento-pagamentos', compact(
            'titulo',
            'subtitulo',
            'breadcrumb',
            'pagamentos',
            'totais',
            'planos',
            'prazo',
            'filtrosAtivos',
            'listaVencidos',
            'listaProximos',
            'listaAcompanhamento',
            'proximosDias'
        ));
    }

    public function financas(Request $request): View
    {
        $titulo = 'Finanças das Assinaturas';
        $subtitulo = 'Receita realizada, previsão do mês e leitura por filtros para decisão rápida';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Finanças', null),
        ]);

        $inicioMes = now()->startOfMonth();
        $fimMes = now()->endOfMonth();
        $inicioProximoMes = now()->copy()->addMonthNoOverflow()->startOfMonth();
        $fimProximoMes = now()->copy()->addMonthNoOverflow()->endOfMonth();

        $filtros = [
            'empresa' => trim((string) $request->input('empresa', '')),
            'plano_id' => trim((string) $request->input('plano_id', '')),
            'status' => trim((string) $request->input('status', '')),
            'metodo' => trim((string) $request->input('metodo', '')),
            'periodicidade' => trim((string) $request->input('periodicidade', '')),
            'mes' => trim((string) $request->input('mes', now()->format('Y-m'))),
            'meses' => max(1, min(24, (int) $request->input('meses', 6))),
        ];

        $baseQuery = EmpresaPlanoPagamento::query()
            ->with(['empresaPlano.plano', 'empresa'])
            ->when($filtros['empresa'] !== '', fn ($q) => $q->whereHas('empresa', fn ($empresaQuery) => $empresaQuery->where('nome', 'like', '%'.$filtros['empresa'].'%')))
            ->when($filtros['plano_id'] !== '', fn ($q) => $q->whereHas('empresaPlano', fn ($planoQuery) => $planoQuery->where('plano_id', $filtros['plano_id'])))
            ->when($filtros['status'] !== '', fn ($q) => $q->where('status', $filtros['status']))
            ->when($filtros['metodo'] !== '', fn ($q) => $q->where('metodo', $filtros['metodo']))
            ->when($filtros['periodicidade'] !== '', fn ($q) => $q->where('periodicidade', $filtros['periodicidade']));

        $pagamentosMes = (clone $baseQuery)
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->get();

        $pagamentosProximoMes = (clone $baseQuery)
            ->whereBetween('data_vencimento', [$inicioProximoMes, $fimProximoMes])
            ->get();

        $pagamentosPagos = (clone $baseQuery)
            ->where('status', 'pago')
            ->whereBetween('pago_em', [$inicioMes->copy()->startOfDay(), $fimMes->copy()->endOfDay()])
            ->get();

        $receitaRealizadaMes = (float) $pagamentosPagos->sum('valor');
        $receitaPrevistaMes = (float) (clone $baseQuery)
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->whereIn('status', ['pago', 'pendente', 'vencido', 'aguardando_pagamento'])
            ->sum('valor');

        $receitaPrevistaProximoMes = (float) $pagamentosProximoMes
            ->whereIn('status', ['pendente', 'vencido', 'aguardando_pagamento'])
            ->sum('valor');

        $receitaEmAtraso = (float) (clone $baseQuery)
            ->where('status', '!=', 'pago')
            ->where('data_vencimento', '<', now()->startOfDay())
            ->sum('valor');

        $futuroRecebivel = (float) (clone $baseQuery)
            ->whereIn('status', ['pendente', 'aguardando_pagamento'])
            ->where('data_vencimento', '>=', now()->startOfDay())
            ->sum('valor');

        $dadosMensais = EmpresaPlanoPagamento::query()
            ->where('status', 'pago')
            ->whereBetween('pago_em', [now()->copy()->subMonths($filtros['meses'] - 1)->startOfMonth(), $fimMes])
            ->selectRaw("DATE_FORMAT(pago_em, '%Y-%m') as mes")
            ->selectRaw('SUM(valor) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $labelsMensais = [];
        $valoresMensais = [];
        for ($i = $filtros['meses'] - 1; $i >= 0; $i--) {
            $mes = now()->copy()->subMonths($i)->format('Y-m');
            $labelsMensais[] = now()->copy()->subMonths($i)->locale('pt_BR')->translatedFormat('M/Y');
            $valoresMensais[] = (float) ($dadosMensais[$mes] ?? 0);
        }

        $pagamentosRecentes = (clone $baseQuery)
            ->whereBetween('data_vencimento', [now()->copy()->subMonths(1)->startOfMonth(), $fimMes])
            ->orderByDesc('data_vencimento')
            ->orderByDesc('id')
            ->paginate(18)
            ->withQueryString();

        $empresas = Empresa::orderBy('nome')->get();
        $planos = Plano::orderBy('nome')->get();
        $metodos = [
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'cartao' => 'Cartão',
            'dinheiro' => 'Dinheiro',
            'transferencia' => 'Transferência',
            'manual' => 'Manual',
        ];
        $periodicidades = [
            'mensal' => 'Mensal',
            'anual' => 'Anual',
        ];
        $statusList = [
            'pago' => 'Pago',
            'pendente' => 'Pendente',
            'vencido' => 'Vencido',
            'cancelado' => 'Cancelado',
            'aguardando_pagamento' => 'Aguardando pagamento',
        ];

        return view('assinatura.financas', compact(
            'titulo',
            'subtitulo',
            'breadcrumb',
            'filtros',
            'empresas',
            'planos',
            'metodos',
            'periodicidades',
            'statusList',
            'pagamentosMes',
            'pagamentosProximoMes',
            'pagamentosRecentes',
            'receitaRealizadaMes',
            'receitaPrevistaMes',
            'receitaPrevistaProximoMes',
            'receitaEmAtraso',
            'futuroRecebivel',
            'labelsMensais',
            'valoresMensais'
        ));
    }

    public function show(EmpresaPlano $assinatura): View
    {
        $assinatura->load(['plano.itens', 'pagamentos.registradoPor', 'pagamentos.empresa', 'criadoPor']);
        $planos = Plano::ativos()->get();
        $empresa = $this->resolverEmpresaDaAssinatura($assinatura);

        $qtdEstacoes = $empresa ? \App\Models\Estacao::where('empresa_id', $empresa->id)->count() : 0;
        $qtdVeiculos = $empresa ? \App\Models\Veiculo::where('empresa_id', $empresa->id)->count() : 0;
        $armazenamentoUsadoBytes = $empresa?->storage_used_bytes ?? 0;
        $armazenamentoLimiteBytes = $empresa?->storage_limit_bytes ?? 0;
        $armazenamentoUsadoTexto = $empresa?->storage_used ?? '0 B';
        $armazenamentoLimiteTexto = $empresa?->storage_formatted ?? '0 B';
        $armazenamentoPercentual = $empresa?->storage_percent ?? 0;

        return view('assinatura.show', compact(
            'assinatura',
            'planos',
            'qtdEstacoes',
            'qtdVeiculos',
            'armazenamentoUsadoBytes',
            'armazenamentoLimiteBytes',
            'armazenamentoUsadoTexto',
            'armazenamentoLimiteTexto',
            'armazenamentoPercentual'
        ));
    }

    public function regras(): View
    {
        // Backward-compatible: a tela de regras virou o modulo de ajuda.
        return $this->ajuda();
    }

    public function ajuda(): View
    {
        $titulo = 'Central de Ajuda (FAQ)';
        $subtitulo = 'Respostas rápidas e orientações sobre planos, faturamento, frota, reservas e o Módulo Fiscal';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Ajuda', null),
        ]);

        $ajudaPath = base_path('docs/ajuda-planos.md');
        $ajudaMd = File::exists($ajudaPath) ? File::get($ajudaPath) : "# Geral\n\n## Arquivo não encontrado\nEdite o arquivo docs/ajuda-planos.md";

        $faqs = $this->parseFaqMarkdown($ajudaMd);

        return view('assinatura.ajuda', compact('faqs', 'breadcrumb', 'titulo', 'subtitulo'));
    }

    private function parseFaqMarkdown(string $md): array
    {
        $md = str_replace(["\r\n", "\r"], "\n", $md);

        $parts = preg_split('/^#\\s+/m', $md);
        array_shift($parts); // remove header before first '#'

        $groupedFaqs = [];
        $faqIndex = 1;

        foreach ($parts as $part) {
            $part = ltrim($part);
            if ($part === '') {
                continue;
            }

            $lines = explode("\n", $part, 2);
            $categoryName = trim($lines[0] ?? 'Geral');
            $categoryContent = trim($lines[1] ?? '');

            // Now parse questions (##) in this category
            $qParts = preg_split('/^##\\s+/m', $categoryContent);
            array_shift($qParts); // remove any text before first ##

            $faqs = [];
            foreach ($qParts as $qChunk) {
                $qChunk = ltrim($qChunk);
                if ($qChunk === '') {
                    continue;
                }

                $qLines = explode("\n", $qChunk, 2);
                $q = trim($qLines[0] ?? '');
                $a = trim($qLines[1] ?? '');
                if ($q === '') {
                    continue;
                }

                $faqs[] = [
                    'id' => 'faq-' . $faqIndex++,
                    'q' => $q,
                    'aHtml' => Str::markdown($a === '' ? '_' : $a),
                ];
            }

            if (!empty($faqs)) {
                $groupedFaqs[$categoryName] = $faqs;
            }
        }

        return $groupedFaqs;
    }

    /** Exibe formulario para admin atribuir plano. */
    public function criarAtribuir(Request $request, ?Empresa $empresa = null)
    {
        $empresas = Empresa::with(['planoAtivo.plano'])
            ->orderBy('nome')
            ->get();

        $empresas->transform(function (Empresa $empresa) {
            $empresa->setAttribute('qtd_veiculos_ativos', \App\Models\Veiculo::where('empresa_id', $empresa->id)->where('ativo', true)->where('vendido', false)->count());
            $empresa->setAttribute('qtd_estacoes', \App\Models\Estacao::where('empresa_id', $empresa->id)->count());
            $empresa->setAttribute('armazenamento_usado_mb', (float) round(($empresa->storage_used_bytes ?? 0) / 1048576, 2));

            return $empresa;
        });

        $planos = Plano::ativos()->get();
        $stripePublicKey = config('services.stripe.key');
        $empresaSelecionadaId = $empresa?->id ?? $request->integer('empresa_id') ?: null;
        $empresaSelecionadaNome = $empresa?->nome;
        if (! $empresaSelecionadaNome && $empresaSelecionadaId) {
            $empresaSelecionadaNome = optional($empresas->firstWhere('id', (int) $empresaSelecionadaId))->nome;
        }
        $empresaSelecionada = $empresaSelecionadaId ? $empresas->firstWhere('id', (int) $empresaSelecionadaId) : null;
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Assinaturas', route('assinatura.index')),
            new \App\DTO\BreadCrumbItem('Atribuir Plano'),
        ]);

        return view('assinatura.atribuir', compact('empresas', 'planos', 'stripePublicKey', 'breadcrumb', 'empresaSelecionadaId', 'empresaSelecionadaNome', 'empresaSelecionada'))
            ->with('titulo', 'Atribuir Plano');
    }

    /** Admin atribui plano diretamente a uma empresa. */
    public function atribuir(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'plano_id' => 'required|exists:planos,id',
            'periodicidade' => 'required|in:mensal,anual',
            'data_inicio' => 'required|date',
            'data_vencimento' => 'nullable|date|after_or_equal:data_inicio',
            'dia_vencimento' => 'nullable|integer|min:1|max:28',
            'valor' => 'nullable|numeric|min:0',
            'armazenamento_mb' => 'nullable|numeric|min:0',
            'veiculos_adicionais' => 'nullable|integer|min:0',
            'filiais_adicionais' => 'nullable|integer|min:0',
            'armazenamento_adicional_qtd' => 'nullable|integer|min:0',
            'observacao' => 'nullable|string|max:500',
        ]);

        $empresa = Empresa::findOrFail($data['empresa_id']);
        $plano = Plano::findOrFail($data['plano_id']);

        $assinaturaAtual = $empresa->planoAtivo()->first();
        if ($assinaturaAtual) {
            $planoAtual = $assinaturaAtual->plano?->nome ?? 'plano atual';

            return redirect()->back()
                ->withInput()
                ->with('error', "A empresa {$empresa->nome} já está vinculada ao {$planoAtual}. Cancele ou encerre a assinatura atual antes de atribuir outro plano.");
        }

        $empresaPlano = $this->planoService->contratar(
            empresa: $empresa,
            plano: $plano,
            periodicidade: $data['periodicidade'],
            metodoPagamento: 'manual',
            diaVencimento: $data['dia_vencimento'] ?? null,
            criadoPorUserId: Auth::id(),
        );

        $metadados = (array) ($empresaPlano->metadados ?? []);
        $metadados['ajustes_atribuir'] = [
            'valor' => isset($data['valor']) ? (float) $data['valor'] : null,
            'armazenamento_mb' => isset($data['armazenamento_mb']) ? (float) $data['armazenamento_mb'] : null,
            'veiculos_adicionais' => isset($data['veiculos_adicionais']) ? (int) $data['veiculos_adicionais'] : null,
            'filiais_adicionais' => isset($data['filiais_adicionais']) ? (int) $data['filiais_adicionais'] : null,
            'armazenamento_adicional_qtd' => isset($data['armazenamento_adicional_qtd']) ? (int) $data['armazenamento_adicional_qtd'] : null,
            'observacao' => $data['observacao'] ?? null,
        ];
        $empresaPlano->update(['metadados' => $metadados]);

        return redirect()->route('assinatura.index')
            ->with('success', 'Assinatura lançada com sucesso. O pagamento pode ser registrado depois.');
    }

    public function alterarStatus(Request $request, EmpresaPlano $assinatura): RedirectResponse
    {
        $request->validate(['status' => 'required|in:ativo,inadimplente,cancelado,trial,aguardando_pagamento']);
        $assinatura->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status da assinatura atualizado.');
    }

    public function registrarPagamento(Request $request, EmpresaPlano $assinatura): RedirectResponse
    {
        $baseRules = [
            'valor' => 'nullable|numeric|min:0.01',
            'metodo' => 'nullable|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'status' => 'nullable|in:pendente,pago,vencido,cancelado',
            'data_vencimento' => 'nullable|date',
            'pago_em' => 'nullable|date|required_if:status,pago',
            'observacao' => 'nullable|string|max:500',
            'parcelas' => 'nullable|array|min:1',
            'parcelas.*.valor' => 'required|numeric|min:0.01',
            'parcelas.*.metodo' => 'required|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'parcelas.*.status' => 'required|in:pendente,pago,vencido,cancelado',
            'parcelas.*.data_vencimento' => 'required|date',
            'parcelas.*.pago_em' => 'nullable|date|required_if:parcelas.*.status,pago',
            'parcelas.*.observacao' => 'nullable|string|max:500',
        ];

        $data = $request->validate($baseRules);

        $linhas = collect($data['parcelas'] ?? [array_intersect_key($data, array_flip([
            'valor', 'metodo', 'status', 'data_vencimento', 'pago_em', 'observacao',
        ]))])
            ->filter(fn ($item) => filled(data_get($item, 'valor')))
            ->values();

        $empresa = $this->resolverEmpresaDaAssinatura($assinatura);

        foreach ($linhas as $linha) {
            EmpresaPlanoPagamento::create([
                'empresa_plano_id' => $assinatura->id,
                'empresa_id' => $empresa?->id,
                'valor' => $linha['valor'],
                'periodicidade' => $assinatura->periodicidade,
                'metodo' => $linha['metodo'] ?? 'boleto',
                'status' => $linha['status'] ?? 'pendente',
                'data_vencimento' => $linha['data_vencimento'],
                'pago_em' => $linha['pago_em'] ?? null,
                'metadados' => filled($linha['observacao'] ?? null) ? ['observacao' => $linha['observacao']] : null,
                'registrado_por_user_id' => Auth::id(),
            ]);
        }

        if (collect($linhas)->contains(fn ($linha) => ($linha['status'] ?? 'pendente') === 'pago') && ! $assinatura->estaAtivo()) {
            $assinatura->update(['status' => 'ativo']);
        }

        return redirect()->back()->with('success', count($linhas) > 1 ? 'Pagamentos registrados.' : 'Pagamento registrado.');
    }

    public function update(Request $request, EmpresaPlano $assinatura): RedirectResponse
    {
        $data = $request->validate([
            'plano_id' => 'required|exists:planos,id',
            'periodicidade' => 'required|in:mensal,anual',
            'status' => 'required|in:ativo,trial,inadimplente,cancelado,aguardando_pagamento',
            'metodo_cobranca' => 'required|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'data_inicio' => 'required|date',
            'data_vencimento' => 'required|date|after_or_equal:data_inicio',
            'dia_vencimento' => 'nullable|integer|min:1|max:28',
            'valor_cobrado' => 'required|numeric|min:0',
        ]);

        $assinatura->update($data);

        return redirect()->back()->with('success', 'Assinatura atualizada com sucesso.');
    }

    public function destroy(EmpresaPlano $assinatura): RedirectResponse
    {
        $temPagamentos = $assinatura->pagamentos()->exists();
        if ($temPagamentos) {
            return redirect()->back()->with('error', 'Não foi possível remover esta assinatura porque ela já possui pagamentos vinculados.');
        }

        $assinatura->delete();

        return redirect()->route('assinatura.index')->with('success', 'Assinatura removida com sucesso.');
    }

    public function atualizarPagamento(Request $request, EmpresaPlanoPagamento $pagamento): RedirectResponse
    {
        $data = $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'metodo' => 'required|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'status' => 'required|in:pendente,pago,vencido,cancelado',
            'data_vencimento' => 'required|date',
            'pago_em' => 'nullable|date|required_if:status,pago',
            'gateway_payment_id' => 'nullable|string|max:200',
            'observacao' => 'nullable|string|max:500',
        ]);

        $pagamento->update([
            'valor' => $data['valor'],
            'metodo' => $data['metodo'],
            'status' => $data['status'],
            'data_vencimento' => $data['data_vencimento'],
            'pago_em' => $data['pago_em'] ?? null,
            'gateway_payment_id' => $data['gateway_payment_id'] ?? null,
            'metadados' => $data['observacao'] ? array_merge((array) ($pagamento->metadados ?? []), ['observacao' => $data['observacao']]) : $pagamento->metadados,
        ]);

        return redirect()->back()->with('success', 'Pagamento atualizado.');
    }

    public function excluirPagamento(EmpresaPlanoPagamento $pagamento): RedirectResponse
    {
        if ($pagamento->status === 'pago') {
            return redirect()->back()->with('error', 'Não é possível remover um pagamento já pago.');
        }

        $pagamento->delete();

        return redirect()->back()->with('success', 'Pagamento removido.');
    }

    public function confirmarPagamento(Request $request, EmpresaPlanoPagamento $pagamento): RedirectResponse
    {
        $data = $request->validate([
            'pago_em' => 'required|date',
            'metodo' => 'required|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'gateway_payment_id' => 'nullable|string|max:200',
            'observacao' => 'nullable|string|max:500',
        ]);

        $this->planoService->ativarAposConfirmacao($pagamento);

        $pagamento->update([
            'metodo' => $data['metodo'],
            'pago_em' => $data['pago_em'],
            'gateway_payment_id' => $data['gateway_payment_id'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Pagamento confirmado e assinatura ativada.');
    }

    // ── Tela da própria empresa ─────────────────────────────────────────────

    /** Tela dedicada de pagamento no fluxo de onboarding. */
    public function onboardingPagamento(): View|RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);

        $pagamento = $empresa->pagamentosPlano()
            ->where('status', 'pendente')
            ->orderByDesc('created_at')
            ->first();

        if (! $pagamento) {
            return redirect()->route('onboarding.orientacoes');
        }

        $pagamento->load('empresaPlano.plano');

        return view('onboarding.pagamento-plano', compact('empresa', 'pagamento'));
    }

    /** Exibe o plano atual, histórico de pagamentos e lista de planos disponíveis. */
    public function meuPlano(): View
    {
        $empresa = Auth::user()->empresa;
        if (! $empresa) {
            return redirect()->route('home')->with('error', 'Seu usuario nao esta vinculado a uma empresa.');
        }

        $assinatura = $empresa->planoAtivo()->with('plano.itens')->first();
        $planos = Plano::ativos()->with('itens')->get();
        $historico = $empresa->pagamentosPlano()
            ->with('empresaPlano.plano')
            ->orderByDesc('data_vencimento')
            ->paginate(10);

        return view('assinatura.meu-plano', compact('empresa', 'assinatura', 'planos', 'historico'));
    }

    /**
     * Exibe a tela de contratação/troca de plano.
     * Se houver downgrade, redireciona para seleção de veículos.
     */
    public function prepararContratacao(Request $request): View|RedirectResponse
    {
        $planoId = $request->query('plano_id');
        $periodicidade = $request->query('periodicidade', 'mensal');

        if (! $planoId) {
            return redirect()->route('assinatura.meu-plano');
        }

        $plano = Plano::with('itens')->findOrFail($planoId);
        $empresa = Auth::user()->empresa;

        $totalVeiculos = $empresa ? \App\Models\Veiculo::where('empresa_id', $empresa->id)->where('ativo', true)->where('vendido', false)->count() : 0;
        $assinaturaAtual = $empresa?->planoAtivo()->with('plano')->first();
        $erroDowngrade = null;

        $cartoesSalvos = [];
        if ($empresa && $empresa->gateway_customer_id) {
            try {
                $cartoesSalvos = app(\App\Services\ClienteStripeService::class)->listarMetodosPagamento($empresa->gateway_customer_id);
            } catch (\Throwable $e) {
                $cartoesSalvos = [];
            }
        }

        return view('assinatura.contratar', compact('plano', 'periodicidade', 'empresa', 'totalVeiculos', 'assinaturaAtual', 'erroDowngrade', 'cartoesSalvos'));
    }

    private function resolverEmpresaDaAssinatura(EmpresaPlano $assinatura): ?Empresa
    {
        if ($assinatura->empresa) {
            return $assinatura->empresa;
        }

        $empresaId = $assinatura->empresa_id;
        return $empresaId ? Empresa::find($empresaId) : null;
    }

    /** Processa contratação interna: cria assinatura + pagamento pendente. */
    public function contratar(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'plano_id' => 'required|exists:planos,id',
            'periodicidade' => 'required|in:mensal,anual',
            'metodo_pagamento' => 'required|in:pix,cartao',
            'renovacao_automatica' => 'nullable|boolean',
            'dia_vencimento' => 'nullable|integer|min:1|max:28',
        ], [
            'plano_id.required' => 'Selecione um plano.',
            'metodo_pagamento.required' => 'Escolha um método de pagamento.',
        ]);

        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);

        $plano = Plano::findOrFail($data['plano_id']);

        // Bloqueia downgrade no mesmo mês pago
        $assinaturaAtual = $empresa->planoAtivo()->with('plano')->first();

        // Helper para retornar JSON redirect quando AJAX
        $jsonRedirect = function ($url, $message = null) use ($request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'redirect' => $url, 'message' => $message]);
            }
            if ($message) {
                return redirect($url)->with('success', $message);
            }

            return redirect($url);
        };

        // Se já existe assinatura ativa, trata como troca de plano.
        if ($assinaturaAtual && $assinaturaAtual->estaAtivo()) {
            if ((int) $assinaturaAtual->plano_id === (int) $plano->id && (string) $assinaturaAtual->periodicidade === (string) $data['periodicidade']) {
                return $jsonRedirect(route('assinatura.meu-plano'), 'Voce ja esta neste plano e periodicidade.');
            }

            $valorAtualTotal = (float) ($assinaturaAtual->valor_cobrado ?? 0);
            $valorNovoTotal = (float) $this->planoService->valorTotalPlano($plano, (string) $data['periodicidade']);

            if ((string) $assinaturaAtual->periodicidade === 'anual' && (string) $data['periodicidade'] === 'mensal') {
                $this->agendarMudancaPlano(
                    assinatura: $assinaturaAtual,
                    proximoPlano: $plano,
                    periodicidade: $data['periodicidade'],
                    tipo: 'downgrade',
                );

                return $jsonRedirect(route('assinatura.meu-plano'), 'Mudanca para mensal agendada para a proxima renovacao anual.');
            }

            if ($valorNovoTotal < $valorAtualTotal) {
                $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);
                if ($excedentes->isNotEmpty()) {
                    session([
                        'downgrade_plano_id' => $data['plano_id'],
                        'downgrade_periodicidade' => $data['periodicidade'],
                        'downgrade_metodo' => $data['metodo_pagamento'],
                        'downgrade_dia_vencimento' => $data['dia_vencimento'],
                        'downgrade_origem' => 'agendar',
                    ]);

                    return $jsonRedirect(route('assinatura.downgrade'));
                }

                $this->agendarMudancaPlano(
                    assinatura: $assinaturaAtual,
                    proximoPlano: $plano,
                    periodicidade: $data['periodicidade'],
                    tipo: 'downgrade',
                );

                return $jsonRedirect(route('assinatura.meu-plano'), 'Downgrade agendado para a proxima renovacao.');
            }

            $jaTemUpgradePendente = EmpresaPlanoPagamento::where('empresa_plano_id', $assinaturaAtual->id)
                ->where('status', 'pendente')
                ->where('metadados->acao', 'upgrade')
                ->exists();

            if ($jaTemUpgradePendente) {
                return $jsonRedirect(route('assinatura.meu-plano'), 'Ja existe um upgrade pendente de pagamento.');
            }

            try {
                $this->planoService->criarCobrancaUpgrade(
                    assinatura: $assinaturaAtual,
                    novoPlano: $plano,
                    novaPeriodicidade: $data['periodicidade'],
                    metodoPagamento: $data['metodo_pagamento'],
                    criadoPorUserId: Auth::id(),
                );
            } catch (\Throwable $e) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }

                return back()->withErrors(['metodo_pagamento' => $e->getMessage()])->withInput();
            }

            return $jsonRedirect(route('assinatura.meu-plano'), 'Upgrade solicitado! Apos pagamento, os recursos serao liberados.');
        }

        $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);

        if ($excedentes->isNotEmpty()) {
            session([
                'downgrade_plano_id' => $data['plano_id'],
                'downgrade_periodicidade' => $data['periodicidade'],
                'downgrade_metodo' => $data['metodo_pagamento'],
                'downgrade_dia_vencimento' => $data['dia_vencimento'],
                'downgrade_origem' => 'assinatura',
            ]);

            return $jsonRedirect(route('assinatura.downgrade'));
        }

        $assinatura = $this->planoService->contratar(
            empresa: $empresa,
            plano: $plano,
            periodicidade: $data['periodicidade'],
            metodoPagamento: $data['metodo_pagamento'],
            diaVencimento: $data['dia_vencimento'] ?? null,
            criadoPorUserId: Auth::id(),
            renovacaoAutomatica: ! empty($data['renovacao_automatica']),
        );

        $pagamento = $assinatura?->pagamentos()->where('status', 'pendente')->first();

        if ($request->expectsJson() || $request->ajax()) {
            if (! $pagamento) {
                return response()->json(['success' => false, 'message' => 'Erro ao criar pagamento.'], 422);
            }

            return response()->json([
                'success' => true,
                'pagamento_id' => $pagamento->id,
                'message' => 'Assinatura criada. Realize o pagamento.',
            ]);
        }

        return redirect()->route('assinatura.meu-plano', $pagamento ? ['pagamento' => $pagamento->id] : [])
            ->with('success', 'Plano contratado! Escolha como pagar abaixo.');
    }

    /** Exibe tela de conclusao apos confirmacao de pagamento. */
    public function concluido(Request $request): View|RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);

        $pagamentoId = $request->query('pagamento');
        $pagamento = $pagamentoId
            ? EmpresaPlanoPagamento::where('empresa_id', $empresa->id)->with('empresaPlano.plano.itens')->find($pagamentoId)
            : null;

        if (! $pagamento) {
            $pagamento = $empresa->pagamentosPlano()
                ->with('empresaPlano.plano.itens')
                ->where('status', 'pago')
                ->orderByDesc('pago_em')
                ->first();
        }

        if (! $pagamento) {
            return redirect()->route('assinatura.meu-plano')
                ->with('error', 'Nenhum pagamento encontrado.');
        }

        $assinatura = $pagamento->empresaPlano;
        $plano = $assinatura?->plano;

        return view('assinatura.concluido', compact('pagamento', 'assinatura', 'plano', 'empresa'));
    }

    /** Exibe seleção de veículos para downgrade (via painel, não onboarding). */
    public function downgrade(): View|RedirectResponse
    {
        $planoId = session('downgrade_plano_id');

        if (! $planoId) {
            return redirect()->route('assinatura.meu-plano');
        }

        $empresa = Auth::user()->empresa;
        $plano = Plano::findOrFail($planoId);
        $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);
        $todos = $this->planoService->veiculosAtivos($empresa);

        return view('assinatura.downgrade', compact('plano', 'excedentes', 'todos'));
    }

    public function confirmarDowngrade(Request $request): RedirectResponse
    {
        $planoId = session('downgrade_plano_id');

        if (! $planoId) {
            return redirect()->route('assinatura.meu-plano');
        }

        $plano = Plano::findOrFail($planoId);
        $empresa = Auth::user()->empresa;

        $data = $request->validate([
            'veiculos_desativar' => 'required|array|min:1',
            'veiculos_desativar.*' => 'integer|exists:veiculos,id',
        ], [
            'veiculos_desativar.required' => 'Selecione ao menos um veículo para desativar.',
        ]);

        $ainda = $this->planoService->veiculosAtivos($empresa)
            ->whereNotIn('id', $data['veiculos_desativar'])
            ->count();

        if ($plano->limite_veiculos !== null && $ainda > $plano->limite_veiculos) {
            $faltam = $ainda - $plano->limite_veiculos;

            return back()->withErrors([
                'veiculos_desativar' => "Desative mais {$faltam} veículo(s) para se enquadrar no limite do plano {$plano->nome}.",
            ]);
        }

        $origem = (string) session('downgrade_origem', 'assinatura');

        // Se o downgrade veio de uma troca com assinatura ativa, agenda para a renovação.
        if ($origem === 'agendar') {
            $assinaturaAtual = $empresa->planoAtivo()->firstOrFail();

            $this->agendarMudancaPlano(
                assinatura: $assinaturaAtual,
                proximoPlano: $plano,
                periodicidade: (string) session('downgrade_periodicidade', 'mensal'),
                tipo: 'downgrade',
                metadadosExtras: [
                    'veiculos_desativar' => array_values($data['veiculos_desativar']),
                ],
            );
        } else {
            // Fluxo original: contratação/alteração imediata (cria nova assinatura + cobrança pendente).
            $this->planoService->contratar(
                empresa: $empresa,
                plano: $plano,
                periodicidade: session('downgrade_periodicidade', 'mensal'),
                metodoPagamento: session('downgrade_metodo', 'boleto'),
                diaVencimento: session('downgrade_dia_vencimento'),
                criadoPorUserId: Auth::id(),
                veiculosDesativar: $data['veiculos_desativar'],
            );
        }

        session()->forget(['downgrade_plano_id', 'downgrade_periodicidade', 'downgrade_metodo', 'downgrade_dia_vencimento', 'downgrade_origem']);

        return redirect()->route('assinatura.meu-plano')
            ->with('success', $origem === 'agendar'
                ? 'Downgrade agendado para a proxima renovacao. Ajuste de frota registrado para aplicar no proximo ciclo.'
                : 'Plano alterado e veiculos ajustados com sucesso.');
    }

    /** Gera (ou reutiliza) o PaymentIntent PIX via Stripe e retorna QR Code. */
    public function gerarPixStripe(Request $request, EmpresaPlanoPagamento $pagamento): JsonResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $pagamento->empresa_id === $empresa->id && $pagamento->status === 'pendente', 403);

        try {
            // Reutiliza intent existente se ainda não expirado
            if ($pagamento->stripe_payment_intent_id && $pagamento->pix_expira_em?->isFuture()) {
                return response()->json([
                    'qr_image' => $pagamento->pix_qr_code_image,
                    'qr_text' => $pagamento->pix_qr_code_text,
                    'expira_em' => $pagamento->pix_expira_em->toIso8601String(),
                    'valor' => number_format($pagamento->valor, 2, ',', '.'),
                ]);
            }

            $pagamento = $this->stripePixService->criarIntentPix($pagamento);

            return response()->json([
                'qr_image' => $pagamento->pix_qr_code_image,
                'qr_text' => $pagamento->pix_qr_code_text,
                'expira_em' => $pagamento->pix_expira_em?->toIso8601String(),
                'valor' => number_format($pagamento->valor, 2, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Verifica status atual do pagamento (polling do frontend). */
    public function statusPagamento(EmpresaPlanoPagamento $pagamento): JsonResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $pagamento->empresa_id === $empresa->id, 403);

        $pagamento->refresh();

        return response()->json([
            'status' => $pagamento->status,
            'pago' => $pagamento->status === 'pago',
            'pago_em' => $pagamento->pago_em?->format('d/m/Y H:i'),
        ]);
    }

    /** Empresa altera o método de pagamento de uma cobrança pendente. */
    public function alterarMetodoPagamento(Request $request, EmpresaPlanoPagamento $pagamento): RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $pagamento->empresa_id === $empresa->id && $pagamento->status === 'pendente', 403);

        $data = $request->validate([
            'metodo' => 'required|in:pix,cartao',
            'renovacao_automatica' => 'nullable|boolean',
        ]);

        $renovacaoAutomatica = ! empty($data['renovacao_automatica']);

        $pagamento->update([
            'metodo' => $data['metodo'],
            'pix_chave' => $data['metodo'] === 'pix' ? env('PIX_CHAVE') : null,
            'instrucoes_pagamento' => $this->instrucoesPagamentoTexto($data['metodo'], $pagamento->valor, $renovacaoAutomatica),
        ]);

        // Atualiza metodo_cobranca e renovacao_automatica na assinatura
        $pagamento->empresaPlano?->update([
            'metodo_cobranca' => $data['metodo'],
            'renovacao_automatica' => $data['metodo'] === 'cartao' && $renovacaoAutomatica,
        ]);

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Método de pagamento atualizado.');
    }

    private function agendarMudancaPlano(
        EmpresaPlano $assinatura,
        Plano $proximoPlano,
        string $periodicidade,
        string $tipo,
        array $metadadosExtras = [],
    ): void {
        $metadados = is_array($assinatura->metadados) ? $assinatura->metadados : [];

        if (! empty($metadadosExtras)) {
            $metadados['mudanca'] = array_merge($metadados['mudanca'] ?? [], $metadadosExtras);
        }

        $assinatura->update([
            'proximo_plano_id' => $proximoPlano->id,
            'proxima_periodicidade' => $periodicidade,
            'mudanca_tipo' => $tipo,
            'mudanca_efetiva_em' => $assinatura->data_vencimento,
            'mudanca_solicitada_em' => now(),
            'metadados' => $metadados,
        ]);
    }

    private function instrucoesPagamentoTexto(string $metodo, float $valor, bool $renovacaoAutomatica = false): string
    {
        $v = 'R$ '.number_format($valor, 2, ',', '.');

        return match ($metodo) {
            'pix' => "Pague {$v} via PIX usando a chave abaixo. O pagamento é instantâneo.",
            'boleto' => "Boleto de {$v}. Você receberá o boleto por e-mail em até 24 horas.",
            'cartao' => $renovacaoAutomatica
                ? "Cartão de crédito {$v} com renovação automática. Nossa equipe entrará em contato para salvar o cartão."
                : "Cartão de crédito {$v}. Nossa equipe entrará em contato para processar o pagamento.",
            default => "Pagamento de {$v}. Entre em contato com nosso suporte para finalizar.",
        };
    }

    /** Cria PaymentIntent para pagamento com cartão de crédito via Stripe Elements. */
    public function criarIntentCartao(Request $request, EmpresaPlanoPagamento $pagamento): JsonResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $pagamento->empresa_id === $empresa->id && $pagamento->status === 'pendente', 403);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Peek: só retorna a chave pública para inicializar Stripe.js sem criar intent
            if ($request->boolean('peek', false)) {
                return response()->json(['stripe_key' => config('services.stripe.key')]);
            }

            $salvar = $request->boolean('salvar_cartao', false);
            $paymentMethod = $request->input('payment_method');
            $valorCentavos = (int) round((float) $pagamento->valor * 100);

            // Cria ou reutiliza Customer
            $customerId = $empresa->gateway_customer_id ?? null;
            if (! $customerId) {
                $emailEmpresa = filter_var($empresa->email ?? '', FILTER_VALIDATE_EMAIL) ? $empresa->email : null;
                $emailUsuario = $empresa->users()->whereNotNull('email')->get()
                    ->first(fn ($u) => filter_var($u->email, FILTER_VALIDATE_EMAIL))?->email;
                $emailFinal = $emailEmpresa ?? $emailUsuario ?? null;

                $customerData = ['name' => $empresa->nome, 'metadata' => ['empresa_id' => $empresa->id]];
                if ($emailFinal) {
                    $customerData['email'] = $emailFinal;
                }

                $customer = Customer::create($customerData);
                $customerId = $customer->id;
                $empresa->update(['gateway_customer_id' => $customerId]);
            }

            $intentParams = [
                'amount' => $valorCentavos,
                'currency' => 'brl',
                'payment_method_types' => ['card'],
                'customer' => $customerId,
                'description' => 'Plano '.($pagamento->empresaPlano?->plano?->nome ?? 'DeCars'),
                'metadata' => [
                    'pagamento_id' => $pagamento->id,
                    'empresa_id' => $pagamento->empresa_id,
                ],
            ];

            if ($paymentMethod) {
                $intentParams['payment_method'] = $paymentMethod;
            }

            if ($salvar) {
                $intentParams['setup_future_usage'] = 'off_session';
            }

            $intent = PaymentIntent::create($intentParams);

            $pagamento->update(['stripe_payment_intent_id' => $intent->id]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'intent_id' => $intent->id,
                'stripe_key' => config('services.stripe.key'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Registra pagamento de cartão após confirmação pelo Stripe Elements no frontend. */
    public function confirmarCartao(Request $request, EmpresaPlanoPagamento $pagamento): JsonResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $pagamento->empresa_id === $empresa->id, 403);

        $request->validate(['payment_intent_id' => 'required|string']);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $intent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($intent->status !== 'succeeded') {
                return response()->json(['error' => 'Pagamento não confirmado. Status: '.$intent->status], 422);
            }

            $this->planoService->ativarAposConfirmacao($pagamento);

            $pagamento->update([
                'status' => 'pago',
                'pago_em' => now(),
                'gateway_payment_id' => $intent->id,
                'metodo' => 'cartao',
            ]);

            $redirect = route('assinatura.concluido', ['pagamento' => $pagamento->id]);

            return response()->json(['ok' => true, 'redirect' => $redirect]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Renova o plano atual (mesmo plano, nova vigência). */
    public function cancelarPagamento(Request $request, EmpresaPlanoPagamento $pagamento): JsonResponse
    {
        if ($pagamento->empresa_id !== auth()->user()->empresa_id) {
            return response()->json(['success' => false, 'error' => 'Pagamento não encontrado.'], 404);
        }
        if ($pagamento->status !== 'pendente') {
            return response()->json(['success' => false, 'error' => 'Apenas pagamentos pendentes podem ser cancelados.'], 400);
        }
        $pagamento->update(['status' => 'cancelado', 'observacao' => 'Cancelado pelo usuário']);

        return response()->json(['success' => true]);
    }

    public function cancelar(): RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $assinatura->update([
            'mudanca_tipo' => 'cancelamento',
            'mudanca_efetiva_em' => $assinatura->data_vencimento,
            'mudanca_solicitada_em' => now(),
            'renovacao_automatica' => false,
            // Cancelamento prevalece sobre qualquer downgrade agendado.
            'proximo_plano_id' => null,
            'proxima_periodicidade' => null,
        ]);

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Cancelamento agendado. Voce mantera o acesso ate o fim do periodo vigente.');
    }

    public function removerMudancaAgendada(): RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $assinatura->update([
            'proximo_plano_id' => null,
            'proxima_periodicidade' => null,
            'mudanca_tipo' => null,
            'mudanca_efetiva_em' => null,
            'mudanca_solicitada_em' => null,
        ]);

        $metadados = is_array($assinatura->metadados) ? $assinatura->metadados : [];
        if (isset($metadados['mudanca'])) {
            unset($metadados['mudanca']);
            $assinatura->update(['metadados' => $metadados]);
        }

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Mudanca agendada removida.');
    }

    public function renovar(Request $request): RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $data = $request->validate([
            'periodicidade' => 'required|in:mensal,anual',
            'metodo_cobranca' => 'required|in:pix,cartao,manual',
            'renovacao_automatica' => 'nullable|boolean',
            'dia_vencimento' => 'nullable|integer|min:1|max:28',
        ]);

        if ($assinatura->mudanca_tipo === 'cancelamento') {
            return redirect()->route('assinatura.meu-plano')
                ->with('error', 'Sua assinatura esta com cancelamento agendado. Remova o cancelamento para renovar.');
        }

        $planoRenovar = $assinatura->plano;
        $periodicidadeRenovar = (string) $data['periodicidade'];
        $veiculosDesativar = [];

        // Se houver downgrade agendado, a renovacao deve respeitar o plano/periodicidade do proximo ciclo.
        if ($assinatura->mudanca_tipo === 'downgrade' && $assinatura->proximo_plano_id) {
            $planoRenovar = Plano::findOrFail($assinatura->proximo_plano_id);
            $periodicidadeRenovar = (string) ($assinatura->proxima_periodicidade ?? $periodicidadeRenovar);

            $mudanca = is_array($assinatura->metadados['mudanca'] ?? null) ? $assinatura->metadados['mudanca'] : [];
            $veiculosDesativar = is_array($mudanca['veiculos_desativar'] ?? null) ? $mudanca['veiculos_desativar'] : [];
        }

        $this->planoService->contratar(
            empresa: $empresa,
            plano: $planoRenovar,
            periodicidade: $periodicidadeRenovar,
            metodoPagamento: $data['metodo_cobranca'],
            diaVencimento: $data['dia_vencimento'] ?? null,
            criadoPorUserId: Auth::id(),
            veiculosDesativar: $veiculosDesativar,
            renovacaoAutomatica: ! empty($data['renovacao_automatica']),
        );

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Renovação solicitada. Aguarde a confirmação do pagamento.');
    }
}
