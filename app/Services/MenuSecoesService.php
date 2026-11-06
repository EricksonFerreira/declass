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
                    ['Alunos', 'fas fa-user-graduate', 'aluno.index', 'Cadastro e dados dos alunos', '#4f46e5'],
                    ['Responsáveis', 'fas fa-user-friends', 'responsavel.index', 'Responsáveis legais, pedagógicos e financeiros', '#db2777'],
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
