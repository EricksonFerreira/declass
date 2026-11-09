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
        Schema::table('documento_solicitacoes', function (Blueprint $table) {
            $table->date('prazo')->nullable()->after('descricao');
            $table->text('observacao')->nullable()->after('enviado_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documento_solicitacoes', function (Blueprint $table) {
            $table->dropColumn(['prazo', 'observacao']);
        });
    }
};
