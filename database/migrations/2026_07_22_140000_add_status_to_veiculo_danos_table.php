<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('veiculo_danos')) {
            Schema::table('veiculo_danos', function (Blueprint $table) {
                if (!Schema::hasColumn('veiculo_danos', 'status')) {
                    $table->string('status', 32)->default('pendente')->after('contrato_id');
                }
            });

            // Atualiza registros existentes que já têm correções
            DB::table('veiculo_danos')
                ->whereNotNull('correcao_data')
                ->update(['status' => 'corrigida']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('veiculo_danos')) {
            Schema::table('veiculo_danos', function (Blueprint $table) {
                if (Schema::hasColumn('veiculo_danos', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
