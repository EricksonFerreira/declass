<?php

namespace App\Support;

use App\Models\Empresa;

class EmpresaDominio
{
    /**
     * Empresa ativa dona do host atual (dominio ou subdominio customizado).
     * Retorna null quando o host nao pertence a nenhuma empresa — ou quando
     * o banco esta inacessivel, para nao derrubar paginas publicas.
     */
    public static function porHost(?string $host): ?Empresa
    {
        $host = DomainNormalizer::normalize($host);
        if (! $host || ! SchemaCache::hasTable('empresas')) {
            return null;
        }

        try {
            return Empresa::query()
                ->withoutGlobalScopes()
                ->where('ativo', 'S')
                ->whereNotNull('dominio')
                ->get()
                ->first(fn (Empresa $empresa) => DomainNormalizer::normalize($empresa->dominio) === $host);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
