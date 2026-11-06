<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaContext = app(EmpresaContext::class);
        $empresaContext->setEmpresaId(null);

        if (! SchemaCache::hasTable('empresas')) {
            return $next($request);
        }

        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        if (! SchemaCache::hasColumn($user->getTable(), 'empresa_id')) {
            return $next($request);
        }

        // Superadmin da plataforma pode alternar o tenant de contexto
        // via sessao (null = visao consolidada de todos os tenants).
        if ($user->isSuperAdmin()) {
            $empresaContext->setEmpresaId($this->resolveEmpresaIdParaSuperAdmin($request));

            return $next($request);
        }

        // Usuarios de tenant sao sempre vinculados a propria empresa —
        // nunca podem trocar de tenant pelo contexto de sessao.
        $empresaId = $user->empresa_id;

        if (! $empresaId) {
            throw new AccessDeniedHttpException('Usuario sem empresa vinculada.');
        }

        if (! $this->empresaExiste($empresaId)) {
            throw new AccessDeniedHttpException('Empresa vinculada nao encontrada.');
        }

        $empresaContext->setEmpresaId((int) $empresaId);

        return $next($request);
    }

    /**
     * Contexto de tenant do superadmin: valor da sessao quando existe
     * (0 = consolidado), caso contrario null.
     */
    private function resolveEmpresaIdParaSuperAdmin(Request $request): ?int
    {
        if (! $request->session()->has('empresa_context_id')) {
            return null;
        }

        $sessionEmpresaId = (int) $request->session()->get('empresa_context_id', 0);

        if ($sessionEmpresaId === 0) {
            return null;
        }

        if ($sessionEmpresaId > 0 && $this->empresaExiste($sessionEmpresaId)) {
            return $sessionEmpresaId;
        }

        return null;
    }

    /**
     * Verifica existencia da empresa respeitando soft delete apenas quando a
     * coluna deleted_at ja existe — evita quebrar toda request autenticada no
     * intervalo entre o deploy do codigo e a execucao da migration.
     */
    private function empresaExiste($empresaId): bool
    {
        return $this->empresaQuery()->whereKey($empresaId)->exists();
    }

    private function empresaQuery()
    {
        $query = Empresa::query();

        if (! SchemaCache::hasColumn('empresas', 'deleted_at')) {
            $query->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }

        return $query;
    }
}
