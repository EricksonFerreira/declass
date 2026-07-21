<?php

namespace App\Services;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Models\Calcao;
use App\Models\Reserva;
use App\Models\ReservaCodigo;
use App\Models\ReservaMotoristaApp;
use App\Models\User;
use App\Models\VeiculoDanos;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\StripeClient;
use Throwable;

class AvariaCobrancaService
{
    public function __construct(
        private readonly GatewayEmpresaConfigService $gatewayConfig,
        private readonly CalcaoService $calcaoService,
    ) {}

    /**
     * Cria e executa uma cobrança por avarias.
     *
     * Fluxo padrão:
     * 1. Utiliza o valor disponível no calção.
     * 2. Cobra o valor restante no Stripe.
     * 3. Caso o Stripe falhe após captura parcial, mantém o restante pendente.
     */
    public function criarECobrar(
        ReservaResolvida $reservaResolvida,
        array $danoVeiculoIds,
        float $valor,
        string $descricao,
        ?string $metodoPreferido = null,
        ?string $paymentMethodId = null,
    ): AvariaCobranca {
        $registro = $reservaResolvida->registro;

        $this->carregarRelacionamentos($reservaResolvida);

        $reservaCodigo = $registro->codigoReserva;

        if (! $reservaCodigo instanceof ReservaCodigo) {
            throw new RuntimeException(
                'A reserva não possui código público vinculado.'
            );
        }

        $cliente = $this->obterCliente($reservaResolvida);

        if (! $cliente) {
            throw new RuntimeException(
                'Cliente da reserva não encontrado.'
            );
        }

        if ($valor <= 0) {
            throw new RuntimeException(
                'Informe um valor válido para a cobrança.'
            );
        }

        $danoVeiculoIds = $this->normalizarIdsDosDanos(
            $danoVeiculoIds
        );

        $danos = $this->buscarDanosDaReserva(
            reservaCodigoId: (int) $reservaCodigo->id,
            danoVeiculoIds: $danoVeiculoIds,
        );

        if (
            count($danoVeiculoIds) > 0
            && $danos->count() !== count($danoVeiculoIds)
        ) {
            throw new RuntimeException(
                'Um ou mais danos selecionados não pertencem à reserva.'
            );
        }

        $empresa = $this->gatewayConfig->empresaPorId(
            (int) ($reservaResolvida->empresaId() ?? 0)
        ) ?? $this->gatewayConfig->empresaAtual();

        $moeda = $empresa
            ? $this->gatewayConfig->moeda($empresa)
            : 'BRL';

        $gateway = $empresa
            ? $this->gatewayConfig->gateway($empresa)
            : 'stripe';

        $calcao = $this->obterCalcao($reservaResolvida);

        $cobranca = AvariaCobranca::create([
            'empresa_id' => $reservaResolvida->empresaId(),
            'reserva_codigo_id' => $reservaCodigo->id,
            'veiculo_id' => $reservaResolvida->registro->veiculo_id ?? null,
            'cliente_id' => $cliente->id,
            'calcao_id' => $calcao?->id,

            'metodo' => AvariaCobranca::METODO_MANUAL,
            'status' => AvariaCobranca::STATUS_PROCESSANDO,
            'moeda' => $moeda,

            'valor' => $valor,
            'valor_calcao' => 0,
            'valor_stripe' => 0,
            'valor_pendente' => $valor,

            'descricao' => $descricao,
            'dano_veiculo_ids' => $danoVeiculoIds,
            'usuario_responsavel_id' => Auth::id(),
        ]);

        try {
            /*
             * Escolha explícita: cobrança manual.
             */
            if ($metodoPreferido === AvariaCobranca::METODO_MANUAL) {
                return $this->registrarComoManual(
                    cobranca: $cobranca,
                    valorPendente: $valor,
                );
            }

            /*
             * Escolha explícita: somente Stripe.
             */
            if ($metodoPreferido === AvariaCobranca::METODO_STRIPE) {
                return $this->cobrarSomenteStripe(
                    reservaResolvida: $reservaResolvida,
                    cobranca: $cobranca,
                    valor: $valor,
                    descricao: $descricao,
                    paymentMethodId: $paymentMethodId,
                );
            }

            /*
             * Escolha explícita: utilizar calção.
             *
             * Se o calção não cobrir tudo, o restante será tentado no Stripe.
             */
            if ($metodoPreferido === AvariaCobranca::METODO_CALCAO) {
                if (! $calcao || ! $calcao->podeCapturar()) {
                    throw new RuntimeException(
                        'O calção da reserva não está disponível para captura.'
                    );
                }

                return $this->cobrarComCalcaoEStripe(
                    reservaResolvida: $reservaResolvida,
                    cobranca: $cobranca,
                    valorTotal: $valor,
                    descricao: $descricao,
                    paymentMethodId: $paymentMethodId,
                );
            }

            /*
             * Fluxo automático:
             * 1. Calção;
             * 2. Stripe;
             * 3. Manual.
             */
            if ($calcao && $calcao->podeCapturar()) {
                return $this->cobrarComCalcaoEStripe(
                    reservaResolvida: $reservaResolvida,
                    cobranca: $cobranca,
                    valorTotal: $valor,
                    descricao: $descricao,
                    paymentMethodId: $paymentMethodId,
                );
            }

            if ($gateway === 'stripe') {
                return $this->cobrarSomenteStripe(
                    reservaResolvida: $reservaResolvida,
                    cobranca: $cobranca,
                    valor: $valor,
                    descricao: $descricao,
                    paymentMethodId: $paymentMethodId,
                );
            }

            return $this->registrarComoManual(
                cobranca: $cobranca,
                valorPendente: $valor,
            );
        } catch (Throwable $exception) {
            Log::warning(
                'Falha ao realizar cobrança de avaria.',
                [
                    'reserva_codigo' => $reservaResolvida->codigo,
                    'tipo_reserva' => $reservaResolvida->tipo->value,
                    'registro_id' => $reservaResolvida->id(),
                    'cobranca_id' => $cobranca->id,
                    'erro' => $exception->getMessage(),
                ]
            );

            $cobranca->update([
                'status' => AvariaCobranca::STATUS_FALHOU,
                'valor_pendente' => $valor,
                'gateway_response' => [
                    'error' => $exception->getMessage(),
                ],
            ]);

            return $cobranca->fresh();
        }
    }

