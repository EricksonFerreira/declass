<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Alinha os perfis 'usuario' existentes a matriz padrao revisada:
     * membro visualiza o calendario e agenda apenas para si (sem
     * mover/remover alocacoes) e nao exclui folgas/ferias.
     */
    public function up(): void
    {
        $permissaoIds = DB::table('permissions')
            ->whereIn('name', [
                'operacao.calendario.mover',
                'operacao.calendario.destroy',
                'operacao.timeoff.destroy',
            ])
            ->pluck('id');

        if ($permissaoIds->isEmpty()) {
            return;
        }

        $usuarioRoleIds = DB::table('roles')->where('name', 'usuario')->pluck('id');

        if ($usuarioRoleIds->isEmpty()) {
            return;
        }

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $permissaoIds)
            ->whereIn('role_id', $usuarioRoleIds)
            ->delete();
    }

    public function down(): void
    {
        // Desfazer exigiria saber quais perfis tinham cada permissao antes;
        // o admin pode re-vincular manualmente pela edicao de perfil.
    }
};
