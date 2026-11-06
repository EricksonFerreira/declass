<?php

namespace App\Services;

use App\Models\User;

/**
 * Secoes de atalhos em cards (home e hub de menu mobile).
 *
 * Fonte unica da estrutura: titulo, slug (usado na rota menu.index),
 * cards [nome, icone, rota, descricao, cor]. Filtra os cards que o
 * usuario nao pode acessar para evitar 403 no clique.
 */
class MenuSecoesService
{
    public function secoes(User $user, bool $incluirConfiguracoes = false): array
    {
        $rotaPermissao = app(PermissaoRotaService::class);

        $secoes = collect($this->definicoes())->map(function ($secao) use ($rotaPermissao, $user) {
            $secao['cards'] = array_values(array_filter($secao['cards'], function ($card) use ($rotaPermissao, $user) {
                return $rotaPermissao->usuarioPodeAcessarRota($user, $card[2]);
            }));

            return $secao;
        })->filter(fn ($secao) => count($secao['cards']) > 0)->values()->all();

        if ($incluirConfiguracoes && $user->isSuperAdmin()) {
            $secoes[] = [
                'slug' => 'configuracoes',
                'titulo' => 'Configurações',
                'cards' => [
                    ['Tenants', 'fas fa-building', 'empresa.index', 'Tenants (escolas/redes) cadastrados na plataforma', '#dc2626'],
                    ['Comandos', 'fas fa-terminal', 'comandos.index', 'Manutenção e comandos do sistema', '#0f172a'],
                ],
            ];
        }

        return $secoes;
    }

