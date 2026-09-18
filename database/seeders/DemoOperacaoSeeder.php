<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Popula a empresa com ~3 meses de operacao realista:
 * clientes, projetos, estimativas, time, tags, apontamentos,
 * folgas/ferias e alocacoes de calendario.
 *
 * Uso: php artisan db:seed --class=DemoOperacaoSeeder
 * Empresa alvo: env DEMO_EMPRESA_ID (padrao 1).
 * Limpeza: php artisan db:seed --class=DemoLimpezaSeeder
 */
class DemoOperacaoSeeder extends Seeder
{
    private int $empresaId;

    private ?int $empresaIdOverride = null;

    /** Sufixo que torna os e-mails demo unicos por empresa ('.e1157'). */
    private string $emailSufixo = '';

    /** @var array<int> ids dos usuarios demo criados */
    private array $demoUserIds = [];

    /** @var array<int> ids dos projetos demo criados */
    private array $projetoIds = [];

    private const EMAIL_DOMAIN = '@demo.declock.app';

    /**
     * Aponta o seeder para outra empresa. Como e-mails sao globalmente
     * unicos, empresas diferentes da 1 recebem sufixo no login
     * (ana.souza.e1157@demo.declock.app) — continua coberto pelo
     * padrao '%@demo.declock.app' do DemoLimpezaSeeder.
     */
    public function paraEmpresa(int $empresaId): static
    {
        $this->empresaIdOverride = $empresaId;
        $this->emailSufixo = $empresaId === 1 ? '' : '.e'.$empresaId;

        return $this;
    }

    private const DESCRICOES = [
        'dev' => [
            'Fiscalizacao da execucao de fundacoes',
            'Levantamento quantitativo de materiais',
            'Analise e revisao de projeto estrutural',
            'Visita tecnica a obra — medicao de servicos',
            'Elaboracao de relatorio de progresso',
            'Reuniao de alinhamento com a fiscalizacao',
            'Revisao de memorial descritivo',
            'Acompanhamento de concretagem em campo',
            'Verificacao de especificacoes tecnicas',
            'Atualizacao do cronograma fisico-financeiro',
        ],
        'design' => [
            'Modelagem BIM da arquitetura',
            'Detalhamento de projeto estrutural',
            'Compatibilizacao de projetos complementares',
            'Revisao de pranchas de instalacoes',
            'Atualizacao do modelo apos alteracao',
            'Exportacao de pranchas para aprovacao',
        ],
        'gestao' => [
            'Reuniao de planejamento da etapa',
            'Atualizacao do cronograma da obra',
            'Follow-up com cliente e fiscalizacao',
            'Revisao da medicao do periodo',
            'Apresentacao de progresso para diretoria',
            'Visita tecnica de acompanhamento',
        ],
        'qa' => [
            'Inspecao de qualidade do concreto',
            'Verificacao de armaduras e formas',
            'Coleta de corpos de prova para ensaio',
            'Checklist de seguranca NR-18',
            'Controle tecnologico de materiais',
        ],
    ];

    public function run(): void
    {
        $this->empresaId = $this->empresaIdOverride ?? (int) env('DEMO_EMPRESA_ID', 1);

        $this->command->info("Semeando dados demo na empresa {$this->empresaId}...");

        $funcoes = $this->seedFuncoes();
        $userIds = $this->seedUsuarios($funcoes);
        $clienteIds = $this->seedClientes();
        $projetoIds = $this->seedProjetos($clienteIds);
        $this->seedEstimativas($projetoIds, $funcoes);
        $this->seedTimes($userIds);
        $this->seedProjetoUser($projetoIds, $userIds);
        $tagIds = DB::table('clockify_tags')->where('empresa_id', $this->empresaId)->where('ativo', 1)->pluck('id')->all();
        $this->seedTrackers($projetoIds, $userIds, $funcoes, $tagIds);
        $this->seedTimeOffs($userIds);
        $this->seedCalendarios($projetoIds, $userIds);

        $this->command->info('Demo semeada: '.count($userIds).' usuarios, '.count($projetoIds).' projetos.');
    }

