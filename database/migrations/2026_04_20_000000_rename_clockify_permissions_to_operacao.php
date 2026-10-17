<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renomeia permissões "clockify.*" para "operacao.*".
     * O pivot role_has_permissions referencia permission_id, então os
     * vínculos com os perfis são preservados — só o nome muda.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissaoIds = DB::table('permissions')
            ->where('name', 'like', 'clockify.%')
            ->pluck('id');

        foreach ($permissaoIds as $id) {
            $name = DB::table('permissions')->where('id', $id)->value('name');
            DB::table('permissions')
                ->where('id', $id)
                ->update([
                    'name' => 'operacao.' . substr($name, strlen('clockify.')),
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasColumn('permissions', 'grupo')) {
            DB::table('permissions')
                ->where('grupo', 'clockify')
                ->update(['grupo' => 'operacao']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissaoIds = DB::table('permissions')
            ->where('name', 'like', 'operacao.%')
            ->pluck('id');

        foreach ($permissaoIds as $id) {
            $name = DB::table('permissions')->where('id', $id)->value('name');
            DB::table('permissions')
                ->where('id', $id)
                ->update([
                    'name' => 'clockify.' . substr($name, strlen('operacao.')),
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasColumn('permissions', 'grupo')) {
            DB::table('permissions')
                ->where('grupo', 'operacao')
                ->update(['grupo' => 'clockify']);
        }
    }
};
