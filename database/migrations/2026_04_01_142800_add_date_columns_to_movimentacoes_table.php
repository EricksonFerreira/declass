<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('movimentacoes')) {
            return;
        }
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dateTime('data_saida')->nullable()->after('data_registro');
            $table->dateTime('data_chegada_prevista')->nullable()->after('data_saida');
            $table->dateTime('data_chegada_real')->nullable()->after('data_chegada_prevista');
    });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('movimentacoes')) { Schema::enableForeignKeyConstraints(); return; }
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropColumn(['data_saida', 'data_chegada_prevista', 'data_chegada_real']);
    });
        Schema::enableForeignKeyConstraints();
    }
};
