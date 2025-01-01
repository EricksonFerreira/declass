<?php

namespace App\Services\Pagamentos;

class PayPalService implements IPagamento
{
    public function cobrarPagamento(array $dados): array
    {
        return [
            'status' => 'success',
            'transaction_id' => 'PAYPAL123456',
        ];
    }

    public function cancelarPagamento(string $id): array
    {
        return [];
    }

    public function reembolsarPagamento(string $id): array
    {
        return [];
    }
}
