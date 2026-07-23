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
        array $servicosPrevistos = [],
        ?int $sinistroId = null,
        ?int $osCategoriaId = null,
        ?int $osSubcategoriaId = null,
        array $pendenciaPreventivaIds = [],
        array $camposDinamicos = []
    ): OrdemServico {
        return DB::transaction(function () use ($veiculoId, $reservaId, $empresaId, $observacoes, $veiculoDanoIds, $servicosPrevistos, $sinistroId, $osCategoriaId, $osSubcategoriaId, $pendenciaPreventivaIds, $camposDinamicos) {
            $os = OrdemServico::create([
                'numero' => null,
                'empresa_id' => $empresaId,
                'veiculo_id' => $veiculoId,
                'reserva_id' => $reservaId,
                'sinistro_id' => $sinistroId,
                'os_categoria_id' => $osCategoriaId,
                'os_subcategoria_id' => $osSubcategoriaId,
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

                $dano->update(['status' => VeiculoDanos::STATUS_EM_CORRECAO]);

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

            foreach ($servicosPrevistos as $servico) {
                $desc = trim((string) ($servico['descricao'] ?? ''));
                $obs = trim((string) ($servico['observacao'] ?? ''));
                if ($desc === '') {
                    continue;
                }
                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => null,
                    'descricao_servico' => $desc,
                    'resultado' => $obs !== '' ? $obs : null,
                    'sort_order' => $order++,
                ]);
            }

            foreach ($pendenciaPreventivaIds as $pendenciaId) {
                $pendencia = \App\Models\PendenciaManutencaoPreventiva::with('item')->find($pendenciaId);
                if ($pendencia && (int) $pendencia->veiculo_id === $veiculoId) {
                    OrdemServicoItem::create([
                        'ordem_servico_id' => $os->id,
                        'pendencia_manutencao_preventiva_id' => $pendencia->id,
                        'descricao_servico' => 'Preventiva: ' . $pendencia->item->nome,
                        'sort_order' => $order++,
                    ]);
                }
            }

            if (!empty($camposDinamicos)) {
                foreach ($camposDinamicos as $campoId => $valor) {
                    $os->valoresCampos()->create([
                        'os_subcategoria_campo_id' => $campoId,
                        'valor' => is_array($valor) ? json_encode($valor) : $valor,
                    ]);
                }
            }

            if ($order === 0) {
                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => null,
                    'descricao_servico' => 'Servico a definir',
                    'sort_order' => $order++,
                ]);
            }

            return $os->fresh(['itens', 'valoresCampos.campo']);
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
            DB::transaction(function () use ($os) {
                $os->update([
                    'status' => OrdemServico::STATUS_CANCELADA,
                    'user_id_encerramento' => Auth::id(),
                    'data_conclusao' => now(),
                ]);

                // Reverter avarias vinculadas para pendente
                $os->load('itens.veiculoDano');
                foreach ($os->itens as $item) {
                    if ($item->veiculo_dano_id && $item->veiculoDano) {
                        $item->veiculoDano->update([
                            'status' => VeiculoDanos::STATUS_PENDENTE,
                            'correcao_descricao' => null,
                            'correcao_data' => null,
                            'correcao_user_id' => null,
                        ]);
                    }
                }
            });

            return;
        }

        if ($os->status === OrdemServico::STATUS_ABERTA && $novoStatus === OrdemServico::STATUS_EM_ANDAMENTO) {
            $os->update(['status' => OrdemServico::STATUS_EM_ANDAMENTO]);

            return;
        }

        throw new \InvalidArgumentException('Transição de status não permitida.');
    }

    /**
     * Conclui a ordem de serviço, atualiza as avarias vinculadas, cria itens executados (peças/serviços) e integra com o fluxo de caixa.
     */
    public function concluir(
        OrdemServico $os,
        string $relatorioGeral,
        array $anexosMeta = [],
        array $avariasResolutions = [], // [ dano_id => [ 'status' => '...', 'obs' => '...' ] ]
        array $pecasExecutadas = [],   // [ [ 'nome' => '...', 'qtd' => X, 'valor' => Y ] ]
        array $servicosExecutados = [], // [ [ 'nome' => '...', 'qtd' => X, 'valor' => Y ] ]
        array $itensCompletados = []    // [ item_id_1, item_id_2 ]
    ): void {
        if (! $os->podeConcluir()) {
            throw new \InvalidArgumentException('Esta OS não pode ser concluída.');
        }

        DB::transaction(function () use ($os, $relatorioGeral, $anexosMeta, $avariasResolutions, $pecasExecutadas, $servicosExecutados, $itensCompletados) {
            $os->load('itens.veiculoDano');

            // 1. Atualizar itens planejados/existentes
            foreach ($os->itens as $item) {
                // Se for um item de avaria vinculada, vamos atualizar seu resultado com a observação específica ou geral
                if ($item->veiculo_dano_id) {
                    $resInfo = $avariasResolutions[$item->veiculo_dano_id] ?? null;
                    $obs = trim((string) ($resInfo['obs'] ?? ''));
                    $statusDano = $resInfo['status'] ?? 'corrigida';

                    $textoResultado = $obs !== '' ? $obs : 'Resultado: ' . strtoupper($statusDano) . '. ' . $relatorioGeral;
                    $item->update(['resultado' => $textoResultado]);

                    // Atualiza o VeiculoDanos correspondente
                    if ($item->veiculoDano) {
                        $dano = $item->veiculoDano;
                        if (in_array($statusDano, ['corrigida', 'parcialmente_corrigida', 'descartada'])) {
                            $dano->update([
                                'status' => $statusDano,
                                'correcao_descricao' => $obs !== '' ? $obs : $relatorioGeral,
                                'correcao_data' => now(),
                                'correcao_user_id' => Auth::id(),
                            ]);
                        } else {
                            // Se voltou para pendente ou outra coisa, limpa campos de correção e volta status para pendente
                            $dano->update([
                                'status' => 'pendente',
                                'correcao_descricao' => null,
                                'correcao_data' => null,
                                'correcao_user_id' => null,
                            ]);
                        }
                    }
                } else {
                    // Item planejado (serviço previsto)
                    $completado = in_array($item->id, $itensCompletados);
                    $item->update([
                        'resultado' => $completado ? 'Executado' : 'Não executado/Pendente'
                    ]);
                }
            }

            $somatoriaPecas = 0.0;
            $somatoriaMaoObra = 0.0;
            $order = $os->itens->max('sort_order') + 1;

            // 2. Criar lançamentos de execução - Peças
            foreach ($pecasExecutadas as $peca) {
                $nome = trim((string) ($peca['nome'] ?? ''));
                if ($nome === '') continue;

                $qtd = max(1, (int) ($peca['qtd'] ?? 1));
                $valorUnit = max(0.0, (float) $this->normalizarMoeda($peca['valor'] ?? 0));
                $totalItem = round($qtd * $valorUnit, 2);

                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => null,
                    'descricao_servico' => $nome,
                    'tipo_item' => 'peca',
                    'quantidade' => $qtd,
                    'valor_unitario' => $valorUnit,
                    'valor_total' => $totalItem,
                    'resultado' => 'Aplicado',
                    'sort_order' => $order++,
                ]);

                $somatoriaPecas += $totalItem;
            }

            // 3. Criar lançamentos de execução - Mão de obra
            foreach ($servicosExecutados as $srv) {
                $nome = trim((string) ($srv['nome'] ?? ''));
                if ($nome === '') continue;

                $qtd = max(1, (int) ($srv['qtd'] ?? 1));
                $valorUnit = max(0.0, (float) $this->normalizarMoeda($srv['valor'] ?? 0));
                $totalItem = round($qtd * $valorUnit, 2);

                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'veiculo_dano_id' => null,
                    'descricao_servico' => $nome,
                    'tipo_item' => 'servico',
                    'quantidade' => $qtd,
                    'valor_unitario' => $valorUnit,
                    'valor_total' => $totalItem,
                    'resultado' => 'Realizado',
                    'sort_order' => $order++,
                ]);

                $somatoriaMaoObra += $totalItem;
            }

            $valorTotal = round($somatoriaPecas + $somatoriaMaoObra, 2);

            $os->update([
                'status' => OrdemServico::STATUS_CONCLUIDA,
                'relatorio_conclusao' => trim($relatorioGeral) !== '' ? trim($relatorioGeral) : null,
                'valor_pecas' => $somatoriaPecas,
                'valor_mao_obra' => $somatoriaMaoObra,
                'valor_total' => $valorTotal,
                'user_id_encerramento' => Auth::id(),
                'data_conclusao' => now(),
            ]);

            $this->registrarDespesaFinanceira($os->fresh(), $valorTotal);

            // Anexos
            foreach ($anexosMeta as $meta) {
                $path = trim((string) ($meta['path'] ?? ''));
                if ($path === '') continue;

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

    private function normalizarMoeda($valor): float
    {
        if (is_numeric($valor)) {
            return (float) $valor;
        }
        $valor = str_replace('.', '', (string) $valor);
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }
}
