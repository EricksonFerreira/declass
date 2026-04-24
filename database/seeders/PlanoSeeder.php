<?php

namespace Database\Seeders;

use App\Models\ItemPlano;
use App\Models\Plano;
use Illuminate\Database\Seeder;

class PlanoSeeder extends Seeder
{
    public function run(): void
    {
        $planos = [
            [
                'nome'           => 'Gestão',
                'slug'           => 'gestao',
                'descricao'      => 'Para quem quer começar com o sistema administrativo e deixar o site de vendas para uma próxima etapa.',
                'valor_mensal'   => 247.00,
                'valor_anual'    => 197.00,
                'limite_veiculos'=> null,
                'site_vendas'    => false,
                'modulo_multas'  => false,
                'modulo_nfse'    => false,
                'multi_estacao'  => false,
                'api_integracao' => false,
                'ativo'          => true,
                'destaque'       => false,
                'ordem'          => 1,
                'itens'          => [
                    'Gestão da frota',
                    'Reservas e contratos',
                    'Check-in digital',
                    'Financeiro básico',
                    'Suporte via chat',
                ],
            ],
            [
                'nome'           => 'Essencial',
                'slug'           => 'essencial',
                'descricao'      => 'Para locadoras que querem começar com presença digital profissional e reservas online no mesmo pacote.',
                'valor_mensal'   => 397.00,
                'valor_anual'    => 317.00,
                'limite_veiculos'=> 20,
                'site_vendas'    => true,
                'modulo_multas'  => false,
                'modulo_nfse'    => false,
                'multi_estacao'  => false,
                'api_integracao' => false,
                'ativo'          => true,
                'destaque'       => false,
                'ordem'          => 2,
                'itens'          => [
                    'Até 20 veículos',
                    'Reservas e contratos',
                    'Check-in digital',
                    'Financeiro básico',
                    'Site de vendas com 1 modelo',
                    'Personalização de cores e textos',
                    'Suporte via chat',
                ],
            ],
            [
                'nome'           => 'Crescimento',
                'slug'           => 'crescimento',
                'descricao'      => 'Para locadoras que querem vender mais, acelerar reservas e ter um site com cara de marca forte.',
                'valor_mensal'   => 697.00,
                'valor_anual'    => 557.00,
                'limite_veiculos'=> 80,
                'site_vendas'    => true,
                'modulo_multas'  => true,
                'modulo_nfse'    => true,
                'multi_estacao'  => true,
                'api_integracao' => false,
                'ativo'          => true,
                'destaque'       => true,
                'ordem'          => 3,
                'itens'          => [
                    'Até 80 veículos',
                    'Tudo do Essencial',
                    'Módulo de multas',
                    'NFS-e automática',
                    'Portal de reservas para clientes',
                    'Até 3 modelos de site',
                    'Customização completa de branding',
                    'Multi-estação / filiais',
                    'Relatórios avançados',
                    'Suporte prioritário',
                ],
            ],
            [
                'nome'           => 'Escala',
                'slug'           => 'escala',
                'descricao'      => 'Para operações que precisam de uma vitrine white-label, integrações e atendimento dedicado.',
                'valor_mensal'   => 1297.00,
                'valor_anual'    => 1037.00,
                'limite_veiculos'=> null,
                'site_vendas'    => true,
                'modulo_multas'  => true,
                'modulo_nfse'    => true,
                'multi_estacao'  => true,
                'api_integracao' => true,
                'ativo'          => true,
                'destaque'       => false,
                'ordem'          => 4,
                'itens'          => [
                    'Frota ilimitada',
                    'Tudo do Crescimento',
                    'API de integração',
                    'Site white-label de reservas',
                    'Customizações sob medida',
                    'Gerente de conta dedicado',
                    'SLA 99,9% garantido',
                ],
            ],
        ];

        foreach ($planos as $dadosPlano) {
            $itens = $dadosPlano['itens'];
            unset($dadosPlano['itens']);

            $plano = Plano::updateOrCreate(
                ['slug' => $dadosPlano['slug']],
                $dadosPlano
            );

            // Remove itens antigos e recria para manter consistência ao re-rodar o seeder.
            $plano->itens()->delete();

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
