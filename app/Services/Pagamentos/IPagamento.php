<?php

namespace App\Services\Pagamentos;

interface IPagamento
{
    public function cobrarPagamento(array $dados): array;
    public function cancelarPagamento(string $id, array $dados = []): array;
    public function reembolsarPagamento(string $id, array $dados = []): array;
}
