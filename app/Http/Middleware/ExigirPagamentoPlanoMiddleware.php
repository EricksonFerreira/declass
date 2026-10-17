<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigirPagamentoPlanoMiddleware
{
    /**
     * Rotas liberadas antes do pagamento do plano ser confirmado.
     *
     * @var string[]
     */
    private array $allowedRoutePrefixes = [
        'logout',
        'profile.',
        'password.',
        'verification.',
        'onboarding.',
        'assinatura.',
        'notificacoes.',
        'site.apresentacao',
        'site.reserva.',
        'site.multa.',
        'reserva.minhas',
        'reserva.minhas.show',
        'reserva.minhas.multa.show',
        'reserva.show',
        'reserva.cancelar',
        'reserva.efetuarPagamento',
        'reserva.processarPagamento',
        'reserva.historico',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // A cobranca do plano e responsabilidade do administrador da empresa.
        // Membros (perfil usuario etc.) seguem o acesso do proprio perfil.
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        if (! method_exists($user, 'isAdministrador') || ! $user->isAdministrador()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($this->isAllowedRoute($routeName)) {
            return $next($request);
        }

        $empresa = $user->empresa ?? null;
        if (! $empresa) {
            return $next($request);
        }

        $assinatura = $empresa->planoAtivo()->with('plano')->first();
        if ($assinatura && method_exists($assinatura, 'estaAtivo') && $assinatura->estaAtivo()) {
            return $next($request);
        }

        return redirect()->route('assinatura.meu-plano')
            ->with('warning', 'Seu período de teste terminou. Escolha um plano para continuar usando o sistema.');
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
