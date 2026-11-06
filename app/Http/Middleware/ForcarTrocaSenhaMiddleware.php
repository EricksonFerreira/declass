<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcarTrocaSenhaMiddleware
{
    private array $allowedRoutePrefixes = [
        'logout',
        'profile.force-password.',
        'password.',
        'verification.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! $user->forcar_troca_senha) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($this->isAllowedRoute($routeName)) {
            return $next($request);
        }

        return redirect()->route('profile.force-password.edit');
    }

    private function isAllowedRoute(?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        foreach ($this->allowedRoutePrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
