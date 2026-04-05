<?php

namespace App\Services\Pagamentos;

class PayPalService implements IPagamento
{
    public function cobrarPagamento(array $dados): array
    {
        $clientId = (string) ($dados['paypal_client_id'] ?? '');
        $clientSecret = (string) ($dados['paypal_client_secret'] ?? '');
        if ($clientId === '' || $clientSecret === '') {
            return [
                'status' => 'error',
                'message' => 'Credenciais PayPal nao configuradas para a empresa.',
            ];
        }

        return [
            'status' => 'success',
            'transaction_id' => 'PAYPAL123456',
            'message' => 'Pagamento processado no gateway PayPal (simulado).',
            'currency' => strtolower((string) ($dados['currency'] ?? 'brl')),
        ];
    }

    public function cancelarPagamento(string $id, array $dados = []): array
    {
        return [
            'status' => 'error',
            'message' => 'Cancelamento nao implementado para PayPal neste fluxo.',
            'transaction_id' => $id,
        ];
    }

    public function reembolsarPagamento(string $id, array $dados = []): array
    {
        return [
            'status' => 'error',
            'message' => 'Reembolso nao implementado para PayPal neste fluxo.',
            'transaction_id' => $id,
        ];
    }
}