    /**
     * Funcoes com custo_hora. Reaproveita as existentes e
     * complementa com QA/Gestao se faltarem.
     */
    private function seedFuncoes(): array
    {
        $mapa = [
            'dev' => ['nomes' => ['Engenheiro Civil', 'Técnico', 'Desenvolvedor', 'Eng. Auxiliar'], 'custo' => 95.00],
            'design' => ['nomes' => ['Projetista CAD/BIM', 'Designer'], 'custo' => 85.00],
            'gestao' => ['nomes' => ['Coordenador de Equipe', 'Gerente de Projetos', 'Coord. Equipe'], 'custo' => 120.00],
            'qa' => ['nomes' => ['Técnico de Edificações', 'Analista de QA', 'QA'], 'custo' => 75.00],
        ];

        $out = [];
        foreach ($mapa as $chave => $cfg) {
            $id = DB::table('funcoes')
                ->where('empresa_id', $this->empresaId)
                ->whereIn('nome', $cfg['nomes'])
                ->value('id');

            if (! $id) {
                $id = DB::table('funcoes')->insertGetId([
                    'empresa_id' => $this->empresaId,
                    'nome' => $cfg['nomes'][0],
                    'custo_hora' => $cfg['custo'],
                    'ativo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('funcoes')->where('id', $id)->whereNull('custo_hora')->update(['custo_hora' => $cfg['custo']]);
            }

            $out[$chave] = $id;
        }

        return $out;
    }

    /**
     * Seis colaboradores (tipo_usuario_id 8 = membro) distribuidos nas funcoes.
     */
    private function seedUsuarios(array $funcoes): array
    {
        $senha = Hash::make('password');
        $agora = now();

        $pessoas = [
            ['Ana Souza', 'ana.souza', 'Engenheira Civil', $funcoes['dev']],
            ['Bruno Carvalho', 'bruno.carvalho', 'Engenheiro Civil', $funcoes['dev']],
            ['Camila Duarte', 'camila.duarte', 'Projetista BIM', $funcoes['design']],
            ['Diego Nogueira', 'diego.nogueira', 'Técnico de Edificações', $funcoes['qa']],
            ['Fernanda Lima', 'fernanda.lima', 'Coordenadora de Equipe', $funcoes['gestao']],
            ['Rafael Teixeira', 'rafael.teixeira', 'Engenheiro Auxiliar', $funcoes['dev']],
        ];

        $ids = [];
        foreach ($pessoas as [$nome, $login, $cargo, $funcaoId]) {
            $email = $login.$this->emailSufixo.self::EMAIL_DOMAIN;

            $id = DB::table('users')->where('email', $email)->value('id');
            if (! $id) {
                $id = DB::table('users')->insertGetId([
                    'empresa_id' => $this->empresaId,
                    'tipo_usuario_id' => 8,
                    'role_id' => 8,
                    'nome' => $nome,
                    'cargo' => $cargo,
                    'funcao_id' => $funcaoId,
                    'email' => $email,
                    'password' => $senha,
                    'ativo' => 1,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }

            $ids[] = $id;
            $this->demoUserIds[] = $id;
        }

        return $ids;
    }

    private function seedClientes(): array
    {
        $agora = now();
        $clientes = [
            ['Construtora Horizonte', 'contato@construtora-horizonte.com.br'],
            ['Incorporadora Vila Nova', 'obras@vilanova-inc.com.br'],
            ['Hospital Regional Santa Cruz', 'engenharia@hrsantacruz.org.br'],
            ['Indústria MetalFort', 'projetos@metalfort.ind.br'],
        ];

        $ids = [];
        foreach ($clientes as [$nome, $email]) {
            $id = DB::table('clockify_clientes')
                ->where('empresa_id', $this->empresaId)
                ->where('nome', $nome)
                ->value('id');

            if (! $id) {
                $id = DB::table('clockify_clientes')->insertGetId([
                    'empresa_id' => $this->empresaId,
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => '(85) 9'.rand(8000, 9999).'-'.rand(1000, 9999),
                    'ativo' => 1,
                    'usuinc' => 1,
                    'datainc' => $agora,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }

            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * Projetos variados: em andamento, um concluido e um
     * prestes a estourar a estimativa (% consumido alto).
     */
    private function seedProjetos(array $clienteIds): array
    {
        $agora = now();
        $projetos = [
            [
                'nome' => 'Loteamento Residencial Vista Alegre — Lote 2',
                'cliente' => $clienteIds[0],
                'cor' => '#2563eb',
                'horas_orcadas' => 620,
                'valor_hora' => 110.00,
                'valor_faturado' => 68200.00,
                'entrega' => Carbon::now()->addDays(45)->toDateString(),
                'status' => 'em_andamento',
                'descricao' => 'Infraestrutura completa do lote 2: terraplenagem, redes de água e esgoto, pavimentação e drenagem.',
            ],
            [
                'nome' => 'Edifício Comercial Parque Central',
                'cliente' => $clienteIds[1],
                'cor' => '#16a34a',
                'horas_orcadas' => 560,
                'valor_hora' => 105.00,
                'valor_faturado' => 58800.00,
                'entrega' => Carbon::now()->addDays(90)->toDateString(),
                'status' => 'em_andamento',
                'descricao' => 'Gerenciamento de obra do edifício corporativo de 12 pavimentos.',
            ],
            [
                'nome' => 'Ampliação da Ala Sul — Hospital Regional',
                'cliente' => $clienteIds[2],
                'cor' => '#db2777',
                'horas_orcadas' => 480,
                'valor_hora' => 90.00,
                'valor_faturado' => 43200.00,
                'entrega' => Carbon::now()->subDays(20)->toDateString(),
                'status' => 'concluido',
                'descricao' => 'Ampliação de 800m² da ala sul com adaptação de instalações hospitalares.',
            ],
            [
                'nome' => 'Reforma da Unidade Fabril — Planta Norte',
                'cliente' => $clienteIds[3],
                'cor' => '#b45309',
                'horas_orcadas' => 700,
                'valor_hora' => 115.00,
                'valor_faturado' => 80500.00,
                'entrega' => Carbon::now()->addDays(30)->toDateString(),
                'status' => 'em_andamento',
                'descricao' => 'Retrofit estrutural e adequação de segurança da planta industrial.',
            ],
            [
                'nome' => 'Projeto Executivo de Instalações — Ala Norte',
                'cliente' => $clienteIds[2],
                'cor' => '#7c3aed',
                'horas_orcadas' => 850,
                'valor_hora' => 100.00,
                'valor_faturado' => 85000.00,
                'entrega' => Carbon::now()->addDays(60)->toDateString(),
                'status' => 'em_andamento',
                'descricao' => 'Projeto executivo de instalações hidrossanitárias, elétricas e de climatização.',
            ],
        ];

        $ids = [];
        foreach ($projetos as $p) {
            $id = DB::table('clockify_projetos')
                ->where('empresa_id', $this->empresaId)
                ->where('nome', $p['nome'])
                ->value('id');

            if (! $id) {
                $id = DB::table('clockify_projetos')->insertGetId([
                    'empresa_id' => $this->empresaId,
                    'clockify_cliente_id' => $p['cliente'],
                    'nome' => $p['nome'],
                    'descricao' => $p['descricao'],
                    'cor' => $p['cor'],
                    'publico' => 1,
                    'horas_orcadas' => $p['horas_orcadas'],
                    'valor_hora' => $p['valor_hora'],
                    'valor_faturado' => $p['valor_faturado'],
                    'data_entrega_previsao' => $p['entrega'],
                    'ativo' => 1,
                    'status' => $p['status'],
                    'usuinc' => 1,
                    'datainc' => $agora,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }

            $ids[] = $id;
            $this->projetoIds[] = $id;
        }

        return $ids;
    }

    /**
     * Estimativas por funcao. O projeto de e-commerce fica
     * subestimado propositalmente (consumo > 100%).
     */
    private function seedEstimativas(array $projetoIds, array $funcoes): void
    {
        $agora = now();
        $estimativas = [
            [$projetoIds[0], ['dev' => 200, 'design' => 60, 'gestao' => 40, 'qa' => 20]],
            [$projetoIds[1], ['dev' => 300, 'design' => 80, 'gestao' => 60, 'qa' => 40]],
            [$projetoIds[2], ['dev' => 50, 'design' => 55, 'gestao' => 15]],
            [$projetoIds[3], ['dev' => 120, 'gestao' => 30, 'qa' => 20]],
            [$projetoIds[4], ['dev' => 160, 'design' => 40, 'gestao' => 35, 'qa' => 25]],
        ];

        foreach ($estimativas as [$projetoId, $porFuncao]) {
            foreach ($porFuncao as $chave => $horas) {
                DB::table('clockify_projeto_estimativas')->updateOrInsert(
                    [
                        'empresa_id' => $this->empresaId,
                        'clockify_projeto_id' => $projetoId,
                        'funcao_id' => $funcoes[$chave],
                    ],
                    ['horas_estimadas' => $horas, 'updated_at' => $agora, 'created_at' => $agora]
                );
            }
        }
    }

    private function seedTimes(array $userIds): void
    {
        $agora = now();

        $timeId = DB::table('clockify_times')
            ->where('empresa_id', $this->empresaId)
            ->where('nome', 'Equipe de Fiscalização — Obras Sul')
            ->value('id');

        if (! $timeId) {
            $timeId = DB::table('clockify_times')->insertGetId([
                'empresa_id' => $this->empresaId,
                'nome' => 'Equipe de Fiscalização — Obras Sul',
                'descricao' => 'Equipe principal de campo e fiscalização de obras',
                'usuinc' => 1,
                'datainc' => $agora,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }

        foreach ($userIds as $uid) {
            DB::table('clockify_time_user')->updateOrInsert(
                ['clockify_time_id' => $timeId, 'user_id' => $uid],
                ['created_at' => $agora, 'updated_at' => $agora]
            );
        }
    }

    private function seedProjetoUser(array $projetoIds, array $userIds): void
    {
        $agora = now();
        foreach ($projetoIds as $pid) {
            // Cada projeto recebe 4-6 membros sorteados
            $membros = collect($userIds)->shuffle()->take(rand(4, count($userIds)));
            foreach ($membros as $uid) {
                DB::table('clockify_projeto_user')->updateOrInsert(
                    ['clockify_projeto_id' => $pid, 'user_id' => $uid],
                    ['created_at' => $agora, 'updated_at' => $agora]
                );
            }
        }
    }

    /**
     * ~3 meses de apontamentos: seg-sex, presenca ~85%, 6.5-9.5h/dia
     * em 1-3 entradas, sabados ocasionais e um timer ativo hoje.
     */
    private function seedTrackers(array $projetoIds, array $userIds, array $funcoes, array $tagIds): void
    {
        $agora = now();
        $inicio = Carbon::now()->startOfWeek()->subMonths(3);
        $fim = Carbon::now();

        $funcaoPorUser = DB::table('users')->whereIn('id', $userIds)->pluck('funcao_id', 'id');
        $descricaoPorFuncao = [
            $funcoes['dev'] => self::DESCRICOES['dev'],
            $funcoes['design'] => self::DESCRICOES['design'],
            $funcoes['gestao'] => self::DESCRICOES['gestao'],
            $funcoes['qa'] => self::DESCRICOES['qa'],
        ];

        // Peso de projeto por usuario: cada pessoa tem 2 projetos preferidos
        $rows = [];
        $totalDias = $inicio->diffInDays($fim);

        for ($d = 0; $d <= $totalDias; $d++) {
            $dia = (clone $inicio)->addDays($d);
            $dow = $dia->dayOfWeekIso; // 1=seg ... 7=dom

            if ($dow >= 6) {
                // Sabado: 10% de chance de meio periodo para um dev
                if ($dow === 7 || rand(1, 100) > 10) {
                    continue;
                }
            }

            foreach ($userIds as $uid) {
                // Presenca ~85% em dias uteis; sabado so o primeiro dev
                if ($dow <= 5 && rand(1, 100) > 85) {
                    continue;
                }
                if ($dow === 6 && $uid !== $userIds[0]) {
                    continue;
                }

                $projetosDoDia = collect($projetoIds)->shuffle()->take(rand(1, 3));
                $horasDia = $dow === 6 ? rand(2, 4) : (rand(65, 95) / 10);

                // Divide as horas do dia entre os projetos
                $restante = $horasDia;
                $slots = $projetosDoDia->count();
                $cursor = (clone $dia)->hour(rand(8, 9))->minute(rand(0, 59));

                foreach ($projetosDoDia->values() as $i => $pid) {
                    $horas = $i === $slots - 1
                        ? $restante
                        : round($restante / ($slots - $i) * (rand(80, 120) / 100), 2);
                    $horas = min($horas, $restante);
                    $restante -= $horas;
                    if ($horas < 0.5) {
                        continue;
                    }

                    $ini = (clone $cursor);
                    $fimEntry = (clone $ini)->addMinutes((int) round($horas * 60));
                    $cursor = (clone $fimEntry)->addMinutes(rand(10, 90)); // pausa/almoço

                    $funcaoId = $funcaoPorUser[$uid] ?? null;
                    $descs = $descricaoPorFuncao[$funcaoId] ?? self::DESCRICOES['dev'];

                    $rows[] = [
                        'empresa_id' => $this->empresaId,
                        'user_id' => $uid,
                        'clockify_projeto_id' => $pid,
                        'descricao' => $descs[array_rand($descs)],
                        'inicio' => $ini->toDateTimeString(),
                        'fim' => $fimEntry->toDateTimeString(),
                        'faturavel' => rand(1, 100) <= 80 ? 1 : 0,
                        'usuinc' => $uid,
                        'datainc' => $agora,
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ];
                }
            }
        }

        // Um timer ATIVO hoje (sem fim) pra aparecer em "Rastreadores Ativos"
        $rows[] = [
            'empresa_id' => $this->empresaId,
            'user_id' => $userIds[0],
            'clockify_projeto_id' => $projetoIds[0],
            'descricao' => 'Fiscalização da execução de fundações',
            'inicio' => Carbon::now()->subHours(2)->toDateTimeString(),
            'fim' => null,
            'faturavel' => 1,
            'usuinc' => $userIds[0],
            'datainc' => $agora,
            'created_at' => $agora,
            'updated_at' => $agora,
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('clockify_time_trackers')->insert($chunk);
        }

        // Tags em ~15% dos apontamentos demo
        if ($tagIds) {
            $trackerIds = DB::table('clockify_time_trackers')
                ->where('empresa_id', $this->empresaId)
                ->whereIn('user_id', $userIds)
                ->pluck('id');

            $tagRows = [];
            foreach ($trackerIds->random(min((int) ($trackerIds->count() * 0.15), $trackerIds->count())) as $tid) {
                $tagRows[] = [
                    'clockify_time_tracker_id' => $tid,
                    'clockify_tag_id' => $tagIds[array_rand($tagIds)],
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];
            }
            if ($tagRows) {
                DB::table('clockify_tracker_tag')->insert($tagRows);
            }
        }

        $this->command->info('Apontamentos criados: '.count($rows));
    }

    /**
     * Folgas: ferias passada aprovada, afastamento medico,
     * ferias futura aprovada e um pedido pendente.
     */
    private function seedTimeOffs(array $userIds): void
    {
        $agora = now();
        $rows = [
            [$userIds[1], 'ferias', Carbon::now()->subDays(45), Carbon::now()->subDays(36), 'aprovado', 'Férias anuais - primeira parcela'],
            [$userIds[3], 'medico', Carbon::now()->subDays(20), Carbon::now()->subDays(18), 'aprovado', 'Afastamento por procedimento cirúrgico'],
            [$userIds[0], 'ferias', Carbon::now()->addDays(30), Carbon::now()->addDays(39), 'aprovado', 'Férias anuais - segunda parcela'],
            [$userIds[4], 'pessoal', Carbon::now()->addDays(10), Carbon::now()->addDays(10), 'pendente', 'Assunto pessoal - mudança de residência'],
            [$userIds[5], 'ferias', Carbon::now()->addDays(60), Carbon::now()->addDays(74), 'pendente', 'Férias de fim de ano'],
        ];

        foreach ($rows as [$uid, $tipo, $ini, $fim, $status, $obs]) {
            DB::table('clockify_time_offs')->updateOrInsert(
                [
                    'empresa_id' => $this->empresaId,
                    'user_id' => $uid,
                    'tipo' => $tipo,
                    'data_inicio' => $ini->toDateString(),
                    'data_fim' => $fim->toDateString(),
                ],
                [
                    'status' => $status,
                    'observacao' => $obs,
                    'usuinc' => 1,
                    'datainc' => $agora,
                    'updated_at' => $agora,
                    'created_at' => $agora,
                ]
            );
        }
    }

    /**
     * Alocacoes de calendario nas proximas 2 semanas.
     */
    private function seedCalendarios(array $projetoIds, array $userIds): void
    {
        $agora = now();
        $alocacoes = [
            [$userIds[0], $projetoIds[0], Carbon::now()->addDays(1), Carbon::now()->addDays(10), 4.0],
            [$userIds[1], $projetoIds[1], Carbon::now()->addDays(3), Carbon::now()->addDays(14), 6.0],
            [$userIds[2], $projetoIds[4], Carbon::now()->addDays(2), Carbon::now()->addDays(8), 3.0],
        ];

        foreach ($alocacoes as [$uid, $pid, $ini, $fim, $horas]) {
            DB::table('clockify_calendarios')->updateOrInsert(
                [
                    'empresa_id' => $this->empresaId,
                    'user_id' => $uid,
                    'clockify_projeto_id' => $pid,
                    'data_inicio' => $ini->toDateString(),
                    'data_fim' => $fim->toDateString(),
                ],
                [
                    'horas_por_dia' => $horas,
                    'observacao' => 'Alocação planejada da etapa',
                    'trabalho_junto' => 0,
                    'usuinc' => 1,
                    'datainc' => $agora,
                    'updated_at' => $agora,
                    'created_at' => $agora,
                ]
            );
        }
    }
}
