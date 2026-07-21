<?php

namespace App\Actions\Reserva\Pagamento\Exibir\Admin;

use App\DTO\BreadCrumbItem;
use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use App\Services\BreadcrumbService;
use App\Services\ClienteStripeService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use Illuminate\Support\Facades\Auth;

class ExibirPagamentoAdminMotoristaAppAction
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
        private ClienteStripeService $clienteStripeService,
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
    ) {}

    public function handle(ReservaMotoristaApp $reservaMotoristaApp): mixed
    {
        $reservaMotoristaApp->loadMissing(['codigoReserva', 'empresa', 'user', 'planoMotoristaApp', 'estacaoRetirada', 'pagamentos']);

        $this->garantirPrimeiroPagamentoMotoristaApp($reservaMotoristaApp);
        $reservaMotoristaApp->load('pagamentos');

        $pagamento = $reservaMotoristaApp->pagamentos
            ->filter(fn ($item) => strtolower((string) ($item->status ?? '')) === 'pendente')
            ->sortByDesc('id')
            ->first();

        if (! $pagamento) {
            $codigoReserva = $reservaMotoristaApp->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaMotoristaApp->id, 6, '0', STR_PAD_LEFT));

            return redirect()
                ->route('reserva.show', $codigoReserva)
                ->with('success', 'Não há pagamento pendente para esta reserva.');
        }

        $plano = $reservaMotoristaApp->planoMotoristaApp;
        $periodos = max(1, (int) ($pagamento->periodos ?? 1));
        $valorUnitarioContrato = (float) ($reservaMotoristaApp->valor_periodo_contrato ?? 0);
        $valorUnitarioReserva = (float) ($reservaMotoristaApp->valor_plano ?? 0);
        $valorUnitarioPlano = (float) ($plano?->valor_periodo ?? 0);
        $valorUnitarioPagamento = (float) ($pagamento->valor_unitario ?? 0);
        $valorUnitario = $valorUnitarioContrato > 0
            ? $valorUnitarioContrato
            : ($valorUnitarioReserva > 0
                ? $valorUnitarioReserva
                : ($valorUnitarioPlano > 0 ? $valorUnitarioPlano : $valorUnitarioPagamento));
        $valorOpcionaisReserva = (float) ($reservaMotoristaApp->valor_opcionais ?? 0);
        $valorBaseCalculado = round($valorUnitario * $periodos, 2);
        $valorTotalCicloOriginal = round($valorBaseCalculado + $valorOpcionaisReserva, 2);

        $totalPagoAcumulado = (float) $reservaMotoristaApp->pagamentos()
            ->where('status', 'pago')
            ->sum('valor_total');

        $valorCobranca = max(0, round($valorTotalCicloOriginal - $totalPagoAcumulado, 2));

        if ($valorCobranca <= 0.009) {
            $codigoReserva = $reservaMotoristaApp->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaMotoristaApp->id, 6, '0', STR_PAD_LEFT));

            return redirect()
                ->route('reserva.show', $codigoReserva)
                ->with('success', 'Esta reserva já se encontra com o pagamento totalmente quitado.');
        }

        $inicio = $pagamento->data_inicio_prevista ? \Carbon\Carbon::parse($pagamento->data_inicio_prevista)->format('d/m/Y H:i') : '-';
        $fim = $pagamento->data_fim_prevista ? \Carbon\Carbon::parse($pagamento->data_fim_prevista)->format('d/m/Y H:i') : '-';
        $dias = ($pagamento->data_inicio_prevista && $pagamento->data_fim_prevista)
            ? max(1, \Carbon\Carbon::parse($pagamento->data_inicio_prevista)->diffInDays(\Carbon\Carbon::parse($pagamento->data_fim_prevista)))
            : (int) ($reservaMotoristaApp->quantidade_dias ?? 0);
        $statusAtual = strtolower((string) ($pagamento->status ?? 'pendente'));

        $empresa = $pagamento->empresa ?? $reservaMotoristaApp->empresa ?? Auth::user()?->empresa;
        $gatewayPadrao = $empresa ? $this->gatewayEmpresaConfigService->gateway($empresa) : 'manual';
        $gatewayNome = strtoupper($gatewayPadrao);
        $moedaPadrao = $empresa ? $this->gatewayEmpresaConfigService->moeda($empresa) : 'BRL';

        $clientePagamento = $reservaMotoristaApp->user ?? $pagamento->user ?? Auth::user();
        $metodosPagamento = [];
        $stripePublicKey = null;
        if ($gatewayPadrao === 'stripe' && $empresa) {
            $stripePublicKey = (string) ($empresa->stripe_public_key ?? config('services.stripe.key'));
            $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
            if ($stripeSecret !== '' && ! empty($clientePagamento?->stripe_id)) {
                try {
                    $metodosPagamento = $this->clienteStripeService->getPaymentMethods($clientePagamento->stripe_id, $stripeSecret);
                } catch (\Throwable $e) {
                    $metodosPagamento = [];
                }
            }
        }

        $pagamentosAnteriores = $reservaMotoristaApp->pagamentos()
            ->where('status', 'pago')
            ->orderByDesc('id')
            ->get();

        $codigoReserva = $reservaMotoristaApp->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaMotoristaApp->id, 6, '0', STR_PAD_LEFT));

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva Nº '.$codigoReserva, route('reserva.show', $codigoReserva)),
            new BreadCrumbItem('Efetuar Pagamento'),
        ]);

        $titulo = 'Efetuar Pagamento da Reserva Motorista APP';

        return view('reserva.efetuar-pagamento.efetuar-pagamento-motorista-app', compact(
            'pagamento', 'reservaMotoristaApp', 'breadcrumb', 'titulo',
            'gatewayPadrao', 'gatewayNome', 'moedaPadrao',
            'metodosPagamento', 'stripePublicKey', 'pagamentosAnteriores',
            'codigoReserva', 'plano', 'clientePagamento',
            'periodos', 'valorUnitario', 'valorOpcionaisReserva',
            'valorCobranca', 'valorTotalCicloOriginal', 'totalPagoAcumulado',
            'inicio', 'fim', 'dias', 'statusAtual',
        ));
    }

    private function garantirPrimeiroPagamentoMotoristaApp(ReservaMotoristaApp $reserva): void
    {
        if ($reserva->pagamentos()->exists()) {
            return;
        }

        $plano = $reserva->planoMotoristaApp;
        $valorUnitario = (float) ($reserva->valor_periodo_contrato ?: ($reserva->valor_plano ?: ($plano?->valor_periodo ?? 0)));
        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio) : now();
        $dataFim = $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim) : $dataInicio->copy()->addWeeks(1);
        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);

        ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id' => $reserva->empresa_id,
            'user_id' => (int) $reserva->user_id,
            'periodos' => 1,
            'periodo_unidade' => 'Semana',
            'valor_unitario' => $valorUnitario,
            'valor_total' => round($valorUnitario + $valorOpcionais, 2),
            'data_inicio_prevista' => $dataInicio,
            'data_fim_prevista' => $dataFim,
            'status' => 'pendente',
            'observacoes' => 'Primeiro ciclo criado automaticamente na exibição do pagamento.',
        ]);
    }
}
