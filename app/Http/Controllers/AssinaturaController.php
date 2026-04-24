<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Services\EmpresaPlanoService;
use App\Services\StripePixService;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssinaturaController extends Controller
{
    public function __construct(
        private EmpresaPlanoService $planoService,
        private StripePixService $stripePixService,
    ) {}

    // ── Gestão global (superadmin / gerenciar-planos) ───────────────────────

    public function index(Request $request): View
    {
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

        return view('assinatura.index', compact('assinaturas', 'planos'));
    }

    public function show(EmpresaPlano $assinatura): View
    {
        $assinatura->load(['empresa', 'plano.itens', 'pagamentos.registradoPor', 'criadoPor']);
        $planos = Plano::ativos()->get();

        return view('assinatura.show', compact('assinatura', 'planos'));
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

    /** Exibe o plano atual, histórico de pagamentos e lista de planos disponíveis. */
    public function meuPlano(): View
    {
        $empresa    = Auth::user()->empresa;
        abort_unless($empresa, 404);

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

        return view('assinatura.contratar', compact('plano', 'periodicidade', 'empresa'));
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

        $plano      = Plano::findOrFail($data['plano_id']);
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

        $this->planoService->contratar(
            empresa:          $empresa,
            plano:            $plano,
            periodicidade:    session('downgrade_periodicidade', 'mensal'),
            metodoPagamento:  session('downgrade_metodo', 'boleto'),
            diaVencimento:    session('downgrade_dia_vencimento'),
            criadoPorUserId:  Auth::id(),
            veiculosDesativar:$data['veiculos_desativar'],
        );

        session()->forget(['downgrade_plano_id', 'downgrade_periodicidade', 'downgrade_metodo', 'downgrade_dia_vencimento', 'downgrade_origem']);

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Plano alterado e veículos ajustados com sucesso.');
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
            'metodo'               => 'required|in:boleto,pix,cartao',
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
    public function renovar(Request $request): RedirectResponse
    {
        $empresa    = Auth::user()->empresa;
        $assinatura = $empresa->planoAtivo()->firstOrFail();

        $data = $request->validate([
            'periodicidade'        => 'required|in:mensal,anual',
            'metodo_cobranca'      => 'required|in:boleto,pix,cartao,manual',
            'renovacao_automatica' => 'nullable|boolean',
            'dia_vencimento'       => 'nullable|integer|min:1|max:28',
        ]);

        $this->planoService->contratar(
            empresa:             $empresa,
            plano:               $assinatura->plano,
            periodicidade:       $data['periodicidade'],
            metodoPagamento:     $data['metodo_cobranca'],
            diaVencimento:       $data['dia_vencimento'] ?? null,
            criadoPorUserId:     Auth::id(),
            renovacaoAutomatica: !empty($data['renovacao_automatica']),
        );

        return redirect()->route('assinatura.meu-plano')
            ->with('success', 'Renovação solicitada. Aguarde a confirmação do pagamento.');
    }
}
