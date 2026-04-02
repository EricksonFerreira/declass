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
