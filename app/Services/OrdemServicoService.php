<?php

namespace App\Services;

use App\Models\OrdemServico;
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
                throw new \InvalidArgumentException('Inclua ao menos uma avaria ou um serviço descrito.');
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
     */
    public function concluir(OrdemServico $os, string $relatorioGeral, array $resultadoPorItemId): void
    {
        if (! $os->podeConcluir()) {
            throw new \InvalidArgumentException('Esta OS não pode ser concluída.');
        }

        DB::transaction(function () use ($os, $relatorioGeral, $resultadoPorItemId) {
            $os->load('itens.veiculoDano');

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
                'user_id_encerramento' => Auth::id(),
                'data_conclusao' => now(),
            ]);
        });
    }
}
