<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\GrupoFrota;
use App\Models\ItemManutencaoPreventiva;
use App\Models\PlanoManutencaoPreventiva;
use App\Models\PlanoManutencaoItem;

class PreventiveMaintenanceSeeder extends Seeder
{
    public function run()
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            // Criar itens comuns
            $oleo = ItemManutencaoPreventiva::updateOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => 'Troca de Óleo e Filtro'],
                ['descricao' => 'Substituição do óleo do motor e filtro de óleo.']
            );

            $pneus = ItemManutencaoPreventiva::updateOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => 'Alinhamento e Balanceamento'],
                ['descricao' => 'Alinhamento e balanceamento dos 4 pneus.']
            );

            $freios = ItemManutencaoPreventiva::updateOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => 'Revisão de Freios'],
                ['descricao' => 'Verificação e troca de pastilhas se necessário.']
            );

            $grupos = GrupoFrota::all();

            foreach ($grupos as $grupo) {
                $plano = PlanoManutencaoPreventiva::updateOrCreate(
                    ['empresa_id' => $empresa->id, 'grupo_frota_id' => $grupo->id],
                    ['nome' => 'Plano Padrão - ' . $grupo->nome, 'descricao' => 'Plano de manutenção preventiva padrão']
                );

                PlanoManutencaoItem::updateOrCreate(
                    ['plano_id' => $plano->id, 'item_id' => $oleo->id],
                    ['frequencia_meses' => 6, 'frequencia_km' => 10000]
                );

                PlanoManutencaoItem::updateOrCreate(
                    ['plano_id' => $plano->id, 'item_id' => $pneus->id],
                    ['frequencia_meses' => 12, 'frequencia_km' => 20000]
                );

                PlanoManutencaoItem::updateOrCreate(
                    ['plano_id' => $plano->id, 'item_id' => $freios->id],
                    ['frequencia_meses' => 12, 'frequencia_km' => 30000]
                );
            }
        }
    }
}
