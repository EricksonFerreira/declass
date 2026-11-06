<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Perfis de acesso (roles) do sistema escolar.
 *
 * Os 19 perfis da especificacao + 'membro' (baseline self-service).
 * Perfis sao por tenant: cada empresa tem seus proprios roles
 * (roles.empresa_id). A matriz de cada perfil e feita de padroes:
 *
 *  - '*'               : todas as permissoes nao-internas
 *  - 'prefixo.*'       : permissoes cujo nome comeca com 'prefixo.'
 *  - 'prefixo'         : permissao exata
 *  - 'acao' generica   : nao usada — permissoes sao rotas nomeadas
 *
 * A matriz padrao so e aplicada na CRIACAO do perfil — re-syncs
 * nao sobrescrevem ajustes manuais feitos na tela de perfis.
 */
class PerfisEscolaresSeeder extends Seeder
{
    /** Baseline self-service: home, perfil e notificacoes sao sempre liberados. */
    private const BASE = ['home', 'dashboard'];

    /**
     * Matriz padrao por perfil (role.name => padroes de permissao).
     */
    public const PERFIS = [
        'administrador' => ['*'],

        'administrador_rede' => [
            ...self::BASE,
            'menu.*',
            'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.edit', 'usuario.update',
            'usuario.filtro', 'usuario.removerFiltro', 'usuario.setPerPage',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.*', 'organizacao.*', 'documentos.*',
        ],

        'diretor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show', 'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.filtro', 'documentos.removerFiltro',
        ],

        'vice_diretor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show', 'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.filtro', 'documentos.removerFiltro',
        ],

        'coordenador_pedagogico' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index',
        ],

        'secretario' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.*',
            'escola.index', 'escola.show', 'escola.create', 'escola.store', 'escola.edit', 'escola.update',
            'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'organizacao.index', 'organizacao.show',
            'documentos.*',
        ],

        'auxiliar_secretaria' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.create', 'usuario.store',
            'usuario.filtro', 'usuario.removerFiltro', 'usuario.setPerPage',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.show',
        ],

        'professor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
        ],

        'professor_substituto' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
        ],

        'funcionario_administrativo' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.show',
        ],

        'financeiro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.show',
        ],

        'bibliotecario' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
        ],

        'responsavel_transporte' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
        ],

        'responsavel_alimentacao' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
        ],

        'atendimento_especializado' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'documentos.index', 'documentos.show',
        ],

        'aluno' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
        ],

        'responsavel_legal' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
        ],

        'responsavel_financeiro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
        ],

        // Baseline interno: usuario generico com acesso apenas as
        // telas self-service. Usado como fallback de remapeamento.
        'membro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
        ],
    ];

    /**
     * Resolve os padroes de um perfil contra as permissoes existentes.
     *
     * @param  array<int, string>  $padroes
     * @param  \Illuminate\Support\Collection<int, string>  $nomesPermissoes
     * @return array<int, string>
     */
    public static function resolverPadroes(array $padroes, $nomesPermissoes): array
    {
        $resolvidas = [];

        foreach ($padroes as $padrao) {
            if ($padrao === '*') {
                // Tudo que nao e permissao interna de plataforma.
                foreach ($nomesPermissoes as $nome) {
                    if (! \App\Models\Permissao::isNomeInterno($nome)) {
                        $resolvidas[] = $nome;
                    }
                }

                continue;
            }

            if (str_ends_with($padrao, '.*')) {
                $prefixo = substr($padrao, 0, -1); // mantem o ponto: 'escola.'
                foreach ($nomesPermissoes as $nome) {
                    if (str_starts_with($nome, $prefixo)) {
                        $resolvidas[] = $nome;
                    }
                }

                continue;
            }

            if (in_array($padrao, $nomesPermissoes->all(), true)) {
                $resolvidas[] = $padrao;
            }
        }

        return array_values(array_unique($resolvidas));
    }

    /**
     * Garante que todos os perfis existam para uma empresa
     * (usado no cadastro de novo tenant).
     */
    public function garantirPerfisParaEmpresa(int $empresaId): void
    {
        foreach (array_keys(self::PERFIS) as $nome) {
            DB::table('roles')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web', 'empresa_id' => $empresaId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Sincroniza permissoes (rotas nomeadas) e cria os perfis com a
     * matriz padrao para todas as empresas existentes.
     */
    public function run(): void
    {
        app(\App\Services\PermissaoSyncService::class)->sync();
    }
}