    /**
     * Captura até o limite disponível no calção e cobra o restante no Stripe.
     */
    private function cobrarComCalcaoEStripe(
        ReservaResolvida $reservaResolvida,
        AvariaCobranca $cobranca,
        float $valorTotal,
        string $descricao,
        ?string $paymentMethodId = null,
    ): AvariaCobranca {
        $calcao = $this->obterCalcao($reservaResolvida);

        if (! $calcao) {
            throw new RuntimeException(
                'A reserva não possui calção vinculado.'
            );
        }

        if (! $calcao->podeCapturar()) {
            throw new RuntimeException(
                'O calção da reserva não está disponível para captura.'
            );
        }

        $valorDisponivelCalcao = $this->obterValorDisponivelCalcao(
            $calcao
        );

        if ($valorDisponivelCalcao <= 0) {
            return $this->cobrarSomenteStripe(
                reservaResolvida: $reservaResolvida,
                cobranca: $cobranca,
                valor: $valorTotal,
                descricao: $descricao,
                paymentMethodId: $paymentMethodId,
            );
        }

        $valorCalcao = min(
            $valorTotal,
            $valorDisponivelCalcao
        );

        $valorRestante = max(
            0,
            $valorTotal - $valorCalcao
        );

        /*
         * Primeiro captura o calção.
         */
        $calcaoAtualizado = $this->calcaoService->capturar(
            $calcao,
            $valorCalcao,
            $descricao
        );

        $respostasGateway = [
            'calcao' => [
                'valor' => $valorCalcao,
                'payment_intent_id' => $calcaoAtualizado->payment_intent_id,
                'response' => $calcaoAtualizado->stripe_response,
            ],
        ];

        /*
         * Registra imediatamente a captura parcial.
         *
         * Isso evita perder a informação caso a cobrança do restante falhe.
         */
        $cobranca->update([
            'calcao_id' => $calcaoAtualizado->id,
            'metodo' => AvariaCobranca::METODO_CALCAO,
            'valor_calcao' => $valorCalcao,
            'valor_stripe' => 0,
            'valor_pendente' => $valorRestante,
            'gateway_response' => $respostasGateway,
        ]);

        /*
         * O calção cobriu a cobrança inteira.
         */
        if ($valorRestante <= 0) {
            $cobranca->update([
                'metodo' => AvariaCobranca::METODO_CALCAO,
                'status' => AvariaCobranca::STATUS_COBRADO,
                'valor_calcao' => $valorCalcao,
                'valor_stripe' => 0,
                'valor_pendente' => 0,
                'gateway_payment_intent_id' => $calcaoAtualizado->payment_intent_id,
                'cobrado_em' => now(),
            ]);

            return $cobranca->fresh();
        }

        /*
         * O calção cobriu apenas parte da avaria.
         * O restante será tentado no Stripe.
         */
        try {
            $resultadoStripe = $this->executarCobrancaStripe(
                reservaResolvida: $reservaResolvida,
                cobranca: $cobranca,
                valor: $valorRestante,
                descricao: $descricao,
                paymentMethodId: $paymentMethodId,
            );

            $respostasGateway['stripe'] = [
                'valor' => $valorRestante,
                'payment_intent_id' => $resultadoStripe['payment_intent_id'],
                'response' => $resultadoStripe['response'],
            ];

            $cobranca->update([
                'metodo' => AvariaCobranca::METODO_MISTO,
                'status' => AvariaCobranca::STATUS_COBRADO,

                'valor_calcao' => $valorCalcao,
                'valor_stripe' => $valorRestante,
                'valor_pendente' => 0,

                'gateway_payment_intent_id' => $resultadoStripe['payment_intent_id'],
                'gateway_response' => $respostasGateway,
                'cobrado_em' => now(),
            ]);

            return $cobranca->fresh();
        } catch (Throwable $exception) {
            /*
             * O calção já foi capturado.
             *
             * Portanto, a cobrança não deve ser marcada totalmente como falha.
             * Apenas o valor restante permanece pendente.
             */
            $respostasGateway['stripe'] = [
                'valor' => $valorRestante,
                'error' => $exception->getMessage(),
            ];

            Log::warning(
                'Calção capturado, mas cobrança complementar no Stripe falhou.',
                [
                    'reserva_codigo' => $reservaResolvida->codigo,
                    'cobranca_id' => $cobranca->id,
                    'valor_calcao' => $valorCalcao,
                    'valor_pendente' => $valorRestante,
                    'erro' => $exception->getMessage(),
                ]
            );

            $cobranca->update([
                'metodo' => AvariaCobranca::METODO_MISTO,
                'status' => AvariaCobranca::STATUS_PENDENTE,

                'valor_calcao' => $valorCalcao,
                'valor_stripe' => 0,
                'valor_pendente' => $valorRestante,

                'gateway_response' => $respostasGateway,
            ]);

            return $cobranca->fresh();
        }
    }

