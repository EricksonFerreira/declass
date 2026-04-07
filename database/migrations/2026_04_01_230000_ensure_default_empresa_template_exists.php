<?php

use App\Models\Empresa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            return;
        }

        $table = 'empresas';

        $empresaId = DB::table($table)
            ->where('slug', Empresa::DEFAULT_TEMPLATE_SLUG)
            ->value('id');

        if (!$empresaId) {
            $empresaId = DB::table($table)->insertGetId([
                'nome' => 'Empresa Padrao',
                'slug' => Empresa::DEFAULT_TEMPLATE_SLUG,
                'ativo' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasColumn($table, 'empresa_id')) {
            DB::table($table)
                ->where('id', $empresaId)
                ->whereNull('empresa_id')
                ->update(['empresa_id' => $empresaId]);
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No-op: mantemos a empresa padrao para nao afetar dados em uso.
    }
};
