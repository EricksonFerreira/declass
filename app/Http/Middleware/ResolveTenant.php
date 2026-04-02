<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Support\EmpresaContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaContext = app(EmpresaContext::class);
        $empresaContext->setEmpresaId(null);

        if (!Schema::hasTable('empresas')) {
            return $next($request);
        }

        if (auth()->check()) {
            $user = auth()->user();
            if (!Schema::hasColumn($user->getTable(), 'empresa_id')) {
                return $next($request);
            }

            if ($user->isAdministrador()) {
                $empresaId = $this->resolveEmpresaIdForAdministrador($request, $user->empresa_id);
                if ($empresaId === null) {
                    throw new AccessDeniedHttpException('Nenhuma empresa disponivel para administracao.');
                }

                if ((int) $empresaId === 0) {
                    $empresaContext->setEmpresaId(null);
                    return $next($request);
                }

                $empresaContext->setEmpresaId((int) $empresaId);
                return $next($request);
            }

            $empresaId = $user->empresa_id;

            if (!$empresaId) {
                throw new AccessDeniedHttpException('Usuario sem empresa vinculada.');
            }

            if (!Empresa::query()->whereKey($empresaId)->exists()) {
                throw new AccessDeniedHttpException('Empresa vinculada nao encontrada.');
            }

            $empresaContext->setEmpresaId((int) $empresaId);
        }

        return $next($request);
    }

    private function resolveEmpresaIdForAdministrador(Request $request, $fallbackEmpresaId = null): ?int
    {
        if ($request->session()->has('empresa_context_id')) {
            $sessionEmpresaId = (int) $request->session()->get('empresa_context_id', 0);
            if ($sessionEmpresaId === 0) {
                return 0;
            }

            if ($sessionEmpresaId > 0 && Empresa::query()->whereKey($sessionEmpresaId)->exists()) {
                return $sessionEmpresaId;
            }
        }

        if ($fallbackEmpresaId && Empresa::query()->whereKey($fallbackEmpresaId)->exists()) {
            $request->session()->put('empresa_context_id', (int) $fallbackEmpresaId);
            return (int) $fallbackEmpresaId;
        }

        $firstEmpresaId = Empresa::query()->orderBy('id')->value('id');
        if ($firstEmpresaId) {
            $request->session()->put('empresa_context_id', (int) $firstEmpresaId);
            return (int) $firstEmpresaId;
        }

        return null;
    }
}