    private function definicoes(): array
    {
        return [
            [
                'slug' => 'institucional',
                'titulo' => 'Institucional',
                'cards' => [
                    ['Organizações', 'fas fa-sitemap', 'organizacao.index', 'Redes e mantenedoras do tenant', '#7c3aed'],
                    ['Escolas', 'fas fa-school', 'escola.index', 'Unidades e escolas do tenant', '#1d4ed8'],
                ],
            ],
            [
                'slug' => 'pessoas',
                'titulo' => 'Pessoas',
                'cards' => [
                    ['Usuários', 'fas fa-users', 'usuario.index', 'Colaboradores e contas de acesso', '#0891b2'],
                    ['Perfis de Acesso', 'fas fa-user-shield', 'regra.index', 'Perfis e permissões do tenant', '#0d9488'],
                ],
            ],
            [
                'slug' => 'academico',
                'titulo' => 'Vida Acadêmica',
                'cards' => [
                    ['Anos Letivos', 'fas fa-calendar-alt', 'ano_letivo.index', 'Períodos letivos por escola', '#0ea5e9'],
                    ['Períodos Letivos', 'fas fa-calendar-week', 'periodo_letivo.index', 'Bimestres, trimestres ou semestres', '#0284c7'],
                    ['Etapas', 'fas fa-layer-group', 'etapa.index', 'Estágios de ensino configuráveis', '#8b5cf6'],
                    ['Disciplinas', 'fas fa-book', 'disciplina.index', 'Componentes curriculares', '#f59e0b'],
                    ['Turmas', 'fas fa-chalkboard-teacher', 'turma.index', 'Turmas, disciplinas e professores', '#10b981'],
                    ['Matrículas', 'fas fa-id-card', 'matricula.index', 'Inscrições de alunos em turmas', '#f97316'],
                    ['Diário de Classe', 'fas fa-clipboard-list', 'diario.index', 'Registro de aulas e chamada', '#0d9488'],
                    ['Avaliações', 'fas fa-star-half-alt', 'avaliacao.index', 'Provas, trabalhos e lançamento de notas', '#e11d48'],
                    ['Boletim', 'fas fa-file-alt', 'boletim.index', 'Médias e frequência consolidadas', '#6366f1'],
                    ['Ocorrências', 'fas fa-exclamation-circle', 'ocorrencia.index', 'Registros disciplinares e pedagógicos', '#dc2626'],
                    ['Planos de Atendimento', 'fas fa-hands-helping', 'plano_atendimento.index', 'PEI, PDI e atendimento especializado', '#7c3aed'],
                    ['Saúde dos Alunos', 'fas fa-notes-medical', 'saude.index', 'Alergias, medicamentos e emergências', '#dc2626'],
                    ['Tarefas', 'fas fa-tasks', 'tarefa.index', 'Dever de casa e controle de entregas', '#2563eb'],
                    ['Alunos', 'fas fa-user-graduate', 'aluno.index', 'Cadastro e dados dos alunos', '#4f46e5'],
                    ['Responsáveis', 'fas fa-user-friends', 'responsavel.index', 'Responsáveis legais, pedagógicos e financeiros', '#db2777'],
                ],
            ],
            [
                'slug' => 'relatorios',
                'titulo' => 'Relatórios',
                'cards' => [
                    ['Frequência', 'fas fa-clipboard-check', 'relatorio_frequencia.index', 'Faltas e assiduidade por aluno', '#dc2626'],
                    ['Inadimplência', 'fas fa-money-bill-wave', 'relatorio_inadimplencia.index', 'Cobranças vencidas e em aberto', '#b45309'],
                    ['Transporte', 'fas fa-route', 'relatorio_transporte.index', 'Ocupação de rotas e veículos', '#0d9488'],
                ],
            ],
            [
                'slug' => 'comunicacao',
                'titulo' => 'Comunicação',
                'cards' => [
                    ['Avisos', 'fas fa-bullhorn', 'aviso.index', 'Comunicados da escola para as famílias', '#dc2626'],
                    ['Agenda', 'fas fa-calendar-alt', 'evento.index', 'Eventos, reuniões e compromissos', '#2563eb'],
                    ['Pesquisas', 'fas fa-clipboard-question', 'pesquisa.index', 'Questionários de satisfação para as famílias', '#7c3aed'],
                ],
            ],
            [
                'slug' => 'biblioteca',
                'titulo' => 'Biblioteca',
                'cards' => [
                    ['Empréstimos', 'fas fa-book-reader', 'emprestimo.index', 'Circulação do acervo e devoluções', '#b45309'],
                    ['Acervo', 'fas fa-book', 'livro.index', 'Livros e exemplares das escolas', '#0f766e'],
                ],
            ],
            [
                'slug' => 'transporte',
                'titulo' => 'Transporte',
                'cards' => [
                    ['Passageiros', 'fas fa-user-friends', 'transporte_aluno.index', 'Alunos vinculados às rotas', '#2563eb'],
                    ['Rotas', 'fas fa-route', 'rota_transporte.index', 'Linhas, horários e paradas', '#7c3aed'],
                    ['Veículos', 'fas fa-bus', 'veiculo.index', 'Frota e motoristas das unidades', '#ea580c'],
                ],
            ],
            [
                'slug' => 'alimentacao',
                'titulo' => 'Alimentação',
                'cards' => [
                    ['Cardápios', 'fas fa-utensils', 'cardapio.index', 'Refeições servidas em cada unidade', '#16a34a'],
                ],
            ],
            [
                'slug' => 'estoque',
                'titulo' => 'Estoque',
                'cards' => [
                    ['Materiais', 'fas fa-box-open', 'material.index', 'Materiais, insumos e controle de estoque', '#0891b2'],
                ],
            ],
            [
                'slug' => 'financeiro',
                'titulo' => 'Financeiro',
                'cards' => [
                    ['Mensalidades', 'fas fa-file-invoice-dollar', 'mensalidade.index', 'Cobranças, baixas e inadimplência', '#059669'],
                    ['Planos de Mensalidade', 'fas fa-tags', 'plano_mensalidade.index', 'Tabelas de valores por perfil', '#d97706'],
                ],
            ],
            [
                'slug' => 'portal',
                'titulo' => 'Portal',
                'cards' => [
                    ['Portal Familiar', 'fas fa-home', 'portal.index', 'Boletim e frequência dos alunos vinculados', '#0891b2'],
                ],
            ],
            [
                'slug' => 'documentos',
                'titulo' => 'Documentos',
                'cards' => [
                    ['Documentos', 'fas fa-folder-open', 'documentos.index', 'Documentos institucionais do tenant', '#b45309'],
                ],
            ],
        ];
    }
}
