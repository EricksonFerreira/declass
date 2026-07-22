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
        $reservaMotoristaApp->loadMissing([
            'codigoReserva',
            'empresa',
            'user',
            'planoMotoristaApp',
            'estacaoRetirada',
            'pagamentos',
        ]);

        $plano = $reservaMotoristaApp->planoMotoristaApp;

        /*
         * Um pagamento anterior pode ser usado apenas como referência
         * para dados históricos. Ele não é obrigatório para abrir a tela.
         */
        $ultimoPagamento = $reservaMotoristaApp->pagamentos
            ->sortByDesc('id')
            ->first();

        $periodos = max(
            1,
            (int) (
                $ultimoPagamento?->periodos
                ?? $reservaMotoristaApp->periodos
                ?? 1
            )
        );

        $valorUnitarioContrato = (float) (
            $reservaMotoristaApp->valor_periodo_contrato ?? 0
        );

        $valorUnitarioReserva = (float) (
            $reservaMotoristaApp->valor_plano ?? 0
        );

        $valorUnitarioPlano = (float) (
            $plano?->valor_periodo ?? 0
        );

        $valorUnitarioPagamento = (float) (
            $ultimoPagamento?->valor_unitario ?? 0
        );

        $valorUnitario = $valorUnitarioContrato > 0
            ? $valorUnitarioContrato
            : (
                $valorUnitarioReserva > 0
                    ? $valorUnitarioReserva
                    : (
                        $valorUnitarioPlano > 0
                            ? $valorUnitarioPlano
                            : $valorUnitarioPagamento
                    )
            );

        $valorOpcionaisReserva = (float) (
            $reservaMotoristaApp->valor_opcionais ?? 0
        );

        $valorBaseCalculado = round(
            $valorUnitario * $periodos,
            2
        );

        $valorTotalCicloOriginal = round(
            $valorBaseCalculado + $valorOpcionaisReserva,
            2
        );

        /*
         * Somente pagamentos efetivamente confirmados diminuem o saldo.
         */
        $totalPagoAcumulado = round(
            (float) $reservaMotoristaApp->pagamentos()
                ->where('status', 'pago')
                ->sum('valor_total'),
            2
        );

        $valorCobranca = max(
            0,
            round($valorTotalCicloOriginal - $totalPagoAcumulado, 2)
        );

        $codigoReserva = $reservaMotoristaApp->codigoReserva?->codigo
            ?? (
                'MAP-'.str_pad(
                    (string) $reservaMotoristaApp->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                )
            );

        /*
         * Somente bloqueia a tela quando não existe mais saldo.
         */
        if ($valorCobranca <= 0.009) {
            return redirect()
                ->route('reserva.show', $codigoReserva)
                ->with(
                    'success',
                    'Esta reserva já se encontra totalmente quitada.'
                );
        }

        /*
         * As datas pertencem à reserva/ciclo, e não necessariamente
         * ao último pagamento realizado.
         */
        $dataInicio = $reservaMotoristaApp->data_inicio
            ?? $ultimoPagamento?->data_inicio_prevista;

        $dataFim = $reservaMotoristaApp->data_fim
            ?? $ultimoPagamento?->data_fim_prevista;

        $inicio = $dataInicio
            ? \Carbon\Carbon::parse($dataInicio)->format('d/m/Y H:i')
            : '-';

        $fim = $dataFim
            ? \Carbon\Carbon::parse($dataFim)->format('d/m/Y H:i')
            : '-';

        $dias = ($dataInicio && $dataFim)
            ? max(
                1,
                \Carbon\Carbon::parse($dataInicio)
                    ->diffInDays(\Carbon\Carbon::parse($dataFim))
            )
            : (int) ($reservaMotoristaApp->quantidade_dias ?? 0);

        /*
         * O status exibido passa a representar o estado da cobrança.
         */
        $statusAtual = $valorCobranca > 0
            ? ($totalPagoAcumulado > 0 ? 'parcialmente_pago' : 'pendente')
            : 'pago';

        $empresa = $reservaMotoristaApp->empresa
            ?? $ultimoPagamento?->empresa
            ?? Auth::user()?->empresa;

        $gatewayPadrao = $empresa
            ? $this->gatewayEmpresaConfigService->gateway($empresa)
            : 'manual';

        $gatewayNome = strtoupper($gatewayPadrao);

        $moedaPadrao = $empresa
            ? $this->gatewayEmpresaConfigService->moeda($empresa)
            : 'BRL';

        $clientePagamento = $reservaMotoristaApp->user
            ?? $ultimoPagamento?->user
            ?? Auth::user();

        $metodosPagamento = [];
        $stripePublicKey = null;

        if ($gatewayPadrao === 'stripe' && $empresa) {
            $stripePublicKey = (string) (
                $empresa->stripe_public_key
                ?? config('services.stripe.key')
            );

            $stripeSecret = (string) (
                $empresa->stripe_secret
                ?? config('services.stripe.secret')
            );

            if (
                $stripeSecret !== ''
                && ! empty($clientePagamento?->stripe_id)
            ) {
                try {
                    $metodosPagamento =
                        $this->clienteStripeService->getPaymentMethods(
                            $clientePagamento->stripe_id,
                            $stripeSecret
                        );
                } catch (\Throwable $e) {
                    report($e);

                    $metodosPagamento = [];
                }
            }
        }

        $pagamentosAnteriores = $reservaMotoristaApp->pagamentos()
            ->where('status', 'pago')
            ->orderByDesc('id')
            ->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem(
                'Reserva Nº '.$codigoReserva,
                route('reserva.show', $codigoReserva)
            ),
            new BreadCrumbItem('Efetuar Pagamento'),
        ]);

        $titulo = 'Efetuar Pagamento da Reserva Motorista APP';

        /*
         * Para manter compatibilidade temporária com a view,
         * podemos enviar o último pagamento como $pagamento.
         *
         * O ideal é alterar a view para usar $ultimoPagamento.
         */
        $pagamento = $ultimoPagamento;

        return view(
            'reserva.efetuar-pagamento.efetuar-pagamento-motorista-app',
            compact(
                'pagamento',
                'ultimoPagamento',
                'reservaMotoristaApp',
                'breadcrumb',
                'titulo',
                'gatewayPadrao',
                'gatewayNome',
                'moedaPadrao',
                'metodosPagamento',
                'stripePublicKey',
                'pagamentosAnteriores',
                'codigoReserva',
                'plano',
                'clientePagamento',
                'periodos',
                'valorUnitario',
                'valorOpcionaisReserva',
                'valorCobranca',
                'valorTotalCicloOriginal',
                'totalPagoAcumulado',
                'inicio',
                'fim',
                'dias',
                'statusAtual',
            )
        );
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
