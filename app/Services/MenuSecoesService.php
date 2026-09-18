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
        $planoPermissao = app(PlanoPermissaoService::class);

        $secoes = collect($this->definicoes())->map(function ($secao) use ($planoPermissao, $user) {
            $secao['cards'] = array_values(array_filter($secao['cards'], function ($card) use ($planoPermissao, $user) {
                return $planoPermissao->usuarioPodeAcessarRota($user, $card[2]);
            }));

            return $secao;
        })->filter(fn ($secao) => count($secao['cards']) > 0)->values()->all();

        if ($incluirConfiguracoes && $user->can('acessar-superadmin')) {
            $secoes[] = [
                'slug' => 'configuracoes',
                'titulo' => 'Configurações',
                'cards' => [
                    ['Empresas', 'fas fa-building', 'empresa.index', 'Tenants cadastrados na plataforma', '#dc2626'],
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
                'slug' => 'registrar-tempo',
                'titulo' => 'Registrar tempo',
                'cards' => [
                    ['Rastreador', 'fas fa-stopwatch', 'clockify.tracker.index', 'Cronometre ou lance horas manualmente', '#0d9488'],
                    ['Planilha de Horas', 'fas fa-table', 'clockify.timesheet', 'Sua grade semanal de apontamentos', '#0891b2'],
                    ['Calendário', 'fas fa-calendar-alt', 'clockify.calendario.index', 'Apontamentos e alocações por dia', '#2563eb'],
                ],
            ],
            [
                'slug' => 'painel',
                'titulo' => 'Painel',
                'cards' => [
                    ['Visão Geral', 'fas fa-tachometer-alt', 'dashboard', 'Visão geral com gráficos e KPIs', '#2563eb'],
                    ['Produtividade', 'fas fa-chart-line', 'home.produtividade', 'Horas e evolução da equipe', '#0d9488'],
                    ['Equipe', 'fas fa-users', 'home.equipe', 'Quem está rastreando, status e alertas', '#16a34a'],
                    ['Projetos', 'fas fa-project-diagram', 'home.projetos', 'Saúde e horas por projeto', '#0284c7'],
                ],
            ],
            [
                'slug' => 'relatorios',
                'titulo' => 'Relatórios',
                'cards' => [
                    ['Relatórios', 'fas fa-file-alt', 'relatorios.index', 'Resumos e visões de horas', '#7c3aed'],
                    ['Consumo × Estimado', 'fas fa-chart-bar', 'relatorios.consumo', 'Horas consumidas vs estimadas', '#b45309'],
                    ['Matriz de Horas', 'fas fa-th', 'relatorios.matriz', 'Horas de cada colaborador por projeto', '#7c3aed'],
                    ['Exportar CSV', 'fas fa-file-export', 'relatorios.export', 'Baixe os dados em planilha', '#475569'],
                ],
            ],
            [
                'slug' => 'gerenciar',
                'titulo' => 'Gerenciar',
                'cards' => [
                    ['Projetos', 'fas fa-project-diagram', 'clockify.projetos.index', 'Cadastro e status dos projetos', '#0284c7'],
                    ['Planejar Equipe', 'fas fa-users-cog', 'clockify.projetos.planejar-equipe-geral', 'Distribua a equipe nos projetos', '#4f46e5'],
                    ['Clientes', 'fas fa-user-friends', 'clockify.clientes.index', 'Carteira de clientes', '#0891b2'],
                    ['Equipe', 'fas fa-users', 'clockify.times.index', 'Times e membros', '#16a34a'],
                    ['Folgas e Férias', 'fas fa-plane', 'clockify.timeoff.index', 'Férias e folgas da equipe', '#7c3aed'],
                    ['Funções', 'fas fa-briefcase', 'clockify.funcoes.index', 'Funções e custo por hora', '#b45309'],
                    ['Tags', 'fas fa-tags', 'clockify.tags.index', 'Etiquetas de classificação', '#db2777'],
                    ['Usuários', 'fas fa-user-cog', 'usuario.index', 'Colaboradores e permissões', '#475569'],
                ],
            ],
            [
                'slug' => 'ajuda',
                'titulo' => 'Ajuda',
                'cards' => [
                    ['Central de Ajuda', 'fas fa-question-circle', 'assinatura.ajuda', 'Dúvidas frequentes e guias', '#0891b2'],
                ],
            ],
        ];
    }
}
