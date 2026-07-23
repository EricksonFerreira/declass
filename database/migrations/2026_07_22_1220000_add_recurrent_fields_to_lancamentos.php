<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            $table->boolean('recorrente')->default(false)->after('observacoes');
            $table->string('recorrencia', 16)->nullable()->after('recorrente'); // semanal | mensal | anual
            $table->integer('parcelas_totais')->nullable()->after('recorrencia');
            $table->integer('parcela_atual')->nullable()->after('parcelas_totais');
            $table->string('grupo_recorrencia_id', 64)->nullable()->after('parcela_atual');
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            $table->dropColumn(['recorrente', 'recorrencia', 'parcelas_totais', 'parcela_atual', 'grupo_recorrencia_id']);
        });
    }
};
