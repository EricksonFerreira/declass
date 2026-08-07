<?php

namespace Database\Seeders;

use App\Models\ItemPlano;
use App\Models\Plano;
use Illuminate\Database\Seeder;

class PlanoSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        ItemPlano::truncate();
        Plano::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $planos = [
            [
                'nome'           => 'Starter',
                'slug'           => 'starter',
                'descricao'      => 'Plano inicial com até 10 veículos ativos, 3 usuários e os módulos essenciais da locadora.',
                'valor_mensal'   => 149.00,
                'valor_anual'    => 119.00,
                'limite_veiculos'=> 10,
                'limite_usuarios'=> 3,
                'site_vendas'    => false,
                'modulo_multas'  => false,
                'modulo_nfse'    => false,
                'multi_estacao'  => false,
                'api_integracao' => false,
                'ativo'          => true,
                'destaque'       => false,
                'ordem'          => 1,
                'itens'          => [
                    'Até 10 veículos ativos',
                    'Até 3 usuários simultâneos',
                    'Reservas e contratos digitais',
                    'Check-in e check-out com vistoria',
                    'Controle financeiro básico',
                    'Suporte via e-mail e WhatsApp',
                ],
            ],
            [
                'nome'           => 'Professional',
                'slug'           => 'professional',
                'descricao'      => 'Plano completo com usuários ilimitados, até 3 filiais, site de reservas e módulos avançados.',
                'valor_mensal'   => 249.00,
                'valor_anual'    => 199.00,
                'limite_veiculos'=> null,
                'limite_usuarios'=> null,
                'site_vendas'    => true,
                'modulo_multas'  => true,
                'modulo_nfse'    => true,
                'multi_estacao'  => true,
                'api_integracao' => true,
                'ativo'          => true,
                'destaque'       => true,
                'ordem'          => 2,
                'itens'          => [
                    'Frota e veículos ilimitados',
                    'Usuários ilimitados',
                    'Até 3 filiais / estações',
                    'Site de reservas online integrado',
                    'Módulo de multas de trânsito',
                    'NFS-e automática nacional',
                    'API de integração completa',
                    'Suporte prioritário via chat e WhatsApp',
                ],
            ],
        ];

        foreach ($planos as $dadosPlano) {
            $itens = $dadosPlano['itens'];
            unset($dadosPlano['itens']);

            $plano = Plano::create($dadosPlano);

            foreach ($itens as $index => $descricao) {
                ItemPlano::create([
                    'plano_id'  => $plano->id,
                    'descricao' => $descricao,
                    'incluso'   => true,
                    'ordem'     => $index + 1,
                ]);
            }
        }
    }
}
