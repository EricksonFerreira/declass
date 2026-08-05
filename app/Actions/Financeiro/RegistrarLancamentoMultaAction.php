<?php

namespace App\Actions\Financeiro;

use App\Models\CategoriaFinanceira;
use App\Models\LancamentoFinanceiro;
use App\Models\Multa;
use Carbon\CarbonInterface;

class RegistrarLancamentoMultaAction
{
    public function lancamentoDespesaEmpresa(Multa $multa): ?LancamentoFinanceiro
    {
        $empresaId = (int) ($multa->empresa_id ?? $multa->reserva?->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return null;
        }

        return $this->criarOuAtualizarLancamento(
            $multa,
            $empresaId,
            'despesa',
            'Multa da empresa',
            'Despesa gerada automaticamente para multas assumidas pela empresa.',
            'Lançamento de multa da empresa',
            'pendente'
        );
    }

    public function cancelarDespesaEmpresa(Multa $multa): ?LancamentoFinanceiro
    {
        $empresaId = (int) ($multa->empresa_id ?? $multa->reserva?->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return null;
        }

        $lancamento = $this->buscarLancamentoMulta($empresaId, 'despesa', $multa->id);
        if (!$lancamento) {
            return null;
        }

        $lancamento->update([
            'status' => 'cancelado',
            'observacoes' => trim((string) $lancamento->observacoes . ' | Cancelado ao atribuir multa ao cliente.'),
        ]);

        return $lancamento;
    }

    public function lancamentoReceitaCliente(Multa $multa, ?CarbonInterface $pagoEm = null): ?LancamentoFinanceiro
    {
        $empresaId = (int) ($multa->empresa_id ?? $multa->reserva?->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return null;
        }

        $categoria = $this->categoria($empresaId, 'receita', 'Multa do cliente', 'Receitas geradas ao cobrar multas atribuídas ao cliente.');
        $pagoEm ??= now();
        $valor = round((float) ($multa->valor_final ?? $multa->valor_original ?? 0), 2);
        if ($valor <= 0) {
            return null;
        }

        $chave = 'multa_receita|'.$multa->id.'|'.$pagoEm->toDateString().'|'.number_format($valor, 2, '.', '');
        $existente = LancamentoFinanceiro::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'receita')
            ->where('observacoes', 'like', '%'.$chave.'%')
            ->first();

        if ($existente) {
            return $existente;
        }

        return LancamentoFinanceiro::create([
            'empresa_id' => $empresaId,
            'categoria_financeira_id' => $categoria->id,
            'descricao' => 'Pagamento da multa #'.$multa->id,
            'valor' => $valor,
            'tipo' => 'receita',
            'status' => 'pago',
            'data_vencimento' => $pagoEm->toDateString(),
            'data_pagamento' => $pagoEm->toDateString(),
            'veiculo_id' => $multa->veiculo_id,
            'reserva_id' => $multa->reserva_id,
            'user_id' => $multa->cliente_id ?: ($multa->reserva?->user_id ?? null),
            'observacoes' => 'Lancamento gerado automaticamente ao confirmar pagamento da multa do cliente. Chave: '.$chave,
        ]);
    }

    private function criarOuAtualizarLancamento(
        Multa $multa,
        int $empresaId,
        string $tipo,
        string $categoriaNome,
        string $descricaoCategoria,
        string $descricaoLancamento,
        string $status
    ): ?LancamentoFinanceiro {
        $categoria = $this->categoria($empresaId, $tipo, $categoriaNome, $descricaoCategoria);
        $valor = round((float) ($multa->valor_final ?? $multa->valor_original ?? 0), 2);
        if ($valor <= 0) {
            return null;
        }

        $chave = 'multa_'.$tipo.'|'.$multa->id.'|'.$valor;
        $existente = LancamentoFinanceiro::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('observacoes', 'like', '%'.$chave.'%')
            ->first();

        if ($existente) {
            return $existente;
        }

        return LancamentoFinanceiro::create([
            'empresa_id' => $empresaId,
            'categoria_financeira_id' => $categoria->id,
            'descricao' => $descricaoLancamento.' #'.$multa->id,
            'valor' => $valor,
            'tipo' => $tipo,
            'status' => $status,
            'data_vencimento' => $multa->data_vencimento?->toDateString() ?? now()->toDateString(),
            'veiculo_id' => $multa->veiculo_id,
            'reserva_id' => $multa->reserva_id,
            'user_id' => $multa->cliente_id ?: ($multa->reserva?->user_id ?? null),
            'observacoes' => 'Lancamento gerado automaticamente ao registrar multa. Chave: '.$chave,
        ]);
    }

    private function categoria(int $empresaId, string $tipo, string $nome, string $descricao): CategoriaFinanceira
    {
        return CategoriaFinanceira::firstOrCreate(
            [
                'empresa_id' => $empresaId,
                'nome' => $nome,
                'tipo' => $tipo,
            ],
            [
                'descricao' => $descricao,
                'status' => 'ativo',
                'permite_lancamento' => true,
                'origem' => 'multa',
                'codigo' => CategoriaFinanceira::sugerirCodigo($empresaId, $tipo),
            ]
        );
    }

    private function buscarLancamentoMulta(int $empresaId, string $tipo, int $multaId): ?LancamentoFinanceiro
    {
        return LancamentoFinanceiro::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('descricao', 'like', '%#'.$multaId)
            ->latest('id')
            ->first();
    }
}
