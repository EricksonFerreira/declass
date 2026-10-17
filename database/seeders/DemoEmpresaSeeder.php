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
use Illuminate\Support\Facades\Schema;

/**
 * Popula uma EMPRESA DEMO COMPLETA — tudo que um cliente precisa ver
 * em uma apresentação do sistema: empresa, plano ativo, admin, perfis
 * de acesso, usuários com papéis e jornadas variadas, clientes,
 * projetos (andamento/concluído/cancelado/privado), estimativas,
 * equipes, meses de lançamentos de horas, tags, folgas/férias,
 * alocações de calendário e um cronograma de planejamento.
 *
 * Reset completo: a cada execução apaga TODOS os dados ligados à
 * empresa demo (inclusive o que foi criado manualmente em testes)
 * e a recria do zero com as informações padrão — ideal para
 * gravar vídeos e repetir demonstrações sempre do mesmo ponto.
 *
 * Credenciais de demonstração: admin@demo.declock.app / password
 *
 * Uso: php artisan db:seed --class=DemoEmpresaSeeder
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
        $this->resetarEmpresaDemo();

        $empresa = $this->seedEmpresa();
        $this->empresaId = $empresa->id;

        $roles = $this->seedRegras();
        $admin = $this->seedAdmin($roles['administrador']);

        $this->seedAssinatura($empresa, $admin);
        $this->seedOnboarding();
        $this->seedTags();
        $this->seedOperacao();
        $this->posProcessamento($roles, $admin);
        $this->resumo($empresa);
    }

    /**
     * Apaga TODOS os registros ligados à empresa demo: apontamentos,
     * projetos, clientes, usuários, assinatura, licitações e qualquer
     * outra tabela com empresa_id — inclusive dados criados à mão
     * durante testes/vídeos. A linha da empresa é preservada (e
     * restaurada, se estiver arquivada) para manter o mesmo id.
     */
    private function resetarEmpresaDemo(): void
    {
        $empresa = Empresa::withTrashed()->where('slug', self::SLUG)->first();

        if (! $empresa) {
            return;
        }

        $id = $empresa->id;
        $apagados = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $userIds = DB::table('users')->where('empresa_id', $id)->pluck('id');
            $projetoIds = DB::table('clockify_projetos')->where('empresa_id', $id)->pluck('id');
            $trackerIds = DB::table('clockify_time_trackers')->where('empresa_id', $id)->pluck('id');
            $cronogramaIds = Schema::hasTable('clockify_projeto_cronogramas')
                ? DB::table('clockify_projeto_cronogramas')->whereIn('clockify_projeto_id', $projetoIds)->pluck('id')
                : collect();

            // Tabelas sem empresa_id — excluídas via relacionamento.
            $porRelacao = [
                'clockify_tracker_tag' => ['clockify_time_tracker_id', $trackerIds],
                'clockify_projeto_planejamentos' => ['clockify_projeto_cronograma_id', $cronogramaIds],
                'clockify_projeto_user' => ['user_id', $userIds],
                'clockify_time_user' => ['user_id', $userIds],
                'tour_realizacoes' => ['user_id', $userIds],
            ];

            foreach ($porRelacao as $tabela => [$coluna, $ids]) {
                if ($ids->isNotEmpty() && Schema::hasTable($tabela)) {
                    $apagados += DB::table($tabela)->whereIn($coluna, $ids)->delete();
                }
            }

            if ($projetoIds->isNotEmpty()) {
                $apagados += DB::table('clockify_projeto_user')->whereIn('clockify_projeto_id', $projetoIds)->delete();
            }

            // Todas as demais tabelas com coluna empresa_id.
            $tabelas = collect(DB::select(
                'SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = ? AND TABLE_SCHEMA = ?',
                ['empresa_id', DB::getDatabaseName()]
            ))->pluck('TABLE_NAME');

            foreach ($tabelas as $tabela) {
                if ($tabela === 'empresas') {
                    continue;
                }
                $apagados += DB::table($tabela)->where('empresa_id', $id)->delete();
            }

            if ($empresa->trashed()) {
                $empresa->restore();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command?->info("Empresa demo zerada: {$apagados} registros removidos.");
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
            'licitacoes_ativo' => 'S',
        ]);
        $empresa->save();

        $this->command?->info("Empresa demo: {$empresa->nome} (id {$empresa->id})");

        return $empresa;
    }

    private function seedRegras(): array
    {
        $roles = [];
        foreach (['administrador', 'usuario'] as $nome) {
            $roles[$nome] = Regra::firstOrCreate(
                ['name' => $nome, 'guard_name' => 'web', 'empresa_id' => $this->empresaId]
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
                'step_primeiro_registro' => true,
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
            'Campo', 'Escritório', 'Medição', 'Documentação',
            'Fiscalização', 'Urgente', 'Interno', 'Não faturável',
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
            DB::table('users')->where('id', $fernanda->id)->update(['role_id' => $roles['administrador']]);
        }

        DB::table('users')->where('empresa_id', $this->empresaId)
            ->where('email', 'like', '%'.self::EMAIL_DOMINIO)
            ->whereNotIn('id', array_filter([$admin->id, $fernanda?->id]))
            ->update(['role_id' => $roles['usuario']]);

        $paulo = $this->seedUsuarioExtra($roles['usuario'], [
            'nome' => 'Paulo Diretor', 'cargo' => 'Diretor Comercial',
        ], 'paulo.diretor');

        $julia = $this->seedUsuarioExtra($roles['usuario'], [
            'nome' => 'Julia Costa', 'cargo' => 'Estagiária de Engenharia', 'jornada_diaria' => 4, 'fator_esforco' => 0.5,
        ], 'julia.costa');

        $this->seedProjetosExtras($admin, $fernanda);
        $this->atribuirCodigosEResponsaveis($admin, $fernanda);
        $this->seedCronograma();
        $this->seedTrackersExtras($admin, $fernanda, $julia);
        $this->seedLicitacoes($admin);
    }

    /**
     * Licitações de exemplo: uma analisada (análise completa pré-preenchida,
     * para a demo exibir o resultado sem consumir a API) e uma cadastrada
     * aguardando o texto do edital.
     */
    private function seedLicitacoes(object $admin): void
    {
        DB::table('licitacoes')->updateOrInsert(
            ['empresa_id' => $this->empresaId, 'numero_edital' => 'PE-042/2026'],
            [
                'orgao' => 'Prefeitura Municipal de Campinas — Secretaria de Infraestrutura',
                'objeto' => 'Contratação de empresa de engenharia para elaboração de projeto executivo e fiscalização técnica da reforma de 12 escolas municipais, incluindo adequações de acessibilidade (NBR 9050) e instalações elétricas.',
                'modalidade' => 'Concorrência',
                'criterio_julgamento' => 'Técnica e preço',
                'data_abertura' => now()->addDays(21)->toDateString(),
                'valor_estimado' => 1850000.00,
                'url_edital' => 'https://pncp.gov.br/app/editais',
                'texto_edital' => 'Critérios de julgamento — técnica (peso 40%) e preço (peso 60%). Pontuação técnica: atestado de capacidade técnica em reforma de edificações públicas (30 pts); equipe com engenheiro civil responsável CREA e projetista dedicado (25 pts); metodologia de fiscalização e cronograma físico-financeiro (25 pts); certificação ISO 9001 ou PBQP-H (10 pts); experiência com órgãos públicos (10 pts). Habilitação: certidões federais/estaduais/municipais negativas; FGTS (CRF); CREA da empresa e do responsável técnico; balanço patrimonial do último exercício; atestado de capacidade técnica com valor compatível.',
                'status' => 'analisada',
                'usuinc' => $admin->id,
                'datainc' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $licitacaoId = DB::table('licitacoes')
            ->where('empresa_id', $this->empresaId)->where('numero_edital', 'PE-042/2026')
            ->value('id');

        if ($licitacaoId) {
            DB::table('licitacao_analises')->updateOrInsert(
                ['licitacao_id' => $licitacaoId],
                [
                    'empresa_id' => $this->empresaId,
                    'user_id' => $admin->id,
                    'modelo' => 'deepseek-chat',
                    'nota_tecnica_estimada' => 78,
                    'viabilidade' => 'atencao',
                    'parecer' => 'A empresa tem bom histórico para este certame — reforma de edificações públicas e equipe com engenheiro civil e projetista sustentam a maior parte dos critérios técnicos. Porém a habilitação não está confirmada: não há evidência cadastrada de CREA da empresa/responsável, nem do balanço patrimonial, e a experiência com escolas é indireta. Não comprovado não significa inexistente — reunir os documentos antes da sessão.',
                    'criterios' => json_encode([
                        ['criterio' => 'Atestado de capacidade técnica em reforma de edificações públicas', 'avaliacao' => 'favoravel', 'pontos_max' => 30, 'pontos_estimados' => 26, 'observacao' => 'Reforma da Unidade Fabril e Ampliação Hospital Regional são atestados compatíveis; nenhum é especificamente escolar.'],
                        ['criterio' => 'Equipe técnica (engenheiro civil CREA + projetista)', 'avaliacao' => 'moderada', 'pontos_max' => 25, 'pontos_estimados' => 22, 'observacao' => 'Equipe possui Engenheiro Civil e Projetista CAD/BIM ativos; falta comprovar CREA do responsável no cadastro.'],
                        ['criterio' => 'Metodologia de fiscalização e cronograma físico-financeiro', 'avaliacao' => 'moderada', 'pontos_max' => 25, 'pontos_estimados' => 20, 'observacao' => 'Histórico de fiscalização de obras é forte; metodologia precisa ser customizada para 12 escolas simultâneas.'],
                        ['criterio' => 'Certificação ISO 9001 ou PBQP-H', 'avaliacao' => 'desfavoravel', 'pontos_max' => 10, 'pontos_estimados' => 0, 'observacao' => 'Nenhuma certificação registrada — gap de pontuação.'],
                        ['criterio' => 'Experiência com órgãos públicos', 'avaliacao' => 'favoravel', 'pontos_max' => 10, 'pontos_estimados' => 10, 'observacao' => 'Hospital Regional Santa Cruz é atestado válido de obra pública.'],
                    ]),
                    'exigencias' => json_encode([
                        ['exigencia' => 'Certidões negativas federais/estaduais/municipais', 'situacao' => 'comprovado', 'evidencia' => 'CNPJ ativo 12.345.678/0001-90 — emitir certidões antes da sessão.'],
                        ['exigencia' => 'CRF — FGTS', 'situacao' => 'comprovado', 'evidencia' => 'Emitir CRF atualizada.'],
                        ['exigencia' => 'CREA da empresa e do responsável técnico', 'situacao' => 'nao_comprovado', 'evidencia' => 'Não há CREA cadastrado no perfil — não significa que a empresa não possua; registrar/vincular responsável com registro ativo.'],
                        ['exigencia' => 'Balanço patrimonial do último exercício', 'situacao' => 'nao_comprovado', 'evidencia' => 'Documento não encontrado no cadastro — providenciar com contador.'],
                        ['exigencia' => 'Atestado de capacidade técnica com valor compatível', 'situacao' => 'comprovado', 'evidencia' => 'Ampliação Hospital Regional (concluído) e Reforma Unidade Fabril somam porte compatível com o estimado de R$ 1,85 mi.'],
                    ]),
                    'gaps' => json_encode([
                        'Comprovar CREA da empresa e do engenheiro responsável técnico',
                        'Anexar balanço patrimonial do último exercício',
                        'Sem certificação ISO 9001/PBQP-H — perda de até 10 pts; avaliar declaração de implantação se o edital aceitar',
                        'Atestados específicos de escolas não existem — usar reformas de edificações de uso coletivo',
                    ]),
                    'divergencia' => null,
                    'preco_max_edital' => 1850000.00,
                    'preco_sugerido' => null,
                    'preco_motivo' => 'Planilha orçamentária e composição de BDI não disponíveis — preço não calculável com segurança apenas pelo custo-hora da equipe.',
                    'tokens_entrada' => 21480,
                    'tokens_saida' => 1370,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        DB::table('licitacoes')->updateOrInsert(
            ['empresa_id' => $this->empresaId, 'numero_edital' => 'CC-118/2026'],
            [
                'orgao' => 'Governo do Estado — Secretaria de Transporte e Obras',
                'objeto' => 'Serviços de consultoria para elaboração de estudo de viabilidade técnica e projeto básico de pavimentação de 14 km de rodovia vicinal.',
                'modalidade' => 'Concorrência',
                'criterio_julgamento' => 'Melhor técnica',
                'data_abertura' => now()->addDays(34)->toDateString(),
                'valor_estimado' => 920000.00,
                'status' => 'cadastrada',
                'usuinc' => $admin->id,
                'datainc' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
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
            ['empresa_id' => $this->empresaId, 'nome' => 'Due Diligence — Terreno Distrito Sul'],
            [
                'clockify_cliente_id' => $clienteId,
                'codigo' => '200.01', 'ano' => 2025, 'numero' => 60,
                'descricao' => 'Análise técnica e documental para aquisição de terreno. Acesso restrito à diretoria.',
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
            ['empresa_id' => $this->empresaId, 'nome' => 'Terraplenagem — Loteamento Alto Serra'],
            [
                'clockify_cliente_id' => $clienteId,
                'codigo' => '088.02', 'ano' => 2023, 'numero' => 15,
                'descricao' => 'Obra suspensa pelo cliente durante a fase de terraplenagem.',
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
            ->where('nome', 'Due Diligence — Terreno Distrito Sul')->first();

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
            ->where('nome', 'Terraplenagem — Loteamento Alto Serra')->first();
        if ($cancelado) {
            $this->projetos['cancelado'] = $cancelado;
        }
    }

    private function atribuirCodigosEResponsaveis(object $admin, ?object $fernanda): void
    {
        $liderId = $fernanda?->id ?? $admin->id;

        // nome => [ano, numero, codigo CC, responsavel, horas_orcadas]
        // O orçado é calibrado contra o consumo semeado (~530-680h/projeto)
        // para a demo mostrar percentuais variados no Consumo x Estimado:
        // ~80% confortável, ~90% no limite e >100% estourado.
        $mapa = [
            'Projeto Executivo de Instalações — Ala Norte' => [2024, 87, '101.01', $admin->id, 850],
            'Reforma da Unidade Fabril — Planta Norte' => [2025, 12, '118.02', $admin->id, 700],
            'Loteamento Residencial Vista Alegre — Lote 2' => [2025, 33, '142.05', $liderId, 620],
            'Edifício Comercial Parque Central' => [2025, 41, '158.01', $admin->id, 560],
            'Ampliação da Ala Sul — Hospital Regional' => [2024, 156, '099.03', $liderId, 480],
        ];

        foreach ($mapa as $nome => [$ano, $numero, $codigo, $responsavelId, $orcado]) {
            DB::table('clockify_projetos')
                ->where('empresa_id', $this->empresaId)->where('nome', $nome)
                ->update([
                    'ano' => $ano, 'numero' => $numero, 'codigo' => $codigo,
                    'responsavel_id' => $responsavelId, 'horas_orcadas' => $orcado,
                ]);
        }

        // Estimativas por função coerentes com o orçado total do projeto.
        $estimativas = [
            'Projeto Executivo de Instalações — Ala Norte' => ['Engenheiro Civil' => 530, 'Projetista CAD/BIM' => 130, 'Coordenador de Equipe' => 115, 'Técnico de Edificações' => 75],
            'Reforma da Unidade Fabril — Planta Norte' => ['Engenheiro Civil' => 500, 'Coordenador de Equipe' => 120, 'Técnico de Edificações' => 80],
            'Loteamento Residencial Vista Alegre — Lote 2' => ['Engenheiro Civil' => 390, 'Projetista CAD/BIM' => 120, 'Coordenador de Equipe' => 75, 'Técnico de Edificações' => 35],
            'Edifício Comercial Parque Central' => ['Engenheiro Civil' => 350, 'Projetista CAD/BIM' => 95, 'Coordenador de Equipe' => 70, 'Técnico de Edificações' => 45],
            'Ampliação da Ala Sul — Hospital Regional' => ['Engenheiro Civil' => 200, 'Projetista CAD/BIM' => 220, 'Coordenador de Equipe' => 60],
            'Due Diligence — Terreno Distrito Sul' => ['Engenheiro Civil' => 40, 'Coordenador de Equipe' => 20],
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
            ->where('nome', 'Loteamento Residencial Vista Alegre — Lote 2')->first();

        if (! $projeto) {
            return;
        }

        $inicio = Carbon::today()->addWeek()->startOfWeek();
        $fim = $inicio->copy()->addWeeks(4)->endOfWeek();

        DB::table('clockify_projeto_cronogramas')->updateOrInsert(
            ['clockify_projeto_id' => $projeto->id, 'nome' => 'Etapa 2 — Estrutura e Contenções'],
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
            ->where('nome', 'Etapa 2 — Estrutura e Contenções')->value('id');

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
            'Levantamento quantitativo de materiais', 'Conferência de pranchas e especificações',
            'Apoio em medição de serviços', 'Organização de relatórios fotográficos',
            'Atualização de planilhas de controle', 'Reunião de acompanhamento',
        ];

        // Âncora fixa: o último lançamento da operação base. Usar today()
        // deslizaria a janela a cada execução e duplicaria os extras.
        $ref = Carbon::parse(
            DB::table('clockify_time_trackers')->where('empresa_id', $this->empresaId)->max('inicio')
                ?? now()
        );

        if ($julia) {
            // Ordem determinística — a escolha rotativa mantém a reexecução
            // idempotente (o dedup abaixo usa user+projeto+início).
            $projetoIds = DB::table('clockify_projetos')
                ->where('empresa_id', $this->empresaId)->where('publico', true)
                ->where('status', 'em_andamento')->orderBy('id')->pluck('id')->values();

            for ($i = 56; $i >= 1; $i--) {
                $dia = $ref->copy()->subDays($i);
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
                    $dia = $ref->copy()->subDays($i);
                    if ($dia->isWeekend()) {
                        continue;
                    }
                    $linhas[] = $this->tracker(
                        $u->id, $this->projetos['privado']->id,
                        $dia->copy()->setTime(14, 0), 2,
                        'Vistoria — análise técnica do terreno'
                    );
                }
            }
        }

        if (isset($this->projetos['cancelado'])) {
            $equipe = DB::table('users')->where('empresa_id', $this->empresaId)
                ->where('email', 'like', '%'.self::EMAIL_DOMINIO)
                ->orderBy('id')->pluck('id')->values();
            for ($i = 0; $i < 12; $i++) {
                $dia = $ref->copy()->subMonths(4)->addDays($i * 2);
                if ($dia->isWeekend() || $equipe->isEmpty()) {
                    continue;
                }
                $linhas[] = $this->tracker(
                    $equipe[$i % $equipe->count()], $this->projetos['cancelado']->id,
                    $dia->copy()->setTime(9, 30), 3 + ($i % 4),
                    'Execução — terraplenagem e base estabilizada'
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
        $this->command?->info("Licitações  : {$c('licitacoes')}");
        $this->command?->info('-----------------------------------------------------------');
    }
}