    private function cobrarSomenteStripe(
        ReservaResolvida $reservaResolvida,
        AvariaCobranca $cobranca,
        float $valor,
        string $descricao,
        ?string $paymentMethodId = null,
    ): AvariaCobranca {
        $resultado = $this->executarCobrancaStripe(
            reservaResolvida: $reservaResolvida,
            cobranca: $cobranca,
            valor: $valor,
            descricao: $descricao,
            paymentMethodId: $paymentMethodId,
        );

        $cobranca->update([
            'metodo' => AvariaCobranca::METODO_STRIPE,
            'status' => AvariaCobranca::STATUS_COBRADO,

            'valor_calcao' => 0,
            'valor_stripe' => $valor,
            'valor_pendente' => 0,

            'gateway_payment_intent_id' => $resultado['payment_intent_id'],
            'gateway_response' => [
                'stripe' => [
                    'valor' => $valor,
                    'payment_intent_id' => $resultado['payment_intent_id'],
                    'response' => $resultado['response'],
                ],
            ],
            'cobrado_em' => now(),
        ]);

        return $cobranca->fresh();
    }

    /**
     * Executa a cobrança no Stripe sem alterar a cobrança no banco.
     *
     * @return array{
     *     payment_intent_id: string,
     *     response: array
     * }
     */
    private function executarCobrancaStripe(
        ReservaResolvida $reservaResolvida,
        AvariaCobranca $cobranca,
        float $valor,
        string $descricao,
        ?string $paymentMethodId = null,
    ): array {
        if ($valor <= 0) {
            throw new RuntimeException(
                'O valor da cobrança no Stripe deve ser maior que zero.'
            );
        }

        $empresa = $this->gatewayConfig->empresaPorId(
            (int) ($reservaResolvida->empresaId() ?? 0)
        ) ?? $this->gatewayConfig->empresaAtual();

        $stripeSecret = $empresa?->stripe_secret;

        if (! $stripeSecret) {
            throw new RuntimeException(
                'Configuração do Stripe não encontrada para esta empresa.'
            );
        }

        $cliente = $this->obterCliente($reservaResolvida);

        if (! $cliente) {
            throw new RuntimeException(
                'Cliente da reserva não encontrado.'
            );
        }

        $calcao = $this->obterCalcao($reservaResolvida);

        $customerId = $calcao?->stripe_customer_id
            ?: ($cliente->stripe_id ?? null);

        if (! $customerId) {
            throw new RuntimeException(
                'Cliente não possui Stripe Customer ID.'
            );
        }

        $stripe = new StripeClient($stripeSecret);

        $paymentMethodId = $paymentMethodId
            ?: ($calcao?->payment_method_id ?: null);

        if (! $paymentMethodId) {
            $paymentMethodId = $this->resolverPaymentMethodPadrao(
                stripe: $stripe,
                customerId: (string) $customerId,
            );
        }

        if (! $paymentMethodId) {
            throw new RuntimeException(
                'Não foi possível determinar um método de pagamento.'
            );
        }

        $moeda = strtolower(
            $this->gatewayConfig->moeda($empresa) ?? 'brl'
        );

        $intent = $stripe->paymentIntents->create([
            'amount' => (int) round($valor * 100),
            'currency' => $moeda,
            'customer' => (string) $customerId,
            'payment_method' => (string) $paymentMethodId,
            'confirm' => true,
            'off_session' => true,
            'description' => $descricao,

            'metadata' => [
                'reserva_codigo' => $reservaResolvida->codigo,
                'tipo_reserva' => $reservaResolvida->tipo->value,
                'registro_id' => (string) $reservaResolvida->id(),
                'veiculo_id' => (string) (
                    $reservaResolvida->registro->veiculo_id ?? ''
                ),
                'cobranca_avaria_id' => (string) $cobranca->id,
            ],
        ]);

        if ($intent->status !== 'succeeded') {
            throw new RuntimeException(
                "A cobrança não foi concluída pelo Stripe. Status: {$intent->status}"
            );
        }

        return [
            'payment_intent_id' => $intent->id,
            'response' => $intent->toArray(),
        ];
    }

