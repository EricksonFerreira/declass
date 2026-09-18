<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\Plano;
use App\Models\Regra;
use App\Services\EmpresaPlanoService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Popula uma EMPRESA DEMO COMPLETA — tudo que um cliente precisa ver
 * em uma apresentação do sistema: empresa, plano ativo, admin, perfis
 * de acesso, usuários com papéis e jornadas variadas, clientes,
 * projetos (andamento/concluído/cancelado/privado), estimativas,
 * equipes, meses de lançamentos de horas, tags, folgas/férias,
 * alocações de calendário e um cronograma de planejamento.
 *
 * Idempotente: identifica a empresa pelo slug "demo" e só insere o
 * volume de lançamentos quando a empresa ainda não tem nenhum.
 *
 * Credenciais de demonstração: admin@demo.declock.app / password
 */
class DemoEmpresaSeeder extends Seeder
{
    private const SLUG = 'demo';

    private const EMAIL_DOMINIO = '@demo.declock.app';

    private int $empresaId;

    private array $usuarios = [];

    private array $projetos = [];

    public function run(): void
    {
        $empresa = $this->seedEmpresa();
        $this->empresaId = $empresa->id;

        $roles = $this->seedRegras();
        $admin = $this->seedAdmin($roles['administrador']);

        $this->seedAssinatura($empresa, $admin);
        $this->seedOnboarding();
        $this->seedTags();

        $jaPopulada = DB::table('clockify_time_trackers')
            ->where('empresa_id', $this->empresaId)
            ->exists();

        if (! $jaPopulada) {
            $this->seedOperacao();
        } else {
            $this->command?->info('Empresa demo já possui lançamentos — operação base mantida.');
        }

        $this->posProcessamento($roles, $admin);
        $this->resumo($empresa);
    }

    private function seedEmpresa(): Empresa
    {
        $empresa = Empresa::firstOrNew(['slug' => self::SLUG]);
        $empresa->fill([
            'nome' => 'Atlas Projetos e Engenharia',
            'ativo' => 'S',
            'cnpj' => '12.345.678/0001-90',
            'email' => 'contato@atlas.eng.br',
            'telefone' => '(11) 3456-7890',
            'representante' => 'Marcos Vieira',
            'endereco' => 'Av. Paulista, 1500 — Bela Vista, São Paulo/SP',
            'cor_primaria' => '#0d6efd',
        ]);
        $empresa->save();

        $this->command?->info("Empresa demo: {$empresa->nome} (id {$empresa->id})");

        return $empresa;
    }

    private function seedRegras(): array
    {
        $roles = [];
        foreach (['administrador', 'lider', 'colaborador', 'visualizador'] as $nome) {
            $roles[$nome] = Regra::firstOrCreate(
                ['name' => $nome, 'guard_name' => 'web']
            )->id;
        }

        return $roles;
    }

    private function seedAdmin(int $roleId): object
    {
        $attrs = [
            'nome' => 'Marcos Vieira',
            'celular' => '(11) 99876-5432',
            'password' => Hash::make('password'),
            'empresa_id' => $this->empresaId,
            'role_id' => $roleId,
            'cargo' => 'Diretor',
            'ativo' => true,
            'forcar_troca_senha' => false,
            'jornada_diaria' => 8,
        ];

        DB::table('users')->updateOrInsert(['email' => 'admin'.self::EMAIL_DOMINIO], $attrs);
        $admin = DB::table('users')->where('email', 'admin'.self::EMAIL_DOMINIO)->first();
        $this->usuarios['admin'] = $admin;

        $this->command?->info('Admin demo: admin@demo.declock.app / password');

        return $admin;
    }

