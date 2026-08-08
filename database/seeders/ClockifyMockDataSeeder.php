<?php

namespace Database\Seeders;

use App\Models\ClockifyCliente;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTag;
use App\Models\ClockifyTime;
use App\Models\ClockifyTimeOff;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClockifyMockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Vincular tudo à empresa 1
        $empresaId = 1;

        // 1. Garantir que os usuários principais e fictícios existam
        $password = Hash::make('password123');

        $usersData = [
            // Membros fictícios solicitados
            [
                'nome' => 'Juliana Martins',
                'email' => 'juliana.martins@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Lucas Ferreira',
                'email' => 'lucas.ferreira@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Beatriz Almeida',
                'email' => 'beatriz.almeida@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Amanda Ribeiro',
                'email' => 'amanda.ribeiro@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Carlos Mendes',
                'email' => 'carlos.mendes@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            // Usuários do sistema citados que podem não existir ainda
            [
                'nome' => 'Erickson Ferreira da Silva Nascimento',
                'email' => 'erickson@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Paulo da Silva Santana Arraes',
                'email' => 'paulo@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Gestor Todas Estações',
                'email' => 'gestor@xpandcars.test',
                'password' => $password,
                'empresa_id' => $empresaId,
            ]
        ];

        $users = [];
        foreach ($usersData as $uData) {
            // Tenta buscar por e-mail ou nome similar
            $user = User::where('email', $uData['email'])
                ->orWhere('nome', $uData['nome'])
                ->first();

            if (!$user) {
                $user = User::create($uData);
            }
            $users[$uData['nome']] = $user;
        }

        // 2. Cadastrar Clientes
        $clientesData = [
            [
                'nome' => 'Alfa Contabilidade Empresarial',
                'email' => 'contato@alfacontabilidade.test',
                'telefone' => '(81) 98845-1200',
                'endereco' => 'Av. Agamenon Magalhães, 1250, Recife - PE',
                'ativo' => true,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Clínica Vida Mais',
                'email' => 'administrativo@vidamais.test',
                'telefone' => '(81) 99710-4488',
                'endereco' => 'Rua do Futuro, 450, Graças, Recife - PE',
                'ativo' => true,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Nordeste Auto Peças',
                'email' => 'comercial@nordesteautopecas.test',
                'telefone' => '(81) 3224-9080',
                'endereco' => 'Av. Norte, 2350, Recife - PE',
                'ativo' => true,
                'empresa_id' => $empresaId,
            ],
            [
                'nome' => 'Construtora Monte Azul',
                'email' => 'contato@monteazul.test',
                'telefone' => '(81) 99875-6630',
                'endereco' => 'Rua das Palmeiras, 780, Boa Viagem, Recife - PE',
                'ativo' => true,
                'empresa_id' => $empresaId,
            ],
        ];

        $clientes = [];
        foreach ($clientesData as $cData) {
            $clientes[$cData['nome']] = ClockifyCliente::firstOrCreate(
                ['nome' => $cData['nome']],
                $cData
            );
        }

        // 3. Cadastrar Equipes / Times
        $timesData = [
            [
                'nome' => 'Time de Desenvolvimento',
                'descricao' => 'Responsável pelo desenvolvimento e manutenção dos sistemas.',
                'membros' => [
                    'Erickson Ferreira da Silva Nascimento',
                    'Lucas Ferreira',
                    'Paulo da Silva Santana Arraes'
                ]
            ],
            [
                'nome' => 'Time de Projetos',
                'descricao' => 'Planejamento, acompanhamento e entrega dos projetos dos clientes.',
                'membros' => [
                    'Juliana Martins',
                    'Erickson Ferreira da Silva Nascimento',
                    'Beatriz Almeida'
                ]
            ],
            [
                'nome' => 'Comercial e Atendimento',
                'descricao' => 'Prospecção, atendimento e relacionamento com clientes.',
                'membros' => [
                    'Carlos Mendes',
                    'Amanda Ribeiro',
                    'Gestor Todas Estações'
                ]
            ],
            [
                'nome' => 'Marketing',
                'descricao' => 'Gestão de campanhas, conteúdo e divulgação.',
                'membros' => [
                    'Amanda Ribeiro',
                    'Beatriz Almeida'
                ]
            ]
        ];

        foreach ($timesData as $tData) {
            $team = ClockifyTime::firstOrCreate(
                ['nome' => $tData['nome']],
                [
                    'nome' => $tData['nome'],
                    'descricao' => $tData['descricao'],
                    'empresa_id' => $empresaId,
                ]
            );

            // Sincronizar membros no time
            $userIds = [];
            foreach ($tData['membros'] as $membroNome) {
                if (isset($users[$membroNome])) {
                    $userIds[] = $users[$membroNome]->id;
                }
            }
            $team->users()->sync($userIds);
        }

        // 4. Cadastrar Tags
        $tagsList = [
            'Cliente VIP',
            'Novo Cliente',
            'Em Implantação',
            'Em Desenvolvimento',
            'Aguardando Cliente',
            'Urgente',
            'Alta Prioridade',
            'Manutenção',
            'Suporte',
            'Marketing',
            'Financeiro',
            'Site',
            'Sistema Web',
            'Aplicativo',
            'Concluído',
            'Em Análise',
        ];

        foreach ($tagsList as $tagName) {
            ClockifyTag::firstOrCreate(
                ['nome' => $tagName],
                [
                    'nome' => $tagName,
                    'ativo' => true,
                    'empresa_id' => $empresaId,
                ]
            );
        }

        // 5. Cadastrar Projetos
        $projetosData = [
            [
                'nome' => 'Novo Site Alfa Contabilidade',
                'cliente' => 'Alfa Contabilidade Empresarial',
                'descricao' => 'Desenvolvimento de novo portal institucional da empresa, incluindo página de serviços, formulário de contato, área de notícias e integração com WhatsApp.',
                'cor' => '#007bff', // Azul
                'publico' => true,
            ],
            [
                'nome' => 'Sistema de Agendamentos Vida Mais',
                'cliente' => 'Clínica Vida Mais',
                'descricao' => 'Desenvolvimento de sistema para gerenciamento de pacientes, médicos, horários disponíveis, consultas e confirmações de atendimento.',
                'cor' => '#28a745', // Verde
                'publico' => false,
            ],
            [
                'nome' => 'Dashboard Comercial Nordeste Auto Peças',
                'cliente' => 'Nordeste Auto Peças',
                'descricao' => 'Desenvolvimento de dashboard para acompanhamento de vendas, faturamento, metas mensais, vendedores e principais produtos comercializados.',
                'cor' => '#fd7e14', // Laranja
                'publico' => true,
            ],
            [
                'nome' => 'Portal de Obras Monte Azul',
                'cliente' => 'Construtora Monte Azul',
                'descricao' => 'Portal para gerenciamento das obras da construtora, acompanhamento de etapas, documentos, custos, responsáveis e evolução dos projetos.',
                'cor' => '#6f42c1', // Roxo
                'publico' => false,
            ],
        ];

        foreach ($projetosData as $pData) {
            $clienteId = isset($clientes[$pData['cliente']]) ? $clientes[$pData['cliente']]->id : null;

            ClockifyProjeto::firstOrCreate(
                ['nome' => $pData['nome']],
                [
                    'nome' => $pData['nome'],
                    'clockify_cliente_id' => $clienteId,
                    'descricao' => $pData['descricao'],
                    'cor' => $pData['cor'],
                    'publico' => $pData['publico'],
                    'ativo' => true,
                    'empresa_id' => $empresaId,
                ]
            );
        }

        // 6. Cadastrar Time Off
        $timeOffData = [
            [
                'membro' => 'Erickson Ferreira da Silva Nascimento',
                'tipo' => 'Férias',
                'data_inicio' => '2026-08-17',
                'data_fim' => '2026-08-28',
                'observacao' => 'Período de férias programadas referente ao período aquisitivo vigente.',
            ],
            [
                'membro' => 'Lucas Ferreira',
                'tipo' => 'Folga',
                'data_inicio' => '2026-08-14',
                'data_fim' => '2026-08-14',
                'observacao' => 'Folga compensatória referente às horas adicionais realizadas na implantação do projeto Alfa.',
            ],
            [
                'membro' => 'Juliana Martins',
                'tipo' => 'Férias',
                'data_inicio' => '2026-09-01',
                'data_fim' => '2026-09-15',
                'observacao' => 'Férias previamente programadas com a gestão.',
            ]
        ];

        foreach ($timeOffData as $toData) {
            if (isset($users[$toData['membro']])) {
                ClockifyTimeOff::firstOrCreate(
                    [
                        'user_id' => $users[$toData['membro']]->id,
                        'data_inicio' => $toData['data_inicio'],
                        'data_fim' => $toData['data_fim']
                    ],
                    [
                        'user_id' => $users[$toData['membro']]->id,
                        'tipo' => $toData['tipo'],
                        'data_inicio' => $toData['data_inicio'],
                        'data_fim' => $toData['data_fim'],
                        'status' => 'Aprovado',
                        'observacao' => $toData['observacao'],
                        'empresa_id' => $empresaId,
                    ]
                );
            }
        }
    }
}
