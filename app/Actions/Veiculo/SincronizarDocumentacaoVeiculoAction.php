<?php

namespace App\Actions\Veiculo;

use App\Models\CategoriaFinanceira;
use App\Models\DocumentacaoVeiculo;
use App\Models\DocumentacaoVeiculoParcela;
use App\Models\LancamentoFinanceiro;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SincronizarDocumentacaoVeiculoAction
{
    private const CATEGORIAS_PADRAO = [
        'ipva' => 'IPVA',
        'licenciamento' => 'Licenciamento',
        'seguro' => 'Seguro',
    ];

    public function handle(DocumentacaoVeiculo $documentacao, array $parcelas, ?string $dataPrimeiroVencimento = null): DocumentacaoVeiculo
    {
        return DB::transaction(function () use ($documentacao, $parcelas, $dataPrimeiroVencimento) {
            $documentacao->loadMissing('veiculo');

            $categoria = $this->categoriaPadrao(
                (int) $documentacao->empresa_id,
                (string) $documentacao->tipo
            );

            $documentacao->update([
                'categoria_financeira_id' => $categoria->id,
            ]);

            $documentacao->parcelas()->delete();
            LancamentoFinanceiro::query()
                ->where('documentacao_veiculo_id', $documentacao->id)
                ->delete();

            $primeiroVencimento = $dataPrimeiroVencimento
                ? Carbon::parse($dataPrimeiroVencimento)->startOfDay()
                : null;

            $parcelas = collect($parcelas)->values();

            foreach ($parcelas as $indice => $parcela) {
                $numeroParcela = $indice + 1;
                $dataVencimento = ! empty($parcela['data_vencimento'])
                    ? Carbon::parse($parcela['data_vencimento'])->toDateString()
                    : ($primeiroVencimento
                        ? $primeiroVencimento->copy()->addMonthsNoOverflow($indice)->toDateString()
                        : now()->toDateString());
                $status = (string) ($parcela['status'] ?? 'pendente');
                $dataPagamento = ! empty($parcela['data_pagamento'])
                    ? Carbon::parse($parcela['data_pagamento'])->toDateString()
                    : ($status === 'pago' ? $dataVencimento : null);

                $parcelaModel = DocumentacaoVeiculoParcela::create([
                    'documentacao_veiculo_id' => $documentacao->id,
                    'numero_parcela' => $numeroParcela,
                    'data_vencimento' => $dataVencimento,
                    'data_pagamento' => $dataPagamento,
                    'valor' => $documentacao->valor_parcela,
                    'status' => $status,
                ]);

                LancamentoFinanceiro::create([
                    'empresa_id' => $documentacao->empresa_id,
                    'descricao' => strtoupper((string) $documentacao->tipo) . ' - Parcela ' . $numeroParcela . '/' . count($parcelas),
                    'valor' => $documentacao->valor_parcela,
                    'tipo' => 'despesa',
                    'categoria_financeira_id' => $categoria->id,
                    'data_vencimento' => $dataVencimento,
                    'data_pagamento' => $dataPagamento,
                    'status' => $status,
                    'veiculo_id' => $documentacao->veiculo_id,
                    'documentacao_veiculo_id' => $documentacao->id,
                    'observacoes' => 'Lancamento automatico da documentacao do veiculo.',
                    'recorrente' => false,
                    'recorrencia' => null,
                    'parcelas_totais' => count($parcelas),
                    'parcela_atual' => $numeroParcela,
                    'grupo_recorrencia_id' => null,
                    'user_id' => Auth::id(),
                ]);

                if ($status === 'pago' && $dataPagamento && $parcelaModel->data_pagamento !== $dataPagamento) {
                    $parcelaModel->update(['data_pagamento' => $dataPagamento]);
                }
            }

            return $documentacao->fresh(['parcelas', 'lancamentos', 'categoriaFinanceira']);
        });
    }

    public function pagarParcela(DocumentacaoVeiculoParcela $parcela, string $dataPagamento): DocumentacaoVeiculoParcela
    {
        return DB::transaction(function () use ($parcela, $dataPagamento) {
            $dataPagamento = Carbon::parse($dataPagamento)->toDateString();

            $parcela->update([
                'status' => 'pago',
                'data_pagamento' => $dataPagamento,
            ]);

            LancamentoFinanceiro::query()
                ->where('documentacao_veiculo_id', $parcela->documentacao_veiculo_id)
                ->where('parcela_atual', $parcela->numero_parcela)
                ->where('tipo', 'despesa')
                ->update([
                    'status' => 'pago',
                    'data_pagamento' => $dataPagamento,
                ]);

            return $parcela->fresh();
        });
    }

    private function categoriaPadrao(int $empresaId, string $tipo): CategoriaFinanceira
    {
        $nome = self::CATEGORIAS_PADRAO[$tipo] ?? strtoupper($tipo);

        return CategoriaFinanceira::firstOrCreate(
            [
                'empresa_id' => $empresaId,
                'nome' => $nome,
                'tipo' => 'despesa',
            ],
            [
                'codigo' => CategoriaFinanceira::sugerirCodigo($empresaId, 'despesa', null),
                'status' => 'ativo',
                'descricao' => 'Categoria automática para documentações de veículo.',
                'permite_lancamento' => true,
                'origem' => 'sistema',
            ]
        );
    }
}
