<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licitacao_analises', function (Blueprint $table) {
            $table->decimal('preco_max_edital', 14, 2)->nullable()->after('preco_sugerido');
            $table->string('preco_motivo')->nullable()->after('preco_max_edital');
            $table->json('divergencia')->nullable()->after('gaps');
        });
    }

    public function down(): void
    {
        Schema::table('licitacao_analises', function (Blueprint $table) {
            $table->dropColumn(['preco_max_edital', 'preco_motivo', 'divergencia']);
        });
    }
};
