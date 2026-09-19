<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Support\EmpresaContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * O módulo Licitações é opcional por empresa — sem o flag
 * licitacoes_ativo=S as rotas respondem 404 (módulo inexistente).
 */
class ExigirModuloLicitacoes
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaId = app(EmpresaContext::class)->empresaId();
        $empresa = $empresaId ? Empresa::find($empresaId) : null;

        if (! $empresa || $empresa->licitacoes_ativo !== 'S') {
            abort(404);
        }

        return $next($request);
    }
}
