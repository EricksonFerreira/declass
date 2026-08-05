<?php

namespace App\Actions\Financeiro;

use App\Models\CategoriaFinanceira;
use App\Models\LancamentoFinanceiro;
use App\Models\Sinistro;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;

class RegistrarLancamentoSinistroAction
{
    public function lancamentoDespesaEmpresa(Sinistro $sinistro, ?CarbonInterface $pagoEm = null): ?LancamentoFinanceiro
    {
        return $this->criarLancamento(
            $sinistro,
            'despesa',
            'Sinistro pago pela empresa',
            'Despesa gerada automaticamente para sinistros assumidos pela empresa.',
            'Lancamento de sinistro pago pela empresa',
            $pagoEm,
            'empresa'
        );
    }

    public function lancamentoReceitaCliente(Sinistro $sinistro, ?CarbonInterface $pagoEm = null): ?LancamentoFinanceiro
    {
        return $this->criarLancamento(
            $sinistro,
            'receita',
            'Sinistro pago pelo cliente',
            'Receita gerada automaticamente para sinistros cobrados do cliente.',
            'Lancamento de sinistro pago pelo cliente',
            $pagoEm,
            'cliente'
        );
    }

    private function criarLancamento(
        Sinistro $sinistro,
        string $tipo,
        string $nomeCategoria,
        string $descricaoCategoria,
        string $descricaoLancamento,
        ?CarbonInterface $pagoEm,
        string $origem
    ): ?LancamentoFinanceiro {
        $empresaId = (int) ($sinistro->empresa_id ?? $sinistro->reserva?->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return null;
        }

        $valor = (float) ($origem === 'cliente'
            ? ($sinistro->valor_responsabilidade_cliente ?? 0)
            : ($sinistro->valor_custo_empresa ?? 0));

        $valor = round($valor, 2);
        if ($valor <= 0) {
            return null;
        }

        $pagoEm ??= now();
        $categoria = $this->categoria($empresaId, $tipo, $nomeCategoria, $descricaoCategoria);
        $chave = implode('|', [
            'sinistro',
            $origem,
            (string) $sinistro->id,
            $pagoEm->toDateString(),
            number_format($valor, 2, '.', ''),
        ]);

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
            'descricao' => $descricaoLancamento.' #'.$sinistro->id,
            'valor' => $valor,
            'tipo' => $tipo,
            'status' => 'pago',
            'data_lancamento' => $pagoEm->format('Y-m-d H:i:s'),
            'data_vencimento' => $pagoEm->toDateString(),
            'data_pagamento' => $pagoEm->toDateString(),
            'veiculo_id' => $sinistro->veiculo_id,
            'reserva_id' => $sinistro->reserva_id,
            'user_id' => $origem === 'cliente'
                ? ($sinistro->cliente_id ?: ($sinistro->reserva?->user_id ?? null))
                : Auth::id(),
            'observacoes' => 'Lancamento gerado automaticamente ao confirmar pagamento do sinistro. Chave: '.$chave,
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
                'origem' => 'sinistro',
                'codigo' => CategoriaFinanceira::sugerirCodigo($empresaId, $tipo),
            ]
        );
    }
}
