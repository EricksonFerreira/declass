<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Services\EmpresaAlertaService;
use App\Services\EmpresaPlanoService;
use App\Services\BreadcrumbService;
use App\Services\StripePixService;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
        $subtitulo = 'Gestao de planos contratados por cada locadora';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Assinaturas', null),
        ]);

        $query = EmpresaPlano::with(['empresa', 'plano'])
            ->orderByRaw("FIELD(status, 'inadimplente', 'aguardando_pagamento', 'ativo', 'trial', 'cancelado')")
            ->orderBy('data_vencimento');

        if ($request->filled('empresa')) {
            $query->whereHas('empresa', fn ($q) => $q->where('nome', 'like', "%{$request->empresa}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('plano_id')) {
            $query->where('plano_id', $request->plano_id);
        }

        $assinaturas = $query->paginate(20)->withQueryString();
        $planos      = Plano::ativos()->get();

        return view('assinatura.index', compact('assinaturas', 'planos', 'breadcrumb', 'titulo', 'subtitulo'));
    }

    public function show(EmpresaPlano $assinatura): View
    {
        $assinatura->load(['empresa', 'plano.itens', 'pagamentos.registradoPor', 'criadoPor']);
        $planos = Plano::ativos()->get();

        return view('assinatura.show', compact('assinatura', 'planos'));
    }

    public function regras(): View
    {
        // Backward-compatible: a tela de regras virou o modulo de ajuda.
        return $this->ajuda();
    }

    public function ajuda(): View
    {
        $titulo = 'Ajuda (Planos)';
        $subtitulo = 'FAQ interno para upgrades, downgrades, cancelamentos e cobranca';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Planos', route('planos.hub')),
            new \App\DTO\BreadCrumbItem('Ajuda', null),
        ]);

        $ajudaPath = base_path('docs/ajuda-planos.md');
        $ajudaMd = File::exists($ajudaPath) ? File::get($ajudaPath) : "# Ajuda - Planos\n\nArquivo nao encontrado: docs/ajuda-planos.md";

        $faqs = $this->parseFaqMarkdown($ajudaMd);

        return view('assinatura.ajuda', compact('faqs', 'breadcrumb', 'titulo', 'subtitulo'));
    }

    private function parseFaqMarkdown(string $md): array
    {
        $md = str_replace(["\r\n", "\r"], "\n", $md);

        $parts = preg_split('/^##\\s+/m', $md);
        if (!$parts || count($parts) <= 1) {
            return [[
                'id'    => 'faq-1',
                'q'     => 'Como usar este modulo?',
                'aHtml' => Str::markdown("Edite o arquivo `docs/ajuda-planos.md` e crie perguntas usando titulos `##`."),
            ]];
        }

        array_shift($parts); // ignora cabecalho antes do primeiro ##
        $faqs = [];
        foreach ($parts as $i => $chunk) {
            $chunk = ltrim($chunk);
            if ($chunk === '') continue;

            $lines = explode("\n", $chunk, 2);
            $q = trim($lines[0] ?? '');
            $a = trim($lines[1] ?? '');
            if ($q === '') continue;

            $faqs[] = [
                'id'    => 'faq-' . ($i + 1),
                'q'     => $q,
                'aHtml' => Str::markdown($a === '' ? '_' : $a),
            ];
        }

        return $faqs;
    }

    /** Admin atribui plano diretamente a uma empresa. */
    public function atribuir(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_id'      => 'required|exists:empresas,id',
            'plano_id'        => 'required|exists:planos,id',
            'periodicidade'   => 'required|in:mensal,anual',
            'metodo_cobranca' => 'required|in:boleto,pix,automatico,manual',
            'data_inicio'     => 'required|date',
            'data_vencimento' => 'required|date|after_or_equal:data_inicio',
            'dia_vencimento'  => 'nullable|integer|min:1|max:28',
            'observacao'      => 'nullable|string|max:500',
        ]);

        $empresa = Empresa::findOrFail($data['empresa_id']);
        $plano   = Plano::findOrFail($data['plano_id']);

        $this->planoService->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   $data['periodicidade'],
            metodoPagamento: $data['metodo_cobranca'],
            diaVencimento:   $data['dia_vencimento'] ?? null,
            criadoPorUserId: Auth::id(),
        );

        return redirect()->route('assinatura.index')
            ->with('success', 'Plano atribuído. Registre o pagamento para ativar a assinatura.');
    }

    public function alterarStatus(Request $request, EmpresaPlano $assinatura): RedirectResponse
    {
        $request->validate(['status' => 'required|in:ativo,inadimplente,cancelado,trial,aguardando_pagamento']);
        $assinatura->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status da assinatura atualizado.');
    }

    public function registrarPagamento(Request $request, EmpresaPlano $assinatura): RedirectResponse
    {
        $data = $request->validate([
            'valor'          => 'required|numeric|min:0.01',
            'metodo'         => 'required|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'status'         => 'required|in:pendente,pago,vencido,cancelado',
            'data_vencimento'=> 'required|date',
            'pago_em'        => 'nullable|date',
            'observacao'     => 'nullable|string|max:500',
        ]);

        EmpresaPlanoPagamento::create([
            'empresa_plano_id'      => $assinatura->id,
            'empresa_id'            => $assinatura->empresa_id,
            'valor'                 => $data['valor'],
            'periodicidade'         => $assinatura->periodicidade,
            'metodo'                => $data['metodo'],
            'status'                => $data['status'],
            'data_vencimento'       => $data['data_vencimento'],
            'pago_em'               => $data['pago_em'] ?? null,
            'metadados'             => $data['observacao'] ? ['observacao' => $data['observacao']] : null,
            'registrado_por_user_id'=> Auth::id(),
        ]);

        if ($data['status'] === 'pago' && !$assinatura->estaAtivo()) {
            $assinatura->update(['status' => 'ativo']);
        }

        return redirect()->back()->with('success', 'Pagamento registrado.');
    }

    public function confirmarPagamento(Request $request, EmpresaPlanoPagamento $pagamento): RedirectResponse
    {
        $data = $request->validate([
            'pago_em'            => 'required|date',
            'metodo'             => 'required|in:boleto,cartao,pix,dinheiro,transferencia,manual',
            'gateway_payment_id' => 'nullable|string|max:200',
            'observacao'         => 'nullable|string|max:500',
        ]);

        $this->planoService->ativarAposConfirmacao($pagamento);

        $pagamento->update([
            'metodo'             => $data['metodo'],
            'pago_em'            => $data['pago_em'],
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

        if (!$pagamento) {
            return redirect()->route('onboarding.orientacoes');
        }

        $pagamento->load('empresaPlano.plano');

        return view('onboarding.pagamento-plano', compact('empresa', 'pagamento'));
    }

    /** Exibe o plano atual, histórico de pagamentos e lista de planos disponíveis. */
    public function meuPlano(): View
    {
        $empresa    = Auth::user()->empresa;
        if (!$empresa) {
            return redirect()->route('home')->with('error', 'Seu usuario nao esta vinculado a uma empresa.');
        }

        $assinatura = $empresa->planoAtivo()->with('plano.itens')->first();
        $planos     = Plano::ativos()->with('itens')->get();
        $historico  = $empresa->pagamentosPlano()
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
        $planoId       = $request->query('plano_id');
        $periodicidade = $request->query('periodicidade', 'mensal');

        if (!$planoId) {
            return redirect()->route('assinatura.meu-plano');
        }

        $plano   = Plano::with('itens')->findOrFail($planoId);
        $empresa = Auth::user()->empresa;

        $totalVeiculos    = $empresa ? \App\Models\Veiculo::where('empresa_id', $empresa->id)->where('ativo', true)->where('vendido', false)->count() : 0;
        $assinaturaAtual  = $empresa?->planoAtivo()->with('plano')->first();
        $erroDowngrade    = null;

        return view('assinatura.contratar', compact('plano', 'periodicidade', 'empresa', 'totalVeiculos', 'assinaturaAtual', 'erroDowngrade'));
    }

    /** Processa contratação interna: cria assinatura + pagamento pendente. */
    public function contratar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plano_id'             => 'required|exists:planos,id',
            'periodicidade'        => 'required|in:mensal,anual',
            'metodo_pagamento'     => 'required|in:pix,cartao',
            'renovacao_automatica' => 'nullable|boolean',
            'dia_vencimento'       => 'nullable|integer|min:1|max:28',
        ], [
            'plano_id.required'        => 'Selecione um plano.',
            'metodo_pagamento.required'=> 'Escolha um método de pagamento.',
        ]);

        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);

        $plano = Plano::findOrFail($data['plano_id']);

        // Bloqueia downgrade no mesmo mês pago
        $assinaturaAtual = $empresa->planoAtivo()->with('plano')->first();

        // Se jÃ¡ existe assinatura ativa, trata como troca de plano (padrÃ£o SaaS).
        if ($assinaturaAtual && $assinaturaAtual->estaAtivo()) {
            if ((int) $assinaturaAtual->plano_id === (int) $plano->id && (string) $assinaturaAtual->periodicidade === (string) $data['periodicidade']) {
                return back()->withErrors(['plano_id' => 'Voce ja esta neste plano e periodicidade.'])->withInput();
            }

            // Define upgrade vs downgrade pelo valor TOTAL do periodo (mensal vs anual).
            // Isso evita tratar "mensal -> anual" como downgrade quando o valor_anual e menor (desconto),
            // pois no anual o total cobrado e (valor_anual * 12).
            $valorAtualTotal = (float) ($assinaturaAtual->valor_cobrado ?? 0);
            $valorNovoTotal  = (float) $this->planoService->valorTotalPlano($plano, (string) $data['periodicidade']);

            // Regra padrao SaaS: anual -> mensal reduz compromisso, entao agenda para o fim do periodo anual.
            if ((string) $assinaturaAtual->periodicidade === 'anual' && (string) $data['periodicidade'] === 'mensal') {
                $this->agendarMudancaPlano(
                    assinatura:    $assinaturaAtual,
                    proximoPlano:  $plano,
                    periodicidade: $data['periodicidade'],
                    tipo:          'downgrade',
                );

                return redirect()->route('assinatura.meu-plano')
                    ->with('success', 'Mudanca para mensal agendada para a proxima renovacao anual. Voce mantera o plano atual ate o fim do periodo.');
            }


            // Downgrade: agenda para a prÃ³xima renovaÃ§Ã£o (fim do perÃ­odo vigente).
            if ($valorNovoTotal < $valorAtualTotal) {
                $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);

                if ($excedentes->isNotEmpty()) {
                    session([
                        'downgrade_plano_id'       => $data['plano_id'],
                        'downgrade_periodicidade'  => $data['periodicidade'],
                        'downgrade_metodo'         => $data['metodo_pagamento'],
                        'downgrade_dia_vencimento' => $data['dia_vencimento'],
                        'downgrade_origem'         => 'agendar',
                    ]);

                    return redirect()->route('assinatura.downgrade');
                }

                $this->agendarMudancaPlano(
                    assinatura:    $assinaturaAtual,
                    proximoPlano:  $plano,
                    periodicidade: $data['periodicidade'],
                    tipo:          'downgrade',
                );

                return redirect()->route('assinatura.meu-plano')
                    ->with('success', 'Downgrade agendado para a proxima renovacao. Voce mantera o plano atual ate o fim do periodo.');
            }

            // Upgrade: cria cobranÃ§a proporcional e aplica apÃ³s pagamento confirmado.
            $jaTemUpgradePendente = EmpresaPlanoPagamento::where('empresa_plano_id', $assinaturaAtual->id)
                ->where('status', 'pendente')
                ->where('metadados->acao', 'upgrade')
                ->exists();

            if ($jaTemUpgradePendente) {
                return back()->withErrors(['metodo_pagamento' => 'Ja existe um upgrade pendente de pagamento. Finalize ou cancele antes de solicitar outro.'])->withInput();
            }

            try {
                $this->planoService->criarCobrancaUpgrade(
                    assinatura:        $assinaturaAtual,
                    novoPlano:         $plano,
                    novaPeriodicidade: $data['periodicidade'],
                    metodoPagamento:   $data['metodo_pagamento'],
                    criadoPorUserId:   Auth::id(),
                );
            } catch (\Throwable $e) {
                return back()->withErrors(['metodo_pagamento' => $e->getMessage()])->withInput();
            }

            return redirect()->route('assinatura.meu-plano')
                ->with('success', 'Upgrade solicitado! Assim que o pagamento for confirmado, os recursos do novo plano serao liberados.');
        }

        $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);

        if ($excedentes->isNotEmpty()) {
            session([
                'downgrade_plano_id'       => $data['plano_id'],
                'downgrade_periodicidade'  => $data['periodicidade'],
                'downgrade_metodo'         => $data['metodo_pagamento'],
                'downgrade_dia_vencimento' => $data['dia_vencimento'],
                'downgrade_origem'         => 'assinatura',
            ]);

            return redirect()->route('assinatura.downgrade');
        }

        $this->planoService->contratar(
            empresa:             $empresa,
            plano:               $plano,
            periodicidade:       $data['periodicidade'],
            metodoPagamento:     $data['metodo_pagamento'],
            diaVencimento:       $data['dia_vencimento'] ?? null,
            criadoPorUserId:     Auth::id(),
            renovacaoAutomatica: !empty($data['renovacao_automatica']),
        );

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Plano contratado! Aguarde a confirmação do pagamento para ativar todos os recursos.');
    }

    /** Exibe seleção de veículos para downgrade (via painel, não onboarding). */
    public function downgrade(): View|RedirectResponse
    {
        $planoId = session('downgrade_plano_id');

        if (!$planoId) {
            return redirect()->route('assinatura.meu-plano');
        }

        $empresa    = Auth::user()->empresa;
        $plano      = Plano::findOrFail($planoId);
        $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);
        $todos      = $this->planoService->veiculosAtivos($empresa);

        return view('assinatura.downgrade', compact('plano', 'excedentes', 'todos'));
    }

    public function confirmarDowngrade(Request $request): RedirectResponse
    {
        $planoId = session('downgrade_plano_id');

        if (!$planoId) {
            return redirect()->route('assinatura.meu-plano');
        }

        $plano   = Plano::findOrFail($planoId);
        $empresa = Auth::user()->empresa;

        $data = $request->validate([
            'veiculos_desativar'   => 'required|array|min:1',
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
                assinatura:      $assinaturaAtual,
                proximoPlano:    $plano,
                periodicidade:   (string) session('downgrade_periodicidade', 'mensal'),
                tipo:            'downgrade',
                metadadosExtras: [
                    'veiculos_desativar' => array_values($data['veiculos_desativar']),
                ],
            );
        } else {
            // Fluxo original: contratação/alteração imediata (cria nova assinatura + cobrança pendente).
            $this->planoService->contratar(
                empresa:          $empresa,
                plano:            $plano,
                periodicidade:    session('downgrade_periodicidade', 'mensal'),
                metodoPagamento:  session('downgrade_metodo', 'boleto'),
                diaVencimento:    session('downgrade_dia_vencimento'),
                criadoPorUserId:  Auth::id(),
                veiculosDesativar:$data['veiculos_desativar'],
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
                    'qr_image'   => $pagamento->pix_qr_code_image,
                    'qr_text'    => $pagamento->pix_qr_code_text,
                    'expira_em'  => $pagamento->pix_expira_em->toIso8601String(),
                    'valor'      => number_format($pagamento->valor, 2, ',', '.'),
                ]);
            }

            $pagamento = $this->stripePixService->criarIntentPix($pagamento);

            return response()->json([
                'qr_image'   => $pagamento->pix_qr_code_image,
                'qr_text'    => $pagamento->pix_qr_code_text,
                'expira_em'  => $pagamento->pix_expira_em?->toIso8601String(),
                'valor'      => number_format($pagamento->valor, 2, ',', '.'),
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
            'status'   => $pagamento->status,
            'pago'     => $pagamento->status === 'pago',
            'pago_em'  => $pagamento->pago_em?->format('d/m/Y H:i'),
        ]);
    }

    /** Empresa altera o método de pagamento de uma cobrança pendente. */
    public function alterarMetodoPagamento(Request $request, EmpresaPlanoPagamento $pagamento): RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $pagamento->empresa_id === $empresa->id && $pagamento->status === 'pendente', 403);

        $data = $request->validate([
            'metodo'               => 'required|in:pix,cartao',
            'renovacao_automatica' => 'nullable|boolean',
        ]);

        $renovacaoAutomatica = !empty($data['renovacao_automatica']);

        $pagamento->update([
            'metodo'               => $data['metodo'],
            'pix_chave'            => $data['metodo'] === 'pix' ? env('PIX_CHAVE') : null,
            'instrucoes_pagamento' => $this->instrucoesPagamentoTexto($data['metodo'], $pagamento->valor, $renovacaoAutomatica),
        ]);

        // Atualiza metodo_cobranca e renovacao_automatica na assinatura
        $pagamento->empresaPlano?->update([
            'metodo_cobranca'      => $data['metodo'],
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

        if (!empty($metadadosExtras)) {
            $metadados['mudanca'] = array_merge($metadados['mudanca'] ?? [], $metadadosExtras);
        }

        $assinatura->update([
            'proximo_plano_id'      => $proximoPlano->id,
            'proxima_periodicidade' => $periodicidade,
            'mudanca_tipo'          => $tipo,
            'mudanca_efetiva_em'    => $assinatura->data_vencimento,
            'mudanca_solicitada_em' => now(),
            'metadados'             => $metadados,
        ]);
    }

    private function instrucoesPagamentoTexto(string $metodo, float $valor, bool $renovacaoAutomatica = false): string
    {
        $v = 'R$ ' . number_format($valor, 2, ',', '.');
        return match ($metodo) {
            'pix'    => "Pague {$v} via PIX usando a chave abaixo. O pagamento é instantâneo.",
            'boleto' => "Boleto de {$v}. Você receberá o boleto por e-mail em até 24 horas.",
            'cartao' => $renovacaoAutomatica
                ? "Cartão de crédito {$v} com renovação automática. Nossa equipe entrará em contato para salvar o cartão."
                : "Cartão de crédito {$v}. Nossa equipe entrará em contato para processar o pagamento.",
            default  => "Pagamento de {$v}. Entre em contato com nosso suporte para finalizar.",
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
            $valorCentavos = (int) round((float) $pagamento->valor * 100);

            // Cria ou reutiliza Customer
            $customerId = $empresa->gateway_customer_id ?? null;
            if (!$customerId) {
                $emailEmpresa = filter_var($empresa->email ?? '', FILTER_VALIDATE_EMAIL) ? $empresa->email : null;
                $emailUsuario = $empresa->users()->whereNotNull('email')->get()
                    ->first(fn ($u) => filter_var($u->email, FILTER_VALIDATE_EMAIL))?->email;
                $emailFinal = $emailEmpresa ?? $emailUsuario ?? null;

                $customerData = ['name' => $empresa->nome, 'metadata' => ['empresa_id' => $empresa->id]];
                if ($emailFinal) $customerData['email'] = $emailFinal;

                $customer = Customer::create($customerData);
                $customerId = $customer->id;
                $empresa->update(['gateway_customer_id' => $customerId]);
            }

            $intentParams = [
                'amount'               => $valorCentavos,
                'currency'             => 'brl',
                'payment_method_types' => ['card'],
                'customer'             => $customerId,
                'description'          => 'Plano ' . ($pagamento->empresaPlano?->plano?->nome ?? 'DeCars'),
                'metadata'             => [
                    'pagamento_id' => $pagamento->id,
                    'empresa_id'   => $pagamento->empresa_id,
                ],
            ];

            if ($salvar) {
                $intentParams['setup_future_usage'] = 'off_session';
            }

            $intent = PaymentIntent::create($intentParams);

            $pagamento->update(['stripe_payment_intent_id' => $intent->id]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'intent_id'     => $intent->id,
                'stripe_key'    => config('services.stripe.key'),
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
                return response()->json(['error' => 'Pagamento não confirmado. Status: ' . $intent->status], 422);
            }

            $this->planoService->ativarAposConfirmacao($pagamento);

            $pagamento->update([
                'status'             => 'pago',
                'pago_em'            => now(),
                'gateway_payment_id' => $intent->id,
                'metodo'             => 'cartao',
            ]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Renova o plano atual (mesmo plano, nova vigência). */
    public function cancelar(): RedirectResponse
    {
        $empresa    = Auth::user()->empresa;
        abort_unless($empresa, 403);
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $assinatura->update([
            'mudanca_tipo'          => 'cancelamento',
            'mudanca_efetiva_em'    => $assinatura->data_vencimento,
            'mudanca_solicitada_em' => now(),
            'renovacao_automatica'  => false,
            // Cancelamento prevalece sobre qualquer downgrade agendado.
            'proximo_plano_id'      => null,
            'proxima_periodicidade' => null,
        ]);

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Cancelamento agendado. Voce mantera o acesso ate o fim do periodo vigente.');
    }

    public function removerMudancaAgendada(): RedirectResponse
    {
        $empresa    = Auth::user()->empresa;
        abort_unless($empresa, 403);
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $assinatura->update([
            'proximo_plano_id'      => null,
            'proxima_periodicidade' => null,
            'mudanca_tipo'          => null,
            'mudanca_efetiva_em'    => null,
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
        $empresa    = Auth::user()->empresa;
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $data = $request->validate([
            'periodicidade'        => 'required|in:mensal,anual',
            'metodo_cobranca'      => 'required|in:pix,cartao,manual',
            'renovacao_automatica' => 'nullable|boolean',
            'dia_vencimento'       => 'nullable|integer|min:1|max:28',
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
            empresa:             $empresa,
            plano:               $planoRenovar,
            periodicidade:       $periodicidadeRenovar,
            metodoPagamento:     $data['metodo_cobranca'],
            diaVencimento:       $data['dia_vencimento'] ?? null,
            criadoPorUserId:     Auth::id(),
            veiculosDesativar:   $veiculosDesativar,
            renovacaoAutomatica: !empty($data['renovacao_automatica']),
        );

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Renovação solicitada. Aguarde a confirmação do pagamento.');
    }
}
