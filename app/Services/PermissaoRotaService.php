<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\User;
use App\Support\EspelhamentoContext;
use Illuminate\Support\Collection;

/**
 * Autorizacao por rota: decide SE o usuario pode acessar uma rota
 * nomeada, com base no perfil (role -> role_has_permissions).
 *
 * Substitui o antigo PlanoPermissaoService — nao ha mais gate de
 * plano/assinatura: quem manda no acesso e o perfil do usuario.
 */
class PermissaoRotaService
{
    /**
     * Rotas internas/auxiliares que nao participam da checagem de permissao.
     *
     * @var string[]
     */
    private array $ignoredRoutePrefixes = [
        'login',
        'logout',
        'register',
        'password.',
        'verification.',
        'sanctum.',
        'ignition.',
        'debugbar.',
        'profile.',
        'notificacoes.',
        'menu.',
        'cron.',
        'matricula_publica.',
        'escola_context.',
        'espelhamento.',
    ];

    /**
     * Rotas sempre liberadas para usuarios autenticados.
     *
     * @var string[]
     */
    private array $alwaysAllowedRoutes = [
        'home',
        'dashboard',
        'site.apresentacao',
    ];

    /**
     * Rotas de listagem que o professor acompanha pela tela da turma:
     * continuam acessiveis, mas ficam fora do menu (sidebar e hub).
     *
     * @var string[]
     */
    private const ROTAS_OCULTAS_PROFESSOR = [
        'diario.index',
        'avaliacao.index',
        'avaliacao.agenda',
        'cardapio.index',
    ];

    public function usuarioPodeAcessarRota(User $user, string $routeName): bool
    {
        return $this->motivoBloqueioRota($user, $routeName) === null;
    }

    /**
     * Visibilidade no menu (sidebar/hub de cards) — nao confundir com
     * acesso a rota: aqui so escondemos itens redundantes para o
     * professor, sem bloquear a URL.
     */
    public function rotaVisivelNoMenu(User $user, string $routeName): bool
    {
        return ! ($user->isProfessor()
            && in_array($routeName, self::ROTAS_OCULTAS_PROFESSOR, true));
    }

    public function motivoBloqueioRota(User $user, string $routeName): ?string
    {
        if ($routeName === '' || $this->shouldIgnoreRoute($routeName)) {
            return null;
        }

        // Durante espelhamento o superadmin responde pelas permissoes
        // do usuario espelhado — hasPermissao ja delega para ele.
        if ($user->isSuperAdmin() && ! app(EspelhamentoContext::class)->ativo()) {
            return null;
        }

        if (in_array($routeName, $this->alwaysAllowedRoutes, true)) {
            return null;
        }

        if (! $user->hasPermissao($routeName)) {
            return 'Sem permissao para acessar esta acao.';
        }

        return null;
    }

    /**
     * Agrupa permissoes por modulo para exibicao na matriz de perfis.
     */
    public function gruposDePermissoesParaTela(Collection $permissoes): array
    {
        return $permissoes
            ->groupBy(fn (Permissao $permissao) => $permissao->grupo_efetivo ?: '_outros')
            ->sortKeys()
            ->all();
    }

    /**
     * Rotas liberadas para qualquer usuario autenticado — espelha a regra
     * do middleware (prefixos ignorados + lista alwaysAllowed). Usado
     * tambem por hasPermissao/Gate para que home e afins nao sumam do menu.
     */
    public function rotaSempreLiberada(string $routeName): bool
    {
        return $this->shouldIgnoreRoute($routeName)
            || in_array($routeName, $this->alwaysAllowedRoutes, true);
    }

    private function shouldIgnoreRoute(string $routeName): bool
    {
        foreach ($this->ignoredRoutePrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
