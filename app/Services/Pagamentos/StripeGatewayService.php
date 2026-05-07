<?php

namespace App\Services\Pagamentos;

use Stripe\StripeClient;

class StripeGatewayService implements IPagamento
{
    public function cobrarPagamento(array $dados): array
    {
        $paymentMethodId = (string) ($dados['payment_method'] ?? '');
        $valor = (float) ($dados['valor'] ?? 0);
        $customerId = (string) ($dados['customer_id'] ?? '');
        $stripeSecret = (string) ($dados['stripe_secret'] ?? '');
        $currency = strtolower((string) ($dados['currency'] ?? 'brl'));
        $installments = (int) ($dados['installments'] ?? 1);

        if ($paymentMethodId === '' || $valor <= 0 || $stripeSecret === '') {
            return [
                'status' => 'error',
                'message' => 'Dados invalidos para cobranca no Stripe.',
            ];
        }

        $stripe = new StripeClient($stripeSecret);

        // 1. Cria PaymentIntent sem confirmar para obter planos disponiveis
        $createPayload = [
            'amount' => (int) round($valor * 100),
            'currency' => $currency,
            'payment_method' => $paymentMethodId,
            'confirm' => false,
        ];

        if ($installments > 1) {
            $createPayload['payment_method_options'] = [
                'card' => [
                    'installments' => ['enabled' => true],
                ],
            ];
        }
        if ($customerId !== '') {
            $createPayload['customer'] = $customerId;
        }

        try {
            $intent = $stripe->paymentIntents->create($createPayload);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe error creating intent', ['message' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage(), 'raw_status' => 'api_error'];
        } catch (\Throwable $e) {
            \Log::error('Stripe generic error', ['message' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Falha ao processar o pagamento. Tente novamente.', 'raw_status' => 'error'];
        }

        // 2. Obtem planos de parcelamento disponiveis
        $availablePlans = [];
        if ($installments > 1) {
            $availablePlans = $intent->payment_method_options->card->installments->available_plans ?? [];
        }

        if ($installments > 1 && !empty($availablePlans)) {
            // Encontra um plano com o numero de parcelas solicitado
            $selectedPlan = null;
            foreach ($availablePlans as $plan) {
                if (($plan->count ?? 0) === $installments) {
                    $selectedPlan = $plan;
                    break;
                }
            }

            if (!$selectedPlan) {
                // Usa o primeiro plano disponivel como fallback
                $selectedPlan = $availablePlans[0] ?? null;
            }

            if ($selectedPlan) {
                $confirmPayload = [
                    'payment_method_options' => [
                        'card' => [
                            'installments' => [
                                'enabled' => true,
                                'plan' => [
                                    'count' => $selectedPlan->count ?? $installments,
                                    'type' => $selectedPlan->type ?? 'fixed_count',
                                    'interval' => $selectedPlan->interval ?? 'month',
                                ],
                            ],
                        ],
                    ],
                ];
            } else {
                // Nenhum plano disponivel — prossegue sem parcelamento
                $confirmPayload = [];
            }
        } else {
            $confirmPayload = [
                'payment_method_options' => [
                    'card' => [
                        'installments' => ['enabled' => false],
                    ],
                ],
            ];
        }

        // 3. Confirma o PaymentIntent
        try {
            $intent = $stripe->paymentIntents->confirm($intent->id, $confirmPayload);
        } catch (\Stripe\Exception\CardException $e) {
            \Log::warning('Stripe card error', ['message' => $e->getMessage(), 'code' => $e->getError()?->code]);
            return ['status' => 'error', 'message' => $this->mapStripeCardError($e), 'raw_status' => 'card_error'];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe confirm error', ['message' => $e->getMessage(), 'installments' => $installments]);
            return ['status' => 'error', 'message' => $e->getMessage(), 'raw_status' => 'api_error'];
        } catch (\Throwable $e) {
            \Log::error('Stripe generic error', ['message' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Falha ao processar o pagamento. Tente novamente.', 'raw_status' => 'error'];
        }

        if (in_array($intent->status, ['requires_action', 'requires_source_action'], true)) {
            return [
                'status' => 'action_required',
                'message' => 'O cartao exige autenticacao. Use outro metodo de pagamento.',
                'transaction_id' => $intent->id,
                'raw_status' => $intent->status,
                'client_secret' => $intent->client_secret ?? null,
            ];
        }

        if ($intent->status !== 'succeeded') {
            return [
                'status' => 'error',
                'message' => 'Pagamento nao confirmado no Stripe.',
                'transaction_id' => $intent->id,
                'raw_status' => $intent->status,
            ];
        }

        return [
            'status' => 'success',
            'transaction_id' => $intent->id,
            'raw_status' => $intent->status,
            'customer_id' => $intent->customer,
            'currency' => $intent->currency,
        ];
    }

    private function mapStripeCardError(\Stripe\Exception\CardException $e): string
    {
        $error = $e->getError();
        $code = $error?->code ?? null;
        $decline = $error?->decline_code ?? null;

        $map = [
            'insufficient_funds' => 'Cartao sem saldo suficiente.',
            'card_declined' => 'Pagamento recusado pelo emissor do cartao.',
            'expired_card' => 'Cartao expirado.',
            'incorrect_cvc' => 'CVC invalido.',
            'invalid_cvc' => 'CVC invalido.',
            'invalid_number' => 'Numero do cartao invalido.',
            'invalid_expiry_month' => 'Validade do cartao invalida.',
            'invalid_expiry_year' => 'Validade do cartao invalida.',
            'processing_error' => 'Erro ao processar o cartao. Tente novamente.',
        ];

        if (is_string($code) && isset($map[$code])) {
            return $map[$code];
        }
        if (is_string($decline) && isset($map[$decline])) {
            return $map[$decline];
        }

        return 'Pagamento nao autorizado. Verifique os dados do cartao.';
    }

    public function cancelarPagamento(string $id, array $dados = []): array
    {
        $stripeSecret = (string) ($dados['stripe_secret'] ?? '');
        if ($stripeSecret === '') {
            return [
                'status' => 'error',
                'message' => 'Chave secreta do Stripe nao informada para cancelamento.',
                'transaction_id' => $id,
            ];
        }

        $stripe = new StripeClient($stripeSecret);
        try {
            $intent = $stripe->paymentIntents->retrieve($id, []);
            $status = $intent->status ?? null;

            if ($status === 'succeeded') {
                $refund = $stripe->refunds->create(['payment_intent' => $id]);
                return [
                    'status' => 'success',
                    'action' => 'refund',
                    'transaction_id' => $refund->id ?? $id,
                    'raw_status' => $refund->status ?? null,
                ];
            }

            $cancelled = $stripe->paymentIntents->cancel($id, []);
            return [
                'status' => 'success',
                'action' => 'cancel',
                'transaction_id' => $cancelled->id ?? $id,
                'raw_status' => $cancelled->status ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'transaction_id' => $id,
            ];
        }
    }

    public function reembolsarPagamento(string $id, array $dados = []): array
    {
        $stripeSecret = (string) ($dados['stripe_secret'] ?? '');
        if ($stripeSecret === '') {
            return [
                'status' => 'error',
                'message' => 'Chave secreta do Stripe nao informada para reembolso.',
                'transaction_id' => $id,
            ];
        }

        $stripe = new StripeClient($stripeSecret);
        try {
            $refund = $stripe->refunds->create(['payment_intent' => $id]);
            return [
                'status' => 'success',
                'transaction_id' => $refund->id ?? $id,
                'raw_status' => $refund->status ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'transaction_id' => $id,
            ];
        }
    }
}
