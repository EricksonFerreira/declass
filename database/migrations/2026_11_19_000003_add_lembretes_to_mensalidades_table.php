<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Controle de lembretes automaticos de vencimento: cada coluna marca
 * que o aviso correspondente ja foi disparado para a mensalidade
 * (evita notificacao duplicada no comando diario).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->timestamp('lembrete_a_vencer_em')->nullable()->after('observacoes');
            $table->timestamp('lembrete_vencida_em')->nullable()->after('lembrete_a_vencer_em');
        });
    }

    public function down(): void
    {
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropColumn(['lembrete_a_vencer_em', 'lembrete_vencida_em']);
        });
    }
};
