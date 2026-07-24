<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_financeiros', 'recorrente')) {
                $table->boolean('recorrente')->default(false)->after('observacoes');
            }
            if (!Schema::hasColumn('lancamentos_financeiros', 'recorrencia')) {
                $table->string('recorrencia', 16)->nullable()->after('recorrente'); // semanal | mensal | anual
            }
            if (!Schema::hasColumn('lancamentos_financeiros', 'parcelas_totais')) {
                $table->integer('parcelas_totais')->nullable()->after('recorrencia');
            }
            if (!Schema::hasColumn('lancamentos_financeiros', 'parcela_atual')) {
                $table->integer('parcela_atual')->nullable()->after('parcelas_totais');
            }
            if (!Schema::hasColumn('lancamentos_financeiros', 'grupo_recorrencia_id')) {
                $table->string('grupo_recorrencia_id', 64)->nullable()->after('parcela_atual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            $table->dropColumn(['recorrente', 'recorrencia', 'parcelas_totais', 'parcela_atual', 'grupo_recorrencia_id']);
        });
    }
};
