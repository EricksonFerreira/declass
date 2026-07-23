<?php

namespace App\Observers;

use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use App\Models\ExecucaoManutencaoPreventiva;
use App\Models\PendenciaManutencaoPreventiva;
use App\Models\PlanoManutencaoPreventiva;
use App\Models\PlanoManutencaoItem;
use Carbon\Carbon;

class OrdemServicoObserver
{
    public function updated(OrdemServico $os)
    {
        if ($os->isDirty('status') && $os->status === OrdemServico::STATUS_CONCLUIDA) {
            $this->processPreventiveMaintenance($os);
        }
    }

    private function processPreventiveMaintenance(OrdemServico $os)
    {
        // Pega todos os itens dessa OS que estejam linkados a uma pendencia de preventiva
        $itens = $os->itens()->whereNotNull('pendencia_manutencao_preventiva_id')->get();

        foreach ($itens as $itemOs) {
            $pendencia = PendenciaManutencaoPreventiva::find($itemOs->pendencia_manutencao_preventiva_id);
            
            if (!$pendencia) {
                continue;
            }

            // Cria execução
            $execucao = ExecucaoManutencaoPreventiva::create([
                'empresa_id' => $os->empresa_id,
                'veiculo_id' => $os->veiculo_id,
                'item_id' => $pendencia->item_id,
                'ordem_servico_id' => $os->id,
                'data_execucao' => $os->data_conclusao ?: now(),
                'km_execucao' => $os->veiculo->kms,
            ]);

            // Atualiza status da pendencia original para resolvida (excluir ou marcar concluida)
            // Aqui opto por excluir para limpar, ou manter como historico? A model tem status.
            $pendencia->status = 'concluida';
            $pendencia->save();

            // Recalcula próxima pendencia
            $this->gerarProximaPendencia($pendencia, $execucao);
        }
    }

    private function gerarProximaPendencia(PendenciaManutencaoPreventiva $pendenciaAnterior, ExecucaoManutencaoPreventiva $execucao)
    {
        $veiculo = $execucao->veiculo;
        $grupoId = $veiculo->grupo_frota_id;
        
        // Localiza plano
        $plano = PlanoManutencaoPreventiva::where('grupo_frota_id', $grupoId)
            ->where('empresa_id', $execucao->empresa_id)
            ->first();

        if (!$plano) {
            return;
        }

        $planoItem = PlanoManutencaoItem::where('plano_id', $plano->id)
            ->where('item_id', $execucao->item_id)
            ->first();

        if (!$planoItem) {
            return;
        }

        $dataPrevista = null;
        if ($planoItem->frequencia_meses) {
            $dataPrevista = Carbon::parse($execucao->data_execucao)->addMonths($planoItem->frequencia_meses);
        }

        $kmPrevisto = null;
        if ($planoItem->frequencia_km) {
            $kmPrevisto = $execucao->km_execucao + $planoItem->frequencia_km;
        }

        PendenciaManutencaoPreventiva::create([
            'empresa_id' => $execucao->empresa_id,
            'veiculo_id' => $execucao->veiculo_id,
            'item_id' => $execucao->item_id,
            'data_prevista' => $dataPrevista,
            'km_previsto' => $kmPrevisto,
            'status' => 'pendente'
        ]);
    }
}
