<?php

namespace App\Services;

use App\Actions\Financeiro\CreateLancamentoAction;
use App\Models\CategoriaFinanceira;
use App\Models\LancamentoFinanceiro;
use App\Models\OrdemServico;
use App\Models\OrdemServicoAnexo;
use App\Models\OrdemServicoItem;
use App\Models\VeiculoDanos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdemServicoService
{
    public function gerarNumero(OrdemServico $os): string
    {
        return 'OS-' . now()->format('Y') . '-' . str_pad((string) $os->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<int, int>  $veiculoDanoIds
     * @param  array<int, string>  $servicosLivres  Linhas de texto (sem avaria vinculada)
     */
    public function criar(
        int $veiculoId,
        ?int $reservaId,
        ?int $empresaId,
        ?string $observacoes,
        array $veiculoDanoIds,
        array $servicosLivres
    ): OrdemServico {
        return DB::transaction(function () use ($veiculoId, $reservaId, $empresaId, $observacoes, $veiculoDanoIds, $servicosLivres) {
            $os = OrdemServico::create([
                'numero' => null,
                'empresa_id' => $empresaId,
                'veiculo_id' => $veiculoId,
                'reserva_id' => $reservaId,
                'user_id_abertura' => Auth::id(),
                'status' => OrdemServico::STATUS_ABERTA,
                'observacoes' => $observacoes,
            ]);

            $os->update(['numero' => $this->gerarNumero($os)]);

            $order = 0;

            foreach ($veiculoDanoIds as $danoId) {
                $dano = VeiculoDanos::query()->find($danoId);
                if (! $dano || (int) $dano->veiculo_id !== $veiculoId) {
                    throw new \InvalidArgumentException('Avaria inválida para este veículo.');
                }
                if ($dano->correcao_data) {
                    throw new \InvalidArgumentException('Avaria #' . $dano->id . ' já possui correção registrada.');
                }
                if ($this->danoEmOsAberta($danoId)) {
                    throw new \InvalidArgumentException('Avaria #' . $dano->id . ' já está em uma OS em aberto.');
                }

                $desc = trim((string) $dano->observacoes);
                if ($desc === '') {
                    $desc = 'Avaria: ' . strtoupper((string) $dano->local_dano) . ' — ' . ($dano->tipo_dano ?? '');
                }

                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => $dano->id,
                    'descricao_servico' => $desc,
                    'sort_order' => $order++,
                ]);
            }

            foreach ($servicosLivres as $linha) {
                $linha = trim((string) $linha);
                if ($linha === '') {
                    continue;
                }
                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => null,
                    'descricao_servico' => $linha,
                    'sort_order' => $order++,
                ]);
            }

            if ($order === 0) {
                // OS nao precisa obrigatoriamente estar vinculada a avarias.
                // Se nenhum item foi informado, cria um item "livre" padrao.
                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => null,
                    'descricao_servico' => 'Servico a definir',
                    'sort_order' => $order++,
                ]);
            }

            return $os->fresh(['itens']);
        });
    }

    public function danoEmOsAberta(int $veiculoDanoId): bool
    {
        return OrdemServicoItem::query()
            ->where('veiculo_dano_id', $veiculoDanoId)
            ->whereHas('ordemServico', function ($q) {
                $q->abertasOuEmAndamento();
            })
            ->exists();
    }

    public function atualizarStatus(OrdemServico $os, string $novoStatus): void
    {
        if (! $os->podeAlterarStatus()) {
            throw new \InvalidArgumentException('Esta OS não pode mais ter o status alterado.');
        }

        $permitidos = [
            OrdemServico::STATUS_ABERTA => [OrdemServico::STATUS_EM_ANDAMENTO, OrdemServico::STATUS_CANCELADA],
            OrdemServico::STATUS_EM_ANDAMENTO => [OrdemServico::STATUS_CANCELADA],
        ];

        if ($novoStatus === OrdemServico::STATUS_CANCELADA) {
            $os->update([
                'status' => OrdemServico::STATUS_CANCELADA,
                'user_id_encerramento' => Auth::id(),
                'data_conclusao' => now(),
            ]);

            return;
        }

        if ($os->status === OrdemServico::STATUS_ABERTA && $novoStatus === OrdemServico::STATUS_EM_ANDAMENTO) {
            $os->update(['status' => OrdemServico::STATUS_EM_ANDAMENTO]);

            return;
        }

        throw new \InvalidArgumentException('Transição de status não permitida.');
    }

    /**
     * @param  array<int, string>  $resultadoPorItemId  [ item_id => texto ]
     * @param  array<int, array{path:string, original_name?:string|null, mime?:string|null, size_bytes?:int|null, user_id?:int|null}>  $anexosMeta
     */
    public function concluir(
        OrdemServico $os,
        string $relatorioGeral,
        array $resultadoPorItemId,
        array $anexosMeta = [],
        float $valorPecas = 0,
        float $valorMaoObra = 0
    ): void
    {
        if (! $os->podeConcluir()) {
            throw new \InvalidArgumentException('Esta OS não pode ser concluída.');
        }

        DB::transaction(function () use ($os, $relatorioGeral, $resultadoPorItemId, $anexosMeta, $valorPecas, $valorMaoObra) {
            $os->load('itens.veiculoDano');
            $valorPecas = round(max(0, $valorPecas), 2);
            $valorMaoObra = round(max(0, $valorMaoObra), 2);
            $valorTotal = round($valorPecas + $valorMaoObra, 2);

            foreach ($os->itens as $item) {
                $texto = $resultadoPorItemId[$item->id] ?? $relatorioGeral;
                $texto = trim((string) $texto);
                if ($texto === '') {
                    $texto = trim($relatorioGeral);
                }
                if ($texto === '') {
                    $texto = 'Serviço concluído conforme OS ' . ($os->numero ?? '#' . $os->id);
                }

                $item->update(['resultado' => $texto]);

                if ($item->veiculo_dano_id && $item->veiculoDano && ! $item->veiculoDano->correcao_data) {
                    $item->veiculoDano->update([
                        'correcao_descricao' => $texto,
                        'correcao_data' => now(),
                        'correcao_user_id' => Auth::id(),
                    ]);
                }
            }

            $os->update([
                'status' => OrdemServico::STATUS_CONCLUIDA,
                'relatorio_conclusao' => trim($relatorioGeral) !== '' ? trim($relatorioGeral) : null,
                'valor_pecas' => $valorPecas,
                'valor_mao_obra' => $valorMaoObra,
                'valor_total' => $valorTotal,
                'user_id_encerramento' => Auth::id(),
                'data_conclusao' => now(),
            ]);

            $this->registrarDespesaFinanceira($os->fresh(), $valorTotal);

            foreach ($anexosMeta as $meta) {
                $path = trim((string) ($meta['path'] ?? ''));
                if ($path === '') {
                    continue;
                }

                OrdemServicoAnexo::create([
                    'ordem_servico_id' => $os->id,
                    'path' => $path,
                    'original_name' => $meta['original_name'] ?? null,
                    'mime' => $meta['mime'] ?? null,
                    'size_bytes' => $meta['size_bytes'] ?? null,
                    'user_id' => $meta['user_id'] ?? Auth::id(),
                ]);
            }
        });
    }

    private function registrarDespesaFinanceira(OrdemServico $os, float $valorTotal): void
    {
        if ($valorTotal <= 0 || ! $os->empresa_id) {
            return;
        }

        $chave = 'ordem_servico|'.$os->id;
        $jaExiste = LancamentoFinanceiro::query()
            ->where('empresa_id', (int) $os->empresa_id)
            ->where('tipo', 'despesa')
            ->where('observacoes', 'like', '%'.$chave.'%')
            ->exists();

        if ($jaExiste) {
            return;
        }

        $categoria = CategoriaFinanceira::firstOrCreate(
            [
                'empresa_id' => (int) $os->empresa_id,
                'nome' => 'Manutenção',
                'tipo' => 'despesa',
            ],
            [
                'descricao' => 'Despesas geradas por ordens de serviço e manutenção da frota.',
            ]
        );

        app(CreateLancamentoAction::class)->handle([
            'empresa_id' => (int) $os->empresa_id,
            'descricao' => 'Manutenção OS '.($os->numero ?? '#'.$os->id),
            'valor' => $valorTotal,
            'tipo' => 'despesa',
            'categoria_financeira_id' => $categoria->id,
            'data_vencimento' => now()->toDateString(),
            'data_pagamento' => now()->toDateString(),
            'status' => 'pago',
            'veiculo_id' => $os->veiculo_id,
            'reserva_id' => $os->reserva_id,
            'user_id' => Auth::id(),
            'observacoes' => 'Gerado automaticamente ao encerrar a Ordem de Serviço. Chave: '.$chave,
        ]);
    }
}
