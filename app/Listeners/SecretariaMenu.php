<?php

namespace App\Listeners;

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

/**
 * Menu lateral da secretaria: fluxo de trabalho direto, sem a
 * hierarquia profunda dos grupos administrativos globais.
 */
class SecretariaMenu
{
    private const GRUPOS_REMOVIDOS = [
        'org_admin',
        'op_pedagogica',
        'relatorios',
        'comunicacao',
        'operacoes',
        'financeiro',
        'configuracoes',
    ];

    public function handle(BuildingMenu $event): void
    {
        if (! auth()->user()?->isSecretaria()) {
            return;
        }

        foreach (self::GRUPOS_REMOVIDOS as $key) {
            $event->menu->remove($key);
        }

        $event->menu->add(
            [
                'text' => 'Atendimento',
                'icon' => 'fas fa-headset',
                'submenu' => [
                    ['text' => 'Alunos', 'route' => 'aluno.index', 'can' => 'aluno.index', 'icon' => 'fas fa-user-graduate'],
                    ['text' => 'Responsáveis', 'route' => 'responsavel.index', 'can' => 'responsavel.index', 'icon' => 'fas fa-user-friends'],
                    ['text' => 'Matrículas', 'route' => 'matricula.index', 'can' => 'matricula.index', 'icon' => 'fas fa-id-card'],
                    ['text' => 'Solicitações de Matrícula', 'route' => 'solicitacao_matricula.index', 'can' => 'solicitacao_matricula.index', 'icon' => 'fas fa-file-signature'],
                    ['text' => 'Solicitações das Famílias', 'route' => 'solicitacao.index', 'can' => 'solicitacao.index', 'icon' => 'fas fa-inbox'],
                    ['text' => 'Documentos Solicitados', 'route' => 'documento_solicitacao.index', 'can' => 'documento_solicitacao.index', 'icon' => 'fas fa-file-alt'],
                    ['text' => 'Atestados e Abonos', 'route' => 'justificativa.index', 'can' => 'justificativa.index', 'icon' => 'fas fa-file-medical'],
                ],
            ],
            [
                'text' => 'Acompanhamento',
                'icon' => 'fas fa-heartbeat',
                'submenu' => [
                    ['text' => 'Ocorrências', 'route' => 'ocorrencia.index', 'can' => 'ocorrencia.index', 'icon' => 'fas fa-exclamation-circle'],
                    ['text' => 'Planos de Atendimento', 'route' => 'plano_atendimento.index', 'can' => 'plano_atendimento.index', 'icon' => 'fas fa-hands-helping'],
                    ['text' => 'Saúde dos Alunos', 'route' => 'saude.index', 'can' => 'saude.index', 'icon' => 'fas fa-notes-medical'],
                ],
            ],
            [
                'text' => 'Escolar',
                'icon' => 'fas fa-chalkboard',
                'submenu' => [
                    ['text' => 'Turmas', 'route' => 'turma.index', 'can' => 'turma.index', 'icon' => 'fas fa-users'],
                    ['text' => 'Calendário Letivo', 'route' => 'calendario.index', 'can' => 'calendario.index', 'icon' => 'fas fa-calendar-alt'],
                    ['text' => 'Agenda', 'route' => 'evento.index', 'can' => 'evento.index', 'icon' => 'fas fa-calendar-day'],
                    ['text' => 'Feriados', 'route' => 'feriado.index', 'can' => 'feriado.index', 'icon' => 'fas fa-umbrella-beach'],
                    ['text' => 'Diário de Classe', 'route' => 'diario.index', 'can' => 'diario.index', 'icon' => 'fas fa-book'],
                    ['text' => 'Avaliações', 'route' => 'avaliacao.index', 'can' => 'avaliacao.index', 'icon' => 'fas fa-clipboard-check'],
                    ['text' => 'Agenda de Avaliações', 'route' => 'avaliacao.agenda', 'can' => 'avaliacao.index', 'icon' => 'fas fa-calendar-check'],
                    ['text' => 'Boletim', 'route' => 'boletim.index', 'can' => 'boletim.index', 'icon' => 'fas fa-file-alt'],
                    ['text' => 'Tarefas', 'route' => 'tarefa.index', 'can' => 'tarefa.index', 'icon' => 'fas fa-tasks'],
                ],
            ],
            [
                'text' => 'Comunicação',
                'icon' => 'fas fa-bullhorn',
                'submenu' => [
                    ['text' => 'Avisos', 'route' => 'aviso.index', 'can' => 'aviso.index', 'icon' => 'fas fa-bullhorn'],
                    ['text' => 'Pesquisas', 'route' => 'pesquisa.index', 'can' => 'pesquisa.index', 'icon' => 'fas fa-clipboard-question'],
                    ['text' => 'Portal Familiar', 'route' => 'portal.index', 'can' => 'portal.index', 'icon' => 'fas fa-home'],
                ],
            ],
            [
                'text' => 'Financeiro',
                'icon' => 'fas fa-wallet',
                'submenu' => [
                    ['text' => 'Mensalidades', 'route' => 'mensalidade.index', 'can' => 'mensalidade.index', 'icon' => 'fas fa-money-bill-wave'],
                    ['text' => 'Planos de Mensalidade', 'route' => 'plano_mensalidade.index', 'can' => 'plano_mensalidade.index', 'icon' => 'fas fa-list-ol'],
                    ['text' => 'Contas a Receber', 'route' => 'conta_receber.index', 'can' => 'conta_receber.index', 'icon' => 'fas fa-arrow-circle-down'],
                    ['text' => 'Contas a Pagar', 'route' => 'despesa.index', 'can' => 'despesa.index', 'icon' => 'fas fa-arrow-circle-up'],
                    ['text' => 'Visão Geral', 'route' => 'financeiro_visao_geral.index', 'can' => 'financeiro_visao_geral.index', 'icon' => 'fas fa-chart-line'],
                    ['text' => 'Fluxo de Caixa', 'route' => 'fluxo_caixa.index', 'can' => 'fluxo_caixa.index', 'icon' => 'fas fa-exchange-alt'],
                    ['text' => 'DRE', 'route' => 'dre.index', 'can' => 'dre.index', 'icon' => 'fas fa-chart-pie'],
                ],
            ],
            [
                'text' => 'Relatórios',
                'icon' => 'fas fa-chart-bar',
                'submenu' => [
                    ['text' => 'Frequência', 'route' => 'relatorio_frequencia.index', 'can' => 'relatorio_frequencia.index', 'icon' => 'fas fa-user-check'],
                    ['text' => 'Aproveitamento', 'route' => 'relatorio_aproveitamento.index', 'can' => 'relatorio_aproveitamento.index', 'icon' => 'fas fa-star-half-alt'],
                    ['text' => 'Inadimplência', 'route' => 'relatorio_inadimplencia.index', 'can' => 'relatorio_inadimplencia.index', 'icon' => 'fas fa-exclamation-triangle'],
                    ['text' => 'Transporte', 'route' => 'relatorio_transporte.index', 'can' => 'relatorio_transporte.index', 'icon' => 'fas fa-bus'],
                ],
            ],
            [
                'text' => 'Operações',
                'icon' => 'fas fa-boxes',
                'submenu' => [
                    ['text' => 'Empréstimos', 'route' => 'emprestimo.index', 'can' => 'emprestimo.index', 'icon' => 'fas fa-book-reader'],
                    ['text' => 'Acervo', 'route' => 'livro.index', 'can' => 'livro.index', 'icon' => 'fas fa-book'],
                    ['text' => 'Passageiros', 'route' => 'transporte_aluno.index', 'can' => 'transporte_aluno.index', 'icon' => 'fas fa-bus'],
                    ['text' => 'Rotas', 'route' => 'rota_transporte.index', 'can' => 'rota_transporte.index', 'icon' => 'fas fa-route'],
                    ['text' => 'Veículos', 'route' => 'veiculo.index', 'can' => 'veiculo.index', 'icon' => 'fas fa-shuttle-van'],
                    ['text' => 'Cardápios', 'route' => 'cardapio.index', 'can' => 'cardapio.index', 'icon' => 'fas fa-utensils'],
                    ['text' => 'Materiais', 'route' => 'material.index', 'can' => 'material.index', 'icon' => 'fas fa-box'],
                    ['text' => 'Movimentação de Materiais', 'route' => 'material.movimentacao.index', 'can' => 'material.movimentacao.index', 'icon' => 'fas fa-dolly'],
                ],
            ],
            [
                'text' => 'Cadastros',
                'icon' => 'fas fa-cog',
                'submenu' => [
                    ['text' => 'Escolas', 'route' => 'escola.index', 'can' => 'escola.index', 'icon' => 'fas fa-school'],
                    ['text' => 'Anos Letivos', 'route' => 'ano_letivo.index', 'can' => 'ano_letivo.index', 'icon' => 'fas fa-calendar'],
                    ['text' => 'Períodos Letivos', 'route' => 'periodo_letivo.index', 'can' => 'periodo_letivo.index', 'icon' => 'fas fa-hourglass-half'],
                    ['text' => 'Etapas', 'route' => 'etapa.index', 'can' => 'etapa.index', 'icon' => 'fas fa-layer-group'],
                    ['text' => 'Disciplinas', 'route' => 'disciplina.index', 'can' => 'disciplina.index', 'icon' => 'fas fa-book'],
                    ['text' => 'Grades de Horário', 'route' => 'horarios.index', 'can' => 'horarios.index', 'icon' => 'fas fa-clock'],
                    ['text' => 'Matriz de Horários', 'route' => 'horario_matriz.index', 'can' => 'horario_matriz.index', 'icon' => 'fas fa-th'],
                    ['text' => 'Rematrícula em Lote', 'route' => 'matricula.rematricula', 'can' => 'matricula.rematricula', 'icon' => 'fas fa-retweet'],
                    ['text' => 'Tipos de Solicitação', 'route' => 'tipo_solicitacao.index', 'can' => 'tipo_solicitacao.index', 'icon' => 'fas fa-tags'],
                    ['text' => 'Usuários', 'route' => 'usuario.index', 'can' => 'usuario.index', 'icon' => 'fas fa-user-cog'],
                    ['text' => 'Perfis de Acesso', 'route' => 'regra.index', 'can' => 'regra.index', 'icon' => 'fas fa-key'],
                ],
            ],
        );
    }
}
