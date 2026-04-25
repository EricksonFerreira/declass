<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Plano Gestao: limite de 3 veiculos (era null = ilimitado).
        DB::table('planos')
            ->where('slug', 'gestao')
            ->update([
                'limite_veiculos' => 3,
                'descricao'       => 'Plano de entrada para locadoras que estao comecando. Gerencie ate 3 veiculos com todas as funcionalidades operacionais essenciais.',
            ]);

        // Forca re-sync de permissoes na proxima requisicao autenticada,
        // removendo pivots de roles que nao deveriam ter acesso a modulos de plataforma.
        DB::table('role_has_permissions')->truncate();
    }

    public function down(): void
    {
        DB::table('planos')
            ->where('slug', 'gestao')
            ->update(['limite_veiculos' => null]);
    }
};
