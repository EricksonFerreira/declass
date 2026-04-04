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

        if ($paymentMethodId === '' || $valor <= 0 || $stripeSecret === '') {
            return [
                'status' => 'error',
                'message' => 'Dados invalidos para cobranca no Stripe.',
            ];
        }

        $stripe = new StripeClient($stripeSecret);
        $payload = [
            'amount' => (int) round($valor * 100),
            'currency' => $currency,
            'payment_method' => $paymentMethodId,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ];
        if ($customerId !== '') {
            $payload['customer'] = $customerId;
        }

        $intent = $stripe->paymentIntents->create($payload);

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

    public function cancelarPagamento(string $id): array
    {
        return [
            'status' => 'error',
            'message' => 'Cancelamento nao implementado para Stripe neste fluxo.',
            'transaction_id' => $id,
        ];
    }

    public function reembolsarPagamento(string $id): array
    {
        return [
            'status' => 'error',
            'message' => 'Reembolso nao implementado para Stripe neste fluxo.',
            'transaction_id' => $id,
        ];
    }
}
