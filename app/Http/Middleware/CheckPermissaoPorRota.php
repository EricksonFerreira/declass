<?php

namespace App\Http\Middleware;

use App\Services\PermissaoRotaService;
use App\Support\EspelhamentoContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckPermissaoPorRota
{
    public function __construct(
        private readonly PermissaoRotaService $permissaoRotaService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Durante espelhamento, ate o superadmin passa pela checagem
        // de rota com as permissoes do usuario espelhado.
        if ($user->isSuperAdmin() && ! app(EspelhamentoContext::class)->ativo()) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route?->getName();

        if (! $routeName) {
            return $next($request);
        }

        $blockReason = $this->permissaoRotaService->motivoBloqueioRota($user, $routeName);
        if ($blockReason !== null) {
            if (in_array($routeName, ['home', 'dashboard'], true)) {
                $fallbackRoute = $user->firstPermittedRouteNameForRedirect();
                if ($fallbackRoute && $fallbackRoute !== $routeName) {
                    return redirect()->route($fallbackRoute);
                }
            }

            throw new AccessDeniedHttpException($blockReason);
        }

        return $next($request);
    }
}
