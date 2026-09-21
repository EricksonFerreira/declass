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

        if (auth()->check()) {
            $user = auth()->user();
            if (! SchemaCache::hasColumn($user->getTable(), 'empresa_id')) {
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

            if (! $empresaId) {
                throw new AccessDeniedHttpException('Usuario sem empresa vinculada.');
            }

            if (! $this->empresaExiste($empresaId)) {
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

            if ($sessionEmpresaId > 0 && $this->empresaExiste($sessionEmpresaId)) {
                return $sessionEmpresaId;
            }
        }

        if ($fallbackEmpresaId && $this->empresaExiste($fallbackEmpresaId)) {
            $request->session()->put('empresa_context_id', (int) $fallbackEmpresaId);

            return (int) $fallbackEmpresaId;
        }

        $firstEmpresaId = $this->empresaQuery()->orderBy('id')->value('id');
        if ($firstEmpresaId) {
            $request->session()->put('empresa_context_id', (int) $firstEmpresaId);

            return (int) $firstEmpresaId;
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
