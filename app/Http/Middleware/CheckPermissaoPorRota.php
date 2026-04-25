<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckPermissaoPorRota
{
    /**
     * Rotas ignoradas por nao representarem uma tela/acao de negocio.
     *
     * @var string[]
     */
    private array $ignoredPrefixes = [
        'login',
        'logout',
        'register',
        'password.',
        'verification.',
        'sanctum.',
        'ignition.',
        'debugbar.',
        'profile.',
        'onboarding.',
        'assinatura.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        if ($user->isAdministrador()) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route?->getName();

        if (!$routeName || $this->shouldIgnore($routeName)) {
            return $next($request);
        }

        if (in_array($routeName, ['home', 'dashboard', 'home.chart-data'], true)) {
            return $next($request);
        }

        // Verifica restrições de módulo pelo plano contratado da empresa.
        $planoBlock = $this->checkPlanoModuleAccess($user, $routeName);
        if ($planoBlock) {
            throw new AccessDeniedHttpException($planoBlock);
        }

        if (!$user->hasPermissao($routeName)) {
            if (in_array($routeName, ['home', 'dashboard'], true)) {
                $fallbackRoute = $user->firstPermittedRouteNameForRedirect();
                if ($fallbackRoute && $fallbackRoute !== $routeName) {
                    return redirect()->route($fallbackRoute);
                }
            }

            throw new AccessDeniedHttpException('Sem permissao para acessar esta acao.');
        }

        return $next($request);
    }

    /**
     * Bloqueia rotas cujos módulos não estão incluídos no plano contratado da empresa.
     * Retorna a mensagem de erro ou null se o acesso for permitido.
     */
    private function checkPlanoModuleAccess(mixed $user, string $routeName): ?string
    {
        $empresa = $user->empresa ?? null;
        if (!$empresa) {
            return null;
        }

        $plano = $empresa->planoAtivo()?->with('plano')->first()?->plano ?? null;
        if (!$plano) {
            return null;
        }

        // Módulo de multas
        if (!$plano->modulo_multas && $this->routeIsInModule($routeName, ['multa'])) {
            return 'Seu plano não inclui o módulo de multas. Faça upgrade para acessar este recurso.';
        }

        // Módulo NFS-e
        if (!$plano->modulo_nfse && $this->routeIsInModule($routeName, ['nfse'])) {
            return 'Seu plano não inclui a emissão de NFS-e. Faça upgrade para acessar este recurso.';
        }

        // API de integração
        if (!$plano->api_integracao && $this->routeIsInModule($routeName, ['api', 'webhook'])) {
            return 'Seu plano não inclui acesso à API. Faça upgrade para acessar este recurso.';
        }

        // Site de vendas: bloqueia gestão do site no painel
        if (!$plano->site_vendas && $this->routeIsInModule($routeName, ['empresa.site-apresentacao'])) {
            return 'Seu plano não inclui o site de vendas. Faça upgrade para personalizar seu site.';
        }

        return null;
    }

    private function routeIsInModule(string $routeName, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }
        return false;
    }

    private function shouldIgnore(string $routeName): bool
    {
        foreach ($this->ignoredPrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