    private function seedAssinatura(Empresa $empresa, object $admin): void
    {
        if (EmpresaPlano::where('empresa_id', $this->empresaId)->exists()) {
            return;
        }

        $plano = Plano::where('ativo', true)->where('slug', '<>', 'trial')
            ->orderByDesc('ordem')->first()
            ?? Plano::where('ativo', true)->first();

        if (! $plano) {
            $this->command?->warn('Nenhum plano ativo encontrado — assinatura não criada.');

            return;
        }

        $assinatura = app(EmpresaPlanoService::class)->contratar(
            $empresa, $plano, 'mensal', 'cartao', null, $admin->id, [], false, false
        );

        // A demo deve aparecer como um cliente ativo, não em trial.
        $assinatura->update(['status' => 'ativo']);
        $assinatura->pagamentos()->update(['status' => 'pago', 'pago_em' => now()]);

        $this->command?->info("Plano ativo: {$plano->nome}");
    }

    private function seedOnboarding(): void
    {
        DB::table('onboarding_steps')->updateOrInsert(
            ['empresa_id' => $this->empresaId],
            [
                'step_empresa_configurada' => true,
                'step_plano_contratado' => true,
                'step_pagamento_realizado' => true,
                'step_frota_cadastrada' => true,
                'step_estacao_cadastrada' => true,
                'step_usuario_criado' => true,
                'concluido' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedTags(): void
    {
        $tags = [
            'Desenvolvimento', 'Reunião', 'Suporte', 'Documentação',
            'Homologação', 'Urgente', 'Interno', 'Não faturável',
        ];

        foreach ($tags as $nome) {
            DB::table('clockify_tags')->updateOrInsert(
                ['empresa_id' => $this->empresaId, 'nome' => $nome],
                ['ativo' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    /**
     * Delega a carga operacional (usuários, clientes, projetos, estimativas,
     * equipe, ~3 meses de lançamentos, folgas e calendário) ao seeder
     * especialista, apontando-o para a empresa demo.
     */
    private function seedOperacao(): void
    {
        $seeder = (new DemoOperacaoSeeder)->paraEmpresa($this->empresaId);
        if ($this->command) {
            $seeder->setCommand($this->command);
        }
        $seeder->run();
    }

    /**
     * Complementa a operação base com o que falta para a demo ficar
     * completa: perfis corretos, códigos de projeto, responsáveis,
     * usuários extras com jornadas diferentes, projeto privado e
     * cancelado, e um cronograma de planejamento.
     */
    private function posProcessamento(array $roles, object $admin): void
    {
        // Os usuários da operação podem ter sufixo ".e{id}" no e-mail.
        $fernanda = DB::table('users')
            ->where('empresa_id', $this->empresaId)
            ->where('email', 'like', 'fernanda.lima%')->first();
        if ($fernanda) {
            DB::table('users')->where('id', $fernanda->id)->update(['role_id' => $roles['lider']]);
        }

        DB::table('users')->where('empresa_id', $this->empresaId)
            ->where('email', 'like', '%'.self::EMAIL_DOMINIO)
            ->whereNotIn('id', array_filter([$admin->id, $fernanda?->id]))
            ->update(['role_id' => $roles['colaborador']]);

        $paulo = $this->seedUsuarioExtra($roles['visualizador'], [
            'nome' => 'Paulo Diretor', 'cargo' => 'Diretor Comercial',
        ], 'paulo.diretor');

        $julia = $this->seedUsuarioExtra($roles['colaborador'], [
            'nome' => 'Julia Costa', 'cargo' => 'Estagiária', 'jornada_diaria' => 4,
        ], 'julia.costa');

        $this->seedProjetosExtras($admin, $fernanda);
        $this->atribuirCodigosEResponsaveis($admin, $fernanda);
        $this->seedCronograma();
        $this->seedTrackersExtras($admin, $fernanda, $julia);
    }

    private function seedUsuarioExtra(int $roleId, array $attrs, string $login): object
    {
        $attrs = array_merge([
            'celular' => '(11) 9'.random_int(1000, 9999).'-'.random_int(1000, 9999),
            'password' => Hash::make('password'),
            'empresa_id' => $this->empresaId,
            'role_id' => $roleId,
            'ativo' => true,
            'forcar_troca_senha' => false,
        ], $attrs);

        DB::table('users')->updateOrInsert(['email' => $login.self::EMAIL_DOMINIO], $attrs);

        return DB::table('users')->where('email', $login.self::EMAIL_DOMINIO)->first();
    }

    private function seedProjetosExtras(object $admin, ?object $fernanda): void
    {
        $clienteId = DB::table('clockify_clientes')
            ->where('empresa_id', $this->empresaId)->value('id');
        $liderId = $fernanda?->id ?? $admin->id;

        DB::table('clockify_projetos')->updateOrInsert(
            ['empresa_id' => $this->empresaId, 'nome' => 'Auditoria Interna — Compliance'],
            [
                'clockify_cliente_id' => $clienteId,
                'codigo' => '2025-060-200.01',
                'descricao' => 'Auditoria interna de processos e conformidade. Acesso restrito à diretoria.',
                'status' => 'em_andamento',
                'publico' => false,
                'responsavel_id' => $admin->id,
                'horas_orcadas' => 60,
                'data_entrega_previsao' => Carbon::today()->addDays(45),
                'horas_diarias_planejamento' => 3,
                'ativo' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('clockify_projetos')->updateOrInsert(
            ['empresa_id' => $this->empresaId, 'nome' => 'Portal Legado — Descontinuado'],
            [
                'clockify_cliente_id' => $clienteId,
                'codigo' => '2023-015-088.02',
                'descricao' => 'Projeto encerrado pelo cliente antes da entrega final.',
                'status' => 'cancelado',
                'publico' => true,
                'responsavel_id' => $liderId,
                'horas_orcadas' => 120,
                'ativo' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $privado = DB::table('clockify_projetos')
            ->where('empresa_id', $this->empresaId)
            ->where('nome', 'Auditoria Interna — Compliance')->first();

        if ($privado) {
            $this->projetos['privado'] = $privado;
            $membros = array_filter([$admin->id, $liderId]);
            foreach ($membros as $uid) {
                DB::table('clockify_projeto_user')->updateOrInsert(
                    ['clockify_projeto_id' => $privado->id, 'user_id' => $uid],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $cancelado = DB::table('clockify_projetos')
            ->where('empresa_id', $this->empresaId)
            ->where('nome', 'Portal Legado — Descontinuado')->first();
        if ($cancelado) {
            $this->projetos['cancelado'] = $cancelado;
        }
    }

    private function atribuirCodigosEResponsaveis(object $admin, ?object $fernanda): void
    {
        $liderId = $fernanda?->id ?? $admin->id;

        // nome => [codigo CC, responsavel, horas_orcadas]
        // O orçado é calibrado contra o consumo semeado (~530-680h/projeto)
        // para a demo mostrar percentuais variados no Consumo x Estimado:
        // ~80% confortável, ~90% no limite e >100% estourado.
        $mapa = [
            'Sistema de Agendamento de Consultas' => ['2024-087-101.01', $admin->id, 850],
            'Integração ERP × E-commerce' => ['2025-012-118.02', $admin->id, 700],
            'Portal de Orçamentos Online' => ['2025-033-142.05', $liderId, 620],
            'App de Rastreamento de Frota' => ['2025-041-158.01', $admin->id, 560],
            'Redesign do Site Institucional' => ['2024-156-099.03', $liderId, 480],
        ];

        foreach ($mapa as $nome => [$codigo, $responsavelId, $orcado]) {
            DB::table('clockify_projetos')
                ->where('empresa_id', $this->empresaId)->where('nome', $nome)
                ->update(['codigo' => $codigo, 'responsavel_id' => $responsavelId, 'horas_orcadas' => $orcado]);
        }

        // Estimativas por função coerentes com o orçado total do projeto.
        $estimativas = [
            'Sistema de Agendamento de Consultas' => ['Técnico' => 530, 'Designer' => 130, 'Gerente de Projetos' => 115, 'Analista de QA' => 75],
            'Integração ERP × E-commerce' => ['Técnico' => 500, 'Gerente de Projetos' => 120, 'Analista de QA' => 80],
            'Portal de Orçamentos Online' => ['Técnico' => 390, 'Designer' => 120, 'Gerente de Projetos' => 75, 'Analista de QA' => 35],
            'App de Rastreamento de Frota' => ['Técnico' => 350, 'Designer' => 95, 'Gerente de Projetos' => 70, 'Analista de QA' => 45],
            'Redesign do Site Institucional' => ['Técnico' => 200, 'Designer' => 220, 'Gerente de Projetos' => 60],
            'Auditoria Interna — Compliance' => ['Técnico' => 40, 'Gerente de Projetos' => 20],
        ];

        foreach ($estimativas as $nomeProjeto => $porFuncao) {
            $projetoId = DB::table('clockify_projetos')
                ->where('empresa_id', $this->empresaId)->where('nome', $nomeProjeto)->value('id');
            if (! $projetoId) {
                continue;
            }
            foreach ($porFuncao as $nomeFuncao => $horas) {
                $funcaoId = DB::table('funcoes')
                    ->where('empresa_id', $this->empresaId)->where('nome', $nomeFuncao)->value('id');
                if (! $funcaoId) {
                    continue;
                }
                DB::table('clockify_projeto_estimativas')->updateOrInsert(
                    ['clockify_projeto_id' => $projetoId, 'funcao_id' => $funcaoId],
                    ['empresa_id' => $this->empresaId, 'horas_estimadas' => $horas, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    /**
     * Um cronograma válido com planejamento diário — alimenta a tela
     * "Versões de Planejamento" e a alocação do Planejar Equipe.
     */
    private function seedCronograma(): void
    {
        $projeto = DB::table('clockify_projetos')
            ->where('empresa_id', $this->empresaId)
            ->where('nome', 'Portal de Orçamentos Online')->first();

        if (! $projeto) {
            return;
        }

        $inicio = Carbon::today()->addWeek()->startOfWeek();
        $fim = $inicio->copy()->addWeeks(4)->endOfWeek();

        DB::table('clockify_projeto_cronogramas')->updateOrInsert(
            ['clockify_projeto_id' => $projeto->id, 'nome' => 'Versão 2 — Entrega Sprint 4'],
            [
                'data_inicio' => $inicio->toDateString(),
                'data_fim' => $fim->toDateString(),
                'horas_orcadas' => 80,
                'valor_hora' => 110.00,
                'status' => 'valido',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $cronogramaId = DB::table('clockify_projeto_cronogramas')
            ->where('clockify_projeto_id', $projeto->id)
            ->where('nome', 'Versão 2 — Entrega Sprint 4')->value('id');

        if (! $cronogramaId) {
            return;
        }

        $dia = $inicio->copy();
        while ($dia->lte($fim)) {
            if (! $dia->isWeekend()) {
                DB::table('clockify_projeto_planejamentos')->updateOrInsert(
                    ['clockify_projeto_cronograma_id' => $cronogramaId, 'data' => $dia->toDateString()],
                    ['horas' => 4, 'eh_extra' => false, 'updated_at' => now(), 'created_at' => now()]
                );
            }
            $dia->addDay();
        }
    }

    /**
     * Lançamentos que a operação base não cobre: estagiária (jornada 4h),
     * projeto privado e projeto cancelado (histórico).
     */
    private function seedTrackersExtras(object $admin, ?object $fernanda, ?object $julia): void
    {
        $linhas = [];
        $descricoes = [
            'Análise e desenvolvimento', 'Revisão de requisitos', 'Testes e validação',
            'Documentação técnica', 'Reunião de acompanhamento', 'Correção de bugs',
        ];

        if ($julia) {
            // Ordem determinística — a escolha rotativa mantém a reexecução
            // idempotente (o dedup abaixo usa user+projeto+início).
            $projetoIds = DB::table('clockify_projetos')
                ->where('empresa_id', $this->empresaId)->where('publico', true)
                ->where('status', 'em_andamento')->orderBy('id')->pluck('id')->values();

            for ($i = 56; $i >= 1; $i--) {
                $dia = Carbon::today()->subDays($i);
                if ($dia->isWeekend() || $projetoIds->isEmpty()) {
                    continue;
                }
                $linhas[] = $this->tracker(
                    $julia->id, $projetoIds[$i % $projetoIds->count()],
                    $dia->copy()->setTime(9, 0), 4,
                    $descricoes[$i % count($descricoes)]
                );
            }
        }

        if (isset($this->projetos['privado'])) {
            foreach (array_filter([$admin, $fernanda]) as $u) {
                for ($i = 28; $i >= 2; $i -= 3) {
                    $dia = Carbon::today()->subDays($i);
                    if ($dia->isWeekend()) {
                        continue;
                    }
                    $linhas[] = $this->tracker(
                        $u->id, $this->projetos['privado']->id,
                        $dia->copy()->setTime(14, 0), 2,
                        'Auditoria — análise de processos'
                    );
                }
            }
        }

        if (isset($this->projetos['cancelado'])) {
            $equipe = DB::table('users')->where('empresa_id', $this->empresaId)
                ->where('email', 'like', '%'.self::EMAIL_DOMINIO)
                ->orderBy('id')->pluck('id')->values();
            for ($i = 0; $i < 12; $i++) {
                $dia = Carbon::today()->subMonths(4)->addDays($i * 2);
                if ($dia->isWeekend() || $equipe->isEmpty()) {
                    continue;
                }
                $linhas[] = $this->tracker(
                    $equipe[$i % $equipe->count()], $this->projetos['cancelado']->id,
                    $dia->copy()->setTime(9, 30), 3 + ($i % 4),
                    'Desenvolvimento — portal legado'
                );
            }
        }

        foreach ($linhas as $l) {
            $existe = DB::table('clockify_time_trackers')
                ->where('empresa_id', $this->empresaId)
                ->where('user_id', $l['user_id'])
                ->where('clockify_projeto_id', $l['clockify_projeto_id'])
                ->where('inicio', $l['inicio'])
                ->exists();
            if (! $existe) {
                DB::table('clockify_time_trackers')->insert($l);
            }
        }

        $this->command?->info('Lançamentos extras: '.count($linhas));
    }

    private function tracker(int $userId, int $projetoId, Carbon $inicio, float $horas, string $descricao): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'user_id' => $userId,
            'clockify_projeto_id' => $projetoId,
            'descricao' => $descricao,
            'inicio' => $inicio,
            'fim' => $inicio->copy()->addMinutes((int) round($horas * 60)),
            'faturavel' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function resumo(Empresa $empresa): void
    {
        $c = fn (string $t) => DB::table($t)->where('empresa_id', $this->empresaId)->count();

        $this->command?->info('--- Empresa demo pronta -----------------------------------');
        $this->command?->info("Empresa     : {$empresa->nome} (slug: {$empresa->slug})");
        $this->command?->info('Login admin : admin@demo.declock.app / password');
        $this->command?->info("Usuários    : {$c('users')}");
        $this->command?->info("Clientes    : {$c('clockify_clientes')}");
        $this->command?->info("Projetos    : {$c('clockify_projetos')}");
        $this->command?->info("Lançamentos : {$c('clockify_time_trackers')}");
        $this->command?->info("Folgas      : {$c('clockify_time_offs')}");
        $this->command?->info('-----------------------------------------------------------');
    }
}
