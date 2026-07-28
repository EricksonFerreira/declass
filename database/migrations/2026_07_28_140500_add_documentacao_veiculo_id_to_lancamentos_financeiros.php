<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            return;
        }

        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_financeiros', 'documentacao_veiculo_id')) {
                $table->unsignedBigInteger('documentacao_veiculo_id')->nullable()->after('veiculo_id');
                $table->foreign('documentacao_veiculo_id')->references('id')->on('documentacoes_veiculo')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            return;
        }

        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            if (Schema::hasColumn('lancamentos_financeiros', 'documentacao_veiculo_id')) {
                $table->dropForeign(['documentacao_veiculo_id']);
                $table->dropColumn('documentacao_veiculo_id');
            }
        });
    }
};