    private function registrarComoManual(
        AvariaCobranca $cobranca,
        float $valorPendente,
    ): AvariaCobranca {
        $cobranca->update([
            'metodo' => AvariaCobranca::METODO_MANUAL,
            'status' => AvariaCobranca::STATUS_PENDENTE,
            'valor_calcao' => 0,
            'valor_stripe' => 0,
            'valor_pendente' => $valorPendente,
        ]);

        return $cobranca->fresh();
    }

    private function carregarRelacionamentos(
        ReservaResolvida $reservaResolvida
    ): void {
        $registro = $reservaResolvida->registro;

        $registro->loadMissing([
            'codigoReserva',
            'veiculo',
        ]);

        if ($registro instanceof Reserva) {
            $registro->loadMissing([
                'usuario',
                'calcao',
            ]);

            return;
        }

        if ($registro instanceof ReservaMotoristaApp) {
            $registro->loadMissing([
                'user',
            ]);
        }
    }

    private function obterCliente(
        ReservaResolvida $reservaResolvida
    ): ?User {
        $registro = $reservaResolvida->registro;

        if ($registro instanceof Reserva) {
            return $registro->usuario;
        }

        if ($registro instanceof ReservaMotoristaApp) {
            return $registro->user;
        }

        return null;
    }

    private function obterCalcao(
        ReservaResolvida $reservaResolvida
    ): ?Calcao {
        $registro = $reservaResolvida->registro;

        if (! $registro instanceof Reserva) {
            return null;
        }

        return $registro->calcao;
    }

    /**
     * Ajuste os nomes dos campos conforme o seu model Calcao.
     */
    private function obterValorDisponivelCalcao(
        Calcao $calcao
    ): float {
        /*
         * Se o model já possuir esse método, ele será utilizado.
         */
        if (method_exists($calcao, 'valorDisponivel')) {
            return max(
                0,
                (float) $calcao->valorDisponivel()
            );
        }

        /*
         * Se houver uma coluna específica com o saldo disponível.
         */
        if ($calcao->getAttribute('valor_disponivel') !== null) {
            return max(
                0,
                (float) $calcao->getAttribute('valor_disponivel')
            );
        }

        /*
         * Calcula pelo valor autorizado menos o valor já capturado.
         */
        $valorAutorizado = (float) (
            $calcao->getAttribute('valor_autorizado')
            ?? $calcao->getAttribute('valor')
            ?? 0
        );

        $valorCapturado = (float) (
            $calcao->getAttribute('valor_capturado')
            ?? 0
        );

        return max(
            0,
            $valorAutorizado - $valorCapturado
        );
    }

    private function buscarDanosDaReserva(
        int $reservaCodigoId,
        array $danoVeiculoIds,
    ): Collection {
        if (empty($danoVeiculoIds)) {
            return new Collection();
        }

        return VeiculoDanos::query()
            ->where('reserva_codigo_id', $reservaCodigoId)
            ->whereIn('id', $danoVeiculoIds)
            ->get();
    }

    private function normalizarIdsDosDanos(
        array $ids
    ): array {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function resolverPaymentMethodPadrao(
        StripeClient $stripe,
        string $customerId,
    ): ?string {
        try {
            $customer = $stripe->customers->retrieve(
                $customerId,
                []
            );

            $defaultPaymentMethod =
                $customer?->invoice_settings?->default_payment_method;

            if (
                is_string($defaultPaymentMethod)
                && $defaultPaymentMethod !== ''
            ) {
                return $defaultPaymentMethod;
            }
        } catch (Throwable) {
            // Continua para a tentativa seguinte.
        }

        try {
            $methods = $stripe->paymentMethods->all([
                'customer' => $customerId,
                'type' => 'card',
                'limit' => 1,
            ]);

            $primeiro = $methods->data[0] ?? null;

            return $primeiro?->id ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
